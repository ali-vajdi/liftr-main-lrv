<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>لیفتر - @yield('title', 'سرویس‌های ساختمان')</title>
    
    <!-- Bootstrap RTL CSS -->
    <link href="{{ asset('bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary: #0077B6;
            --primary-light: #0096D6;
            --primary-dark: #005A8A;
            --success: #10b981;
            --success-light: #d1fae5;
            --success-dark: #059669;
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Yekan Bakh', 'Tahoma', sans-serif;
            direction: rtl;
            text-align: right;
            background: #ffffff;
            color: #1a1a1a;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        .site-header {
            background: var(--primary);
            color: white;
            padding: 1rem 0;
            border-bottom: 1px solid var(--primary-dark);
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
            text-decoration: none;
            color: white;
        }

        .site-logo img {
            width: 40px;
            height: 40px;
            background: white;
            padding: 6px;
        }

        .site-logo-text {
            font-size: 1.25rem;
            font-weight: 600;
            color: white;
        }

        .page-header {
            background: white;
            padding: 1.5rem 0;
            border-bottom: 1px solid #e5e5e5;
        }

        .page-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }

        .public-content {
            padding: 3rem 0;
            min-height: calc(100vh - 200px);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .building-info {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .building-info h3 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-weight: 600;
            font-size: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e5e5;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-label {
            font-size: 0.875rem;
            color: #666;
            font-weight: 500;
        }

        .info-value {
            color: #1a1a1a;
            font-weight: 400;
            font-size: 1rem;
        }

        .services-section {
            margin-top: 3rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--primary);
        }

        .service-group {
            margin-bottom: 2rem;
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1.25rem;
        }

        .service-group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
            border-bottom: 1px solid #e5e5e5;
        }

        .service-group-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
        }

        .service-count {
            font-size: 0.8125rem;
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            font-weight: 500;
        }

        .service-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .service-item {
            background: white;
            border: 1px solid #e5e5e5;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .service-item:hover {
            border-color: var(--primary);
        }

        .service-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .service-title {
            font-size: 1rem;
            font-weight: 500;
            color: #1a1a1a;
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
        }

        .service-meta-item i {
            width: 14px;
            color: var(--primary);
        }

        .badge {
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            white-space: nowrap;
        }

        .badge-success {
            background: #d1fae5;
            color: #059669;
        }

        .badge-success i {
            color: #059669;
        }

        .btn-detail {
            padding: 0.5rem 1rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }

        .btn-detail:hover {
            background: var(--primary-dark);
            color: white;
        }

        .btn-detail i {
            font-size: 0.8125rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            background: white;
            border: 1px solid #e5e5e5;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.2;
            color: var(--primary);
        }

        .empty-state h4 {
            color: #1a1a1a;
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1.125rem;
        }

        .empty-state p {
            color: #666;
            font-size: 0.9375rem;
        }

        .site-footer {
            background: var(--primary);
            color: white;
            padding: 1.5rem 0;
            margin-top: 3rem;
            border-top: 1px solid var(--primary-dark);
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
        }

        .footer-logo-text {
            font-size: 1.25rem;
            font-weight: 600;
            color: white;
        }

        .footer-copyright {
            color: white;
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .public-content {
                padding: 2rem 0;
            }

            .container {
                padding: 0 1rem;
            }

            .building-info {
                padding: 1.5rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .service-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .service-meta {
                flex-direction: column;
                gap: 0.75rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .site-logo-text {
                font-size: 1.25rem;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
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
                <a href="/" class="site-logo">
                    <img src="{{ asset('assets/img/90x90.jpg') }}" alt="لیفتر">
                    <span class="site-logo-text">لیفتر</span>
                </a>
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
                <div class="footer-logo">
                    <img src="{{ asset('assets/img/90x90.jpg') }}" alt="لیفتر">
                    <span class="footer-logo-text">لیفتر</span>
                </div>
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

