<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationPackage;
use App\Models\Package;
use App\Models\PackagePayment;
use App\Models\PackagePeriod;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Services\ZarinpalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:organization_api')->except(['showPaymentPage', 'paymentCallback', 'paymentVerify']);
    }

    /**
     * Show payment page (web view)
     */
    public function showPaymentPage()
    {
        return view('organization.payment.index');
    }

    /**
     * Get payment information for current organization
     */
    public function getPaymentInfo()
    {
        $user = Auth::user();
        if (!$user || !$user->organization) {
            return response()->json([
                'message' => 'سازمان یافت نشد'
            ], 404);
        }

        $organization = $user->organization;
        $activePackages = $organization->activePackages();
        $paymentInfo = [];
        $publicPackages = [];
        $allPackagesExpired = false;

        // If no active packages, get public packages
        if ($activePackages->isEmpty()) {
            $publicPackages = Package::where('is_public', true)
                ->orderBy('duration_days', 'asc')
                ->orderBy('price', 'asc')
                ->get()
                ->map(function ($package) {
                    return [
                        'id' => $package->id,
                        'name' => $package->name,
                        'duration_days' => $package->duration_days,
                        'duration_label' => $package->duration_label,
                        'price' => $package->price,
                        'formatted_price' => $package->formatted_price,
                    ];
                });
        } else {
            // Filter out expired packages - only check non-expired packages
            $validPackages = $activePackages->filter(function ($package) {
                // Check if package is expired (remaining_days <= 0)
                return $package->remaining_days > 0 && !$package->is_expired;
            });

            // If all packages are expired, set flag and get public packages
            if ($validPackages->isEmpty()) {
                $allPackagesExpired = true;
                $publicPackages = Package::where('is_public', true)
                    ->orderBy('duration_days', 'asc')
                    ->orderBy('price', 'asc')
                    ->get()
                    ->map(function ($package) {
                        return [
                            'id' => $package->id,
                            'name' => $package->name,
                            'duration_days' => $package->duration_days,
                            'duration_label' => $package->duration_label,
                            'price' => $package->price,
                            'formatted_price' => $package->formatted_price,
                        ];
                    });
            } else {
                // Check existing valid (non-expired) packages for payment requirements
                foreach ($validPackages as $package) {
                    // Load periods
                    $package->load('periods');
                    
                    // If periods are not used, treat as full payment
                    if (!$package->use_periods) {
                        if ($package->payment_status !== OrganizationPackage::PAYMENT_STATUS_FULLY_PAID) {
                            $paymentInfo[] = [
                                'package_id' => $package->id,
                                'package_name' => $package->package_name,
                                'package_duration_days' => $package->package_duration_days,
                                'total_amount' => $package->package_price,
                                'paid_amount' => $package->total_paid_amount,
                                'remaining_amount' => $package->remaining_amount,
                                'payment_type' => 'full',
                                'use_periods' => false,
                                'current_period' => null,
                                'period_amount' => null,
                                'periods' => [],
                            ];
                        }
                    } else {
                        // For packages with periods - use PackagePeriod records
                        $currentPeriod = $package->getCurrentPeriod();
                        $currentPeriodRecord = $package->periods()->where('period_number', $currentPeriod)->first();
                        
                        if ($currentPeriodRecord && !$currentPeriodRecord->is_paid) {
                            $allPeriods = $package->periods->map(function ($period) {
                                return [
                                    'id' => $period->id,
                                    'period_number' => $period->period_number,
                                    'amount' => $period->amount,
                                    'formatted_amount' => $period->formatted_amount,
                                    'days' => $period->days,
                                    'start_date' => $period->start_date->format('Y-m-d H:i:s'),
                                    'end_date' => $period->end_date->format('Y-m-d H:i:s'),
                                    'is_paid' => $period->is_paid,
                                    'paid_at' => $period->paid_at ? $period->paid_at->format('Y-m-d H:i:s') : null,
                                    'is_current' => $period->is_current,
                                    'is_expired' => $period->is_expired,
                                ];
                            })->toArray();
                            
                            $paymentInfo[] = [
                                'package_id' => $package->id,
                                'package_name' => $package->package_name,
                                'package_duration_days' => $package->package_duration_days,
                                'total_amount' => $package->package_price,
                                'paid_amount' => $package->total_paid_amount,
                                'remaining_amount' => $package->remaining_amount,
                                'payment_type' => 'period',
                                'use_periods' => true,
                                'current_period' => $currentPeriod,
                                'period_amount' => $currentPeriodRecord->amount,
                                'current_period_id' => $currentPeriodRecord->id,
                                'total_periods' => $package->getTotalPeriods(),
                                'periods' => $allPeriods,
                            ];
                        }
                    }
                }
            }
        }

        return response()->json([
            'data' => $paymentInfo,
            'public_packages' => $publicPackages,
            'has_active_packages' => $activePackages->isNotEmpty(),
            'all_packages_expired' => $allPackagesExpired,
            'organization' => [
                'id' => $organization->id,
                'name' => $organization->name,
            ]
        ]);
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:organization_packages,id',
            'amount' => 'required|numeric|min:0',
            'payment_type' => 'required|in:period,full',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'period' => 'nullable|integer|min:0',
        ], [
            'package_id.required' => 'شناسه اشتراک الزامی است',
            'package_id.exists' => 'اشتراک یافت نشد',
            'amount.required' => 'مبلغ الزامی است',
            'amount.numeric' => 'مبلغ باید عدد باشد',
            'amount.min' => 'مبلغ نمی‌تواند منفی باشد',
            'payment_type.required' => 'نوع پرداخت الزامی است',
            'payment_type.in' => 'نوع پرداخت معتبر نیست',
            'payment_method_id.required' => 'روش پرداخت الزامی است',
            'payment_method_id.exists' => 'روش پرداخت معتبر نیست',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        if (!$user || !$user->organization) {
            return response()->json([
                'message' => 'سازمان یافت نشد'
            ], 404);
        }

        $organization = $user->organization;
        $package = OrganizationPackage::where('organization_id', $organization->id)
            ->where('id', $request->package_id)
            ->first();

        if (!$package) {
            return response()->json([
                'message' => 'اشتراک یافت نشد'
            ], 404);
        }

        if (!$package->is_active) {
            return response()->json([
                'message' => 'اشتراک غیرفعال است'
            ], 422);
        }

        $amount = (int) round($request->amount, 0);
        $paymentType = $request->payment_type;
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

        // Check if payment method is system (not allowed for organizations)
        if ($paymentMethod->is_system) {
            return response()->json([
                'message' => 'این روش پرداخت برای سازمان‌ها در دسترس نیست'
            ], 422);
        }

        // Validate amount based on payment type
        if ($paymentType === 'period') {
            $periodNumber = $request->period ?? $package->getCurrentPeriod();
            $periodRecord = $package->periods()->where('period_number', $periodNumber)->first();
            
            if (!$periodRecord) {
                return response()->json([
                    'message' => 'دوره یافت نشد'
                ], 404);
            }
            
            if ($periodRecord->is_paid) {
                return response()->json([
                    'message' => 'این دوره قبلا پرداخت شده است'
                ], 422);
            }
            
            $expectedAmount = (int) round($periodRecord->amount, 0);
            
            if ($amount !== $expectedAmount) {
                return response()->json([
                    'message' => "مبلغ باید {$expectedAmount} تومان باشد"
                ], 422);
            }
        } else {
            // Pay full amount
            if (!$package->use_periods) {
                // For packages without periods, require full payment
                $expectedAmount = (int) round($package->remaining_amount, 0);
                if ($amount !== $expectedAmount) {
                    return response()->json([
                        'message' => "برای اشتراک‌های بدون دوره، باید کل مبلغ باقی‌مانده ({$expectedAmount} تومان) پرداخت شود"
                    ], 422);
                }
            } else {
                // For packages with periods but paying full amount, allow partial payments
                if ($amount > (int) round($package->remaining_amount, 0)) {
                    return response()->json([
                        'message' => 'مبلغ پرداختی نمی‌تواند بیشتر از مبلغ باقی‌مانده باشد'
                    ], 422);
                }
            }
        }

        // Check if it's a Zarinpal payment method
        $isZarinpal = in_array($paymentMethod->code, ['zarinpal', 'zarinpal_sandbox']);
        
        if ($isZarinpal) {
            // Handle Zarinpal payment gateway
            $config = $paymentMethod->config ?? [];
            $merchantId = $config['merchant_id'] ?? '12de1ed3-0c38-4d52-add9-7e631e430214';
            $baseUrl = $config['base_url'] ?? ($paymentMethod->code === 'zarinpal_sandbox' ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com');
            
            $zarinpalService = new ZarinpalService($merchantId, $baseUrl, $paymentMethod->code === 'zarinpal_sandbox');
            
            // Create callback URL - Zarinpal will add Authority and Status as query params
            $callbackUrl = route('organization.payment.callback', ['packageId' => $package->id]);
            
            // Prepare description
            if ($paymentType === 'period') {
                $periodNumber = $request->period ?? $package->getCurrentPeriod();
                $description = "پرداخت دوره {$periodNumber} اشتراک: {$package->package_name}";
            } else {
                $description = "پرداخت اشتراک: {$package->package_name}";
            }
            
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
                    'transactionable_type' => OrganizationPackage::class,
                    'transactionable_id' => $package->id,
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
            // System payment (should not happen, but handle it)
            return response()->json([
                'message' => 'روش پرداخت معتبر نیست'
            ], 422);
        }
    }

    /**
     * Activate a public package for organization
     */
    public function activatePackage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
        ], [
            'package_id.required' => 'شناسه اشتراک الزامی است',
            'package_id.exists' => 'اشتراک یافت نشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        if (!$user || !$user->organization) {
            return response()->json([
                'message' => 'سازمان یافت نشد'
            ], 404);
        }

        $organization = $user->organization;
        $package = Package::findOrFail($request->package_id);

        // Check if package is public
        if (!$package->is_public) {
            return response()->json([
                'message' => 'این اشتراک عمومی نیست'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create organization package
            $organizationPackage = OrganizationPackage::create([
                'organization_id' => $organization->id,
                'package_id' => $package->id,
                'package_name' => $package->name,
                'package_duration_days' => $package->duration_days,
                'package_duration_label' => $package->duration_label,
                'package_price' => round((float)$package->price, 0), // Round price to no decimals
                'use_periods' => $package->use_periods ?? false,
                'period_days' => $package->period_days,
                'payment_status' => OrganizationPackage::PAYMENT_STATUS_UNPAID,
                'started_at' => Carbon::now(),
                'is_active' => true,
                'moderator_id' => null, // System activation
            ]);

            // Generate periods (will create single period if use_periods is false)
            $organizationPackage->generatePeriods();

            DB::commit();

            return response()->json([
                'message' => 'اشتراک با موفقیت فعال شد. لطفا پرداخت را انجام دهید.',
                'data' => [
                    'organization_package' => $organizationPackage,
                    'package' => $package,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'خطا در فعال‌سازی اشتراک: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Zarinpal payment callback
     */
    public function paymentCallback(Request $request, $packageId)
    {
        $authority = $request->query('Authority');
        $status = $request->query('Status');

        if (!$authority) {
            return view('organization.payment.result', [
                'success' => false,
                'message' => 'کد مرجع پرداخت یافت نشد',
                'trackingCode' => null,
                'redirectTo' => 'payment'
            ]);
        }

        if ($status !== 'OK') {
            return view('organization.payment.result', [
                'success' => false,
                'message' => 'پرداخت ناموفق بود یا توسط شما لغو شد',
                'trackingCode' => $authority,
                'redirectTo' => 'payment'
            ]);
        }

        // Find the pending transaction by authority
        $transaction = Transaction::where('reference_number', $authority)
            ->where('status', Transaction::STATUS_PENDING)
            ->first();

        if (!$transaction) {
            return view('organization.payment.result', [
                'success' => false,
                'message' => 'تراکنش یافت نشد',
                'trackingCode' => $authority,
                'redirectTo' => 'payment'
            ]);
        }

        // Get package from route parameter and verify it matches transaction
        $package = OrganizationPackage::find($packageId);
        if (!$package || $package->organization_id !== $transaction->organization_id) {
            // Fallback: try to get package from transaction
            if ($transaction->transactionable_type === OrganizationPackage::class) {
                $package = OrganizationPackage::find($transaction->transactionable_id);
            }
            if (!$package || $package->organization_id !== $transaction->organization_id) {
                return view('organization.payment.result', [
                    'success' => false,
                    'message' => 'اشتراک یافت نشد',
                    'trackingCode' => $authority,
                    'redirectTo' => 'payment'
                ]);
            }
        }
        
        // Determine payment type based on package and amount
        // If amount matches a specific period amount, it's a period payment
        $paymentType = 'full';
        $period = null;
        $currentPeriod = $package->getCurrentPeriod();
        $currentPeriodRecord = $package->periods()->where('period_number', $currentPeriod)->first();
        
        if ($currentPeriodRecord && abs($transaction->amount - $currentPeriodRecord->amount) < 0.01 && !$currentPeriodRecord->is_paid) {
            $paymentType = 'period';
            $period = $currentPeriod;
        }

        $paymentMethod = $transaction->paymentMethod;
        if (!in_array($paymentMethod->code, ['zarinpal', 'zarinpal_sandbox'])) {
            return view('organization.payment.result', [
                'success' => false,
                'message' => 'روش پرداخت معتبر نیست',
                'trackingCode' => $authority,
                'redirectTo' => 'payment'
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
                'redirectTo' => 'payment'
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

            // Create package payment
            $payment = PackagePayment::create([
                'organization_package_id' => $package->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $transaction->amount,
                'payment_date' => Carbon::now(),
                'notes' => $paymentType === 'period' 
                    ? "پرداخت دوره {$period} ({$package->package_name})" 
                    : "پرداخت کامل اشتراک ({$package->package_name})",
                'moderator_id' => null,
            ]);

            // Update transaction to point to payment
            $transaction->update([
                'transactionable_type' => PackagePayment::class,
                'transactionable_id' => $payment->id,
            ]);

            // Handle period payment
            if ($paymentType === 'period' && $period) {
                $periodRecord = $package->periods()->where('period_number', $period)->first();
                if ($periodRecord && !$periodRecord->is_paid) {
                    $periodRecord->update([
                        'is_paid' => true,
                        'paid_at' => Carbon::now(),
                    ]);
                }
            } else {
                // Full payment - update payment status
                $package->updatePaymentStatus();
                if ($package->payment_status === OrganizationPackage::PAYMENT_STATUS_FULLY_PAID) {
                    $package->periods()->update([
                        'is_paid' => true,
                        'paid_at' => Carbon::now(),
                    ]);
                }
            }

            DB::commit();

            $trackingCode = $verifyResult['ref_id'] ?? $authority;
            
            return view('organization.payment.result', [
                'success' => true,
                'message' => 'پرداخت شما با موفقیت انجام و تایید شد.',
                'trackingCode' => $trackingCode,
                'redirectTo' => 'payment'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return view('organization.payment.result', [
                'success' => false,
                'message' => 'خطا در ثبت پرداخت: ' . $e->getMessage(),
                'trackingCode' => $authority,
                'redirectTo' => 'payment'
            ]);
        }
    }

    /**
     * Verify payment (API endpoint)
     */
    public function paymentVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'authority' => 'required|string',
        ], [
            'authority.required' => 'کد مرجع الزامی است',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $authority = $request->authority;
        $transaction = Transaction::where('reference_number', $authority)
            ->where('status', Transaction::STATUS_PENDING)
            ->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'تراکنش یافت نشد'
            ], 404);
        }

        $paymentMethod = $transaction->paymentMethod;
        if (!in_array($paymentMethod->code, ['zarinpal', 'zarinpal_sandbox'])) {
            return response()->json([
                'message' => 'روش پرداخت معتبر نیست'
            ], 422);
        }

        $config = $paymentMethod->config ?? [];
        $merchantId = $config['merchant_id'] ?? '12de1ed3-0c38-4d52-add9-7e631e430214';
        $baseUrl = $config['base_url'] ?? ($paymentMethod->code === 'zarinpal_sandbox' ? 'https://sandbox.zarinpal.com' : 'https://payment.zarinpal.com');
        
        $zarinpalService = new ZarinpalService($merchantId, $baseUrl, $paymentMethod->code === 'zarinpal_sandbox');
        $verifyResult = $zarinpalService->verifyPayment((int) $transaction->amount, $authority);

        if (!$verifyResult['success'] || !$verifyResult['verified']) {
            $transaction->update([
                'status' => Transaction::STATUS_FAILED,
                'description' => $transaction->description . ' - ' . ($verifyResult['message'] ?? 'تایید نشد'),
            ]);

            return response()->json([
                'message' => $verifyResult['message'] ?? 'پرداخت تایید نشد',
                'verified' => false,
            ], 400);
        }

        // Payment verified - complete the transaction
        $package = OrganizationPackage::find($transaction->transactionable_id);
        if (!$package) {
            return response()->json([
                'message' => 'اشتراک یافت نشد'
            ], 404);
        }

        DB::beginTransaction();
        try {
            $transaction->update([
                'status' => Transaction::STATUS_COMPLETED,
                'reference_number' => $verifyResult['ref_id'] ?? $authority,
            ]);

            $payment = PackagePayment::create([
                'organization_package_id' => $package->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $transaction->amount,
                'payment_date' => Carbon::now(),
                'notes' => "پرداخت اشتراک: {$package->package_name}",
                'moderator_id' => null,
            ]);

            $transaction->update([
                'transactionable_type' => PackagePayment::class,
                'transactionable_id' => $payment->id,
            ]);

            $package->updatePaymentStatus();
            if ($package->payment_status === OrganizationPackage::PAYMENT_STATUS_FULLY_PAID) {
                $package->periods()->update([
                    'is_paid' => true,
                    'paid_at' => Carbon::now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'پرداخت با موفقیت تایید شد',
                'verified' => true,
                'data' => [
                    'payment' => $payment,
                    'package' => $package->fresh(),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'خطا در ثبت پرداخت: ' . $e->getMessage()
            ], 500);
        }
    }
}
