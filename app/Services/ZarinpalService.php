<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZarinpalService
{
    private string $merchantId;
    private string $baseUrl;
    private bool $isSandbox;

    public function __construct(string $merchantId, string $baseUrl, bool $isSandbox = false)
    {
        $this->merchantId = $merchantId;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->isSandbox = $isSandbox;
    }

    /**
     * Request payment from Zarinpal
     *
     * @param int $amount Amount in Toman
     * @param string $callbackUrl Callback URL after payment
     * @param string $description Payment description
     * @param array|null $metadata Additional metadata (mobile, email, etc.)
     * @return array
     */
    public function requestPayment(int $amount, string $callbackUrl, string $description, ?array $metadata = null): array
    {
        $url = $this->baseUrl . '/pg/v4/payment/request.json';

        $data = [
            'merchant_id' => $this->merchantId,
            'amount' => $amount,
            'callback_url' => $callbackUrl,
            'description' => $description,
            'currency' => 'IRT',
        ];

        if ($metadata) {
            $data['metadata'] = $metadata;
        }

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post($url, $data);

            $result = $response->json();

            if ($response->successful() && isset($result['data']) && $result['data']['code'] == 100) {
                return [
                    'success' => true,
                    'authority' => $result['data']['authority'],
                    'fee' => $result['data']['fee'] ?? 0,
                    'fee_type' => $result['data']['fee_type'] ?? 'Merchant',
                    'redirect_url' => $this->baseUrl . '/pg/StartPay/' . $result['data']['authority'],
                ];
            }

            return [
                'success' => false,
                'message' => $result['errors']['message'] ?? ($result['data']['message'] ?? 'خطا در درخواست پرداخت'),
                'code' => $result['data']['code'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Zarinpal payment request error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'خطا در ارتباط با درگاه پرداخت',
            ];
        }
    }

    /**
     * Verify payment with Zarinpal
     *
     * @param int $amount Amount in Toman
     * @param string $authority Authority code from callback
     * @return array
     */
    public function verifyPayment(int $amount, string $authority): array
    {
        $url = $this->baseUrl . '/pg/v4/payment/verify.json';

        $data = [
            'merchant_id' => $this->merchantId,
            'amount' => $amount,
            'authority' => $authority,
        ];

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post($url, $data);

            $result = $response->json();

            // Code 100: Success (first verification)
            // Code 101: Already verified (payment was successful and verified before)
            if ($response->successful() && isset($result['data'])) {
                $code = $result['data']['code'] ?? null;
                
                if ($code == 100 || $code == 101) {
                    return [
                        'success' => true,
                        'verified' => true,
                        'ref_id' => $result['data']['ref_id'] ?? null,
                        'card_hash' => $result['data']['card_hash'] ?? null,
                        'card_pan' => $result['data']['card_pan'] ?? null,
                        'fee' => $result['data']['fee'] ?? 0,
                        'fee_type' => $result['data']['fee_type'] ?? 'Merchant',
                        'code' => $code,
                        'message' => $code == 100 ? 'پرداخت با موفقیت انجام شد' : 'این تراکنش قبلا تایید شده است',
                    ];
                }
            }

            return [
                'success' => false,
                'verified' => false,
                'message' => $result['errors']['message'] ?? ($result['data']['message'] ?? 'خطا در تایید پرداخت'),
                'code' => $result['data']['code'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Zarinpal payment verify error: ' . $e->getMessage());
            return [
                'success' => false,
                'verified' => false,
                'message' => 'خطا در ارتباط با درگاه پرداخت',
            ];
        }
    }

    /**
     * Get payment gateway URL for redirect
     *
     * @param string $authority Authority code
     * @return string
     */
    public function getPaymentUrl(string $authority): string
    {
        return $this->baseUrl . '/pg/StartPay/' . $authority;
    }
}

