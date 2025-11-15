<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sms;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class SmsController extends Controller
{
    public function index(Request $request)
    {
        $query = Sms::with('organization');

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('phone_number', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('message', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Handle organization filter
        if ($request->has('organization_id') && $request->organization_id !== '') {
            if ($request->organization_id === 'null' || $request->organization_id === 'system') {
                $query->whereNull('organization_id');
            } else {
                $query->where('organization_id', $request->organization_id);
            }
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
        $sms = $query->paginate($perPage);

        return response()->json([
            'data' => $sms->items(),
            'pagination' => [
                'total' => $sms->total(),
                'per_page' => $sms->perPage(),
                'current_page' => $sms->currentPage(),
                'last_page' => $sms->lastPage(),
                'from' => $sms->firstItem(),
                'to' => $sms->lastItem(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'nullable|exists:organizations,id',
            'phone_number' => 'required|string|max:20',
            'message' => 'required|string',
        ], [
            'organization_id.exists' => 'سازمان انتخاب شده معتبر نیست',
            'phone_number.required' => 'شماره تلفن الزامی است',
            'phone_number.max' => 'شماره تلفن نمی‌تواند بیش از 20 کاراکتر باشد',
            'message.required' => 'متن پیامک الزامی است',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        
        // Calculate cost based on organization
        $cost = 0;
        if ($request->has('organization_id') && $request->organization_id) {
            $organization = Organization::find($request->organization_id);
            if ($organization && $organization->sms_cost_per_message > 0) {
                $cost = $organization->sms_cost_per_message;
            }
        }
        // If organization_id is null, it's a system SMS (cost is 0 or can be set differently)

        $data['cost'] = $cost;
        $data['status'] = Sms::STATUS_PENDING;

        $sms = Sms::create($data);

        return response()->json([
            'message' => 'پیامک با موفقیت ایجاد شد',
            'data' => $sms->load('organization')
        ], 201);
    }

    public function show($id)
    {
        $sms = Sms::with('organization')->findOrFail($id);
        
        return response()->json([
            'data' => $sms
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'nullable|exists:organizations,id',
            'phone_number' => 'required|string|max:20',
            'message' => 'required|string',
            'status' => 'nullable|in:pending,sent,failed',
        ], [
            'organization_id.exists' => 'سازمان انتخاب شده معتبر نیست',
            'phone_number.required' => 'شماره تلفن الزامی است',
            'phone_number.max' => 'شماره تلفن نمی‌تواند بیش از 20 کاراکتر باشد',
            'message.required' => 'متن پیامک الزامی است',
            'status.in' => 'وضعیت باید یکی از مقادیر pending، sent یا failed باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sms = Sms::findOrFail($id);
        $data = $request->all();
        
        // Recalculate cost if organization_id changed
        if ($request->has('organization_id')) {
            $cost = 0;
            if ($request->organization_id) {
                $organization = Organization::find($request->organization_id);
                if ($organization && $organization->sms_cost_per_message > 0) {
                    $cost = $organization->sms_cost_per_message;
                }
            }
            $data['cost'] = $cost;
        }

        // Set sent_at if status changed to sent
        if ($request->has('status') && $request->status === Sms::STATUS_SENT && $sms->status !== Sms::STATUS_SENT) {
            $data['sent_at'] = now();
        }

        $sms->update($data);

        return response()->json([
            'message' => 'پیامک با موفقیت ویرایش شد',
            'data' => $sms->load('organization')
        ]);
    }

    public function destroy($id)
    {
        $sms = Sms::findOrFail($id);
        $sms->delete();

        return response()->json([
            'message' => 'پیامک با موفقیت حذف شد'
        ]);
    }
}
