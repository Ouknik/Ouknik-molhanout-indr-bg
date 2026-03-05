<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Login') }} | {{ config('app.name', 'Molhanout') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', sans-serif; }
        body { background: linear-gradient(135deg, #2E7D32 0%, #1B5E20 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { max-width: 420px; width: 100%; border-radius: 16px; overflow: hidden; }
        .login-header { background: rgba(46, 125, 50, 0.1); padding: 2rem; text-align: center; }
        .login-header h2 { color: #2E7D32; font-weight: 700; margin: 0; }
        .login-header p { color: #666; margin: 0.5rem 0 0; font-size: 0.9rem; }
        .btn-molhanout { background: #2E7D32; border-color: #2E7D32; }
        .btn-molhanout:hover { background: #1B5E20; border-color: #1B5E20; }
    </style>
</head>
<body>
    <div class="card login-card shadow-lg border-0">
        <div class="login-header">
            <h2><i class="fas fa-store me-2"></i>Molhanout</h2>
            <p>{{ __('Admin Panel') }}</p>
        </div>
        <div class="card-body p-4">
            @if($errors->any())
            <div class="alert alert-danger py-2">
                <i class="fas fa-exclamation-circle me-1"></i>{{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('Email') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="admin@molhanout.ma" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">{{ __('Password') }}</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="remember" id="remember">
                    <label class="form-check-label" for="remember">{{ __('Remember me') }}</label>
                </div>
                <button type="submit" class="btn btn-molhanout text-white w-100 py-2 fw-semibold">
                    <i class="fas fa-sign-in-alt me-2"></i>{{ __('Login') }}
                </button>
            </form>
        </div>
        <div class="card-footer text-center bg-transparent py-3">
            <small class="text-muted">&copy; {{ date('Y') }} Molhanout — B2B Wholesale Marketplace</small>
        </div>
    </div>
</body>
</html>
