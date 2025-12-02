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
     * Send SMS using a pattern code with internal pattern key (for proper lookup)
     *
     * @param Organization $organization
     * @param string $patternKey Internal pattern key (e.g., 'technician_welcome')
     * @param string $patternCode FarazSMS pattern code (e.g., '8lt442ze0rimu9k')
     * @param array $fillData
     * @param string $phoneNumber
     * @param bool $queue Whether to queue the SMS sending
     * @return array
     */
    private function sendPatternSmsWithKey(Organization $organization, string $patternKey, string $patternCode, array $fillData, string $phoneNumber, bool $queue = false): array
    {
        // Get pattern using internal key for proper lookup
        $pattern = SmsPattern::getPattern($patternKey);
        
        // Calculate message for preview/cost calculation
        if ($pattern) {
            $message = $this->fillPattern($pattern['text'], $fillData);
        } else {
            // If no internal pattern, create a preview message from params
            $message = implode(' ', array_values($fillData));
        }

        // Calculate SMS count and cost
        $smsCount = $this->calculateSmsCount($message);
        $cost = $this->calculateCost($organization, $smsCount);

        // Check if cost per message is configured
        $costPerMessage = (float) ($organization->sms_cost_per_message ?? 0);
        if ($costPerMessage > 0) {
            // Only check balance if SMS has a cost
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
        } else {
            // Log warning if cost per message is not configured
            Log::warning('SMS cost per message is not configured for organization', [
                'organization_id' => $organization->id,
                'cost_per_message' => $costPerMessage
            ]);
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

            // Deduct balance only if cost > 0
            if ($cost > 0) {
                $this->deductBalance($organization, $cost);
            }

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
                if ($cost > 0) {
                    $this->refundBalance($organization, $cost);
                }
                
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

        // Check if cost per message is configured
        $costPerMessage = (float) ($organization->sms_cost_per_message ?? 0);
        if ($costPerMessage > 0) {
            // Only check balance if SMS has a cost
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
        } else {
            // Log warning if cost per message is not configured
            Log::warning('SMS cost per message is not configured for organization', [
                'organization_id' => $organization->id,
                'cost_per_message' => $costPerMessage
            ]);
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

            // Deduct balance only if cost > 0
            if ($cost > 0) {
                $this->deductBalance($organization, $cost);
            }

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

        // Check if cost per message is configured
        $costPerMessage = (float) ($organization->sms_cost_per_message ?? 0);
        if ($costPerMessage > 0) {
            // Only check balance if SMS has a cost
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
        } else {
            // Log warning if cost per message is not configured
            Log::warning('SMS cost per message is not configured for organization', [
                'organization_id' => $organization->id,
                'cost_per_message' => $costPerMessage
            ]);
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

            // Deduct balance only if cost > 0
            if ($cost > 0) {
                $this->deductBalance($organization, $cost);
            }

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
        $calculatedCost = $costPerMessage * $smsCount;
        
        // Ensure minimum cost is always sms_cost_per_message (even for messages under 70 characters)
        return max($calculatedCost, $costPerMessage);
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

            $responseData = $response->json();
            
            // Log full response for debugging
            Log::info('FarazSMS API response', [
                'organization_id' => $organization->id,
                'phone_number' => $phoneNumber,
                'pattern_code' => $patternCode,
                'http_status' => $response->status(),
                'response' => $responseData,
                'raw_body' => $response->body()
            ]);

            // Check if response is valid JSON
            if ($responseData === null) {
                Log::error('FarazSMS API invalid JSON response', [
                    'organization_id' => $organization->id,
                    'phone_number' => $phoneNumber,
                    'pattern_code' => $patternCode,
                    'http_status' => $response->status(),
                    'raw_body' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Invalid JSON response from FarazSMS API',
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ];
            }

            if ($response->successful()) {
                // Check FarazSMS response structure: meta.status indicates success/failure
                $metaStatus = $responseData['meta']['status'] ?? false;
                $metaMessage = $responseData['meta']['message'] ?? 'Unknown error';
                $messageCode = $responseData['meta']['message_code'] ?? null;
                
                if ($metaStatus === true) {
                    return [
                        'success' => true,
                        'response' => $responseData,
                        'message_outbox_ids' => $responseData['data']['message_outbox_ids'] ?? []
                    ];
                } else {
                    // Extract error details from meta
                    $errorMessage = $metaMessage;
                    if ($messageCode) {
                        $errorMessage .= ' (Code: ' . $messageCode . ')';
                    }
                    
                    // Include errors from meta if available
                    if (isset($responseData['meta']['errors']) && !empty($responseData['meta']['errors'])) {
                        $errorMessage .= ' - ' . json_encode($responseData['meta']['errors']);
                    }
                    
                    Log::error('FarazSMS API returned error', [
                        'organization_id' => $organization->id,
                        'phone_number' => $phoneNumber,
                        'pattern_code' => $patternCode,
                        'message' => $metaMessage,
                        'message_code' => $messageCode,
                        'errors' => $responseData['meta']['errors'] ?? null,
                        'full_response' => $responseData
                    ]);
                    
                    return [
                        'success' => false,
                        'error' => $errorMessage,
                        'message_code' => $messageCode,
                        'response' => $responseData
                    ];
                }
            } else {
                // HTTP error (non-200 status)
                $errorMessage = 'FarazSMS API request failed';
                if (isset($responseData['meta']['message'])) {
                    $errorMessage = $responseData['meta']['message'];
                }
                
                Log::error('FarazSMS API HTTP error', [
                    'organization_id' => $organization->id,
                    'phone_number' => $phoneNumber,
                    'pattern_code' => $patternCode,
                    'http_status' => $response->status(),
                    'response' => $responseData
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'status_code' => $response->status(),
                    'response' => $responseData
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
        $patternKey = 'technician_welcome';
        $pattern = SmsPattern::getPattern($patternKey);
        
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

        // Use internal pattern key for proper lookup in sendPatternSms
        return $this->sendPatternSmsWithKey(
            $organization,
            $patternKey,
            $patternCode,
            $fillData,
            $phoneNumber,
            $queue
        );
    }

    /**
     * Send organization user welcome SMS with credentials
     *
     * @param Organization $organization
     * @param string $phoneNumber
     * @param string $userName
     * @param string $password
     * @param bool $queue Whether to queue the SMS sending
     * @return array
     */
    public function sendOrganizationUserWelcomeSms(Organization $organization, string $phoneNumber, string $userName, string $password, bool $queue = false): array
    {
        $patternKey = 'organization_user_welcome';
        $pattern = SmsPattern::getPattern($patternKey);
        
        if (!$pattern) {
            return [
                'success' => false,
                'message' => 'الگوی پیامک یافت نشد',
                'error' => 'Organization user welcome pattern not found'
            ];
        }

        $patternCode = $pattern['code']; // Get FarazSMS pattern code
        $fillData = [
            'user_name' => $userName,
            'organization_name' => $organization->name,
            'password' => $password,
        ];

        // Use internal pattern key for proper lookup in sendPatternSms
        return $this->sendPatternSmsWithKey(
            $organization,
            $patternKey,
            $patternCode,
            $fillData,
            $phoneNumber,
            $queue
        );
    }

    /**
     * Send technician welcome SMS without password
     *
     * @param Organization $organization
     * @param string $phoneNumber
     * @param string $technicianName
     * @param bool $queue Whether to queue the SMS sending
     * @return array
     */
    public function sendTechnicianWelcomeNoPasswordSms(Organization $organization, string $phoneNumber, string $technicianName, bool $queue = false): array
    {
        $patternKey = 'technician_welcome_no_password';
        $pattern = SmsPattern::getPattern($patternKey);
        
        if (!$pattern) {
            return [
                'success' => false,
                'message' => 'الگوی پیامک یافت نشد',
                'error' => 'Technician welcome no password pattern not found'
            ];
        }

        $patternCode = $pattern['code'];
        $fillData = [
            'technician_name' => $technicianName,
            'organization_name' => $organization->name,
        ];

        return $this->sendPatternSmsWithKey(
            $organization,
            $patternKey,
            $patternCode,
            $fillData,
            $phoneNumber,
            $queue
        );
    }

    /**
     * Send technician welcome SMS with password
     *
     * @param Organization $organization
     * @param string $phoneNumber
     * @param string $technicianName
     * @param string $password
     * @param bool $queue Whether to queue the SMS sending
     * @return array
     */
    public function sendTechnicianWelcomeWithPasswordSms(Organization $organization, string $phoneNumber, string $technicianName, string $password, bool $queue = false): array
    {
        $patternKey = 'technician_welcome_with_password';
        $pattern = SmsPattern::getPattern($patternKey);
        
        if (!$pattern) {
            return [
                'success' => false,
                'message' => 'الگوی پیامک یافت نشد',
                'error' => 'Technician welcome with password pattern not found'
            ];
        }

        $patternCode = $pattern['code'];
        $fillData = [
            'technician_name' => $technicianName,
            'organization_name' => $organization->name,
            'password' => $password,
        ];

        return $this->sendPatternSmsWithKey(
            $organization,
            $patternKey,
            $patternCode,
            $fillData,
            $phoneNumber,
            $queue
        );
    }

    /**
     * Send technician password changed SMS
     *
     * @param Organization $organization
     * @param string $phoneNumber
     * @param string $technicianName
     * @param string $password
     * @param bool $queue Whether to queue the SMS sending
     * @return array
     */
    public function sendTechnicianPasswordChangedSms(Organization $organization, string $phoneNumber, string $technicianName, string $password, bool $queue = false): array
    {
        $patternKey = 'technician_password_changed';
        $pattern = SmsPattern::getPattern($patternKey);
        
        if (!$pattern) {
            return [
                'success' => false,
                'message' => 'الگوی پیامک یافت نشد',
                'error' => 'Technician password changed pattern not found'
            ];
        }

        $patternCode = $pattern['code'];
        $fillData = [
            'technician_name' => $technicianName,
            'organization_name' => $organization->name,
            'password' => $password,
        ];

        return $this->sendPatternSmsWithKey(
            $organization,
            $patternKey,
            $patternCode,
            $fillData,
            $phoneNumber,
            $queue
        );
    }
}

