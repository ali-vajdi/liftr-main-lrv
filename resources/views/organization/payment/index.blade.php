@extends('organization.layout.master')

@section('title', 'پرداخت پکیج')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">پرداخت پکیج</h5>
                    </div>
                    <div class="widget-content">
                        <div id="payment-container">
                            <div class="text-center p-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">در حال بارگذاری...</span>
                                </div>
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
        .period-timeline .period-item {
            transition: all 0.3s ease;
            border: 2px solid #e9ecef;
        }
        .period-timeline .period-item.current {
            border-color: #ffc107;
            background-color: #fff3cd;
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.3);
        }
        .period-timeline .period-item.paid {
            border-color: #28a745;
            background-color: #d4edda;
        }
        .period-timeline .period-item.unpaid {
            border-color: #dc3545;
            background-color: #f8d7da;
        }
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        }
        .border-left-info {
            border-left: 4px solid #17a2b8 !important;
        }
        .border-left-primary {
            border-left: 4px solid #007bff !important;
        }
        .progress {
            overflow: visible;
        }
        .progress-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .input-group-lg .form-control {
            font-size: 1.25rem;
        }
        .badge-lg {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
        .card-header.bg-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
        }
        .card-header.bg-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        }
        .alert-info {
            border-radius: 10px;
        }
        .alert-primary {
            border-radius: 10px;
        }
        .payment-form-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
            border: 1px solid #e9ecef;
        }
        .payment-form {
            margin-top: 0;
        }
        .period-item {
            transition: all 0.3s ease;
        }
        .period-item:hover {
            transform: translateX(-5px);
        }
        .period-item.current {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 10px rgba(255, 193, 7, 0.3);
            }
            50% {
                box-shadow: 0 0 20px rgba(255, 193, 7, 0.6);
            }
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
                            // Show public packages for activation
                            renderPublicPackages(response.public_packages, response.organization);
                        } else if (response.data && response.data.length > 0) {
                            // Show payment forms for existing packages
                            renderPaymentForm(response.data, response.organization);
                        } else {
                            // All packages are paid
                            $('#payment-container').html(`
                                <div class="alert alert-success text-center">
                                    <h5>همه پکیج‌های شما پرداخت شده است!</h5>
                                    <p>می‌توانید به پنل خود دسترسی داشته باشید.</p>
                                    <a href="/" class="btn btn-primary">بازگشت به پنل</a>
                                </div>
                            `);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            window.location.href = '/login';
                        } else {
                            $('#payment-container').html(`
                                <div class="alert alert-danger text-center">
                                    <h5>خطا در دریافت اطلاعات</h5>
                                    <p>لطفا دوباره تلاش کنید.</p>
                                </div>
                            `);
                        }
                    }
                });
            }

            function renderPublicPackages(publicPackages, organization) {
                let html = `
                    <div class="alert alert-info text-center mb-4">
                        <h5>شما پکیج فعالی ندارید</h5>
                        <p>لطفا یکی از پکیج‌های زیر را انتخاب و فعال کنید:</p>
                    </div>
                    <div class="row">
                `;
                
                publicPackages.forEach(function(pkg) {
                    html += `
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white text-center">
                                    <h5 class="mb-0">${pkg.name}</h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <strong>مدت زمان:</strong><br>
                                        <span class="badge badge-info">${pkg.duration_label}</span>
                                        <small class="d-block text-muted mt-1">${pkg.duration_days} روز</small>
                                    </div>
                                    <div class="mb-3">
                                        <strong>قیمت:</strong><br>
                                        <h4 class="text-primary">${parseFloat(pkg.price).toLocaleString('fa-IR')} تومان</h4>
                                    </div>
                                    <button class="btn btn-success btn-block activate-package-btn" data-package-id="${pkg.id}">
                                        فعال‌سازی و پرداخت
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                $('#payment-container').html(html);

                // Handle package activation
                $('.activate-package-btn').on('click', function() {
                    const packageId = $(this).data('package-id');
                    const package = publicPackages.find(p => p.id === packageId);
                    
                    swal({
                        title: 'فعال‌سازی پکیج',
                        html: `
                            <p>آیا می‌خواهید پکیج <strong>${package.name}</strong> را فعال کنید؟</p>
                            <p>قیمت: <strong>${parseFloat(package.price).toLocaleString('fa-IR')} تومان</strong></p>
                            <p class="text-warning">پس از فعال‌سازی، باید پرداخت را انجام دهید.</p>
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
                            // Reload payment info to show payment form
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
                    const periodLabel = isPeriodPayment ? `دوره ${info.current_period + 1}` : 'پرداخت کامل';
                    const paymentPercentage = (info.paid_amount / info.total_amount * 100).toFixed(1);
                    
                    html += `
                        <div class="col-md-${paymentInfo.length === 1 ? '8 offset-md-2' : '6'} mb-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-header ${isPeriodPayment ? 'bg-warning' : 'bg-primary'} text-white" style="border-radius: 0.5rem 0.5rem 0 0;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">${info.package_name}</h5>
                                            <small class="opacity-75">${info.package_duration_days} روز - ${info.package_duration_days > 30 ? 'پرداخت دوره‌ای' : 'پرداخت کامل'}</small>
                                        </div>
                                        ${isPeriodPayment ? `
                                            <span class="badge badge-light badge-lg">
                                                دوره ${info.current_period + 1} از ${info.total_periods}
                                            </span>
                                        ` : ''}
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <!-- Payment Progress -->
                                    <div class="mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted">وضعیت پرداخت</span>
                                            <span class="font-weight-bold">${paymentPercentage}%</span>
                                        </div>
                                        <div class="progress" style="height: 25px; border-radius: 15px;">
                                            <div class="progress-bar ${paymentPercentage == 100 ? 'bg-success' : paymentPercentage > 0 ? 'bg-warning' : 'bg-danger'}" 
                                                 role="progressbar" 
                                                 style="width: ${paymentPercentage}%; border-radius: 15px;"
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
                                            <div class="text-center p-3 bg-light rounded">
                                                <div class="text-muted small mb-1">قیمت کل</div>
                                                <div class="h5 mb-0 text-dark font-weight-bold">
                                                    ${parseFloat(info.total_amount).toLocaleString('fa-IR')}
                                                </div>
                                                <div class="text-muted small">تومان</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <div class="text-center p-3 bg-light rounded">
                                                <div class="text-muted small mb-1">پرداخت شده</div>
                                                <div class="h5 mb-0 text-success font-weight-bold">
                                                    ${parseFloat(info.paid_amount).toLocaleString('fa-IR')}
                                                </div>
                                                <div class="text-muted small">تومان</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-light rounded">
                                                <div class="text-muted small mb-1">باقی‌مانده</div>
                                                <div class="h5 mb-0 text-danger font-weight-bold">
                                                    ${parseFloat(info.remaining_amount).toLocaleString('fa-IR')}
                                                </div>
                                                <div class="text-muted small">تومان</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    ${isPeriodPayment ? `
                                        <!-- Period Payment Info -->
                                        <div class="alert alert-info border-left-info" style="border-left: 4px solid #17a2b8;">
                                            <div class="d-flex align-items-start">
                                                <i class="fa fa-info-circle fa-2x mr-3 mt-1"></i>
                                                <div class="flex-grow-1">
                                                    <h6 class="alert-heading mb-2">
                                                        <i class="fa fa-calendar-alt"></i> پرداخت دوره ${info.current_period + 1}
                                                    </h6>
                                                    <div class="mb-2">
                                                        <strong class="text-primary" style="font-size: 1.5rem;">
                                                            ${parseFloat(info.period_amount).toLocaleString('fa-IR')} تومان
                                                        </strong>
                                                    </div>
                                                    <p class="mb-0 small">
                                                        برای دسترسی به ${info.periods && info.periods[info.current_period] ? 
                                                            `<strong>${info.periods[info.current_period].days} روز</strong>` : 
                                                            '<strong>30 روز آینده</strong>'} باید این مبلغ را پرداخت کنید.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Period Timeline (if multiple periods) -->
                                        ${info.periods && info.periods.length > 1 ? `
                                            <div class="mb-4">
                                                <h6 class="mb-3"><i class="fa fa-list-ol"></i> وضعیت دوره‌ها</h6>
                                                <div class="period-timeline">
                                                    ${info.periods.map((period, i) => {
                                                        const isPaid = period.is_paid;
                                                        const isCurrent = period.is_current;
                                                        const periodAmount = parseFloat(period.amount);
                                                        const startDate = new Date(period.start_date).toLocaleDateString('fa-IR');
                                                        const endDate = new Date(period.end_date).toLocaleDateString('fa-IR');
                                                        return `
                                                            <div class="period-item ${isCurrent ? 'current' : ''} ${isPaid ? 'paid' : 'unpaid'} mb-2 p-3 rounded">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div class="d-flex align-items-center">
                                                                        <span class="badge ${isPaid ? 'badge-success' : isCurrent ? 'badge-warning' : 'badge-danger'} mr-3" style="min-width: 140px; font-size: 0.9rem;">
                                                                            ${isPaid ? '<i class="fa fa-check-circle"></i> پرداخت شده' : isCurrent ? '<i class="fa fa-clock"></i> در انتظار پرداخت' : '<i class="fa fa-times-circle"></i> پرداخت نشده'}
                                                                        </span>
                                                                        <div>
                                                                            <strong style="font-size: 1.1rem;">دوره ${period.period_number + 1}</strong>
                                                                            ${isCurrent ? '<span class="badge badge-warning ml-2"><i class="fa fa-exclamation-triangle"></i> دوره فعلی</span>' : ''}
                                                                            <div class="text-muted small mt-1">
                                                                                ${startDate} تا ${endDate} (${period.days} روز)
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <div class="font-weight-bold ${isPaid ? 'text-success' : isCurrent ? 'text-warning' : 'text-danger'}" style="font-size: 1.1rem;">
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
                                        <div class="alert alert-primary border-left-primary" style="border-left: 4px solid #007bff;">
                                            <div class="d-flex align-items-start">
                                                <i class="fa fa-credit-card fa-2x mr-3 mt-1"></i>
                                                <div class="flex-grow-1">
                                                    <h6 class="alert-heading mb-2">پرداخت کامل پکیج</h6>
                                                    <p class="mb-0">می‌توانید کل مبلغ باقی‌مانده را پرداخت کنید.</p>
                                                </div>
                                            </div>
                                        </div>
                                    `}
                                    
                                    <!-- Payment Form -->
                                    <div class="payment-form-section">
                                        <h6 class="mb-3"><i class="fa fa-credit-card"></i> فرم پرداخت</h6>
                                        <form class="payment-form" data-package-id="${info.package_id}" data-payment-type="${info.payment_type}">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold mb-2">
                                                    <i class="fa fa-money-bill-wave"></i> مبلغ پرداختی (تومان)
                                                </label>
                                                <div class="input-group input-group-lg shadow-sm">
                                                    <input type="number" 
                                                           class="form-control payment-amount text-center font-weight-bold" 
                                                           value="${isPeriodPayment ? info.period_amount : info.remaining_amount}" 
                                                           min="0" 
                                                           step="1000" 
                                                           ${isPeriodPayment ? 'readonly style="background-color: #e9ecef; cursor: not-allowed;"' : ''}
                                                           required>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text bg-white font-weight-bold">تومان</span>
                                                    </div>
                                                </div>
                                                ${isPeriodPayment ? `
                                                    <div class="alert alert-secondary mt-2 mb-0 py-2">
                                                        <i class="fa fa-lock"></i> 
                                                        <small>مبلغ دوره به صورت خودکار محاسبه شده است و قابل تغییر نیست</small>
                                                    </div>
                                                ` : ''}
                                            </div>
                                        
                                            ${!isPeriodPayment ? `
                                                <div class="form-check mb-4 p-3 bg-white rounded shadow-sm border">
                                                    <input class="form-check-input" type="checkbox" id="pay-full-${index}" checked style="width: 20px; height: 20px; margin-top: 0.3rem;">
                                                    <label class="form-check-label ml-3" for="pay-full-${index}" style="font-size: 1rem; cursor: pointer; line-height: 1.6;">
                                                        <strong><i class="fa fa-check-square"></i> پرداخت کل مبلغ باقی‌مانده</strong>
                                                        <br>
                                                        <span class="text-muted">(${parseFloat(info.remaining_amount).toLocaleString('fa-IR')} تومان)</span>
                                                    </label>
                                                </div>
                                            ` : ''}
                                            
                                            <input type="hidden" name="period" value="${info.current_period || ''}">
                                            <button type="submit" class="btn btn-lg btn-success btn-block shadow-lg" style="border-radius: 10px; padding: 15px; font-size: 1.1rem; font-weight: bold;">
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
                    const periodNum = period ? parseInt(period) + 1 : '';
                    
                    swal({
                        title: 'تأیید پرداخت',
                        html: `
                            <div class="text-right" style="direction: rtl;">
                                <p class="mb-3">آیا مطمئن هستید که می‌خواهید پرداخت را انجام دهید؟</p>
                                <div class="alert alert-info text-right">
                                    <strong>مبلغ پرداختی:</strong><br>
                                    <span style="font-size: 1.5rem; color: #007bff;">${amount.toLocaleString('fa-IR')} تومان</span>
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
                    // Get remaining amount from the summary box (third column)
                    const remainingAmountText = cardBody.find('.col-md-4').last().find('.h5').text().replace(/[^\d.]/g, '');
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
                            // Reload payment info
                            loadPaymentInfo();
                            // Redirect to dashboard after 2 seconds
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

