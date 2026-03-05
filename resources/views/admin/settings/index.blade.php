@extends('admin.layouts.app')
@section('title', __('Settings'))
@section('page-title', __('Application Settings'))

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        {{-- Theme Colors --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><i class="fas fa-palette me-2"></i>{{ __('Theme Colors') }}</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('Primary Color') }}</label>
                            <div class="input-group">
                                <input type="color" name="primary_color" value="{{ $settings['primary_color'] ?? '#2E7D32' }}" class="form-control form-control-color" style="width:50px">
                                <input type="text" value="{{ $settings['primary_color'] ?? '#2E7D32' }}" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('Secondary Color') }}</label>
                            <div class="input-group">
                                <input type="color" name="secondary_color" value="{{ $settings['secondary_color'] ?? '#FF8F00' }}" class="form-control form-control-color" style="width:50px">
                                <input type="text" value="{{ $settings['secondary_color'] ?? '#FF8F00' }}" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('Accent Color') }}</label>
                            <div class="input-group">
                                <input type="color" name="accent_color" value="{{ $settings['accent_color'] ?? '#1565C0' }}" class="form-control form-control-color" style="width:50px">
                                <input type="text" value="{{ $settings['accent_color'] ?? '#1565C0' }}" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ __('Font Family') }}</label>
                            <select name="font_family" class="form-select">
                                <option value="Cairo" {{ ($settings['font_family'] ?? 'Cairo') == 'Cairo' ? 'selected' : '' }}>Cairo</option>
                                <option value="Tajawal" {{ ($settings['font_family'] ?? '') == 'Tajawal' ? 'selected' : '' }}>Tajawal</option>
                                <option value="Almarai" {{ ($settings['font_family'] ?? '') == 'Almarai' ? 'selected' : '' }}>Almarai</option>
                                <option value="Roboto" {{ ($settings['font_family'] ?? '') == 'Roboto' ? 'selected' : '' }}>Roboto</option>
                            </select>
                        </div>
                    </div>

                    {{-- Preview --}}
                    <div class="mt-4 p-3 rounded border" id="themePreview">
                        <h6>{{ __('Theme Preview') }}</h6>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-primary btn-sm">Primary</button>
                            <button type="button" class="btn btn-secondary btn-sm" style="background-color: var(--secondary-color)">Secondary</button>
                            <button type="button" class="btn btn-sm" style="background-color: var(--accent-color); color:white">Accent</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- App Info --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><i class="fas fa-cog me-2"></i>{{ __('Application Info') }}</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('App Name') }}</label>
                        <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'Molhanout' }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Currency') }}</label>
                        <input type="text" name="currency" value="{{ $settings['currency'] ?? 'MAD' }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Default Language') }}</label>
                        <select name="default_language" class="form-select">
                            <option value="fr" {{ ($settings['default_language'] ?? 'fr') == 'fr' ? 'selected' : '' }}>Français</option>
                            <option value="ar" {{ ($settings['default_language'] ?? '') == 'ar' ? 'selected' : '' }}>العربية</option>
                            <option value="en" {{ ($settings['default_language'] ?? '') == 'en' ? 'selected' : '' }}>English</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('App Logo') }}</label>
                        <input type="file" name="app_logo" class="form-control" accept="image/*">
                        @if(isset($settings['app_logo']) && $settings['app_logo'])
                        <div class="mt-2"><img src="{{ asset('storage/' . $settings['app_logo']) }}" height="40"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Business Settings --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><i class="fas fa-business-time me-2"></i>{{ __('Business Settings') }}</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Max Delivery Radius (km)') }}</label>
                        <input type="number" name="max_delivery_radius" value="{{ $settings['max_delivery_radius'] ?? 50 }}" class="form-control" min="1" max="500">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Offer Expiry (hours)') }}</label>
                        <input type="number" name="offer_expiry_hours" value="{{ $settings['offer_expiry_hours'] ?? 24 }}" class="form-control" min="1" max="168">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="require_verification" value="1" {{ ($settings['require_verification'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ __('Require account verification') }}</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="maintenance_mode" value="1" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ __('Maintenance Mode') }}</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><i class="fas fa-bell me-2"></i>{{ __('Notification Settings') }}</div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="push_notifications" value="1" {{ ($settings['push_notifications'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ __('Enable Push Notifications') }}</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="email_notifications" value="1" {{ ($settings['email_notifications'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">{{ __('Enable Email Notifications') }}</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Admin Email') }}</label>
                        <input type="email" name="admin_email" value="{{ $settings['admin_email'] ?? '' }}" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>{{ __('Save All Settings') }}</button>
    </div>
</form>

@push('scripts')
<script>
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('input', function() {
        this.nextElementSibling.value = this.value;
    });
});
</script>
@endpush
@endsection
