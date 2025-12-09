<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\PdfVerificationCode;
use App\Models\Service;
use App\Models\ServiceView;
use App\Models\UnitChecklist;
use Illuminate\Http\Request;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class BuildingController extends Controller
{
    /**
     * Show completed services for a building grouped by month
     *
     * @param Building $building
     * @return \Illuminate\View\View
     */
    public function showServices(Building $building)
    {
        $building->load(['province', 'city', 'organization']);

        // Get all completed services for this building
        $services = Service::where('building_id', $building->id)
            ->where('status', Service::STATUS_COMPLETED)
            ->with(['technician'])
            ->orderBy('service_year', 'desc')
            ->orderBy('service_month', 'desc')
            ->get();

        // Group services by year and month
        $groupedServices = [];
        foreach ($services as $service) {
            $year = $service->service_year;
            $month = $service->service_month;
            
            if (!isset($groupedServices[$year])) {
                $groupedServices[$year] = [];
            }
            
            if (!isset($groupedServices[$year][$month])) {
                $groupedServices[$year][$month] = [];
            }
            
            $groupedServices[$year][$month][] = $service;
        }

        // Sort months in descending order for each year
        foreach ($groupedServices as $year => $months) {
            krsort($groupedServices[$year]);
        }

        // Month names in Persian
        $monthNames = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];

        return view('public.buildings.services', compact(
            'building',
            'groupedServices',
            'monthNames'
        ));
    }

    /**
     * Show service details
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function showService($id)
    {
        $service = Service::with([
            'building.province',
            'building.city',
            'building.elevators',
            'building.organization',
            'technician',
            'checklist' => function($query) {
                $query->with([
                    'signatures',
                    'managerSignature',
                    'technicianSignature',
                    'elevatorChecklists.elevator',
                    'elevatorChecklists.descriptions'
                ]);
            }
        ])
        ->where('status', Service::STATUS_COMPLETED)
        ->findOrFail($id);
        
        // Ensure checklist relationships are loaded
        if ($service->checklist) {
            $service->checklist->loadMissing([
                'signatures',
                'managerSignature', 
                'technicianSignature'
            ]);
        }

        // Month names in Persian
        $monthNames = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];

        return view('public.services.show', compact('service', 'monthNames'));
    }

    /**
     * Show assigned or completed service details
     *
     * @param Request $request
     * @param Building $building
     * @param Service $service
     * @return \Illuminate\View\View
     */
    public function showAssignedService(Request $request, $slug)
    {
        $service = Service::where('slug', $slug)->firstOrFail();
        $building = $service->building;

        // Allow only assigned or completed services
        if (!in_array($service->status, [Service::STATUS_ASSIGNED, Service::STATUS_COMPLETED])) {
            abort(404, 'این سرویس در دسترس نیست.');
        }

        // Record the view
        $this->recordView($request, $service);

        // Load relationships based on status
        if ($service->status === Service::STATUS_COMPLETED) {
            // Load complete information for completed services
            $service->load([
                'building.province',
                'building.city',
                'building.elevators',
                'building.organization',
                'technician',
                'checklist' => function($query) {
                    $query->with([
                        'signatures',
                        'managerSignature',
                        'technicianSignature',
                        'elevatorChecklists.elevator',
                        'elevatorChecklists.descriptions'
                    ]);
                }
            ]);

            // Ensure checklist relationships are loaded
            if ($service->checklist) {
                $service->checklist->loadMissing([
                    'signatures',
                    'managerSignature', 
                    'technicianSignature'
                ]);
            }
        } else {
            // Load basic information for assigned services
            $service->load([
                'building.province',
                'building.city',
                'building.organization',
                'technician'
            ]);
        }

        // Month names in Persian
        $monthNames = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];

        return view('public.services.assigned', compact('service', 'building', 'monthNames'));
    }

    /**
     * Record a view for a service
     *
     * @param Request $request
     * @param Service $service
     * @return void
     */
    protected function recordView(Request $request, Service $service)
    {
        $userAgent = $request->userAgent();
        $ipAddress = $request->ip();
        
        // Detect device information
        $deviceInfo = $this->detectDeviceInfo($userAgent);
        
        ServiceView::create([
            'service_id' => $service->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'platform' => $deviceInfo['platform'],
            'viewed_at' => now(),
        ]);
    }

    /**
     * Detect device information from user agent
     *
     * @param string|null $userAgent
     * @return array
     */
    protected function detectDeviceInfo(?string $userAgent): array
    {
        if (!$userAgent) {
            return [
                'device_type' => 'unknown',
                'browser' => 'unknown',
                'platform' => 'unknown',
            ];
        }

        $userAgent = strtolower($userAgent);

        // Detect device type
        $deviceType = 'desktop';
        if (preg_match('/mobile|android|iphone|ipod|blackberry|iemobile|opera mini/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        // Detect browser
        $browser = 'unknown';
        if (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/edg/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/opera|opr/i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/msie|trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
        }

        // Detect platform
        $platform = 'unknown';
        if (preg_match('/windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }

    /**
     * Generate PDF for service checklist
     *
     * @param Building $building
     * @param Service $service
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function printService(Building $building, Service $service, Request $request)
    {
        // Ensure service belongs to building
        if ($service->building_id !== $building->id) {
            abort(404);
        }

        // Only allow completed services
        if ($service->status !== Service::STATUS_COMPLETED) {
            abort(404, 'فقط سرویس‌های تکمیل شده قابل چاپ هستند.');
        }

        // Check for download token
        $token = $request->query('token');
        if (!$token) {
            abort(403, 'دسترسی غیرمجاز. لطفا از طریق صفحه سرویس اقدام به دانلود کنید.');
        }

        // Verify token in database
        $verificationCode = PdfVerificationCode::where('service_id', $service->id)
            ->where('download_token', $token)
            ->where('used', false)
            ->where('verified', true)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verificationCode) {
            abort(403, 'کد تایید منقضی شده است یا قبلا استفاده شده است. لطفا دوباره تلاش کنید.');
        }

        // Mark token as used (one-time use)
        $verificationCode->update([
            'used' => true,
        ]);

        // Load all necessary relationships
        $service->load([
            'building.organization',
            'building.province',
            'building.city',
            'technician',
            'checklist' => function($query) {
                $query->with([
                    'signatures',
                    'managerSignature',
                    'technicianSignature',
                    'elevatorChecklists.elevator',
                    'elevatorChecklists.descriptions'
                ]);
            }
        ]);

        if (!$service->checklist || $service->checklist->elevatorChecklists->count() === 0) {
            abort(404, 'چک لیست برای این سرویس موجود نیست.');
        }

        // Get unit checklists ordered by order
        $unitChecklists = UnitChecklist::orderBy('order')->get();

        // Month names in Persian
        $monthNames = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
        ];

        // Format completion date
        $completedDate = null;
        if ($service->completed_at) {
            try {
                if ($service->completed_at instanceof \Carbon\Carbon) {
                    $jalaliDate = \Morilog\Jalali\Jalalian::fromCarbon($service->completed_at);
                } else {
                    $jalaliDate = \Morilog\Jalali\Jalalian::fromDateTime($service->completed_at);
                }
                $completedDate = $jalaliDate->format('Y/m/d');
            } catch (\Exception $e) {
                $completedDate = $service->completed_at instanceof \Carbon\Carbon 
                    ? $service->completed_at->format('Y/m/d')
                    : date('Y/m/d', strtotime($service->completed_at));
            }
        }

        // Get signatures
        $checklist = $service->checklist;
        $allSignatures = $checklist->signatures;
        $technicianSig = $allSignatures->where('type', 'technician')->first();
        $managerSig = $allSignatures->where('type', 'manager')->first();
        
        if (!$technicianSig) {
            $technicianSig = $checklist->technicianSignature;
        }
        if (!$managerSig) {
            $managerSig = $checklist->managerSignature;
        }

        // Generate PDF using niklasravnsborg/laravel-pdf
        $pdf = Pdf::loadView('public.services.pdf', [
            'service' => $service,
            'building' => $building,
            'unitChecklists' => $unitChecklists,
            'monthNames' => $monthNames,
            'completedDate' => $completedDate,
            'technicianSig' => $technicianSig,
            'managerSig' => $managerSig,
        ]);

        $filename = 'checklist_' . $service->slug . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}

