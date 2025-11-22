<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Service;

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
        $building->load(['province', 'city']);

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
}

