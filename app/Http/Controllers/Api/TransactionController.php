<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\PackagePayment;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $search = $request->get('search');
        $type = $request->get('type'); // income, expense
        $status = $request->get('status'); // pending, completed, failed, cancelled
        $organizationId = $request->get('organization_id');
        $sourceType = $request->get('source_type'); // package, etc.

        $query = Transaction::with(['paymentMethod', 'organization', 'moderator', 'transactionable']);

        // Filter by type
        if ($type) {
            $query->where('type', $type);
        }

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by organization
        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        // Filter by source type (package payments)
        if ($sourceType === 'package') {
            $query->where('transactionable_type', PackagePayment::class);
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('organization', function ($orgQ) use ($search) {
                      $orgQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Add calculated attributes
        $items = $transactions->items();
        foreach ($items as $item) {
            $item->formatted_amount = $item->formatted_amount;
            $item->type_text = $item->type_text;
            $item->status_text = $item->status_text;
            $item->status_badge_class = $item->status_badge_class;
            $item->source_type_text = $item->source_type_text;
        }

        // Calculate totals (apply same filters)
        $summaryQuery = Transaction::where('status', Transaction::STATUS_COMPLETED);
        
        if ($organizationId) {
            $summaryQuery->where('organization_id', $organizationId);
        }
        
        if ($sourceType === 'package') {
            $summaryQuery->where('transactionable_type', PackagePayment::class);
        }

        $totalIncome = (clone $summaryQuery)->where('type', Transaction::TYPE_INCOME)->sum('amount');
        $totalExpense = (clone $summaryQuery)->where('type', Transaction::TYPE_EXPENSE)->sum('amount');

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'net_amount' => $totalIncome - $totalExpense,
                'formatted_total_income' => number_format($totalIncome, 0) . ' تومان',
                'formatted_total_expense' => number_format($totalExpense, 0) . ' تومان',
                'formatted_net_amount' => number_format($totalIncome - $totalExpense, 0) . ' تومان',
            ]
        ]);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['paymentMethod', 'organization', 'moderator', 'transactionable'])
            ->findOrFail($id);

        // Add calculated attributes
        $transaction->formatted_amount = $transaction->formatted_amount;
        $transaction->type_text = $transaction->type_text;
        $transaction->status_text = $transaction->status_text;
        $transaction->status_badge_class = $transaction->status_badge_class;
        $transaction->source_type_text = $transaction->source_type_text;

        return response()->json([
            'data' => $transaction
        ]);
    }
}
