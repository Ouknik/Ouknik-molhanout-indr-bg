@extends('admin.layouts.app')
@section('title', __('Edit Product'))
@section('page-title', __('Edit Product') . ': ' . $product->name_fr)

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Name (French)') }} *</label>
                    <input type="text" name="name_fr" value="{{ old('name_fr', $product->name_fr) }}" class="form-control @error('name_fr') is-invalid @enderror" required>
                    @error('name_fr') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Name (Arabic)') }} *</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar', $product->name_ar) }}" dir="rtl" class="form-control @error('name_ar') is-invalid @enderror" required>
                    @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Name (English)') }} *</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $product->name_en) }}" class="form-control @error('name_en') is-invalid @enderror" required>
                    @error('name_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Category') }} *</label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">{{ __('Select...') }}</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name_fr }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Unit') }} *</label>
                    <select name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                        @foreach(['box','kg','pack','bottle','piece','bag','liter'] as $u)
                        <option value="{{ $u }}" {{ old('unit', $product->unit) == $u ? 'selected' : '' }}>{{ ucfirst(__($u)) }}</option>
                        @endforeach
                    </select>
                    @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Reference Price (MAD)') }}</label>
                    <input type="number" name="reference_price" value="{{ old('reference_price', $product->reference_price) }}" step="0.01" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Barcode') }}</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Image') }}</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    @if($product->image)
                    <div class="mt-2"><img src="{{ asset('storage/' . $product->image) }}" width="80" class="rounded"></div>
                    @endif
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ __('Active') }}</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Description (French)') }}</label>
                    <textarea name="description_fr" rows="3" class="form-control">{{ old('description_fr', $product->description_fr) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Description (Arabic)') }}</label>
                    <textarea name="description_ar" rows="3" dir="rtl" class="form-control">{{ old('description_ar', $product->description_ar) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Description (English)') }}</label>
                    <textarea name="description_en" rows="3" class="form-control">{{ old('description_en', $product->description_en) }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('Update Product') }}</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
