<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $organization = null;
        if (isset($building) && $building && $building->organization) {
            $organization = $building->organization;
        } elseif (isset($service) && $service && $service->building && $service->building->organization) {
            $organization = $service->building->organization;
        }
        $orgName = $organization && $organization->name ? 'آسانسور ' . $organization->name : 'لیفتر';
    @endphp
    <title>{{ $orgName }} - @yield('title', 'سرویس‌های ساختمان')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}" />
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo.png') }}" />
    
    <!-- Bootstrap RTL CSS -->
    <link href="{{ asset('bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    
    <!-- Custom Styles -->
    <style>
        @font-face {
            font-family: 'Vazir';
            src: url('{{ asset('storage/fonts/Vazir.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'Vazir';
            src: url('{{ asset('storage/fonts/Vazir-Bold.ttf') }}') format('truetype');
            font-weight: bold;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --primary: #0077B6;
            --primary-light: #0096D6;
            --primary-dark: #005A8A;
            --primary-gradient: linear-gradient(135deg, #0077B6 0%, #0096D6 100%);
            --success: #10b981;
            --success-light: #d1fae5;
            --success-dark: #059669;
            --info: #3b82f6;
            --info-light: #dbeafe;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Vazir', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Yekan Bakh', 'Tahoma', sans-serif;
            direction: rtl;
            text-align: right;
            background: linear-gradient(to bottom, #f8fafc 0%, #ffffff 100%);
            background-attachment: fixed;
            color: #1a1a1a;
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .site-header {
            background: var(--primary-gradient);
            color: white;
            padding: 1.25rem 0;
            box-shadow: var(--shadow-md);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .site-header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .site-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
        }

        .site-logo img {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.95);
            padding: 8px;
            border-radius: var(--radius-md);
            object-fit: contain;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .site-logo:hover img {
            box-shadow: var(--shadow-md);
            transform: scale(1.05);
        }

        .site-logo-text {
            font-size: 1.375rem;
            font-weight: 700;
            color: white;
            letter-spacing: -0.02em;
        }

        .page-header {
            background: white;
            padding: 2rem 0;
            border-bottom: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
        }

        .page-header h1 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.02em;
            position: relative;
            padding-right: 1rem;
        }

        .page-header h1::before {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 70%;
            background: var(--primary-gradient);
            border-radius: var(--radius-sm);
        }

        .public-content {
            padding: 3rem 0 4rem;
            min-height: calc(100vh - 200px);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .building-info {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .building-info:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .building-info h3 {
            color: var(--primary);
            margin-bottom: 1.75rem;
            font-weight: 700;
            font-size: 1.375rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-100);
            position: relative;
        }

        .building-info h3::after {
            content: '';
            position: absolute;
            bottom: -2px;
            right: 0;
            width: 60px;
            height: 2px;
            background: var(--primary-gradient);
            border-radius: var(--radius-sm);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
        }

        .info-item:hover {
            background: var(--gray-100);
            transform: translateX(-4px);
        }

        .info-label {
            font-size: 0.8125rem;
            color: var(--gray-600);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-value {
            color: var(--gray-900);
            font-weight: 500;
            font-size: 1.0625rem;
        }

        .services-section {
            margin-top: 3rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--primary);
            position: relative;
            letter-spacing: -0.02em;
        }

        .service-group {
            margin-bottom: 2rem;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            padding: 1.75rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .service-group:hover {
            box-shadow: var(--shadow-md);
        }

        .service-group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--gray-100);
        }

        .service-group-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.01em;
        }

        .service-count {
            font-size: 0.8125rem;
            background: var(--primary-gradient);
            color: white;
            padding: 0.375rem 1rem;
            font-weight: 600;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .service-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .service-item {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .service-item:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
            transform: translateX(-4px);
        }

        .service-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .service-title {
            font-size: 1.0625rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }

        .service-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.25rem;
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .service-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            background: var(--gray-50);
            border-radius: var(--radius-sm);
            transition: all 0.2s ease;
        }

        .service-meta-item:hover {
            background: var(--gray-100);
        }

        .service-meta-item i {
            width: 16px;
            color: var(--primary);
            font-size: 0.875rem;
        }

        .badge {
            padding: 0.5rem 1rem;
            font-size: 0.8125rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            white-space: nowrap;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
        }

        .badge-success {
            background: var(--success-light);
            color: var(--success-dark);
        }

        .badge-success i {
            color: var(--success-dark);
        }

        .badge-info {
            background: var(--info-light);
            color: var(--info);
        }

        .badge-info i {
            color: var(--info);
        }

        .badge-secondary {
            background: var(--gray-200);
            color: var(--gray-700);
        }

        .badge-secondary i {
            color: var(--gray-700);
        }

        .btn-detail {
            padding: 0.625rem 1.25rem;
            background: var(--primary-gradient);
            color: white;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-detail:hover {
            background: var(--primary-dark);
            color: white;
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .btn-detail:active {
            transform: translateY(0);
        }

        .btn-detail i {
            font-size: 0.875rem;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
            color: var(--primary);
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .empty-state h4 {
            color: var(--gray-900);
            font-weight: 700;
            margin-bottom: 0.75rem;
            font-size: 1.25rem;
        }

        .empty-state p {
            color: var(--gray-600);
            font-size: 1rem;
            line-height: 1.6;
        }

        .site-footer {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-top: 4rem;
            box-shadow: 0 -4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .footer-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .footer-logo img {
            width: 40px;
            height: 40px;
            background: white;
            padding: 6px;
            border-radius: 4px;
            object-fit: contain;
        }

        .footer-logo-text {
            font-size: 1.25rem;
            font-weight: 600;
            color: white;
        }

        .footer-copyright {
            color: rgba(255, 255, 255, 0.95);
            font-size: 0.9375rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .public-content {
                padding: 2rem 0 3rem;
            }

            .container {
                padding: 0 1rem;
            }

            .building-info {
                padding: 1.5rem;
                border-radius: var(--radius-md);
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }

            .info-item {
                padding: 0.875rem;
            }

            .service-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .service-meta {
                flex-direction: column;
                gap: 0.5rem;
                width: 100%;
            }

            .service-meta-item {
                width: 100%;
                justify-content: flex-start;
            }

            .page-header {
                padding: 1.5rem 0;
            }

            .page-header h1 {
                font-size: 1.5rem;
                padding-right: 0.75rem;
            }

            .site-header {
                padding: 1rem 0;
            }

            .site-logo-text {
                font-size: 1.125rem;
            }

            .site-logo img {
                width: 40px;
                height: 40px;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .section-title {
                font-size: 1.25rem;
            }
        }
    </style>

    @yield('page-styles')
</head>

<body>
    <!-- Site Header -->
    <header class="site-header">
        <div class="container">
            <div class="site-header-content">
                @php
                    $organization = null;
                    if (isset($building) && $building && $building->organization) {
                        $organization = $building->organization;
                    } elseif (isset($service) && $service && $service->building && $service->building->organization) {
                        $organization = $service->building->organization;
                    }
                @endphp
                <div class="site-logo">
                    @if($organization && $organization->logo)
                        <img src="{{ asset($organization->logo) }}" alt="{{ $organization->name }}">
                    @endif
                    @if($organization && $organization->name)
                        <span class="site-logo-text">آسانسور {{ $organization->name }}</span>
                    @else
                        <span class="site-logo-text">لیفتر</span>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>@yield('page-title', 'سرویس‌های ساختمان')</h1>
        </div>
    </div>

    <!-- Main Content -->
    <main class="public-content">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Site Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-copyright">
                    © {{ date('Y') }} لیفتر - تمامی حقوق محفوظ است
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/libs/jquery-3.1.1.min.js')}}"></script>
    <script src="{{ asset('bootstrap/js/popper.min.js')}}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.min.js')}}"></script>

    @yield('page-scripts')
</body>

</html>

