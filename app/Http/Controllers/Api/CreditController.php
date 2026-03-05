<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Credit;
use App\Models\CreditTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreditController extends Controller
{
    // ─── CUSTOMERS ───

    /**
     * List shop's customers.
     */
    public function customers(Request $request): JsonResponse
    {
        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'No shop found'], 404);
        }

        $query = Customer::where('shop_id', $shop->id);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $customers = $query->orderBy('name')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data'    => $customers->items(),
            'meta'    => [
                'current_page' => $customers->currentPage(),
                'last_page'    => $customers->lastPage(),
                'per_page'     => $customers->perPage(),
                'total'        => $customers->total(),
            ],
        ]);
    }

    /**
     * Create a new customer.
     */
    public function createCustomer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes'   => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'No shop found'], 404);
        }

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
            'notes'   => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('credits.customer_created'),
            'data'    => $customer,
        ], 201);
    }

    /**
     * Show customer with credits summary.
     */
    public function showCustomer(int $id, Request $request): JsonResponse
    {
        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'No shop found'], 404);
        }

        $customer = Customer::where('shop_id', $shop->id)
            ->with(['credits' => fn($q) => $q->latest()])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $customer,
        ]);
    }

    // ─── CREDITS (DEBTS) ───

    /**
     * Add a new credit/debt for a customer.
     */
    public function addCredit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'description' => 'required|string|max:500',
            'amount'      => 'required|numeric|min:0.01',
            'due_date'    => 'nullable|date',
            'notes'       => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'No shop found'], 404);
        }

        // Verify customer belongs to this shop
        $customer = Customer::where('shop_id', $shop->id)
            ->findOrFail($request->customer_id);

        $credit = DB::transaction(function () use ($request, $shop, $customer) {
            $credit = Credit::create([
                'customer_id' => $customer->id,
                'shop_id'     => $shop->id,
                'description' => $request->description,
                'amount'      => $request->amount,
                'due_date'    => $request->due_date,
                'notes'       => $request->notes,
            ]);

            // Record debt transaction
            CreditTransaction::create([
                'credit_id'   => $credit->id,
                'customer_id' => $customer->id,
                'shop_id'     => $shop->id,
                'type'        => 'debt',
                'amount'      => $request->amount,
                'description' => $request->description,
            ]);

            // Update customer total debt
            $customer->recalculateDebt();

            return $credit;
        });

        return response()->json([
            'success' => true,
            'message' => __('credits.added'),
            'data'    => $credit,
        ], 201);
    }

    /**
     * Record a payment for a credit.
     */
    public function addPayment(int $creditId, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string',
            'description'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'No shop found'], 404);
        }

        $credit = Credit::where('shop_id', $shop->id)
            ->where('status', '!=', 'paid')
            ->findOrFail($creditId);

        if ($request->amount > $credit->remaining) {
            return response()->json([
                'success' => false,
                'message' => __('credits.payment_exceeds'),
            ], 400);
        }

        $transaction = $credit->addPayment(
            $request->amount,
            $request->payment_method,
            $request->description
        );

        return response()->json([
            'success' => true,
            'message' => __('credits.payment_recorded'),
            'data'    => [
                'transaction' => $transaction,
                'credit'      => $credit->fresh(),
                'customer'    => $credit->customer->fresh(),
            ],
        ]);
    }

    /**
     * Get credit transactions history.
     */
    public function transactions(int $customerId, Request $request): JsonResponse
    {
        $shop = $request->user()->shop;

        if (!$shop) {
            return response()->json(['success' => false, 'message' => 'No shop found'], 404);
        }

        $customer = Customer::where('shop_id', $shop->id)->findOrFail($customerId);

        $transactions = CreditTransaction::where('customer_id', $customerId)
            ->with('credit')
            ->latest()
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data'    => $transactions->items(),
            'meta'    => [
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
                'total'        => $transactions->total(),
            ],
        ]);
    }
}
