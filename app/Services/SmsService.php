<?php

namespace App\Services;

use App\Jobs\SendSmsJob;
use App\Models\Organization;
use App\Models\Sms;
use App\Services\SmsPattern;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class SmsService
{
    /**
     * Characters per SMS (standard GSM 7-bit encoding)
     */
    const CHARS_PER_SMS = 70;

    /**
     * Send SMS using a pattern code (synchronously or queued)
     *
     * @param Organization $organization
     * @param string $patternCode
     * @param array $fillData
     * @param string $phoneNumber
     * @param bool $queue Whether to queue the SMS sending
     * @return array
     */
    public function sendPatternSms(Organization $organization, string $patternCode, array $fillData, string $phoneNumber, bool $queue = false): array
    {
        // Get pattern (optional - for internal pattern validation and message preview)
        $pattern = SmsPattern::getPattern($patternCode);
        
        // Calculate message for preview/cost calculation
        // If internal pattern exists, use it for preview, otherwise estimate
        if ($pattern) {
            $message = $this->fillPattern($pattern['text'], $fillData);
        } else {
            // If no internal pattern, create a preview message from params
            // This is just for cost calculation
            $message = implode(' ', array_values($fillData));
        }

        // Calculate SMS count and cost
        $smsCount = $this->calculateSmsCount($message);
        $cost = $this->calculateCost($organization, $smsCount);

        // Check balance
        $balanceCheck = $this->checkBalance($organization, $cost);
        if (!$balanceCheck['has_enough']) {
            return [
                'success' => false,
                'message' => 'موجودی پیامک کافی نیست',
                'error' => 'Insufficient SMS balance',
                'required_balance' => $cost,
                'current_balance' => $organization->sms_balance
            ];
        }

        DB::beginTransaction();
        try {
            // Create SMS record
            $sms = Sms::create([
                'organization_id' => $organization->id,
                'phone_number' => $phoneNumber,
                'message' => $message,
                'cost' => $cost,
                'status' => Sms::STATUS_PENDING,
                'pattern_code' => $patternCode,
                'sms_count' => $smsCount,
            ]);

            // Deduct balance
            $this->deductBalance($organization, $cost);

            // If queue is enabled, dispatch job instead of sending immediately
            if ($queue) {
                SendSmsJob::dispatch(
                    $sms->id,
                    $organization->id,
                    $patternCode,
                    $fillData,
                    $phoneNumber
                );

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'پیامک در صف ارسال قرار گرفت',
                    'sms' => $sms,
                    'sms_count' => $smsCount,
                    'cost' => $cost,
                    'remaining_balance' => $organization->fresh()->sms_balance,
                    'queued' => true
                ];
            }

            // Send SMS via FarazSMS panel with pattern (synchronous)
            $sendResult = $this->sendPatternViaPanel($organization, $patternCode, $fillData, $phoneNumber);

            if ($sendResult['success']) {
                $sms->update([
                    'status' => Sms::STATUS_SENT,
                    'sent_at' => now(),
                ]);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'پیامک با موفقیت ارسال شد',
                    'sms' => $sms,
                    'sms_count' => $smsCount,
                    'cost' => $cost,
                    'remaining_balance' => $organization->fresh()->sms_balance
                ];
            } else {
                // If sending fails, refund the balance
                $this->refundBalance($organization, $cost);
                
                $sms->update([
                    'status' => Sms::STATUS_FAILED,
                    'error_message' => $sendResult['error'] ?? 'خطا در ارسال پیامک',
                ]);

                DB::commit();

                return [
                    'success' => false,
                    'message' => 'خطا در ارسال پیامک',
                    'error' => $sendResult['error'] ?? 'Unknown error',
                    'sms' => $sms
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS sending failed', [
                'organization_id' => $organization->id,
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'خطا در ارسال پیامک',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS directly (synchronously or queued)
     *
     * @param Organization $organization
     * @param string $phoneNumber
     * @param string $message
     * @param string|null $patternCode
     * @param bool $queue Whether to queue the SMS sending
     * @return array
     */
    public function sendSms(Organization $organization, string $phoneNumber, string $message, ?string $patternCode = null, bool $queue = false): array
    {
        // Calculate SMS count and cost
        $smsCount = $this->calculateSmsCount($message);
        $cost = $this->calculateCost($organization, $smsCount);

        // Check balance
        $balanceCheck = $this->checkBalance($organization, $cost);
        if (!$balanceCheck['has_enough']) {
            return [
                'success' => false,
                'message' => 'موجودی پیامک کافی نیست',
                'error' => 'Insufficient SMS balance',
                'required_balance' => $cost,
                'current_balance' => $organization->sms_balance
            ];
        }

        DB::beginTransaction();
        try {
            // Create SMS record
            $sms = Sms::create([
                'organization_id' => $organization->id,
                'phone_number' => $phoneNumber,
                'message' => $message,
                'cost' => $cost,
                'status' => Sms::STATUS_PENDING,
                'pattern_code' => $patternCode,
                'sms_count' => $smsCount,
            ]);

            // Deduct balance
            $this->deductBalance($organization, $cost);

            // If queue is enabled, dispatch job instead of sending immediately
            if ($queue) {
                // Determine pattern code and params
                $finalPatternCode = $patternCode ?? 'notification';
                $params = ['message' => $message];

                SendSmsJob::dispatch(
                    $sms->id,
                    $organization->id,
                    $finalPatternCode,
                    $params,
                    $phoneNumber
                )->onQueue('SendSms');

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'پیامک در صف ارسال قرار گرفت',
                    'sms' => $sms,
                    'sms_count' => $smsCount,
                    'cost' => $cost,
                    'remaining_balance' => $organization->fresh()->sms_balance,
                    'queued' => true
                ];
            }

            // Send SMS via panel (synchronous)
            // If pattern code is provided, use pattern API, otherwise use notification pattern
            if ($patternCode) {
                // Extract params from message if needed, or use message as a param
                // For now, we'll use the notification pattern
                $params = ['message' => $message];
                $sendResult = $this->sendPatternViaPanel($organization, $patternCode, $params, $phoneNumber);
            } else {
                // Use notification pattern for direct messages
                $params = ['message' => $message];
                $sendResult = $this->sendPatternViaPanel($organization, 'notification', $params, $phoneNumber);
            }

            if ($sendResult['success']) {
                $sms->update([
                    'status' => Sms::STATUS_SENT,
                    'sent_at' => now(),
                ]);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'پیامک با موفقیت ارسال شد',
                    'sms' => $sms,
                    'sms_count' => $smsCount,
                    'cost' => $cost,
                    'remaining_balance' => $organization->fresh()->sms_balance
                ];
            } else {
                // If sending fails, refund the balance
                $this->refundBalance($organization, $cost);
                
                $sms->update([
                    'status' => Sms::STATUS_FAILED,
                    'error_message' => $sendResult['error'] ?? 'خطا در ارسال پیامک',
                ]);

                DB::commit();

                return [
                    'success' => false,
                    'message' => 'خطا در ارسال پیامک',
                    'error' => $sendResult['error'] ?? 'Unknown error',
                    'sms' => $sms
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS sending failed', [
                'organization_id' => $organization->id,
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'خطا در ارسال پیامک',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate number of SMS needed based on message length
     *
     * @param string $message
     * @return int
     */
    public function calculateSmsCount(string $message): int
    {
        $length = mb_strlen($message);
        
        if ($length <= self::CHARS_PER_SMS) {
            return 1;
        }

        // Calculate how many SMS are needed
        return (int) ceil($length / self::CHARS_PER_SMS);
    }

    /**
     * Calculate cost for SMS
     *
     * @param Organization $organization
     * @param int $smsCount
     * @return float
     */
    public function calculateCost(Organization $organization, int $smsCount): float
    {
        $costPerMessage = (float) ($organization->sms_cost_per_message ?? 0);
        return $costPerMessage * $smsCount;
    }

    /**
     * Check if organization has enough balance
     *
     * @param Organization $organization
     * @param float $requiredCost
     * @return array
     */
    public function checkBalance(Organization $organization, float $requiredCost): array
    {
        $currentBalance = (float) ($organization->sms_balance ?? 0);
        
        return [
            'has_enough' => $currentBalance >= $requiredCost,
            'current_balance' => $currentBalance,
            'required_balance' => $requiredCost,
            'remaining_after' => $currentBalance - $requiredCost
        ];
    }

    /**
     * Deduct balance from organization
     *
     * @param Organization $organization
     * @param float $amount
     * @return void
     */
    public function deductBalance(Organization $organization, float $amount): void
    {
        $currentBalance = (float) ($organization->sms_balance ?? 0);
        $newBalance = max(0, $currentBalance - $amount);
        
        $organization->update([
            'sms_balance' => $newBalance
        ]);
    }

    /**
     * Refund balance to organization (in case of failed send)
     *
     * @param Organization $organization
     * @param float $amount
     * @return void
     */
    public function refundBalance(Organization $organization, float $amount): void
    {
        $currentBalance = (float) ($organization->sms_balance ?? 0);
        $newBalance = $currentBalance + $amount;
        
        $organization->update([
            'sms_balance' => $newBalance
        ]);
    }

    /**
     * Fill pattern with data
     *
     * @param string $pattern
     * @param array $data
     * @return string
     */
    public function fillPattern(string $pattern, array $data): string
    {
        $message = $pattern;
        
        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }
        
        return $message;
    }

    /**
     * Send SMS via FarazSMS panel with pattern
     *
     * @param Organization $organization
     * @param string $patternCode
     * @param array $params
     * @param string $phoneNumber
     * @return array
     */
    public function sendPatternViaPanel(Organization $organization, string $patternCode, array $params, string $phoneNumber): array
    {
        try {
            $apiUrl = config('services.sms.api_url') . '/api/send';
            $token = config('services.sms.token');
            $fromNumber = config('services.sms.from_number');

            // Prepare request body for FarazSMS API
            $requestBody = [
                'sending_type' => 'pattern',
                'from_number' => $fromNumber,
                'code' => $patternCode,
                'recipients' => [$phoneNumber],
                'params' => $params,
                'phonebook' => null,
            ];

            // Make API request to FarazSMS panel
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                ])
                ->post($apiUrl, $requestBody);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Check if FarazSMS returned success
                // Adjust this based on FarazSMS API response structure
                if (isset($responseData['status']) && ($responseData['status'] === 'success' || $responseData['status'] === 'ok')) {
                    return [
                        'success' => true,
                        'response' => $responseData
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => $responseData['message'] ?? 'FarazSMS returned error',
                        'response' => $responseData
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'error' => 'FarazSMS API request failed',
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('FarazSMS panel request failed', [
                'organization_id' => $organization->id,
                'phone_number' => $phoneNumber,
                'pattern_code' => $patternCode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send SMS via SMS panel (for direct messages without pattern)
     *
     * @param Organization $organization
     * @param string $phoneNumber
     * @param string $message
     * @return array
     */
    public function sendViaPanel(Organization $organization, string $phoneNumber, string $message): array
    {
        // For direct messages, we can still use pattern API with a generic pattern
        // Or implement a different endpoint if FarazSMS supports direct messages
        // For now, we'll use a notification pattern if available
        $patternCode = 'notification';
        $params = ['message' => $message];

        return $this->sendPatternViaPanel($organization, $patternCode, $params, $phoneNumber);
    }


    /**
     * Get all available SMS patterns
     *
     * @return array
     */
    public function getAvailablePatterns(): array
    {
        return SmsPattern::getAllPatterns();
    }

    /**
     * Get pattern by code
     *
     * @param string $code
     * @return array|null
     */
    public function getPattern(string $code): ?array
    {
        return SmsPattern::getPattern($code);
    }

    /**
     * Send technician welcome SMS with OTP code
     *
     * @param Organization $organization
     * @param string $phoneNumber
     * @param string $otpCode
     * @param bool $queue Whether to queue the SMS sending
     * @return array
     */
    public function sendTechnicianWelcomeSms(Organization $organization, string $phoneNumber, string $otpCode, bool $queue = false): array
    {
        $pattern = SmsPattern::getPattern('technician_welcome');
        
        if (!$pattern) {
            return [
                'success' => false,
                'message' => 'الگوی پیامک یافت نشد',
                'error' => 'Technician welcome pattern not found'
            ];
        }

        $patternCode = $pattern['code']; // Get FarazSMS pattern code
        $fillData = [
            'code' => $otpCode,
        ];

        return $this->sendPatternSms(
            $organization,
            $patternCode,
            $fillData,
            $phoneNumber,
            $queue
        );
    }
}

