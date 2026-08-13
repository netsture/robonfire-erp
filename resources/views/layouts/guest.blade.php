<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Authentication') - NexERP</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
        }
        .auth-card {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
        }
        .brand-logo {
            width: 56px;
            height: 56px;
            background: var(--primary-gradient);
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.5);
            font-size: 1.75rem;
            color: #fff;
            margin: 0 auto 1.25rem auto;
        }
        .form-control {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #6366f1;
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
        }
        .form-control::placeholder {
            color: #94a3b8;
        }
        .input-group-text {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-right: none;
            color: #94a3b8;
            border-radius: 0.75rem 0 0 0.75rem;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 0.75rem 0.75rem 0;
        }
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.5);
            color: #fff;
        }
        .demo-box {
            background: rgba(99, 102, 241, 0.1);
            border: 1px dashed rgba(99, 102, 241, 0.4);
            border-radius: 0.75rem;
            padding: 0.85rem;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="brand-logo">
            <i class="bi bi-box-seam-fill"></i>
        </div>
        <h4 class="text-center font-outfit fw-bold text-white mb-1">NexERP Enterprise</h4>
        <p class="text-center text-muted small mb-4">Complete Business Management Suite</p>

        @if(session('success'))
            <div class="alert alert-success bg-success bg-opacity-20 border-success text-success-emphasis border-opacity-20 alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info bg-info bg-opacity-20 border-info text-info-emphasis border-opacity-20 alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger bg-danger bg-opacity-20 border-danger text-danger border-opacity-20 alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Please fix the errors below.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
