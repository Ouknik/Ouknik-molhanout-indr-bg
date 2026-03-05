@extends('admin.layouts.app')
@section('title', __('Order Details'))
@section('page-title', __('Order') . ' ' . $order->order_number)

@section('content')
<div class="row g-4">
    {{-- Order Info --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold"><i class="fas fa-info-circle me-2"></i>{{ __('Order Info') }}</div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted">{{ __('Order #') }}</td><td class="fw-bold">{{ $order->order_number }}</td></tr>
                    <tr><td class="text-muted">{{ __('Status') }}</td><td>
                        @php $statusColors = ['draft'=>'secondary','published'=>'primary','receiving_offers'=>'info','accepted'=>'success','preparing'=>'warning','on_delivery'=>'orange','delivered'=>'dark','cancelled'=>'danger']; @endphp
                        <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                    </td></tr>
                    <tr><td class="text-muted">{{ __('Shop') }}</td><td>{{ $order->user->shop->shop_name ?? $order->user->name }}</td></tr>
                    <tr><td class="text-muted">{{ __('Phone') }}</td><td>{{ $order->user->phone }}</td></tr>
                    <tr><td class="text-muted">{{ __('Delivery Date') }}</td><td>{{ $order->preferred_delivery_date ?? '-' }}</td></tr>
                    <tr><td class="text-muted">{{ __('Created') }}</td><td>{{ $order->created_at->format('d/m/Y H:i') }}</td></tr>
                    @if($order->notes)
                    <tr><td class="text-muted">{{ __('Notes') }}</td><td>{{ $order->notes }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Delivery Info --}}
        @if($order->delivery)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold"><i class="fas fa-truck me-2"></i>{{ __('Delivery') }}</div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted">{{ __('Distributor') }}</td><td>{{ $order->delivery->distributor->user->name ?? '-' }}</td></tr>
                    <tr><td class="text-muted">{{ __('Status') }}</td><td><span class="badge bg-info text-dark">{{ $order->delivery->status }}</span></td></tr>
                    <tr><td class="text-muted">{{ __('PIN') }}</td><td><code>{{ $order->delivery->confirmation_pin }}</code></td></tr>
                    @if($order->delivery->delivered_at)
                    <tr><td class="text-muted">{{ __('Delivered At') }}</td><td>{{ $order->delivery->delivered_at }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Order Items --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold"><i class="fas fa-list me-2"></i>{{ __('Order Items') }} ({{ $order->items->count() }})</div>
            <div class="card-body p-0">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Qty') }}</th>
                            <th>{{ __('Unit') }}</th>
                            <th>{{ __('Notes') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? $item->custom_product_name ?? '-' }}</td>
                            <td class="fw-bold">{{ $item->quantity }}</td>
                            <td>{{ $item->unit }}</td>
                            <td><small class="text-muted">{{ $item->notes ?? '-' }}</small></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Offers --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold"><i class="fas fa-tags me-2"></i>{{ __('Offers') }} ({{ $order->offers->count() }})</div>
            <div class="card-body p-0">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Distributor') }}</th>
                            <th>{{ __('Total') }}</th>
                            <th>{{ __('Delivery') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Flags') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($order->offers as $offer)
                        <tr class="{{ $offer->status == 'accepted' ? 'table-success' : '' }}">
                            <td>{{ $offer->distributor->user->name ?? '-' }}</td>
                            <td class="fw-bold">{{ number_format($offer->total_price, 2) }} MAD</td>
                            <td>{{ $offer->estimated_delivery_time ?? '-' }}</td>
                            <td><span class="badge bg-{{ $offer->status == 'accepted' ? 'success' : ($offer->status == 'rejected' ? 'danger' : 'warning') }}">{{ ucfirst($offer->status) }}</span></td>
                            <td>
                                @if($offer->is_cheapest) <span class="badge bg-success">{{ __('Cheapest') }}</span> @endif
                                @if($offer->is_fastest) <span class="badge bg-info text-dark">{{ __('Fastest') }}</span> @endif
                            </td>
                            <td><small>{{ $offer->created_at->format('d/m H:i') }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-3 text-muted">{{ __('No offers yet') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Disputes --}}
        @if($order->disputes && $order->disputes->count() > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold text-danger"><i class="fas fa-exclamation-triangle me-2"></i>{{ __('Disputes') }}</div>
            <div class="card-body">
                @foreach($order->disputes as $dispute)
                <div class="border rounded p-3 mb-2 {{ $dispute->status == 'resolved' ? 'bg-light' : 'border-danger' }}">
                    <div class="d-flex justify-content-between">
                        <strong>{{ ucfirst($dispute->type) }}</strong>
                        <span class="badge bg-{{ $dispute->status == 'resolved' ? 'success' : 'warning' }}">{{ ucfirst($dispute->status) }}</span>
                    </div>
                    <p class="mb-1 mt-2">{{ $dispute->description }}</p>
                    @if($dispute->admin_response)
                    <div class="bg-light p-2 rounded mt-1"><small><strong>{{ __('Admin') }}:</strong> {{ $dispute->admin_response }}</small></div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
