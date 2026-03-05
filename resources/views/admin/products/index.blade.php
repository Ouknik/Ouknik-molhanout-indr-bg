@extends('admin.layouts.app')
@section('title', __('Products'))
@section('page-title', __('Product Catalog'))

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
        <div>
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="{{ __('Search products...') }}">
                <select name="category_id" class="form-select form-select-sm" style="width:200px">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name_fr }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> {{ __('Add Product') }}
        </a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th width="50"></th>
                    <th>{{ __('Name (FR)') }}</th>
                    <th>{{ __('Name (AR)') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Unit') }}</th>
                    <th>{{ __('Ref. Price') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" width="40" height="40" class="rounded" alt="">
                        @else
                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px">
                            <i class="fas fa-box text-muted"></i>
                        </div>
                        @endif
                    </td>
                    <td>{{ $product->name_fr }}</td>
                    <td dir="rtl">{{ $product->name_ar }}</td>
                    <td>{{ $product->category->name_fr ?? '-' }}</td>
                    <td>{{ ucfirst($product->unit) }}</td>
                    <td>{{ $product->reference_price ? number_format($product->reference_price, 2) . ' MAD' : '-' }}</td>
                    <td>
                        <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $product->is_active ? __('Active') : __('Disabled') }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('admin.products.toggle', $product->id) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-outline-{{ $product->is_active ? 'warning' : 'success' }}">
                                    <i class="fas fa-{{ $product->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">{{ __('No products found') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="card-footer bg-white">
        {{ $products->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
