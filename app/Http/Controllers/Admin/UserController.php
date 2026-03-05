<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shop;
use App\Models\Distributor;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(int $id)
    {
        $user = User::with(['shop', 'distributor'])->findOrFail($id);

        $stats = [];

        if ($user->isShopOwner() && $user->shop) {
            $stats['total_orders'] = $user->shop->orders()->count();
            $stats['total_customers'] = $user->shop->customers()->count();
            $stats['total_credits'] = $user->shop->credits()->sum('amount');
        }

        if ($user->isDistributor() && $user->distributor) {
            $stats['total_offers'] = $user->distributor->offers()->count();
            $stats['accepted_offers'] = $user->distributor->offers()->where('status', 'accepted')->count();
            $stats['total_deliveries'] = $user->distributor->deliveries()->count();
        }

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function toggleStatus(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', 'User status updated.');
    }

    public function verifyShop(int $shopId)
    {
        $shop = Shop::findOrFail($shopId);
        $shop->update(['is_verified' => true]);
        return back()->with('success', 'Shop verified.');
    }

    public function verifyDistributor(int $distributorId)
    {
        $distributor = Distributor::findOrFail($distributorId);
        $distributor->update(['is_verified' => true]);
        return back()->with('success', 'Distributor verified.');
    }
}
