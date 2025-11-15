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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:organization_api')->except(['showPaymentPage']);
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
            // Check existing packages for payment requirements
            foreach ($activePackages as $package) {
                // Load periods for packages > 30 days
                if ($package->package_duration_days > 30) {
                    $package->load('periods');
                }
                
                if ($package->package_duration_days <= 30) {
                    // For packages 30 days or less
                    if ($package->payment_status !== OrganizationPackage::PAYMENT_STATUS_FULLY_PAID) {
                        $paymentInfo[] = [
                            'package_id' => $package->id,
                            'package_name' => $package->package_name,
                            'package_duration_days' => $package->package_duration_days,
                            'total_amount' => $package->package_price,
                            'paid_amount' => $package->total_paid_amount,
                            'remaining_amount' => $package->remaining_amount,
                            'payment_type' => 'full',
                            'current_period' => null,
                            'period_amount' => null,
                            'periods' => [],
                        ];
                    }
                } else {
                    // For packages > 30 days - use PackagePeriod records
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
                            'current_period' => $currentPeriod,
                            'period_amount' => $currentPeriodRecord->amount,
                            'current_period_id' => $currentPeriodRecord->id,
                            'periods' => $allPeriods,
                        ];
                    }
                }
            }
        }

        return response()->json([
            'data' => $paymentInfo,
            'public_packages' => $publicPackages,
            'has_active_packages' => $activePackages->isNotEmpty(),
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
            'period' => 'nullable|integer|min:0',
        ], [
            'package_id.required' => 'شناسه پکیج الزامی است',
            'package_id.exists' => 'پکیج یافت نشد',
            'amount.required' => 'مبلغ الزامی است',
            'amount.numeric' => 'مبلغ باید عدد باشد',
            'amount.min' => 'مبلغ نمی‌تواند منفی باشد',
            'payment_type.required' => 'نوع پرداخت الزامی است',
            'payment_type.in' => 'نوع پرداخت معتبر نیست',
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
                'message' => 'پکیج یافت نشد'
            ], 404);
        }

        if (!$package->is_active) {
            return response()->json([
                'message' => 'پکیج غیرفعال است'
            ], 422);
        }

        $amount = $request->amount;
        $paymentType = $request->payment_type;
        $systemPaymentMethod = PaymentMethod::getSystemMethod();

        if (!$systemPaymentMethod) {
            return response()->json([
                'message' => 'روش پرداخت سیستمی یافت نشد'
            ], 500);
        }

        DB::beginTransaction();
        try {
            if ($paymentType === 'period') {
                // Pay for a specific period using PackagePeriod record
                $periodNumber = $request->period ?? $package->getCurrentPeriod();
                $periodRecord = $package->periods()->where('period_number', $periodNumber)->first();
                
                if (!$periodRecord) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'دوره یافت نشد'
                    ], 404);
                }
                
                if ($periodRecord->is_paid) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'این دوره قبلا پرداخت شده است'
                    ], 422);
                }
                
                $expectedAmount = $periodRecord->amount;
                
                if (abs($amount - $expectedAmount) > 0.01) {
                    DB::rollBack();
                    return response()->json([
                        'message' => "مبلغ باید {$expectedAmount} تومان باشد"
                    ], 422);
                }

                // Create payment
                $payment = PackagePayment::create([
                    'organization_package_id' => $package->id,
                    'payment_method_id' => $systemPaymentMethod->id,
                    'amount' => round($amount, 0),
                    'payment_date' => Carbon::now(),
                    'notes' => "پرداخت دوره {$periodNumber} ({$periodRecord->days} روز)",
                    'moderator_id' => null, // System payment
                ]);

                // Mark period as paid
                $periodRecord->update([
                    'is_paid' => true,
                    'paid_at' => Carbon::now(),
                ]);

                // Create transaction
                Transaction::create([
                    'transactionable_type' => PackagePayment::class,
                    'transactionable_id' => $payment->id,
                    'payment_method_id' => $systemPaymentMethod->id,
                    'amount' => round($amount, 0),
                    'type' => Transaction::TYPE_INCOME,
                    'status' => Transaction::STATUS_COMPLETED,
                    'description' => "پرداخت دوره {$periodNumber} پکیج: {$package->package_name}",
                    'transaction_date' => Carbon::now(),
                    'organization_id' => $organization->id,
                    'moderator_id' => null,
                ]);

                // Update payment status
                $package->updatePaymentStatus();

            } else {
                // Pay full amount
                if ($amount > $package->remaining_amount) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'مبلغ پرداختی نمی‌تواند بیشتر از مبلغ باقی‌مانده باشد'
                    ], 422);
                }

                // Create payment
                $payment = PackagePayment::create([
                    'organization_package_id' => $package->id,
                    'payment_method_id' => $systemPaymentMethod->id,
                    'amount' => round($amount, 0),
                    'payment_date' => Carbon::now(),
                    'notes' => 'پرداخت کامل پکیج',
                    'moderator_id' => null,
                ]);

                // If fully paid, mark all periods as paid
                $package->updatePaymentStatus();
                if ($package->payment_status === OrganizationPackage::PAYMENT_STATUS_FULLY_PAID) {
                    // Mark all periods as paid
                    $package->periods()->update([
                        'is_paid' => true,
                        'paid_at' => Carbon::now(),
                    ]);
                }

                // Create transaction
                Transaction::create([
                    'transactionable_type' => PackagePayment::class,
                    'transactionable_id' => $payment->id,
                    'payment_method_id' => $systemPaymentMethod->id,
                    'amount' => $amount,
                    'type' => Transaction::TYPE_INCOME,
                    'status' => Transaction::STATUS_COMPLETED,
                    'description' => "پرداخت کامل پکیج: {$package->package_name}",
                    'transaction_date' => Carbon::now(),
                    'organization_id' => $organization->id,
                    'moderator_id' => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'پرداخت با موفقیت انجام شد',
                'data' => [
                    'package' => $package->fresh(),
                    'payment' => $payment,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'خطا در پردازش پرداخت: ' . $e->getMessage()
            ], 500);
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
            'package_id.required' => 'شناسه پکیج الزامی است',
            'package_id.exists' => 'پکیج یافت نشد',
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
                'message' => 'این پکیج عمومی نیست'
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
                'payment_status' => OrganizationPackage::PAYMENT_STATUS_UNPAID,
                'started_at' => Carbon::now(),
                'is_active' => true,
                'moderator_id' => null, // System activation
            ]);

            // Generate periods if package is longer than 30 days
            if ($organizationPackage->package_duration_days > 30) {
                $organizationPackage->generatePeriods();
            }

            DB::commit();

            return response()->json([
                'message' => 'پکیج با موفقیت فعال شد. لطفا پرداخت را انجام دهید.',
                'data' => [
                    'organization_package' => $organizationPackage,
                    'package' => $package,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'خطا در فعال‌سازی پکیج: ' . $e->getMessage()
            ], 500);
        }
    }
}
