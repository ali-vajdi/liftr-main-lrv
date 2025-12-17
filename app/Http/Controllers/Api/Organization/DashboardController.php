<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Services\ZarinpalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Morilog\Jalali\Jalalian;

class DashboardController extends Controller
{
    public function getDashboardData(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $organization = $user->organization;
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        // SMS Statistics
        $smsStats = [
            'balance' => (float) ($organization->sms_balance ?? 0),
            'total' => \App\Models\Sms::where('organization_id', $organization->id)->count(),
            'sent' => \App\Models\Sms::where('organization_id', $organization->id)->where('status', 'sent')->count(),
            'pending' => \App\Models\Sms::where('organization_id', $organization->id)->where('status', 'pending')->count(),
        ];

        // Current Package
        $currentPackage = $organization->activePackage();
        $packageData = null;
        if ($currentPackage) {
            $packageData = [
                'id' => $currentPackage->id,
                'package_name' => $currentPackage->package_name,
                'package_duration_label' => $currentPackage->package_duration_label,
                'remaining_days' => $currentPackage->remaining_days,
                'status_badge_class' => $currentPackage->status_badge_class,
                'expires_at' => $currentPackage->expires_at->toISOString(),
            ];
        }

        // User Statistics
        $userStats = [
            'total' => $organization->users()->count(),
            'active' => $organization->users()->where('status', true)->count(),
        ];

        // Technician Statistics
        $technicianStats = [
            'total' => \App\Models\Technician::where('organization_id', $organization->id)->count(),
            'active' => \App\Models\Technician::where('organization_id', $organization->id)->where('status', true)->count(),
        ];

        // Building Statistics
        $buildingStats = [
            'total' => \App\Models\Building::where('organization_id', $organization->id)->count(),
            'active' => \App\Models\Building::where('organization_id', $organization->id)->where('status', true)->count(),
            'expiring_soon' => \App\Models\Building::where('organization_id', $organization->id)
                ->whereHas('contract', function($q) {
                    $q->where('contract_end_date', '<=', now()->addDays(30))
                      ->where('contract_end_date', '>=', now());
                })
                ->count(),
        ];

        // Build service query with filters
        $serviceQuery = \App\Models\Service::whereHas('building', function($query) use ($organization) {
            $query->where('organization_id', $organization->id);
        });

        // Apply date filters (filter by service created_at date)
        if ($request->has('date_from') && !empty($request->date_from)) {
            try {
                $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->date_from);
                $georgianDate = $jalaliDate->toCarbon()->startOfDay();
                $serviceQuery->where('created_at', '>=', $georgianDate);
            } catch (\Exception $e) {
                // If date conversion fails, skip the filter
            }
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            try {
                $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->date_to);
                $georgianDate = $jalaliDate->toCarbon()->endOfDay();
                $serviceQuery->where('created_at', '<=', $georgianDate);
            } catch (\Exception $e) {
                // If date conversion fails, skip the filter
            }
        }

        // Apply service status filter
        if ($request->has('service_status') && !empty($request->service_status)) {
            $serviceQuery->where('status', $request->service_status);
        }

        // Apply building filter
        if ($request->has('building_id') && !empty($request->building_id)) {
            $serviceQuery->where('building_id', $request->building_id);
        }

        // Apply technician filter
        if ($request->has('technician_id') && !empty($request->technician_id)) {
            $serviceQuery->where('technician_id', $request->technician_id);
        }

        // Service Statistics
        $serviceStats = [
            'total' => (clone $serviceQuery)->count(),
            'pending' => (clone $serviceQuery)->where('status', 'pending')->count(),
            'assigned' => (clone $serviceQuery)->where('status', 'assigned')->count(),
            'completed' => (clone $serviceQuery)->where('status', 'completed')->count(),
            'expired' => (clone $serviceQuery)->where('status', 'expired')->count(),
        ];

        // Current Month Service Statistics (only if no date filters applied)
        $now = Jalalian::now();
        $currentYear = $now->getYear();
        $currentMonth = $now->getMonth();
        
        $currentMonthServiceQuery = \App\Models\Service::whereHas('building', function($query) use ($organization) {
            $query->where('organization_id', $organization->id);
        })->where('service_year', $currentYear)
          ->where('service_month', $currentMonth);
        
        // Apply same filters to current month stats if filters are applied
        if ($request->has('service_status') && !empty($request->service_status)) {
            $currentMonthServiceQuery->where('status', $request->service_status);
        }
        if ($request->has('building_id') && !empty($request->building_id)) {
            $currentMonthServiceQuery->where('building_id', $request->building_id);
        }
        if ($request->has('technician_id') && !empty($request->technician_id)) {
            $currentMonthServiceQuery->where('technician_id', $request->technician_id);
        }
        
        $currentMonthServiceStats = [
            'total' => (clone $currentMonthServiceQuery)->count(),
            'pending' => (clone $currentMonthServiceQuery)->where('status', 'pending')->count(),
            'assigned' => (clone $currentMonthServiceQuery)->where('status', 'assigned')->count(),
            'completed' => (clone $currentMonthServiceQuery)->where('status', 'completed')->count(),
            'expired' => (clone $currentMonthServiceQuery)->where('status', 'expired')->count(),
        ];

        return response()->json([
            'data' => [
                'organization' => [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'address' => $organization->address,
                    'logo' => $organization->logo,
                    'status' => $organization->status,
                    'sms_balance' => (float) ($organization->sms_balance ?? 0),
                ],
                'statistics' => [
                    'sms' => $smsStats,
                    'current_package' => $packageData,
                    'users' => $userStats,
                    'technicians' => $technicianStats,
                    'buildings' => $buildingStats,
                    'services' => $serviceStats,
                    'current_month_services' => $currentMonthServiceStats,
                ]
            ]
        ]);
    }

    public function increaseSmsBalance(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $organization = $user->organization;
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        // Validate request
        $request->validate([
            'amount' => 'required|numeric|min:50000',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'description' => 'nullable|string|max:500',
        ], [
            'amount.required' => 'مبلغ الزامی است',
            'amount.numeric' => 'مبلغ باید عدد باشد',
            'amount.min' => 'حداقل مبلغ افزایش موجودی 50,000 تومان است',
            'payment_method_id.required' => 'روش پرداخت الزامی است',
            'payment_method_id.exists' => 'روش پرداخت معتبر نیست',
        ]);

        $amount = (int) round($request->amount, 0);
        $paymentMethodId = $request->payment_method_id;
        $description = $request->description ?? 'افزایش موجودی پیامک';

        // Get payment method
        $paymentMethod = PaymentMethod::findOrFail($paymentMethodId);

        // Check if payment method is system (not allowed for organizations)
        if ($paymentMethod->is_system) {
            return response()->json([
                'message' => 'این روش پرداخت برای سازمان‌ها در دسترس نیست'
            ], 422);
        }

        // Check if it's a Zarinpal payment method
        $isZarinpal = in_array($paymentMethod->code, ['zarinpal', 'zarinpal_sandbox']);
        
        if ($isZarinpal) {
            // Handle Zarinpal payment gateway
            $config = $paymentMethod->config ?? [];
            $merchantId = $config['merchant_id'] ?? '12de1ed3-0c38-4d52-add9-7e631e430214';
            $baseUrl = $config['base_url'] ?? ($paymentMethod->code === 'zarinpal_sandbox' ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com');
            
            $zarinpalService = new ZarinpalService($merchantId, $baseUrl, $paymentMethod->code === 'zarinpal_sandbox');
            
            // Create callback URL
            $callbackUrl = route('organization.sms.payment.callback');
            
            // Request payment from Zarinpal
            $result = $zarinpalService->requestPayment(
                $amount,
                $callbackUrl,
                $description,
                null
            );
            
            if (!$result['success']) {
                return response()->json([
                    'message' => $result['message'] ?? 'خطا در اتصال به درگاه پرداخت'
                ], 500);
            }
            
            // Create pending transaction
            DB::beginTransaction();
            try {
                $transaction = Transaction::create([
                    'transactionable_type' => Organization::class,
                    'transactionable_id' => $organization->id,
                    'payment_method_id' => $paymentMethod->id,
                    'amount' => $amount,
                    'type' => Transaction::TYPE_EXPENSE,
                    'status' => Transaction::STATUS_PENDING,
                    'reference_number' => $result['authority'],
                    'description' => $description,
                    'transaction_date' => Carbon::now(),
                    'organization_id' => $organization->id,
                    'moderator_id' => null,
                ]);
                
                DB::commit();
                
                return response()->json([
                    'message' => 'در حال انتقال به درگاه پرداخت...',
                    'data' => [
                        'redirect_url' => $result['redirect_url'],
                        'authority' => $result['authority'],
                        'transaction_id' => $transaction->id,
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'خطا در ثبت تراکنش: ' . $e->getMessage()
                ], 500);
            }
        } else {
            // Direct payment (should not happen for organizations, but handle it)
            return response()->json([
                'message' => 'روش پرداخت معتبر نیست'
            ], 422);
        }
    }

    /**
     * Handle SMS balance payment callback
     */
    public function smsPaymentCallback(Request $request)
    {
        $authority = $request->query('Authority');
        $status = $request->query('Status');

        if (!$authority) {
            return view('organization.payment.result', [
                'success' => false,
                'message' => 'کد مرجع پرداخت یافت نشد',
                'trackingCode' => null,
                'redirectTo' => 'dashboard'
            ]);
        }

        if ($status !== 'OK') {
            return view('organization.payment.result', [
                'success' => false,
                'message' => 'پرداخت ناموفق بود یا توسط شما لغو شد',
                'trackingCode' => $authority,
                'redirectTo' => 'dashboard'
            ]);
        }

        // Find the pending transaction by authority
        $transaction = Transaction::where('reference_number', $authority)
            ->where('status', Transaction::STATUS_PENDING)
            ->where('transactionable_type', Organization::class)
            ->first();

        if (!$transaction) {
            return view('organization.payment.result', [
                'success' => false,
                'message' => 'تراکنش یافت نشد',
                'trackingCode' => $authority,
                'redirectTo' => 'dashboard'
            ]);
        }

        $organization = Organization::find($transaction->transactionable_id);
        if (!$organization) {
            return view('organization.payment.result', [
                'success' => false,
                'message' => 'سازمان یافت نشد',
                'trackingCode' => $authority,
                'redirectTo' => 'dashboard'
            ]);
        }

        $paymentMethod = $transaction->paymentMethod;
        if (!in_array($paymentMethod->code, ['zarinpal', 'zarinpal_sandbox'])) {
            return view('organization.payment.result', [
                'success' => false,
                'message' => 'روش پرداخت معتبر نیست',
                'trackingCode' => $authority,
                'redirectTo' => 'dashboard'
            ]);
        }

        // Verify payment with Zarinpal
        $config = $paymentMethod->config ?? [];
        $merchantId = $config['merchant_id'] ?? '12de1ed3-0c38-4d52-add9-7e631e430214';
        $baseUrl = $config['base_url'] ?? ($paymentMethod->code === 'zarinpal_sandbox' ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com');
        
        $zarinpalService = new ZarinpalService($merchantId, $baseUrl, $paymentMethod->code === 'zarinpal_sandbox');
        $verifyResult = $zarinpalService->verifyPayment((int) $transaction->amount, $authority);

        if (!$verifyResult['success'] || !$verifyResult['verified']) {
            // Update transaction status to failed
            $transaction->update([
                'status' => Transaction::STATUS_FAILED,
                'description' => $transaction->description . ' - ' . ($verifyResult['message'] ?? 'تایید نشد'),
            ]);

            return view('organization.payment.result', [
                'success' => false,
                'message' => $verifyResult['message'] ?? 'پرداخت تایید نشد',
                'trackingCode' => $authority,
                'redirectTo' => 'dashboard'
            ]);
        }

        // Payment verified successfully
        DB::beginTransaction();
        try {
            // Update transaction
            $transaction->update([
                'status' => Transaction::STATUS_COMPLETED,
                'reference_number' => $verifyResult['ref_id'] ?? $authority,
            ]);

            // Update organization SMS balance
            $currentBalance = (float) ($organization->sms_balance ?? 0);
            $newBalance = $currentBalance + $transaction->amount;
            $organization->update([
                'sms_balance' => $newBalance,
            ]);

            DB::commit();

            $trackingCode = $verifyResult['ref_id'] ?? $authority;
            
            return view('organization.payment.result', [
                'success' => true,
                'message' => 'موجودی پیامک با موفقیت افزایش یافت.',
                'trackingCode' => $trackingCode,
                'redirectTo' => 'dashboard'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return view('organization.payment.result', [
                'success' => false,
                'message' => 'خطا در افزایش موجودی: ' . $e->getMessage(),
                'trackingCode' => $authority,
                'redirectTo' => 'dashboard'
            ]);
        }
    }
}

