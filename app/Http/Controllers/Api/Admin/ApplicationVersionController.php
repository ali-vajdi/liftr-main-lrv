<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class ApplicationVersionController extends Controller
{
    public function index(Request $request)
    {
        $query = ApplicationVersion::query();

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('version', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Handle platform filter
        if ($request->has('platform') && $request->platform !== '') {
            $query->where('platform', $request->platform);
        }

        // Handle force_update filter
        if ($request->has('force_update') && $request->force_update !== '') {
            $query->where('force_update', $request->force_update === 'true' || $request->force_update === true);
        }

        // Handle created_at date range filters
        if ($request->has('created_at_from') && !empty($request->created_at_from)) {
            try {
                $jalaliDate = Jalalian::fromFormat('Y/m/d H:i:s', $request->created_at_from);
                $georgianDate = $jalaliDate->toCarbon()->format('Y-m-d');
                $query->whereDate('created_at', '>=', $georgianDate);
            } catch (\Exception $e) {
                // If date conversion fails, skip the filter
            }
        }

        if ($request->has('created_at_to') && !empty($request->created_at_to)) {
            try {
                $jalaliDate = Jalalian::fromFormat('Y/m/d H:i:s', $request->created_at_to);
                $georgianDate = $jalaliDate->toCarbon()->format('Y-m-d');
                $query->whereDate('created_at', '<=', $georgianDate);
            } catch (\Exception $e) {
                // If date conversion fails, skip the filter
            }
        }

        // Handle sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Get paginated results
        $perPage = $request->input('per_page', 10);
        $versions = $query->paginate($perPage);

        return response()->json([
            'data' => $versions->items(),
            'pagination' => [
                'total' => $versions->total(),
                'per_page' => $versions->perPage(),
                'current_page' => $versions->currentPage(),
                'last_page' => $versions->lastPage(),
                'from' => $versions->firstItem(),
                'to' => $versions->lastItem(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'required|in:web,android',
            'version' => 'required|string|max:50',
            'force_update' => 'nullable',
            'description' => 'nullable|string',
        ], [
            'platform.required' => 'پلتفرم الزامی است',
            'platform.in' => 'پلتفرم باید وب یا اندروید باشد',
            'version.required' => 'نسخه الزامی است',
            'version.max' => 'نسخه نمی‌تواند بیش از 50 کاراکتر باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['moderator_id'] = Auth::id();
        
        // Handle force_update - can be 1, 0, true, 'true', '1', or not set
        $data['force_update'] = isset($data['force_update']) && (
            $data['force_update'] === true || 
            $data['force_update'] === 'true' || 
            $data['force_update'] === 1 || 
            $data['force_update'] === '1' ||
            $data['force_update'] === 'on'
        );

        $version = ApplicationVersion::create($data);

        return response()->json([
            'message' => 'نسخه با موفقیت ایجاد شد',
            'data' => $version
        ], 201);
    }

    public function show($id)
    {
        $version = ApplicationVersion::findOrFail($id);
        
        return response()->json([
            'data' => $version
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'required|in:web,android',
            'version' => 'required|string|max:50',
            'force_update' => 'nullable',
            'description' => 'nullable|string',
        ], [
            'platform.required' => 'پلتفرم الزامی است',
            'platform.in' => 'پلتفرم باید وب یا اندروید باشد',
            'version.required' => 'نسخه الزامی است',
            'version.max' => 'نسخه نمی‌تواند بیش از 50 کاراکتر باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $version = ApplicationVersion::findOrFail($id);
        $data = $request->all();
        
        // Handle force_update - can be 1, 0, true, 'true', '1', or not set
        $data['force_update'] = isset($data['force_update']) && (
            $data['force_update'] === true || 
            $data['force_update'] === 'true' || 
            $data['force_update'] === 1 || 
            $data['force_update'] === '1' ||
            $data['force_update'] === 'on'
        );

        $version->update($data);

        return response()->json([
            'message' => 'نسخه با موفقیت ویرایش شد',
            'data' => $version
        ]);
    }

    public function destroy($id)
    {
        $version = ApplicationVersion::findOrFail($id);
        $version->delete();

        return response()->json([
            'message' => 'نسخه با موفقیت حذف شد'
        ]);
    }
}
