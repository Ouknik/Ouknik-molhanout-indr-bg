@extends('admin.layouts.app')
@section('title', __('Add Product'))
@section('page-title', __('Add New Product'))

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Name (French)') }} *</label>
                    <input type="text" name="name_fr" value="{{ old('name_fr') }}" class="form-control @error('name_fr') is-invalid @enderror" required>
                    @error('name_fr') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Name (Arabic)') }} *</label>
                    <input type="text" name="name_ar" value="{{ old('name_ar') }}" dir="rtl" class="form-control @error('name_ar') is-invalid @enderror" required>
                    @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Name (English)') }} *</label>
                    <input type="text" name="name_en" value="{{ old('name_en') }}" class="form-control @error('name_en') is-invalid @enderror" required>
                    @error('name_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Category') }} *</label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">{{ __('Select...') }}</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name_fr }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Unit') }} *</label>
                    <select name="unit" class="form-select @error('unit') is-invalid @enderror" required>
                        <option value="">{{ __('Select...') }}</option>
                        <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>{{ __('Box') }}</option>
                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>{{ __('Kg') }}</option>
                        <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>{{ __('Pack') }}</option>
                        <option value="bottle" {{ old('unit') == 'bottle' ? 'selected' : '' }}>{{ __('Bottle') }}</option>
                        <option value="piece" {{ old('unit') == 'piece' ? 'selected' : '' }}>{{ __('Piece') }}</option>
                        <option value="bag" {{ old('unit') == 'bag' ? 'selected' : '' }}>{{ __('Bag') }}</option>
                        <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>{{ __('Liter') }}</option>
                    </select>
                    @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Reference Price (MAD)') }}</label>
                    <input type="number" name="reference_price" value="{{ old('reference_price') }}" step="0.01" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Barcode') }}</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Image') }}</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                        <label class="form-check-label">{{ __('Active') }}</label>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Description (French)') }}</label>
                    <textarea name="description_fr" rows="3" class="form-control">{{ old('description_fr') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Description (Arabic)') }}</label>
                    <textarea name="description_ar" rows="3" dir="rtl" class="form-control">{{ old('description_ar') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Description (English)') }}</label>
                    <textarea name="description_en" rows="3" class="form-control">{{ old('description_en') }}</textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('Save Product') }}</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
</div>
@endsection
