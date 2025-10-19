<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Elevator;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ElevatorController extends Controller
{
    public function index(Request $request, $buildingId)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify building belongs to organization
        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($buildingId);

        $query = Elevator::where('building_id', $buildingId);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status === 'true' || $request->status === true);
        }

        $elevators = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $elevators->items(),
            'pagination' => [
                'current_page' => $elevators->currentPage(),
                'last_page' => $elevators->lastPage(),
                'per_page' => $elevators->perPage(),
                'total' => $elevators->total(),
            ]
        ]);
    }

    public function store(Request $request, $buildingId)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify building belongs to organization
        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($buildingId);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'stops_count' => 'required|integer|min:1',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:true,false',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['building_id'] = $buildingId;
        $data['status'] = $data['status'] === 'true' || $data['status'] === true;

        $elevator = Elevator::create($data);

        return response()->json([
            'success' => true,
            'message' => 'آسانسور با موفقیت ایجاد شد',
            'data' => $elevator
        ], 201);
    }

    public function show($buildingId, $id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify building belongs to organization
        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($buildingId);

        $elevator = Elevator::where('building_id', $buildingId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $elevator
        ]);
    }

    public function update(Request $request, $buildingId, $id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify building belongs to organization
        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($buildingId);

        $elevator = Elevator::where('building_id', $buildingId)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'stops_count' => 'required|integer|min:1',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:true,false',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['status'] = $data['status'] === 'true' || $data['status'] === true;

        $elevator->update($data);

        return response()->json([
            'success' => true,
            'message' => 'آسانسور با موفقیت به‌روزرسانی شد',
            'data' => $elevator
        ]);
    }

    public function destroy($buildingId, $id)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify building belongs to organization
        $building = Building::where('organization_id', $user->organization_id)
            ->findOrFail($buildingId);

        $elevator = Elevator::where('building_id', $buildingId)
            ->findOrFail($id);

        $elevator->delete();

        return response()->json([
            'success' => true,
            'message' => 'آسانسور با موفقیت حذف شد'
        ]);
    }
}