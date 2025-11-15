@extends('organization.layout.master')

@section('title', 'پرداخت پکیج')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">
                            <i class="fa fa-credit-card"></i> مدیریت پرداخت پکیج
                        </h5>
                    </div>
                    <div class="widget-content">
                        <div id="payment-container">
                            <div class="text-center p-5">
                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="sr-only">در حال بارگذاری...</span>
                                </div>
                                <p class="mt-3 text-muted">در حال بارگذاری اطلاعات...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-styles')
    <style>
        /* General Card Styles */
        .payment-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .payment-card-header {
            padding: 1.5rem;
            border-bottom: none;
            border-radius: 15px 15px 0 0;
        }
        
        .payment-card-header.bg-primary-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .payment-card-header.bg-warning-gradient {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .payment-card-body {
            padding: 2rem;
        }
        
        /* Progress Bar */
        .payment-progress {
            height: 30px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .payment-progress-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
            transition: width 0.6s ease;
        }
        
        /* Summary Cards */
        .summary-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 2px solid transparent;
        }
        
        .summary-card.total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .summary-card.paid {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .summary-card.remaining {
            background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
            color: white;
        }
        
        .summary-card .summary-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .summary-card .summary-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .summary-card .summary-unit {
            font-size: 0.75rem;
            opacity: 0.8;
        }
        
        /* Period Timeline */
        .period-timeline {
            position: relative;
            padding: 1rem 0;
        }
        
        .period-item {
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            border: 2px solid #e9ecef;
            position: relative;
            overflow: hidden;
        }
        
        .period-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            background: #dc3545;
        }
        
        .period-item.paid::before {
            background: #28a745;
        }
        
        .period-item.current::before {
            background: #ffc107;
            width: 6px;
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
        }
        
        
        .period-item.current {
            border-color: #ffc107;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            animation: pulse-border 2s infinite;
        }
        
        .period-item.paid {
            border-color: #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }
        
        @keyframes pulse-border {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
            }
            50% {
                box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
            }
        }
        
        /* Payment Form Section */
        .payment-form-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            border: 2px solid #dee2e6;
        }
        
        .payment-form-section h6 {
            color: white;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #dee2e6;
        }
        
        .payment-amount-input {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            padding: 1rem;
            border-radius: 10px;
            border: 2px solid #dee2e6;
        }
        
        .payment-amount-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .payment-amount-input[readonly] {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            cursor: not-allowed;
        }
        
        /* Info Alerts */
        .info-alert {
            border-radius: 12px;
            border: none;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .info-alert.period-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border-left: 5px solid #17a2b8;
        }
        
        .info-alert.full-info {
            background: linear-gradient(135deg, #cce5ff 0%, #b3d9ff 100%);
            border-left: 5px solid #007bff;
        }
        
        .info-alert.warning-info {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border-left: 5px solid #ffc107;
        }
        
        /* Buttons */
        .btn-payment {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        /* Public Packages */
        .public-package-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .public-package-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .public-package-body {
            padding: 2rem;
            text-align: center;
        }
        
        .public-package-price {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            margin: 1rem 0;
        }
        
        /* Badges */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .payment-card-body {
                padding: 1.5rem;
            }
            
            .summary-card {
                margin-bottom: 1rem;
            }
            
            .summary-card .summary-value {
                font-size: 1.4rem;
            }
        }
        
        /* Loading Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease;
        }
        
        /* Checkbox Styling */
        .form-check-input-custom {
            width: 22px;
            height: 22px;
            margin-top: 0.3rem;
            cursor: pointer;
        }
        
        .form-check-label-custom {
            cursor: pointer;
            font-size: 1rem;
            line-height: 1.6;
            margin-right: 0.5rem;
        }
    </style>
@endsection

@section('page-scripts')
    <script>
        $(document).ready(function() {
            loadPaymentInfo();

            function loadPaymentInfo() {
                const token = localStorage.getItem('organization_token');
                if (!token) {
                    window.location.href = '/login';
                    return;
                }

                $.ajax({
                    url: '/api/organization/payment/info',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    success: function(response) {
                        if (!response.has_active_packages && response.public_packages && response.public_packages.length > 0) {
                            renderPublicPackages(response.public_packages, response.organization);
                        } else if (response.data && response.data.length > 0) {
                            renderPaymentForm(response.data, response.organization);
                        } else {
                            $('#payment-container').html(`
                                <div class="alert alert-success text-center fade-in" style="border-radius: 15px; padding: 3rem;">
                                    <i class="fa fa-check-circle fa-3x mb-3 text-success"></i>
                                    <h4 class="mb-3 text-white">همه پکیج‌های شما پرداخت شده است!</h4>
                                    <p class="mb-4 text-white">می‌توانید به پنل خود دسترسی داشته باشید.</p>
                                    <a href="/" class="btn btn-primary btn-lg btn-payment">
                                        <i class="fa fa-home"></i> بازگشت به پنل
                                    </a>
                                </div>
                            `);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            window.location.href = '/login';
                        } else {
                            $('#payment-container').html(`
                                <div class="alert alert-danger text-center fade-in" style="border-radius: 15px; padding: 3rem;">
                                    <i class="fa fa-exclamation-triangle fa-3x mb-3"></i>
                                    <h4 class="text-white">خطا در دریافت اطلاعات</h4>
                                    <p class="text-white">لطفا دوباره تلاش کنید.</p>
                                    <button class="btn btn-primary mt-3" onclick="location.reload()">
                                        <i class="fa fa-refresh"></i> تلاش مجدد
                                    </button>
                                </div>
                            `);
                        }
                    }
                });
            }

            function renderPublicPackages(publicPackages, organization) {
                let html = `
                    <div class="alert alert-info text-center mb-4 fade-in" style="border-radius: 15px; padding: 2rem;">
                        <i class="fa fa-info-circle fa-2x mb-3"></i>
                        <h4>شما پکیج فعالی ندارید</h4>
                        <p class="mb-0">لطفا یکی از پکیج‌های زیر را انتخاب و فعال کنید:</p>
                    </div>
                    <div class="row">
                `;
                
                publicPackages.forEach(function(pkg) {
                    html += `
                        <div class="col-md-4 mb-4 fade-in">
                            <div class="public-package-card">
                                <div class="public-package-header">
                                    <h5 class="mb-0 text-white">
                                        <i class="fa fa-box"></i> ${pkg.name}
                                    </h5>
                                </div>
                                <div class="public-package-body">
                                    <div class="mb-3">
                                        <span class="badge badge-info status-badge">
                                            <i class="fa fa-calendar"></i> ${pkg.duration_label}
                                        </span>
                                        <small class="d-block text-muted mt-2">${pkg.duration_days} روز</small>
                                    </div>
                                    <div class="public-package-price">
                                        ${parseFloat(pkg.price).toLocaleString('fa-IR')} تومان
                                    </div>
                                    <button class="btn btn-success btn-block btn-payment activate-package-btn" data-package-id="${pkg.id}">
                                        <i class="fa fa-check-circle"></i> فعال‌سازی و پرداخت
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                $('#payment-container').html(html);

                $('.activate-package-btn').on('click', function() {
                    const packageId = $(this).data('package-id');
                    const package = publicPackages.find(p => p.id === packageId);
                    
                    swal({
                        title: 'فعال‌سازی پکیج',
                        html: `
                            <div class="text-right" style="direction: rtl;">
                                <p>آیا می‌خواهید پکیج <strong>${package.name}</strong> را فعال کنید؟</p>
                                <div class="alert alert-info mt-3">
                                    <strong>قیمت:</strong> ${parseFloat(package.price).toLocaleString('fa-IR')} تومان
                                </div>
                                <p class="text-warning"><i class="fa fa-exclamation-triangle"></i> پس از فعال‌سازی، باید پرداخت را انجام دهید.</p>
                            </div>
                        `,
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'بله، فعال کن',
                        cancelButtonText: 'انصراف',
                        confirmButtonColor: '#28a745',
                        padding: '2em'
                    }).then((result) => {
                        if (result.value) {
                            activatePackage(packageId);
                        }
                    });
                });
            }

            function activatePackage(packageId) {
                const token = localStorage.getItem('organization_token');
                
                swal({
                    title: 'در حال پردازش...',
                    text: 'لطفا صبر کنید',
                    type: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    onOpen: function() {
                        swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '/api/organization/payment/activate-package',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        package_id: packageId
                    },
                    success: function(response) {
                        swal({
                            title: 'موفق',
                            text: response.message,
                            type: 'success',
                            padding: '2em'
                        }).then(function() {
                            loadPaymentInfo();
                        });
                    },
                    error: function(xhr) {
                        let message = 'خطا در فعال‌سازی پکیج';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        swal({
                            title: 'خطا',
                            text: message,
                            type: 'error',
                            padding: '2em'
                        });
                    }
                });
            }

            function renderPaymentForm(paymentInfo, organization) {
                let html = '<div class="row">';
                
                paymentInfo.forEach(function(info, index) {
                    const isPeriodPayment = info.payment_type === 'period';
                    const usePeriods = info.use_periods !== undefined ? info.use_periods : true;
                    const periodLabel = isPeriodPayment ? `دوره ${info.current_period + 1}` : 'پرداخت کامل';
                    const paymentPercentage = Math.min((info.paid_amount / info.total_amount * 100), 100).toFixed(1);
                    
                    html += `
                        <div class="col-md-${paymentInfo.length === 1 ? '10 offset-md-1' : '6'} mb-4 fade-in">
                            <div class="card payment-card">
                                <div class="card-header payment-card-header ${isPeriodPayment ? 'bg-warning-gradient' : 'bg-primary-gradient'} text-white">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                        <div class="mb-2 mb-md-0">
                                            <h5 class="mb-2 text-white">
                                                <i class="fa fa-box"></i> ${info.package_name}
                                            </h5>
                                            <small class="opacity-75 text-white">
                                                <i class="fa fa-calendar"></i> ${info.package_duration_days} روز
                                                ${info.package_duration_days > 30 ? ' - پرداخت دوره‌ای' : ' - پرداخت کامل'}
                                            </small>
                                        </div>
                                        ${isPeriodPayment ? `
                                            <span class="badge badge-light status-badge">
                                                <i class="fa fa-list-ol"></i> دوره ${info.current_period + 1} از ${info.total_periods || info.periods?.length || '?'}
                                            </span>
                                        ` : ''}
                                    </div>
                                </div>
                                <div class="card-body payment-card-body">
                                    <!-- Payment Progress -->
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted font-weight-bold">
                                                <i class="fa fa-chart-line"></i> وضعیت پرداخت
                                            </span>
                                            <span class="font-weight-bold" style="font-size: 1.1rem;">${paymentPercentage}%</span>
                                        </div>
                                        <div class="payment-progress">
                                            <div class="progress-bar payment-progress-bar ${paymentPercentage == 100 ? 'bg-success' : paymentPercentage > 0 ? 'bg-warning' : 'bg-danger'}" 
                                                 role="progressbar" 
                                                 style="width: ${paymentPercentage}%;"
                                                 aria-valuenow="${paymentPercentage}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                ${paymentPercentage}%
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Payment Summary -->
                                    <div class="row mb-4">
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <div class="summary-card total">
                                                <div class="summary-label">
                                                    <i class="fa fa-tag"></i> قیمت کل
                                                </div>
                                                <div class="summary-value">
                                                    ${parseFloat(info.total_amount).toLocaleString('fa-IR')}
                                                </div>
                                                <div class="summary-unit">تومان</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <div class="summary-card paid">
                                                <div class="summary-label">
                                                    <i class="fa fa-check-circle"></i> پرداخت شده
                                                </div>
                                                <div class="summary-value">
                                                    ${parseFloat(info.paid_amount).toLocaleString('fa-IR')}
                                                </div>
                                                <div class="summary-unit">تومان</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="summary-card remaining">
                                                <div class="summary-label">
                                                    <i class="fa fa-exclamation-circle"></i> باقی‌مانده
                                                </div>
                                                <div class="summary-value">
                                                    ${parseFloat(info.remaining_amount).toLocaleString('fa-IR')}
                                                </div>
                                                <div class="summary-unit">تومان</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    ${isPeriodPayment ? `
                                        <!-- Period Payment Info -->
                                        <div class="info-alert period-info">
                                            <div class="d-flex align-items-start">
                                                <i class="fa fa-info-circle fa-2x mr-3 mt-1"></i>
                                                <div class="flex-grow-1">
                                                    <h6 class="alert-heading mb-2 text-white">
                                                        <i class="fa fa-calendar-alt"></i> پرداخت دوره ${info.current_period + 1}
                                                    </h6>
                                                    <div class="mb-2">
                                                        <strong class="text-primary" style="font-size: 1.8rem; font-weight: bold;">
                                                            ${parseFloat(info.period_amount).toLocaleString('fa-IR')} تومان
                                                        </strong>
                                                    </div>
                                                    <p class="mb-0 small">
                                                        برای دسترسی به ${info.periods && info.periods[info.current_period] ? 
                                                            `<strong>${info.periods[info.current_period].days} روز آینده</strong>` : 
                                                            '<strong>30 روز آینده</strong>'} باید این مبلغ را پرداخت کنید.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Period Timeline -->
                                        ${info.periods && info.periods.length > 1 ? `
                                            <div class="mb-4">
                                                <h6 class="mb-3 text-white">
                                                    <i class="fa fa-list-ol"></i> وضعیت دوره‌ها
                                                </h6>
                                                <div class="period-timeline">
                                                    ${info.periods.map((period, i) => {
                                                        const isPaid = period.is_paid;
                                                        const isCurrent = period.is_current;
                                                        const periodAmount = parseFloat(period.amount);
                                                        const startDate = new Date(period.start_date).toLocaleDateString('fa-IR');
                                                        const endDate = new Date(period.end_date).toLocaleDateString('fa-IR');
                                                        return `
                                                            <div class="period-item ${isCurrent ? 'current' : ''} ${isPaid ? 'paid' : 'unpaid'}">
                                                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                                    <div class="d-flex align-items-center mb-2 mb-md-0">
                                                                        <span class="badge ${isPaid ? 'badge-success' : isCurrent ? 'badge-warning' : 'badge-danger'} status-badge mr-3">
                                                                            ${isPaid ? '<i class="fa fa-check-circle"></i> پرداخت شده' : isCurrent ? '<i class="fa fa-clock"></i> در انتظار پرداخت' : '<i class="fa fa-times-circle"></i> پرداخت نشده'}
                                                                        </span>
                                                                        <div>
                                                                            <strong style="font-size: 1.1rem;">دوره ${period.period_number + 1}</strong>
                                                                            ${isCurrent ? '<span class="badge badge-warning ml-2"><i class="fa fa-exclamation-triangle"></i> دوره فعلی</span>' : ''}
                                                                            <div class="text-muted small mt-1">
                                                                                <i class="fa fa-calendar"></i> ${startDate} تا ${endDate} (${period.days} روز)
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <div class="font-weight-bold ${isPaid ? 'text-success' : isCurrent ? 'text-warning' : 'text-danger'}" style="font-size: 1.3rem;">
                                                                            ${periodAmount.toLocaleString('fa-IR')}
                                                                        </div>
                                                                        <div class="text-muted small">تومان</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        `;
                                                    }).join('')}
                                                </div>
                                            </div>
                                        ` : ''}
                                    ` : `
                                        <!-- Full Payment Info -->
                                        <div class="info-alert full-info">
                                            <div class="d-flex align-items-start">
                                                <i class="fa fa-credit-card fa-2x mr-3 mt-1"></i>
                                                <div class="flex-grow-1">
                                                    <h6 class="alert-heading mb-2 text-white">پرداخت کامل پکیج</h6>
                                                    <p class="mb-0">می‌توانید کل مبلغ باقی‌مانده را پرداخت کنید.</p>
                                                </div>
                                            </div>
                                        </div>
                                    `}
                                    
                                    <!-- Payment Form -->
                                    <div class="payment-form-section">
                                        <h6 class="text-white">
                                            <i class="fa fa-credit-card"></i> فرم پرداخت
                                        </h6>
                                        <form class="payment-form" 
                                              data-package-id="${info.package_id}" 
                                              data-payment-type="${info.payment_type}"
                                              data-use-periods="${usePeriods}"
                                              data-remaining-amount="${info.remaining_amount}">
                                            <div class="form-group mb-4">
                                                <label class="font-weight-bold mb-3" style="font-size: 1.1rem;">
                                                    <i class="fa fa-money-bill-wave"></i> مبلغ پرداختی (تومان)
                                                </label>
                                                <div class="input-group input-group-lg">
                                                    <input type="number" 
                                                           class="form-control payment-amount-input payment-amount" 
                                                           value="${isPeriodPayment ? info.period_amount : info.remaining_amount}" 
                                                           min="0" 
                                                           step="1000" 
                                                           ${isPeriodPayment || !info.use_periods ? 'readonly' : ''}
                                                           required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-white font-weight-bold" style="font-size: 1.1rem;">تومان</span>
                                                    </div>
                                                </div>
                                                ${isPeriodPayment ? `
                                                    <div class="alert alert-secondary mt-3 mb-0" style="border-radius: 10px;">
                                                        <i class="fa fa-lock"></i> 
                                                        <small>مبلغ دوره به صورت خودکار محاسبه شده است و قابل تغییر نیست</small>
                                                    </div>
                                                ` : !info.use_periods ? `
                                                    <div class="alert alert-warning mt-3 mb-0 info-alert warning-info">
                                                        <i class="fa fa-exclamation-triangle"></i> 
                                                        <small>این پکیج بدون دوره است و باید کل مبلغ پرداخت شود</small>
                                                    </div>
                                                ` : ''}
                                            </div>
                                        
                                            ${!isPeriodPayment && info.use_periods ? `
                                                <div class="form-check mb-4 p-3 bg-white rounded shadow-sm border" style="border-radius: 10px;">
                                                    <input class="form-check-input form-check-input-custom" type="checkbox" id="pay-full-${index}" checked>
                                                    <label class="form-check-label form-check-label-custom" for="pay-full-${index}">
                                                        <strong><i class="fa fa-check-square"></i> پرداخت کل مبلغ باقی‌مانده</strong>
                                                        <br>
                                                        <span class="text-muted">(${parseFloat(info.remaining_amount).toLocaleString('fa-IR')} تومان)</span>
                                                    </label>
                                                </div>
                                            ` : ''}
                                            
                                            <input type="hidden" name="period" value="${info.current_period || ''}">
                                            <button type="submit" class="btn btn-success btn-block btn-payment">
                                                <i class="fa fa-check-circle"></i> 
                                                پرداخت ${isPeriodPayment ? `دوره ${info.current_period + 1}` : 'مبلغ'}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                $('#payment-container').html(html);

                // Handle form submission
                $('.payment-form').on('submit', function(e) {
                    e.preventDefault();
                    const form = $(this);
                    const packageId = form.data('package-id');
                    const paymentType = form.data('payment-type');
                    const amount = parseFloat(form.find('.payment-amount').val());
                    const period = form.find('input[name="period"]').val();

                    if (amount <= 0) {
                        swal({
                            title: 'خطا',
                            text: 'مبلغ باید بیشتر از صفر باشد',
                            type: 'error',
                            padding: '2em'
                        });
                        return;
                    }

                    const isPeriod = paymentType === 'period';
                    const usePeriods = form.data('use-periods') !== false;
                    const remainingAmount = parseFloat(form.data('remaining-amount') || 0);
                    const periodNum = period ? parseInt(period) + 1 : '';
                    
                    // For packages without periods, ensure full payment
                    if (!usePeriods && paymentType === 'full') {
                        if (Math.abs(amount - remainingAmount) > 0.01) {
                            swal({
                                title: 'خطا',
                                text: `برای پکیج‌های بدون دوره، باید کل مبلغ باقی‌مانده (${remainingAmount.toLocaleString('fa-IR')} تومان) پرداخت شود`,
                                type: 'error',
                                padding: '2em'
                            });
                            return;
                        }
                    }
                    
                    swal({
                        title: 'تأیید پرداخت',
                        html: `
                            <div class="text-right" style="direction: rtl;">
                                <p class="mb-3">آیا مطمئن هستید که می‌خواهید پرداخت را انجام دهید؟</p>
                                <div class="alert alert-info text-right" style="border-radius: 10px;">
                                    <strong>مبلغ پرداختی:</strong><br>
                                    <span style="font-size: 1.5rem; color: #007bff; font-weight: bold;">${amount.toLocaleString('fa-IR')} تومان</span>
                                    ${isPeriod ? `<br><small class="text-muted">برای دوره ${periodNum}</small>` : ''}
                                </div>
                            </div>
                        `,
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'بله، پرداخت کن',
                        cancelButtonText: 'انصراف',
                        confirmButtonColor: '#28a745',
                        padding: '2em'
                    }).then((result) => {
                        if (result.value) {
                            processPayment(packageId, amount, paymentType, period);
                        }
                    });
                });

                // Handle full payment checkbox
                $(document).on('change', '.form-check-input', function() {
                    const form = $(this).closest('.card').find('.payment-form');
                    const cardBody = $(this).closest('.card-body');
                    const remainingAmountText = cardBody.find('.col-md-4').last().find('.summary-value').text().replace(/[^\d.]/g, '');
                    const remainingAmount = parseFloat(remainingAmountText);
                    if ($(this).is(':checked')) {
                        form.find('.payment-amount').val(remainingAmount);
                    } else {
                        form.find('.payment-amount').val(0);
                    }
                });
            }

            function processPayment(packageId, amount, paymentType, period) {
                const token = localStorage.getItem('organization_token');
                
                swal({
                    title: 'در حال پردازش...',
                    text: 'لطفا صبر کنید',
                    type: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    onOpen: function() {
                        swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '/api/organization/payment/process',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        package_id: packageId,
                        amount: amount,
                        payment_type: paymentType,
                        period: period || null
                    },
                    success: function(response) {
                        swal({
                            title: 'موفق',
                            text: response.message,
                            type: 'success',
                            padding: '2em'
                        }).then(function() {
                            loadPaymentInfo();
                            setTimeout(function() {
                                window.location.href = '/';
                            }, 2000);
                        });
                    },
                    error: function(xhr) {
                        let message = 'خطا در پردازش پرداخت';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        swal({
                            title: 'خطا',
                            text: message,
                            type: 'error',
                            padding: '2em'
                        });
                    }
                });
            }
        });
    </script>
@endsection
