<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

class ReportController extends Controller
{
    /**
     * Get combined reports (today, current month, overall, and last 10 services) for the authenticated technician
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get current Jalali date
        $now = Jalalian::now();
        $currentYear = $now->getYear();
        $currentMonth = $now->getMonth();
        $today = $now->toCarbon()->startOfDay();
        $tomorrow = $now->toCarbon()->copy()->addDay()->startOfDay();

        // Get all services for this technician
        $allServices = Service::where('technician_id', $technician->id)->get();

        // ========== TODAY'S DATA ==========
        $todayServices = $allServices->filter(function ($service) use ($today, $tomorrow) {
            // Services assigned or completed today
            $assignedToday = $service->assigned_at && 
                             $service->assigned_at->gte($today) && 
                             $service->assigned_at->lt($tomorrow);
            $completedToday = $service->completed_at && 
                              $service->completed_at->gte($today) && 
                              $service->completed_at->lt($tomorrow);
            return $assignedToday || $completedToday;
        });
        $todayData = $this->getStatsData($todayServices);

        // ========== CURRENT MONTH DATA ==========
        $currentMonthServices = $allServices->filter(function ($service) use ($currentYear, $currentMonth) {
            return $service->service_year == $currentYear && $service->service_month == $currentMonth;
        });
        $currentMonthData = $this->getStatsData($currentMonthServices);

        // ========== OVERALL DATA ==========
        $overallData = $this->getStatsData($allServices);

        // ========== LAST 10 SERVICES ==========
        $lastServices = Service::where('technician_id', $technician->id)
            ->with(['building:id,name,address'])
            ->orderByRaw('COALESCE(assigned_at, completed_at, created_at) DESC')
            ->limit(10)
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'building_name' => $service->building ? $service->building->name : null,
                    'building_address' => $service->building ? $service->building->address : null,
                    'service_month' => $service->service_month,
                    'service_year' => $service->service_year,
                    'status' => $service->status,
                    'status_text' => $service->status_text,
                    'assigned_at' => $service->assigned_at ? Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s') : null,
                    'completed_at' => $service->completed_at ? Jalalian::forge($service->completed_at)->format('Y/m/d H:i:s') : null,
                ];
            });

        return response()->json([
            'success' => true,
            'today' => $todayData,
            'current_month' => $currentMonthData,
            'overall' => $overallData,
            'last_services' => $lastServices,
        ]);
    }

    /**
     * Get monthly services report for the authenticated technician
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthlyReport(Request $request)
    {
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get optional year filter (default: current year)
        $year = $request->get('year', Jalalian::now()->getYear());

        // Get all services for this technician in the specified year
        $services = Service::where('technician_id', $technician->id)
            ->where('service_year', $year)
            ->get();

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

        // Initialize monthly data
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[$month] = [
                'month' => $month,
                'month_name' => $monthNames[$month],
                'total' => 0,
                'pending' => 0,
                'assigned' => 0,
                'completed' => 0,
                'expired' => 0,
            ];
        }

        // Group services by month and status
        foreach ($services as $service) {
            $month = $service->service_month;
            if (isset($monthlyData[$month])) {
                $monthlyData[$month]['total']++;
                $monthlyData[$month][$service->status]++;
            }
        }

        // Convert to array and filter out months with no services (optional)
        $monthlyReport = array_values($monthlyData);

        // Calculate year totals
        $yearTotals = [
            'total' => $services->count(),
            'pending' => $services->where('status', Service::STATUS_PENDING)->count(),
            'assigned' => $services->where('status', Service::STATUS_ASSIGNED)->count(),
            'completed' => $services->where('status', Service::STATUS_COMPLETED)->count(),
            'expired' => $services->where('status', Service::STATUS_EXPIRED)->count(),
        ];

        return response()->json([
            'success' => true,
            'year' => $year,
            'monthly_data' => $monthlyReport,
            'year_totals' => $yearTotals,
        ]);
    }

    /**
     * Get overall services report for the authenticated technician
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function overallReport(Request $request)
    {
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get all services for this technician
        $services = Service::where('technician_id', $technician->id)->get();

        // Overall statistics
        $overallStats = [
            'total_services' => $services->count(),
            'pending' => $services->where('status', Service::STATUS_PENDING)->count(),
            'assigned' => $services->where('status', Service::STATUS_ASSIGNED)->count(),
            'completed' => $services->where('status', Service::STATUS_COMPLETED)->count(),
            'expired' => $services->where('status', Service::STATUS_EXPIRED)->count(),
        ];

        // Calculate completion rate
        $completionRate = $overallStats['total_services'] > 0 
            ? round(($overallStats['completed'] / $overallStats['total_services']) * 100, 2)
            : 0;

        // Yearly breakdown
        $yearlyBreakdown = $services->groupBy('service_year')->map(function ($yearServices) {
            return [
                'total' => $yearServices->count(),
                'pending' => $yearServices->where('status', Service::STATUS_PENDING)->count(),
                'assigned' => $yearServices->where('status', Service::STATUS_ASSIGNED)->count(),
                'completed' => $yearServices->where('status', Service::STATUS_COMPLETED)->count(),
                'expired' => $yearServices->where('status', Service::STATUS_EXPIRED)->count(),
            ];
        })->sortKeysDesc()->toArray();

        // Get first and last service dates
        $firstService = $services->sortBy(function ($service) {
            return $service->service_year * 100 + $service->service_month;
        })->first();

        $lastService = $services->sortByDesc(function ($service) {
            return $service->service_year * 100 + $service->service_month;
        })->first();

        $servicePeriod = null;
        if ($firstService && $lastService) {
            $servicePeriod = [
                'start' => [
                    'year' => $firstService->service_year,
                    'month' => $firstService->service_month,
                    'month_name' => $this->getMonthName($firstService->service_month),
                ],
                'end' => [
                    'year' => $lastService->service_year,
                    'month' => $lastService->service_month,
                    'month_name' => $this->getMonthName($lastService->service_month),
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'overall_stats' => $overallStats,
            'completion_rate' => $completionRate,
            'yearly_breakdown' => $yearlyBreakdown,
            'service_period' => $servicePeriod,
        ]);
    }

    /**
     * Get month name in Persian
     * 
     * @param int $month
     * @return string
     */
    private function getMonthName($month)
    {
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

        return $monthNames[$month] ?? $month;
    }

    /**
     * Get statistics data for a collection of services
     * 
     * @param \Illuminate\Support\Collection $services
     * @return array
     */
    private function getStatsData($services)
    {
        $total = $services->count();
        $pending = $services->where('status', Service::STATUS_PENDING)->count();
        $assigned = $services->where('status', Service::STATUS_ASSIGNED)->count();
        $completed = $services->where('status', Service::STATUS_COMPLETED)->count();
        $expired = $services->where('status', Service::STATUS_EXPIRED)->count();

        $completionRate = $total > 0 
            ? round(($completed / $total) * 100, 2)
            : 0;

        return [
            'total' => $total,
            'pending' => $pending,
            'assigned' => $assigned,
            'completed' => $completed,
            'expired' => $expired,
            'completion_rate' => $completionRate,
        ];
    }
}

