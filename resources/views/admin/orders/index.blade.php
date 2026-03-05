@extends('admin.layouts.app')
@section('title', __('Orders Management'))
@section('page-title', __('Orders'))

@section('content')
{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="{{ __('Order number or shop...') }}">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('All Status') }}</option>
                    @foreach(['draft','published','receiving_offers','accepted','preparing','on_delivery','delivered','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm" placeholder="{{ __('From') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm" placeholder="{{ __('To') }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Stats Row --}}
<div class="row g-3 mb-3">
    @php
        $statusCounts = [
            'published' => ['icon' => 'bullhorn', 'color' => 'primary'],
            'receiving_offers' => ['icon' => 'handshake', 'color' => 'info'],
            'accepted' => ['icon' => 'check-circle', 'color' => 'success'],
            'on_delivery' => ['icon' => 'truck', 'color' => 'warning'],
            'delivered' => ['icon' => 'flag-checkered', 'color' => 'secondary'],
        ];
    @endphp
    @foreach($statusCounts as $status => $meta)
    <div class="col">
        <div class="card border-0 shadow-sm text-center py-2">
            <i class="fas fa-{{ $meta['icon'] }} text-{{ $meta['color'] }} mb-1"></i>
            <div class="fw-bold">{{ $stats[$status] ?? 0 }}</div>
            <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $status)) }}</small>
        </div>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Order #') }}</th>
                        <th>{{ __('Shop') }}</th>
                        <th>{{ __('Items') }}</th>
                        <th>{{ __('Offers') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Delivery') }}</th>
                        <th>{{ __('Created') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="fw-semibold">{{ $order->order_number }}</td>
                        <td>{{ $order->user->shop->shop_name ?? $order->user->name }}</td>
                        <td><span class="badge bg-light text-dark">{{ $order->items_count ?? $order->items->count() }}</span></td>
                        <td><span class="badge bg-light text-dark">{{ $order->offers_count ?? $order->offers->count() }}</span></td>
                        <td>
                            @php
                                $statusColors = [
                                    'draft' => 'secondary', 'published' => 'primary', 'receiving_offers' => 'info',
                                    'accepted' => 'success', 'preparing' => 'warning', 'on_delivery' => 'orange',
                                    'delivered' => 'dark', 'cancelled' => 'danger',
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        </td>
                        <td>{{ $order->preferred_delivery_time ? \Carbon\Carbon::parse($order->preferred_delivery_time)->format('d/m') : '-' }}</td>
                        <td><small>{{ $order->created_at->format('d/m/Y H:i') }}</small></td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">{{ __('No orders found.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($orders->hasPages())
    <div class="card-footer bg-transparent">{{ $orders->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
