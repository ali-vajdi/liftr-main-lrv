<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Organization;
use App\Models\OrganizationPackage;

class CheckOrganizationPackagePayment
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'کاربر یافت نشد',
                'locked' => true,
                'requires_payment' => true
            ], 403);
        }

        // Load organization relationship if not already loaded
        if (!$user->relationLoaded('organization')) {
            $user->load('organization');
        }
        
        if (!$user->organization) {
            return response()->json([
                'message' => 'سازمان یافت نشد',
                'locked' => true,
                'requires_payment' => true
            ], 403);
        }

        $organization = $user->organization;
        $activePackages = $organization->activePackages();

        // If no active packages, lock access
        if ($activePackages->isEmpty()) {
            return response()->json([
                'message' => 'شما پکیج فعالی ندارید. لطفا پکیج خریداری کنید.',
                'locked' => true,
                'requires_payment' => true,
                'payment_url' => '/packages/payment'
            ], 403);
        }

        // Check each active package for payment status
        $hasAccess = false;
        $needsPayment = false;
        $paymentInfo = [];

        foreach ($activePackages as $package) {
            $package->load('periods');
            
            // If periods are not used, check if fully paid
            if (!$package->use_periods) {
                if ($package->payment_status === OrganizationPackage::PAYMENT_STATUS_FULLY_PAID) {
                    $hasAccess = true;
                } else {
                    $needsPayment = true;
                    $paymentInfo[] = [
                        'package_id' => $package->id,
                        'package_name' => $package->package_name,
                        'remaining_amount' => $package->remaining_amount,
                        'total_amount' => $package->package_price,
                        'paid_amount' => $package->total_paid_amount,
                        'period' => 'full'
                    ];
                }
            } else {
                // For packages with periods, check current period payment
                $currentPeriod = $package->getCurrentPeriod();
                $currentPeriodRecord = $package->periods()->where('period_number', $currentPeriod)->first();
                
                if ($currentPeriodRecord && $currentPeriodRecord->is_paid) {
                    $hasAccess = true;
                } else {
                    $needsPayment = true;
                    $periodAmount = $currentPeriodRecord ? $currentPeriodRecord->amount : 0;
                    $paymentInfo[] = [
                        'package_id' => $package->id,
                        'package_name' => $package->package_name,
                        'current_period' => $currentPeriod,
                        'period_amount' => $periodAmount,
                        'total_amount' => $package->package_price,
                        'paid_amount' => $package->total_paid_amount,
                        'remaining_amount' => $package->remaining_amount,
                        'period' => 'period'
                    ];
                }
            }
        }

        // If access is granted, continue
        if ($hasAccess && !$needsPayment) {
        return $next($request);
        }

        // Otherwise, lock access and return payment info
        return response()->json([
            'message' => 'برای دسترسی به سیستم، لطفا پرداخت خود را انجام دهید.',
            'locked' => true,
            'requires_payment' => true,
            'payment_url' => '/packages/payment',
            'payment_info' => $paymentInfo
        ], 403);
    }
}
