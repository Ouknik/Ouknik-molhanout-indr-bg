@extends('admin.layouts.app')
@section('title', __('Users Management'))
@section('page-title', __('Users'))

@section('content')
{{-- Filters --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="{{ __('Name, email or phone...') }}">
            </div>
            <div class="col-md-2">
                <select name="role" class="form-select form-select-sm">
                    <option value="">{{ __('All Roles') }}</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
                    <option value="shop_owner" {{ request('role') == 'shop_owner' ? 'selected' : '' }}>{{ __('Shop Owner') }}</option>
                    <option value="distributor" {{ request('role') == 'distributor' ? 'selected' : '' }}>{{ __('Distributor') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-undo"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Verified') }}</th>
                        <th>{{ __('Joined') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td><small>{{ $user->email }}</small></td>
                        <td><small>{{ $user->phone }}</small></td>
                        <td>
                            @php $roleColors = ['admin' => 'danger', 'shop_owner' => 'primary', 'distributor' => 'success']; @endphp
                            <span class="badge bg-{{ $roleColors[$user->role] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($user->email_verified_at)
                                <i class="fas fa-check-circle text-success"></i>
                            @else
                                <i class="fas fa-times-circle text-danger"></i>
                            @endif
                        </td>
                        <td><small>{{ $user->created_at->format('d/m/Y') }}</small></td>
                        <td>
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                            <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-{{ $user->is_active ? 'warning' : 'success' }}" title="{{ $user->is_active ? __('Deactivate') : __('Activate') }}">
                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">{{ __('No users found.') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-transparent">{{ $users->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
