<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::query();

        // Handle search
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('duration_label', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Handle is_public filter
        if ($request->has('is_public') && $request->is_public !== '') {
            $query->where('is_public', $request->is_public);
        }

        // Handle price range filters
        if ($request->has('price_from') && !empty($request->price_from)) {
            $query->where('price', '>=', $request->price_from);
        }

        if ($request->has('price_to') && !empty($request->price_to)) {
            $query->where('price', '<=', $request->price_to);
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
        $packages = $query->paginate($perPage);

        // Add calculated attributes to each item
        $items = $packages->items();
        foreach ($items as $item) {
            $item->formatted_price = $item->formatted_price;
            $item->status_text = $item->status_text;
            $item->status_badge_class = $item->status_badge_class;
        }

        return response()->json([
            'data' => $items,
            'pagination' => [
                'total' => $packages->total(),
                'per_page' => $packages->perPage(),
                'current_page' => $packages->currentPage(),
                'last_page' => $packages->lastPage(),
                'from' => $packages->firstItem(),
                'to' => $packages->lastItem(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'duration_label' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_public' => 'required|in:true,false',
            'use_periods' => 'nullable',
            'period_days' => 'nullable|integer|min:1',
        ], [
            'name.required' => 'نام پکیج الزامی است',
            'name.max' => 'نام پکیج نمی‌تواند بیش از 255 کاراکتر باشد',
            'duration_days.required' => 'مدت زمان الزامی است',
            'duration_days.integer' => 'مدت زمان باید عدد باشد',
            'duration_days.min' => 'مدت زمان باید حداقل 1 روز باشد',
            'duration_label.required' => 'برچسب مدت الزامی است',
            'duration_label.max' => 'برچسب مدت نمی‌تواند بیش از 255 کاراکتر باشد',
            'price.required' => 'قیمت الزامی است',
            'price.numeric' => 'قیمت باید عدد باشد',
            'price.min' => 'قیمت نمی‌تواند منفی باشد',
            'is_public.required' => 'وضعیت عمومی الزامی است',
            'is_public.in' => 'وضعیت عمومی باید فعال یا غیرفعال باشد',
            'period_days.integer' => 'تعداد روزهای دوره باید عدد باشد',
            'period_days.min' => 'تعداد روزهای دوره باید حداقل 1 روز باشد',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['moderator_id'] = Auth::id();
        $data['is_public'] = $data['is_public'] === 'true' || $data['is_public'] === true;
        
        // Handle use_periods - can be 1, 0, true, 'true', '1', or not set
        $data['use_periods'] = isset($data['use_periods']) && (
            $data['use_periods'] === true || 
            $data['use_periods'] === 'true' || 
            $data['use_periods'] === 1 || 
            $data['use_periods'] === '1' ||
            $data['use_periods'] === 'on'
        );
        
        // Validate period_days if use_periods is true
        if ($data['use_periods'] && empty($data['period_days'])) {
            return response()->json([
                'errors' => ['period_days' => ['تعداد روزهای دوره الزامی است وقتی استفاده از دوره‌ها فعال است']]
            ], 422);
        }
        
        // If use_periods is false, set period_days to null
        if (!$data['use_periods']) {
            $data['period_days'] = null;
        } else {
            $data['period_days'] = isset($data['period_days']) ? (int)$data['period_days'] : 30;
        }

        $package = Package::create($data);

        return response()->json([
            'message' => 'پکیج با موفقیت ایجاد شد',
            'data' => $package
        ], 201);
    }

    public function show($id)
    {
        $package = Package::findOrFail($id);
        
        // Add calculated attributes
        $package->formatted_price = $package->formatted_price;
        $package->status_text = $package->status_text;
        $package->status_badge_class = $package->status_badge_class;
        
        return response()->json([
            'data' => $package
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'duration_label' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_public' => 'required|in:true,false',
            'use_periods' => 'nullable',
            'period_days' => 'nullable|integer|min:1|required_if:use_periods,true,1',
        ], [
            'name.required' => 'نام پکیج الزامی است',
            'name.max' => 'نام پکیج نمی‌تواند بیش از 255 کاراکتر باشد',
            'duration_days.required' => 'مدت زمان الزامی است',
            'duration_days.integer' => 'مدت زمان باید عدد باشد',
            'duration_days.min' => 'مدت زمان باید حداقل 1 روز باشد',
            'duration_label.required' => 'برچسب مدت الزامی است',
            'duration_label.max' => 'برچسب مدت نمی‌تواند بیش از 255 کاراکتر باشد',
            'price.required' => 'قیمت الزامی است',
            'price.numeric' => 'قیمت باید عدد باشد',
            'price.min' => 'قیمت نمی‌تواند منفی باشد',
            'is_public.required' => 'وضعیت عمومی الزامی است',
            'is_public.in' => 'وضعیت عمومی باید فعال یا غیرفعال باشد',
            'period_days.integer' => 'تعداد روزهای دوره باید عدد باشد',
            'period_days.min' => 'تعداد روزهای دوره باید حداقل 1 روز باشد',
            'period_days.required_if' => 'تعداد روزهای دوره الزامی است وقتی استفاده از دوره‌ها فعال است',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $package = Package::findOrFail($id);
        $data = $request->all();
        $data['is_public'] = $data['is_public'] === 'true' || $data['is_public'] === true;
        
        // Handle use_periods - can be 1, 0, true, 'true', '1', or not set
        $data['use_periods'] = isset($data['use_periods']) && (
            $data['use_periods'] === true || 
            $data['use_periods'] === 'true' || 
            $data['use_periods'] === 1 || 
            $data['use_periods'] === '1' ||
            $data['use_periods'] === 'on'
        );
        
        // Validate period_days if use_periods is true
        if ($data['use_periods'] && empty($data['period_days'])) {
            return response()->json([
                'errors' => ['period_days' => ['تعداد روزهای دوره الزامی است وقتی استفاده از دوره‌ها فعال است']]
            ], 422);
        }
        
        // If use_periods is false, set period_days to null
        if (!$data['use_periods']) {
            $data['period_days'] = null;
        } else {
            $data['period_days'] = isset($data['period_days']) ? (int)$data['period_days'] : 30;
        }
        
        $package->update($data);

        // Add calculated attributes
        $package->formatted_price = $package->formatted_price;
        $package->status_text = $package->status_text;
        $package->status_badge_class = $package->status_badge_class;

        return response()->json([
            'message' => 'پکیج با موفقیت ویرایش شد',
            'data' => $package
        ]);
    }

    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        
        // Check if package is being used
        if ($package->organizationPackages()->count() > 0) {
            return response()->json([
                'message' => 'این پکیج در حال استفاده است و قابل حذف نیست'
            ], 422);
        }
        
        $package->delete();

        return response()->json([
            'message' => 'پکیج با موفقیت حذف شد'
        ]);
    }
}
