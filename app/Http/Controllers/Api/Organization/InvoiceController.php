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
use niklasravnsborg\LaravelPdf\Facades\Pdf;

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

    /**
     * Export invoice as PDF
     */
    public function exportPdf(Invoice $invoice)
    {
        $user = auth('organization_api')->user();
        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Verify invoice belongs to the organization
        if ($invoice->organization_id !== $user->organization_id) {
            abort(403, 'Unauthorized');
        }

        // Load relationships
        $invoice->load(['building', 'items', 'organization']);
        $organization = $invoice->organization;
        $building = $invoice->building;

        if (!$organization || !$building) {
            abort(404, 'سازمان یا ساختمان یافت نشد');
        }

        // Get invoice date in Jalali format
        $invoiceDate = $invoice->invoice_date 
            ? Jalalian::forge($invoice->invoice_date)->format('Y/m/d')
            : Jalalian::forge($invoice->created_at)->format('Y/m/d');

        // Convert total to Persian words
        $totalInWords = $this->numberToPersianWords($invoice->total);
        
        // Generate PDF
        $pdf = Pdf::loadView('organization.financial.invoices.pdf', [
            'invoice' => $invoice,
            'organization' => $organization,
            'building' => $building,
            'invoiceDate' => $invoiceDate,
            'totalInWords' => $totalInWords,
        ]);

        $filename = 'فاکتور_' . $invoice->invoice_number . '_' . $building->name . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Convert number to Persian words
     */
    private function numberToPersianWords($number)
    {
        $ones = [
            0 => '',
            1 => 'یک',
            2 => 'دو',
            3 => 'سه',
            4 => 'چهار',
            5 => 'پنج',
            6 => 'شش',
            7 => 'هفت',
            8 => 'هشت',
            9 => 'نه',
            10 => 'ده',
            11 => 'یازده',
            12 => 'دوازده',
            13 => 'سیزده',
            14 => 'چهارده',
            15 => 'پانزده',
            16 => 'شانزده',
            17 => 'هفده',
            18 => 'هجده',
            19 => 'نوزده',
        ];

        $tens = [
            2 => 'بیست',
            3 => 'سی',
            4 => 'چهل',
            5 => 'پنجاه',
            6 => 'شصت',
            7 => 'هفتاد',
            8 => 'هشتاد',
            9 => 'نود',
        ];

        $hundreds = [
            1 => 'یکصد',
            2 => 'دویست',
            3 => 'سیصد',
            4 => 'چهارصد',
            5 => 'پانصد',
            6 => 'ششصد',
            7 => 'هفتصد',
            8 => 'هشتصد',
            9 => 'نهصد',
        ];

        if ($number == 0) {
            return 'صفر';
        }

        // Handle negative numbers
        $isNegative = $number < 0;
        $number = abs($number);

        // Split into integer and decimal parts
        $parts = explode('.', (string)$number);
        $integerPart = (int)$parts[0];
        $decimalPart = isset($parts[1]) ? (int)substr($parts[1], 0, 2) : 0;

        $result = '';

        // Convert integer part
        if ($integerPart >= 1000000000) {
            $billions = (int)($integerPart / 1000000000);
            $result .= $this->convertThreeDigits($billions, $ones, $tens, $hundreds) . ' میلیارد ';
            $integerPart = $integerPart % 1000000000;
        }

        if ($integerPart >= 1000000) {
            $millions = (int)($integerPart / 1000000);
            $result .= $this->convertThreeDigits($millions, $ones, $tens, $hundreds) . ' میلیون ';
            $integerPart = $integerPart % 1000000;
        }

        if ($integerPart >= 1000) {
            $thousands = (int)($integerPart / 1000);
            if ($thousands == 1) {
                $result .= 'هزار ';
            } else {
                $result .= $this->convertThreeDigits($thousands, $ones, $tens, $hundreds) . ' هزار ';
            }
            $integerPart = $integerPart % 1000;
        }

        if ($integerPart > 0) {
            $result .= $this->convertThreeDigits($integerPart, $ones, $tens, $hundreds);
        }

        // Remove trailing space
        $result = trim($result);

        // Add negative prefix if needed
        if ($isNegative) {
            $result = 'منفی ' . $result;
        }

        return $result;
    }

    /**
     * Convert three-digit number to words
     */
    private function convertThreeDigits($number, $ones, $tens, $hundreds)
    {
        $result = '';

        if ($number >= 100) {
            $hundred = (int)($number / 100);
            $result .= $hundreds[$hundred] . ' ';
            $number = $number % 100;
        }

        if ($number >= 20) {
            $ten = (int)($number / 10);
            $result .= $tens[$ten] . ' ';
            $number = $number % 10;
        }

        if ($number > 0) {
            $result .= $ones[$number];
        }

        return trim($result);
    }
}
