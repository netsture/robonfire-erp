<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RobonfireERP</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
        }
        .login-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        .brand-logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #3b82f6;
            text-align: center;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .brand-logo span {
            color: #ef4444;
        }
        .brand-subtitle {
            text-align: center;
            color: #94a3b8;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        .form-control {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #3b82f6;
            color: #f8fafc;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        }
        .form-label {
            color: #cbd5e1;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .btn-primary {
            background: linear-gradient(90deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
            background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        .form-check-input {
            background-color: rgba(15, 23, 42, 0.6);
            border-color: rgba(255, 255, 255, 0.1);
        }
        .form-check-input:checked {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        .form-check-label {
            color: #94a3b8;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-logo">
            <i class="bi bi-shield-lock-fill"></i> Robonfire<span>ERP</span>
        </div>
        <div class="brand-subtitle">Fire Safety Product Management System</div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success rounded-3 mb-4" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Username or Email Address -->
            <div class="mb-3">
                <label for="login" class="form-label">Email or Username</label>
                <input id="login" type="text" name="login" class="form-control @error('login') is-invalid @enderror" value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="Enter email or username">
                @error('login')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password" placeholder="••••••••">
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input id="remember_me" type="checkbox" name="remember" class="form-check-input">
                    <label for="remember_me" class="form-check-label">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5">
                Sign In <i class="bi bi-box-arrow-in-right ms-1"></i>
            </button>
        </form>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
