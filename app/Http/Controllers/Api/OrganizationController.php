<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::query();

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('address', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Handle status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
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
        $organizations = $query->paginate($perPage);

        return response()->json([
            'data' => $organizations->items(),
            'pagination' => [
                'total' => $organizations->total(),
                'per_page' => $organizations->perPage(),
                'current_page' => $organizations->currentPage(),
                'last_page' => $organizations->lastPage(),
                'from' => $organizations->firstItem(),
                'to' => $organizations->lastItem(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:true,false',
        ], [
            'name.required' => 'نام شرکت الزامی است',
            'name.max' => 'نام شرکت نمی‌تواند بیش از 255 کاراکتر باشد',
            'logo.image' => 'فایل انتخاب شده باید تصویر باشد',
            'logo.mimes' => 'فرمت تصویر باید JPG یا PNG باشد',
            'logo.max' => 'حجم تصویر نمی‌تواند بیش از 2 مگابایت باشد',
            'status.required' => 'وضعیت الزامی است',
            'status.in' => 'وضعیت باید فعال یا غیرفعال باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['moderator_id'] = Auth::id();
        $data['status'] = $data['status'] === 'true' || $data['status'] === true;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '_' . $logo->getClientOriginalName();
            $logoPath = $logo->storeAs('public/organization_logos', $logoName);
            $data['logo'] = Storage::url($logoPath);
        }

        $organization = Organization::create($data);

        return response()->json([
            'message' => 'شرکت با موفقیت ایجاد شد',
            'data' => $organization
        ], 201);
    }

    public function show($id)
    {
        $organization = Organization::findOrFail($id);
        
        return response()->json([
            'data' => $organization
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:true,false',
        ], [
            'name.required' => 'نام شرکت الزامی است',
            'name.max' => 'نام شرکت نمی‌تواند بیش از 255 کاراکتر باشد',
            'logo.image' => 'فایل انتخاب شده باید تصویر باشد',
            'logo.mimes' => 'فرمت تصویر باید JPG یا PNG باشد',
            'logo.max' => 'حجم تصویر نمی‌تواند بیش از 2 مگابایت باشد',
            'status.required' => 'وضعیت الزامی است',
            'status.in' => 'وضعیت باید فعال یا غیرفعال باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $organization = Organization::findOrFail($id);
        $data = $request->all();
        $data['status'] = $data['status'] === 'true' || $data['status'] === true;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($organization->logo) {
                $oldLogoPath = str_replace('/storage/', 'public/', $organization->logo);
                Storage::delete($oldLogoPath);
            }
            
            $logo = $request->file('logo');
            $logoName = time() . '_' . $logo->getClientOriginalName();
            $logoPath = $logo->storeAs('public/organization_logos', $logoName);
            $data['logo'] = Storage::url($logoPath);
        }

        $organization->update($data);

        return response()->json([
            'message' => 'شرکت با موفقیت ویرایش شد',
            'data' => $organization
        ]);
    }

    public function destroy($id)
    {
        $organization = Organization::findOrFail($id);
        
        // Delete logo file if exists
        if ($organization->logo) {
            $logoPath = str_replace('/storage/', 'public/', $organization->logo);
            Storage::delete($logoPath);
        }
        
        $organization->delete();

        return response()->json([
            'message' => 'شرکت با موفقیت حذف شد'
        ]);
    }
}
