<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UnitChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class UnitChecklistController extends Controller
{
    public function index(Request $request)
    {
        $query = UnitChecklist::query();

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Handle sorting
        $sortField = $request->input('sort_field', 'order');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Get paginated results
        $perPage = $request->input('per_page', 10);
        $checklists = $query->paginate($perPage);

        return response()->json([
            'data' => $checklists->items(),
            'pagination' => [
                'total' => $checklists->total(),
                'per_page' => $checklists->perPage(),
                'current_page' => $checklists->currentPage(),
                'last_page' => $checklists->lastPage(),
                'from' => $checklists->firstItem(),
                'to' => $checklists->lastItem(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:1000',
            'order' => 'nullable|integer|min:0',
        ], [
            'title.required' => 'عنوان چک لیست الزامی است',
            'title.max' => 'عنوان چک لیست نمی‌تواند بیش از 1000 کاراکتر باشد',
            'order.integer' => 'ترتیب باید عدد باشد',
            'order.min' => 'ترتیب نمی‌تواند منفی باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['moderator_id'] = Auth::id();
        
        if (!isset($data['order'])) {
            $maxOrder = UnitChecklist::max('order') ?? 0;
            $data['order'] = $maxOrder + 1;
        }

        $checklist = UnitChecklist::create($data);

        return response()->json([
            'message' => 'چک لیست با موفقیت ایجاد شد',
            'data' => $checklist
        ], 201);
    }

    public function show($id)
    {
        $checklist = UnitChecklist::findOrFail($id);
        
        return response()->json([
            'data' => $checklist
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:1000',
            'order' => 'nullable|integer|min:0',
        ], [
            'title.required' => 'عنوان چک لیست الزامی است',
            'title.max' => 'عنوان چک لیست نمی‌تواند بیش از 1000 کاراکتر باشد',
            'order.integer' => 'ترتیب باید عدد باشد',
            'order.min' => 'ترتیب نمی‌تواند منفی باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $checklist = UnitChecklist::findOrFail($id);
        $data = $request->all();
        $checklist->update($data);

        return response()->json([
            'message' => 'چک لیست با موفقیت ویرایش شد',
            'data' => $checklist
        ]);
    }

    public function destroy($id)
    {
        $checklist = UnitChecklist::findOrFail($id);
        $checklist->delete();

        return response()->json([
            'message' => 'چک لیست با موفقیت حذف شد'
        ]);
    }
}
