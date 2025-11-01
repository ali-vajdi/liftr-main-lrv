<?php

namespace App\Http\Controllers\Api\Technician;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

class ServiceController extends Controller
{
    /**
     * Get assigned buildings/services for the authenticated technician
     */
    public function assignedBuildings(Request $request)
    {
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Only load building and elevators count - get all assigned services
        $services = Service::with([
            'building' => function ($query) {
                $query->select('id', 'name', 'address');
            },
            'building.elevators' => function ($query) {
                $query->select('id', 'building_id');
            }
        ])
            ->where('technician_id', $technician->id)
            ->assigned() // Only assigned services
            ->orderBy('assigned_at', 'desc')
            ->orderBy('service_year', 'desc')
            ->orderBy('service_month', 'desc')
            ->get();

        // Format response - only return required fields
        $items = $services->map(function ($service) {
            $building = $service->building;
            $elevatorsCount = $building && $building->elevators ? $building->elevators->count() : 0;
            
            return [
                'id' => $service->id,
                'assigned_at_jalali' => $service->assigned_at ? Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s') : null,
                'building_name' => $building ? $building->name : null,
                'building_address' => $building ? $building->address : null,
                'elevators_count' => $elevatorsCount,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items->all()
        ]);
    }

    /**
     * Get a specific assigned service/building details
     */
    public function show($id)
    {
        $technician = auth('technician_api')->user();
        if (!$technician) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $service = Service::with([
            'building.province', 
            'building.city', 
            'building.elevators',
        ])
            ->where('technician_id', $technician->id)
            ->findOrFail($id);

        $service->status_text = $service->status_text;
        $service->status_badge_class = $service->status_badge_class;
        $service->service_date_text = $service->service_date_text;
        if ($service->assigned_at) {
            $service->assigned_at_jalali = Jalalian::forge($service->assigned_at)->format('Y/m/d H:i:s');
        }
        if ($service->completed_at) {
            $service->completed_at_jalali = Jalalian::forge($service->completed_at)->format('Y/m/d H:i:s');
        }

        return response()->json([
            'success' => true,
            'data' => $service
        ]);
    }
}
