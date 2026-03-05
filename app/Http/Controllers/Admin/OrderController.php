<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Dispute;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['shop', 'user'])
            ->withCount(['items', 'offers']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->latest()->paginate(20);

        // Status counts for stats row
        $stats = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(int $id)
    {
        $order = Order::with([
            'items.product',
            'offers.distributor',
            'offers.items.product',
            'shop',
            'user',
            'delivery',
        ])->findOrFail($id);

        return view('admin.orders.show', compact('order'));
    }

    // ─── DISPUTES ───

    public function disputes(Request $request)
    {
        $query = Dispute::with(['order', 'raisedByUser', 'againstUser']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $disputes = $query->latest()->paginate(20);

        return view('admin.disputes.index', compact('disputes'));
    }

    public function resolveDispute(Request $request, int $id)
    {
        $request->validate([
            'resolution' => 'required|string',
            'status'     => 'required|in:investigating,resolved,closed',
        ]);

        $dispute = Dispute::findOrFail($id);
        $dispute->update([
            'resolution'  => $request->resolution,
            'status'      => $request->status,
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Dispute resolved.');
    }
}
