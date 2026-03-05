@extends('admin.layouts.app')
@section('title', __('User Details'))
@section('page-title', __('User') . ': ' . $user->name)

@section('content')
<div class="row g-4">
    {{-- Profile Card --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px;">
                    <i class="fas fa-user fa-2x text-primary"></i>
                </div>
                <h5>{{ $user->name }}</h5>
                @php $roleColors = ['admin' => 'danger', 'shop_owner' => 'primary', 'distributor' => 'success']; @endphp
                <span class="badge bg-{{ $roleColors[$user->role] ?? 'secondary' }} mb-2">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                <ul class="list-unstyled text-start mt-3">
                    <li class="mb-2"><i class="fas fa-envelope text-muted me-2"></i>{{ $user->email }}</li>
                    <li class="mb-2"><i class="fas fa-phone text-muted me-2"></i>{{ $user->phone }}</li>
                    <li class="mb-2"><i class="fas fa-language text-muted me-2"></i>{{ strtoupper($user->preferred_language ?? 'fr') }}</li>
                    <li class="mb-2"><i class="fas fa-calendar text-muted me-2"></i>{{ $user->created_at->format('d/m/Y H:i') }}</li>
                    <li class="mb-2">
                        <i class="fas fa-circle me-2 {{ $user->is_active ? 'text-success' : 'text-danger' }}"></i>
                        {{ $user->is_active ? __('Active') : __('Inactive') }}
                    </li>
                </ul>

                <div class="mt-3 d-flex gap-2 justify-content-center">
                    <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}">
                        @csrf
                        <button class="btn btn-sm btn-{{ $user->is_active ? 'warning' : 'success' }}">
                            <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                            {{ $user->is_active ? __('Deactivate') : __('Activate') }}
                        </button>
                    </form>
                    @if($user->role === 'shop_owner' && $user->shop && !$user->shop->is_verified)
                    <form method="POST" action="{{ route('admin.shops.verify', $user->shop->id) }}">
                        @csrf
                        <button class="btn btn-sm btn-info text-white"><i class="fas fa-shield-alt"></i> {{ __('Verify') }}</button>
                    </form>
                    @elseif($user->role === 'distributor' && $user->distributor && !$user->distributor->is_verified)
                    <form method="POST" action="{{ route('admin.distributors.verify', $user->distributor->id) }}">
                        @csrf
                        <button class="btn btn-sm btn-info text-white"><i class="fas fa-shield-alt"></i> {{ __('Verify') }}</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Details --}}
    <div class="col-md-8">
        @if($user->role === 'shop_owner' && $user->shop)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold"><i class="fas fa-store me-2"></i>{{ __('Shop Details') }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><strong>{{ __('Shop Name') }}:</strong> {{ $user->shop->shop_name }}</div>
                    <div class="col-md-6"><strong>{{ __('City') }}:</strong> {{ $user->shop->city }}</div>
                    <div class="col-md-6 mt-2"><strong>{{ __('Address') }}:</strong> {{ $user->shop->address }}</div>
                    <div class="col-md-6 mt-2">
                        <strong>{{ __('Verified') }}:</strong>
                        @if($user->shop->is_verified)
                            <span class="badge bg-success">{{ __('Yes') }}</span>
                        @else
                            <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                        @endif
                    </div>
                    @if($user->shop->latitude && $user->shop->longitude)
                    <div class="col-12 mt-2"><strong>{{ __('GPS') }}:</strong> {{ $user->shop->latitude }}, {{ $user->shop->longitude }}</div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($user->role === 'distributor' && $user->distributor)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-transparent fw-semibold"><i class="fas fa-truck me-2"></i>{{ __('Distributor Details') }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><strong>{{ __('Company') }}:</strong> {{ $user->distributor->company_name }}</div>
                    <div class="col-md-6"><strong>{{ __('City') }}:</strong> {{ $user->distributor->city }}</div>
                    <div class="col-md-6 mt-2"><strong>{{ __('Service Radius') }}:</strong> {{ $user->distributor->service_radius_km }} km</div>
                    <div class="col-md-6 mt-2"><strong>{{ __('Rating') }}:</strong> {{ $user->distributor->rating ?? 'N/A' }} / 5</div>
                    <div class="col-md-6 mt-2">
                        <strong>{{ __('Verified') }}:</strong>
                        @if($user->distributor->is_verified)
                            <span class="badge bg-success">{{ __('Yes') }}</span>
                        @else
                            <span class="badge bg-warning text-dark">{{ __('Pending') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Recent Activity --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold"><i class="fas fa-clock me-2"></i>{{ __('Recent Activity') }}</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if($user->role === 'shop_owner')
                        @forelse($user->orders()->latest()->take(10)->get() as $order)
                        <tr>
                            <td><span class="badge bg-primary">{{ __('Order') }}</span></td>
                            <td>{{ $order->order_number }}</td>
                            <td><span class="badge bg-info text-dark">{{ $order->status }}</span></td>
                            <td>{{ $order->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-muted text-center py-3">{{ __('No activity yet') }}</td></tr>
                        @endforelse
                    @elseif($user->role === 'distributor')
                        @forelse($user->offers()->latest()->take(10)->get() as $offer)
                        <tr>
                            <td><span class="badge bg-success">{{ __('Offer') }}</span></td>
                            <td>{{ $offer->order->order_number ?? '-' }}</td>
                            <td><span class="badge bg-info text-dark">{{ $offer->status }}</span></td>
                            <td>{{ $offer->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-muted text-center py-3">{{ __('No activity yet') }}</td></tr>
                        @endforelse
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
