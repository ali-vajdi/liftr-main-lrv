<?php

namespace App\Http\Controllers\Api\Organization;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Building;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    /**
     * Get all invoices for the organization
     */
    public function index(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $query = Invoice::with(['building', 'items'])
            ->where('organization_id', $user->organization_id);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('building', function($bq) use ($search) {
                      $bq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by building
        if ($request->has('building_id') && $request->building_id) {
            $query->where('building_id', $request->building_id);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);

        $items = collect($invoices->items())->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'building_id' => $invoice->building_id,
                'building_name' => $invoice->building->name ?? '',
                'subtotal' => $invoice->subtotal,
                'discount' => $invoice->discount,
                'tax_percentage' => $invoice->tax_percentage,
                'tax_amount' => $invoice->tax_amount,
                'total' => $invoice->total,
                'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->toIso8601String() : null,
                'invoice_date_jalali' => $invoice->invoice_date ? Jalalian::forge($invoice->invoice_date)->format('Y/m/d') : null,
                'items_count' => $invoice->items->count(),
                'created_at' => $invoice->created_at->toIso8601String(),
                'created_at_jalali' => Jalalian::forge($invoice->created_at)->format('Y/m/d'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ]
        ]);
    }

    /**
     * Get a single invoice with items
     */
    public function show(Invoice $invoice)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Verify invoice belongs to the organization
        if ($invoice->organization_id !== $user->organization_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $invoice->load(['building', 'items']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'building_id' => $invoice->building_id,
                'building' => [
                    'id' => $invoice->building->id,
                    'name' => $invoice->building->name,
                    'manager_name' => $invoice->building->manager_name,
                    'manager_phone' => $invoice->building->manager_phone,
                    'address' => $invoice->building->address,
                ],
                'subtotal' => $invoice->subtotal,
                'discount' => $invoice->discount,
                'tax_percentage' => $invoice->tax_percentage,
                'tax_amount' => $invoice->tax_amount,
                'total' => $invoice->total,
                'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->toIso8601String() : null,
                'invoice_date_jalali' => $invoice->invoice_date ? Jalalian::forge($invoice->invoice_date)->format('Y/m/d') : null,
                'items' => $invoice->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total' => $item->total,
                        'order' => $item->order,
                    ];
                }),
                'created_at' => $invoice->created_at->toIso8601String(),
                'created_at_jalali' => Jalalian::forge($invoice->created_at)->format('Y/m/d H:i'),
            ]
        ]);
    }

    /**
     * Create a new invoice
     */
    public function store(Request $request)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'building_id' => 'required|exists:buildings,id',
            'discount' => 'nullable|numeric|min:0',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'invoice_date' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify building belongs to the organization
        $building = Building::where('id', $request->building_id)
            ->where('organization_id', $user->organization_id)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            // Calculate subtotal from items
            $subtotal = 0;
            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemTotal;
            }

            // Calculate discount
            $discount = $request->discount ?? 0;
            $subtotalAfterDiscount = $subtotal - $discount;

            // Calculate tax
            $taxPercentage = $request->tax_percentage ?? 0;
            $taxAmount = ($subtotalAfterDiscount * $taxPercentage) / 100;

            // Calculate total
            $total = $subtotalAfterDiscount + $taxAmount;

            // Parse invoice date (Jalali format: Y/m/d)
            $invoiceDate = null;
            if ($request->invoice_date) {
                try {
                    $dateParts = explode('/', $request->invoice_date);
                    if (count($dateParts) === 3) {
                        $jalaliDate = Jalalian::fromFormat('Y/m/d', $request->invoice_date);
                        $invoiceDate = $jalaliDate->toCarbon();
                    }
                } catch (\Exception $e) {
                    // If date parsing fails, use current date
                    $invoiceDate = Carbon::now();
                }
            } else {
                $invoiceDate = Carbon::now();
            }

            // Get organization to generate invoice number
            $organization = $user->organization;
            if (!$organization) {
                throw new \Exception('Organization not found');
            }

            // Create invoice
            $invoice = Invoice::create([
                'organization_id' => $user->organization_id,
                'building_id' => $building->id,
                'invoice_number' => $organization->generateInvoiceNumber(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax_percentage' => $taxPercentage,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'invoice_date' => $invoiceDate,
            ]);

            // Create invoice items
            $order = 0;
            foreach ($request->items as $itemData) {
                $itemTotal = $itemData['quantity'] * $itemData['unit_price'];
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total' => $itemTotal,
                    'order' => $order++,
                ]);
            }

            DB::commit();

            $invoice->load(['building', 'items']);

            return response()->json([
                'success' => true,
                'message' => 'فاکتور با موفقیت ایجاد شد',
                'data' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد فاکتور: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get buildings for invoice creation (dropdown)
     */
    public function getBuildings()
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $buildings = Building::where('organization_id', $user->organization_id)
            ->select('id', 'name', 'manager_name', 'manager_phone')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $buildings
        ]);
    }
}
