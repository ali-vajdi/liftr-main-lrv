<?php

namespace App\Jobs;

use App\Models\Organization;
use App\Models\Sms;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Create a new job instance.
     */
    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    public $connection = 'redis';

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'sms';

    public function __construct(
        public int $smsId,
        public int $organizationId,
        public string $patternCode,
        public array $params,
        public string $phoneNumber
    ) {
        // Connection and queue are set via properties above
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService): void
    {
        try {
            $sms = Sms::findOrFail($this->smsId);
            $organization = Organization::findOrFail($this->organizationId);

            // Send SMS via FarazSMS panel with pattern
            $sendResult = $smsService->sendPatternViaPanel(
                $organization,
                $this->patternCode,
                $this->params,
                $this->phoneNumber
            );

            if ($sendResult['success']) {
                $sms->update([
                    'status' => Sms::STATUS_SENT,
                    'sent_at' => now(),
                ]);

                Log::info('SMS sent successfully via queue', [
                    'sms_id' => $this->smsId,
                    'organization_id' => $this->organizationId,
                    'phone_number' => $this->phoneNumber,
                ]);
            } else {
                // If sending fails, refund the balance
                $cost = (float) $sms->cost;
                if ($cost > 0) {
                    $smsService->refundBalance($organization, $cost);
                }

                $sms->update([
                    'status' => Sms::STATUS_FAILED,
                    'error_message' => $sendResult['error'] ?? 'خطا در ارسال پیامک',
                ]);

                Log::error('SMS sending failed via queue', [
                    'sms_id' => $this->smsId,
                    'organization_id' => $this->organizationId,
                    'phone_number' => $this->phoneNumber,
                    'error' => $sendResult['error'] ?? 'Unknown error',
                ]);

                // Throw exception to trigger retry
                throw new \Exception($sendResult['error'] ?? 'Failed to send SMS');
            }
        } catch (\Exception $e) {
            Log::error('SMS job failed', [
                'sms_id' => $this->smsId,
                'organization_id' => $this->organizationId,
                'error' => $e->getMessage(),
            ]);

            // If this is the last attempt, mark SMS as failed
            if ($this->attempts() >= $this->tries) {
                try {
                    $sms = Sms::findOrFail($this->smsId);
                    $organization = Organization::findOrFail($this->organizationId);
                    $cost = (float) $sms->cost;
                    if ($cost > 0) {
                        $smsService->refundBalance($organization, $cost);
                    }

                    $sms->update([
                        'status' => Sms::STATUS_FAILED,
                        'error_message' => $e->getMessage(),
                    ]);
                } catch (\Exception $updateException) {
                    Log::error('Failed to update SMS status after job failure', [
                        'sms_id' => $this->smsId,
                        'error' => $updateException->getMessage(),
                    ]);
                }
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        try {
            $sms = Sms::find($this->smsId);
            $organization = Organization::find($this->organizationId);

            if ($sms && $organization) {
                $smsService = app(SmsService::class);
                $cost = $sms->cost;
                $smsService->refundBalance($organization, $cost);

                $sms->update([
                    'status' => Sms::STATUS_FAILED,
                    'error_message' => $exception->getMessage(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to handle SMS job failure', [
                'sms_id' => $this->smsId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
