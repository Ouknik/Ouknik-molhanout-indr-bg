<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Offer;
use App\Models\Distributor;
use App\Models\Shop;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'        => User::count(),
            'total_shops'        => Shop::count(),
            'total_distributors' => Distributor::count(),
            'total_orders'       => Order::count(),
            'active_orders'      => Order::whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'total_offers'       => Offer::count(),
            'delivered_orders'   => Order::where('status', 'delivered')->count(),
            'revenue'            => Order::where('status', 'delivered')->sum('total_amount'),
        ];

        $recentOrders = Order::with(['shop', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        $recentUsers = User::latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'recentUsers'));
    }
}
