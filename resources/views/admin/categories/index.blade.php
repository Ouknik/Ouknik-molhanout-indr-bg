@extends('admin.layouts.app')
@section('title', __('Categories'))
@section('page-title', __('Categories'))

@section('content')
<div class="row g-4">
    {{-- Category Form --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-semibold">
                <i class="fas fa-plus-circle me-2"></i>{{ isset($editCategory) ? __('Edit Category') : __('Add Category') }}
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($editCategory) ? route('admin.categories.update', $editCategory) : route('admin.categories.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if(isset($editCategory)) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Name (French)') }} *</label>
                        <input type="text" name="name_fr" value="{{ old('name_fr', $editCategory->name_fr ?? '') }}" class="form-control @error('name_fr') is-invalid @enderror" required>
                        @error('name_fr') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Name (Arabic)') }} *</label>
                        <input type="text" name="name_ar" value="{{ old('name_ar', $editCategory->name_ar ?? '') }}" dir="rtl" class="form-control @error('name_ar') is-invalid @enderror" required>
                        @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Name (English)') }} *</label>
                        <input type="text" name="name_en" value="{{ old('name_en', $editCategory->name_en ?? '') }}" class="form-control @error('name_en') is-invalid @enderror" required>
                        @error('name_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Image') }}</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        @if(isset($editCategory) && $editCategory->image)
                        <div class="mt-2"><img src="{{ asset('storage/' . $editCategory->image) }}" width="60" class="rounded"></div>
                        @endif
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $editCategory->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ __('Active') }}</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-1"></i>{{ isset($editCategory) ? __('Update') : __('Add Category') }}
                    </button>
                    @if(isset($editCategory))
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary w-100 mt-2">{{ __('Cancel') }}</a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Categories List --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">{{ __('Image') }}</th>
                            <th>{{ __('French') }}</th>
                            <th>{{ __('Arabic') }}</th>
                            <th>{{ __('English') }}</th>
                            <th>{{ __('Products') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td>
                                @if($cat->image)
                                <img src="{{ asset('storage/' . $cat->image) }}" width="40" height="40" class="rounded object-fit-cover">
                                @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                    <i class="fas fa-tag text-muted"></i>
                                </div>
                                @endif
                            </td>
                            <td>{{ $cat->name_fr }}</td>
                            <td dir="rtl">{{ $cat->name_ar }}</td>
                            <td>{{ $cat->name_en }}</td>
                            <td><span class="badge bg-light text-dark">{{ $cat->products_count ?? $cat->products->count() }}</span></td>
                            <td>
                                @if($cat->is_active)
                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.categories.index', ['edit' => $cat->id]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('{{ __('Delete this category?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">{{ __('No categories yet.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
