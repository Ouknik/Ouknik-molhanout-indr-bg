@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('page-title', __('Dashboard'))

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">{{ __('Total Users') }}</div>
                    <div class="value">{{ number_format($stats['total_users']) }}</div>
                </div>
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">{{ __('Shops') }}</div>
                    <div class="value">{{ number_format($stats['total_shops']) }}</div>
                </div>
                <div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-store"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">{{ __('Distributors') }}</div>
                    <div class="value">{{ number_format($stats['total_distributors']) }}</div>
                </div>
                <div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-truck"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">{{ __('Total Orders') }}</div>
                    <div class="value">{{ number_format($stats['total_orders']) }}</div>
                </div>
                <div class="icon bg-info bg-opacity-10 text-info"><i class="fas fa-shopping-cart"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">{{ __('Active Orders') }}</div>
                    <div class="value text-warning">{{ number_format($stats['active_orders']) }}</div>
                </div>
                <div class="icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-spinner"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">{{ __('Delivered') }}</div>
                    <div class="value text-success">{{ number_format($stats['delivered_orders']) }}</div>
                </div>
                <div class="icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">{{ __('Total Offers') }}</div>
                    <div class="value">{{ number_format($stats['total_offers']) }}</div>
                </div>
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-hand-holding-usd"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">{{ __('Revenue') }}</div>
                    <div class="value text-primary">{{ number_format($stats['revenue'], 2) }} MAD</div>
                </div>
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-coins"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">{{ __('Recent Orders') }}</h6>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>{{ __('Order #') }}</th>
                            <th>{{ __('Shop') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order->id) }}">{{ $order->order_number }}</a></td>
                            <td>{{ $order->shop->shop_name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-status
                                    @if($order->status === 'delivered') bg-success
                                    @elseif(in_array($order->status, ['published', 'receiving_offers'])) bg-info
                                    @elseif($order->status === 'cancelled') bg-danger
                                    @else bg-warning text-dark
                                    @endif
                                ">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                            </td>
                            <td>{{ $order->total_amount ? number_format($order->total_amount, 2) . ' MAD' : '-' }}</td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No orders yet') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
                <h6 class="mb-0 fw-bold">{{ __('Recent Users') }}</h6>
            </div>
            <div class="card-body p-0">
                @forelse($recentUsers as $user)
                <div class="d-flex align-items-center p-3 border-bottom">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center"
                         style="width:40px;height:40px;font-size:0.9rem;font-weight:600">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="ms-3">
                        <div class="fw-semibold">{{ $user->name }}</div>
                        <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $user->role)) }} &bull; {{ $user->created_at->diffForHumans() }}</small>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">{{ __('No users yet') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
