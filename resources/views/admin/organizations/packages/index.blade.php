@extends('admin.layout.master')

@section('title', 'مدیریت پکیج‌های شرکت')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">مدیریت پکیج‌های شرکت: {{ $organization->name }}</h5>
                            <div>
                                <button type="button" class="btn btn-primary create-new-button">
                                    <i class="fa fa-plus"></i> اختصاص پکیج جدید
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="widget-content">
                        <!-- Package Summary -->
                        @include('admin.organizations.packages.partials.package-summary', ['organization' => $organization])

                        @include('admin.components.datatable', [
                            'title' => 'پکیج‌های اختصاص داده شده',
                            'apiUrl' => '/api/admin/organizations/' . $organization->id . '/packages',
                            'createButton' => false,
                            'hideDefaultActions' => true,
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                ['field' => 'package_name', 'label' => 'نام پکیج (اختصاص داده شده)'],
                                ['field' => 'package_duration_label', 'label' => 'مدت زمان (اختصاص داده شده)'],
                                ['field' => 'formatted_price', 'label' => 'قیمت (اختصاص داده شده)'],
                                [
                                    'field' => 'started_at',
                                    'label' => 'تاریخ شروع',
                                    'formatter' => 'function(value) {
                                        return new Date(value).toLocaleDateString("fa-IR");
                                    }',
                                ],
                                [
                                    'field' => 'expires_at',
                                    'label' => 'تاریخ انقضا',
                                    'formatter' => 'function(value) {
                                        return new Date(value).toLocaleDateString("fa-IR");
                                    }',
                                ],
                                [
                                    'field' => 'is_active',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? `<span class="badge badge-success">فعال</span>` : `<span class="badge badge-danger">غیرفعال</span>`;
                                    }',
                                ],
                                [
                                    'field' => 'has_package_changed',
                                    'label' => 'تغییر پکیج',
                                    'formatter' => 'function(value) {
                                        if (value) {
                                            return `<span class="badge badge-warning">تغییر کرده</span>`;
                                        } else {
                                            return `<span class="badge badge-success">بدون تغییر</span>`;
                                        }
                                    }',
                                ],
                                [
                                    'field' => 'payment_status_text',
                                    'label' => 'وضعیت پرداخت',
                                    'formatter' => 'function(value, item) {
                                        var badgeClass = item.payment_status_badge_class || "badge-secondary";
                                        return `<span class="badge ${badgeClass}">${value || "نامشخص"}</span>`;
                                    }',
                                ],
                                [
                                    'field' => 'formatted_total_paid_amount',
                                    'label' => 'پرداخت شده',
                                    'formatter' => 'function(value) {
                                        return value || "0 تومان";
                                    }',
                                ],
                                [
                                    'field' => 'formatted_remaining_amount',
                                    'label' => 'باقی‌مانده',
                                    'formatter' => 'function(value) {
                                        return value || "0 تومان";
                                    }',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'actions' => '
                                // Show button
                                html += \'<button type="button" class="btn btn-sm btn-info show-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                                
                                // Payment button (only for active packages)
                                if (item.is_active) {
                                    html += \'<button type="button" class="btn btn-sm btn-success payment-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مدیریت پرداخت">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>\';
                                    html += \'</button>\';
                                }
                                
                                // Deactivate button (only for active packages)
                                if (item.is_active) {
                                    html += \'<button type="button" class="btn btn-sm btn-warning deactivate-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="غیرفعال کردن">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-toggle-left"><rect x="1" y="5" width="22" height="14" rx="7" ry="7"></rect><circle cx="8" cy="12" r="3"></circle></svg>\';
                                    html += \'</button>\';
                                }
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(".show-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShow(id);
                                });
                                
                                // Handle payment button click
                                $(".payment-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onPayment(id);
                                });
                                
                                // Handle deactivate button click
                                $(".deactivate-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onDeactivate(id);
                                });
                            ',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for assigning package -->
        <div class="modal fade" id="assignPackageModal" tabindex="-1" role="dialog" aria-labelledby="assignPackageModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignPackageModalLabel">اختصاص پکیج جدید</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="assignPackageForm">
                            <div class="form-group">
                                <label for="package_id">انتخاب پکیج</label>
                                <select class="form-control" id="package_id" name="package_id" required>
                                    <option value="">انتخاب کنید...</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="started_at">تاریخ شروع (اختیاری)</label>
                                <input type="datetime-local" class="form-control" id="started_at" name="started_at">
                                <small class="form-text text-muted">اگر خالی باشد، از زمان فعلی شروع می‌شود</small>
                            </div>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <strong>نکته:</strong> اگر شرکت پکیج‌های فعالی داشته باشد، روزهای باقی‌مانده از همه پکیج‌های فعال به پکیج جدید اضافه خواهد شد.
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="saveAssignPackage">اختصاص پکیج</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="paymentModalLabel">مدیریت پرداخت پکیج</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="paymentPackageInfo" class="mb-4"></div>
                        
                        <ul class="nav nav-tabs" id="paymentTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="add-payment-tab" data-toggle="tab" href="#add-payment" role="tab">افزودن پرداخت</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="payments-list-tab" data-toggle="tab" href="#payments-list" role="tab">لیست پرداخت‌ها</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content mt-3" id="paymentTabContent">
                            <div class="tab-pane fade show active" id="add-payment" role="tabpanel">
                                <form id="addPaymentForm">
                                    <div class="form-group">
                                        <label for="payment_method_id">روش پرداخت <span class="text-danger">*</span></label>
                                        <select class="form-control" id="payment_method_id" name="payment_method_id" required>
                                            <option value="">انتخاب کنید...</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="payment_amount">مبلغ پرداختی (تومان)</label>
                                        <input type="number" class="form-control" id="payment_amount" name="amount" min="0" step="1000" required>
                                        <small class="form-text text-muted" id="payment_amount_help"></small>
                                    </div>
                                    <div class="form-group">
                                        <label for="payment_date">تاریخ پرداخت</label>
                                        <input type="datetime-local" class="form-control" id="payment_date" name="payment_date">
                                        <small class="form-text text-muted">اگر خالی باشد، تاریخ فعلی استفاده می‌شود</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="payment_notes">یادداشت (اختیاری)</label>
                                        <textarea class="form-control" id="payment_notes" name="notes" rows="3"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">ثبت پرداخت</button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="payments-list" role="tabpanel">
                                <div id="paymentsListContainer">
                                    <div class="text-center p-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">در حال بارگذاری...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailsModalLabel">جزئیات پکیج</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>شناسه</th>
                                        <td id="detailId"></td>
                                    </tr>
                                    <tr>
                                        <th>نام پکیج (اختصاص داده شده)</th>
                                        <td id="detailPackageName"></td>
                                    </tr>
                                    <tr>
                                        <th>مدت زمان (اختصاص داده شده)</th>
                                        <td id="detailDuration"></td>
                                    </tr>
                                    <tr>
                                        <th>قیمت (اختصاص داده شده)</th>
                                        <td id="detailPrice"></td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ شروع</th>
                                        <td id="detailStartedAt"></td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ انقضا</th>
                                        <td id="detailExpiresAt"></td>
                                    </tr>
                                    <tr>
                                        <th>تغییر پکیج</th>
                                        <td id="detailPackageChanged"></td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ ایجاد</th>
                                        <td id="detailCreatedAt"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Current vs Assigned Package Info -->
                        <div id="packageComparison" style="display: none;">
                            <hr>
                            <h6>مقایسه اطلاعات پکیج</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">اطلاعات فعلی پکیج</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>نام:</strong> <span id="currentPackageName"></span></p>
                                            <p><strong>مدت زمان:</strong> <span id="currentPackageDuration"></span></p>
                                            <p><strong>قیمت:</strong> <span id="currentPackagePrice"></span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0">اطلاعات اختصاص داده شده</h6>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>نام:</strong> <span id="assignedPackageName"></span></p>
                                            <p><strong>مدت زمان:</strong> <span id="assignedPackageDuration"></span></p>
                                            <p><strong>قیمت:</strong> <span id="assignedPackagePrice"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('page-scripts')
    <script>
        $(document).ready(function() {
            let currentPackageId = null;

            // Load available packages
            loadAvailablePackages();

            // Show package details
            window.onShow = function(id) {
                $.ajax({
                    url: `/api/admin/organizations/{{ $organization->id }}/packages/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#detailId').text(data.id);
                        $('#detailPackageName').text(data.package_name);
                        $('#detailDuration').text(data.package_duration_label);
                        $('#detailPrice').text(data.formatted_price);
                        $('#detailStartedAt').text(new Date(data.started_at).toLocaleDateString('fa-IR'));
                        $('#detailExpiresAt').text(new Date(data.expires_at).toLocaleDateString('fa-IR'));
                        $('#detailPackageChanged').html(data.has_package_changed ? 
                            '<span class="badge badge-warning">تغییر کرده</span>' : 
                            '<span class="badge badge-success">بدون تغییر</span>'
                        );
                        $('#detailCreatedAt').text(new Date(data.created_at).toLocaleDateString('fa-IR'));

                        // Show package comparison if package has changed
                        if (data.has_package_changed) {
                            $('#currentPackageName').text(data.current_package_info.name);
                            $('#currentPackageDuration').text(data.current_package_info.duration_label);
                            $('#currentPackagePrice').text(data.current_package_info.formatted_price);
                            
                            $('#assignedPackageName').text(data.assigned_package_info.name);
                            $('#assignedPackageDuration').text(data.assigned_package_info.duration_label);
                            $('#assignedPackagePrice').text(data.assigned_package_info.formatted_price);
                            
                            $('#packageComparison').show();
                        } else {
                            $('#packageComparison').hide();
                        }

                        $('#detailsModal').modal('show');
                    },
                    error: function(xhr) {
                        if (xhr.status === 404) {
                            swal({
                                title: 'خطا',
                                text: 'پکیج مورد نظر یافت نشد',
                                type: 'error',
                                padding: '2em'
                            });
                        } else if (xhr.status === 401) {
                            swal({
                                title: 'خطای دسترسی',
                                text: 'لطفا مجددا وارد سیستم شوید',
                                type: 'error',
                                padding: '2em'
                            }).then(function() {
                                window.location.href = '/admin/login';
                            });
                        } else {
                            swal({
                                title: 'خطا',
                                text: 'خطا در دریافت اطلاعات',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            };

            // Create new package assignment
            $('.create-new-button').click(function() {
                $('#assignPackageModalLabel').text('اختصاص پکیج جدید');
                $('#assignPackageForm')[0].reset();
                loadAvailablePackages();
                $('#assignPackageModal').modal('show');
            });

            // Save package assignment
            $('#saveAssignPackage').click(function() {
                const packageId = $('#package_id').val();
                const startedAt = $('#started_at').val();

                if (!packageId) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا پکیج را انتخاب کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const data = {
                    package_id: packageId,
                    started_at: startedAt || null
                };

                $.ajax({
                    url: `/api/admin/organizations/{{ $organization->id }}/packages`,
                    type: 'POST',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        $('#assignPackageModal').modal('hide');
                        
                        swal({
                            title: 'موفقیت',
                            text: response.message,
                            type: 'success',
                            padding: '2em'
                        });

                        window.datatableApi.refresh();
                        // Reload the page to update the package summary
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            let errorMessage = '';

                            for (const key in errors) {
                                errorMessage += errors[key].join('\n') + '\n';
                            }

                            swal({
                                title: 'خطا در اعتبارسنجی',
                                text: errorMessage,
                                type: 'error',
                                padding: '2em'
                            });
                        } else if (xhr.status === 401) {
                            swal({
                                title: 'خطای دسترسی',
                                text: 'لطفا مجددا وارد سیستم شوید',
                                type: 'error',
                                padding: '2em'
                            }).then(function() {
                                window.location.href = '/admin/login';
                            });
                        } else {
                            swal({
                                title: 'خطا',
                                text: 'خطا در ذخیره اطلاعات',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            });

            // Deactivate package
            window.onDeactivate = function(id) {
                // First check if package has payments
                $.ajax({
                    url: `/api/admin/organizations/{{ $organization->id }}/packages/${id}`,
                    type: 'PUT',
                    data: {
                        is_active: false
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        // Check if response requires confirmation
                        if (response.requires_confirmation && response.warning) {
                            const packageInfo = response.package_info;
                            swal({
                                title: 'هشدار: پکیج دارای پرداخت است',
                                html: `
                                    <p>این پکیج دارای پرداخت است:</p>
                                    <ul style="text-align: right; direction: rtl;">
                                        <li>مبلغ پرداخت شده: <strong>${parseFloat(packageInfo.total_paid).toLocaleString('fa-IR')} تومان</strong></li>
                                        <li>مبلغ باقی‌مانده: <strong>${parseFloat(packageInfo.remaining_amount).toLocaleString('fa-IR')} تومان</strong></li>
                                        <li>وضعیت پرداخت: <strong>${packageInfo.payment_status_text}</strong></li>
                                    </ul>
                                    <p>آیا مطمئن هستید که می‌خواهید این پکیج را غیرفعال کنید؟</p>
                                `,
                                type: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'بله، غیرفعال کن',
                                cancelButtonText: 'انصراف',
                                confirmButtonColor: '#f39c12',
                                padding: '2em'
                            }).then((result) => {
                                if (result.value) {
                                    // Force disable
                                    $.ajax({
                                        url: `/api/admin/organizations/{{ $organization->id }}/packages/${id}/force-disable`,
                                        type: 'POST',
                                        data: {
                                            confirm: true
                                        },
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                            'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                                        },
                                        success: function(response) {
                                            swal({
                                                title: 'موفق',
                                                text: response.message,
                                                type: 'success',
                                                padding: '2em'
                                            });
                                            window.datatableApi.refresh();
                                            setTimeout(function() {
                                                location.reload();
                                            }, 1500);
                                        },
                                        error: function() {
                                            swal({
                                                title: 'خطا',
                                                text: 'خطا در غیرفعال کردن پکیج',
                                                type: 'error',
                                                padding: '2em'
                                            });
                                        }
                                    });
                                }
                            });
                        } else {
                            // No payments, proceed normally
                            swal({
                                title: 'موفق',
                                text: 'پکیج با موفقیت غیرفعال شد',
                                type: 'success',
                                padding: '2em'
                            });
                            window.datatableApi.refresh();
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            swal({
                                title: 'خطای دسترسی',
                                text: 'لطفا مجددا وارد سیستم شوید',
                                type: 'error',
                                padding: '2em'
                            }).then(function() {
                                window.location.href = '/admin/login';
                            });
                        } else {
                            swal({
                                title: 'خطا',
                                text: 'خطا در غیرفعال کردن پکیج',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            };

            // Payment management
            window.onPayment = function(id) {
                $.ajax({
                    url: `/api/admin/organizations/{{ $organization->id }}/packages/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const pkg = response.data;
                        const packageInfo = `
                            <div class="card">
                                <div class="card-body">
                                    <h6>${pkg.package_name}</h6>
                                    <p class="mb-1"><strong>قیمت کل:</strong> ${pkg.formatted_price}</p>
                                    <p class="mb-1"><strong>پرداخت شده:</strong> <span class="text-success">${pkg.formatted_total_paid_amount || '0 تومان'}</span></p>
                                    <p class="mb-1"><strong>باقی‌مانده:</strong> <span class="text-danger">${pkg.formatted_remaining_amount || '0 تومان'}</span></p>
                                    <p class="mb-0"><strong>وضعیت:</strong> <span class="badge ${pkg.payment_status_badge_class}">${pkg.payment_status_text}</span></p>
                                    ${!pkg.can_accept_partial_payment ? '<p class="text-warning mt-2"><small><i class="fa fa-info-circle"></i> این پکیج باید به صورت کامل پرداخت شود</small></p>' : ''}
                                </div>
                            </div>
                        `;
                        $('#paymentPackageInfo').html(packageInfo);
                        $('#payment_amount').attr('max', pkg.remaining_amount || pkg.package_price);
                        $('#payment_amount_help').text(`حداکثر مبلغ قابل پرداخت: ${pkg.formatted_remaining_amount || pkg.formatted_price}`);
                        $('#paymentModal').data('package-id', id);
                        $('#paymentModal').modal('show');
                        loadPaymentMethods();
                        loadPaymentsList(id);
                    },
                    error: function(xhr) {
                        swal({
                            title: 'خطا',
                            text: 'خطا در دریافت اطلاعات پکیج',
                            type: 'error',
                            padding: '2em'
                        });
                    }
                });
            };

            // Load payment methods
            function loadPaymentMethods() {
                $.ajax({
                    url: '/api/admin/payment-methods',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const select = $('#payment_method_id');
                        select.empty();
                        select.append('<option value="">انتخاب کنید...</option>');
                        
                        response.data.forEach(function(method) {
                            select.append(`<option value="${method.id}">${method.name}${method.is_system ? ' (سیستمی)' : ''}</option>`);
                        });
                    },
                    error: function() {
                        console.error('Error loading payment methods');
                    }
                });
            }

            // Load payments list
            function loadPaymentsList(packageId) {
                $.ajax({
                    url: `/api/admin/organizations/{{ $organization->id }}/packages/${packageId}/payments`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        let html = '<table class="table table-striped">';
                        html += '<thead><tr><th>مبلغ</th><th>روش پرداخت</th><th>تاریخ</th><th>یادداشت</th><th>ثبت کننده</th><th>عملیات</th></tr></thead><tbody>';
                        
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(function(payment) {
                                html += `<tr>
                                    <td>${parseFloat(payment.amount).toLocaleString('fa-IR')} تومان</td>
                                    <td>${payment.payment_method ? payment.payment_method.name + (payment.payment_method.is_system ? ' <span class="badge badge-info">سیستمی</span>' : '') : '-'}</td>
                                    <td>${new Date(payment.payment_date).toLocaleDateString('fa-IR')}</td>
                                    <td>${payment.notes || '-'}</td>
                                    <td>${payment.moderator ? payment.moderator.name : '-'}</td>
                                    <td>
                                        <button class="btn btn-sm btn-danger delete-payment-btn" data-payment-id="${payment.id}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>`;
                            });
                        } else {
                            html += '<tr><td colspan="6" class="text-center">هیچ پرداختی ثبت نشده است</td></tr>';
                        }
                        
                        html += '</tbody></table>';
                        $('#paymentsListContainer').html(html);
                        
                        // Handle delete payment
                        $('.delete-payment-btn').on('click', function() {
                            const paymentId = $(this).data('payment-id');
                            deletePayment(packageId, paymentId);
                        });
                    },
                    error: function() {
                        $('#paymentsListContainer').html('<div class="alert alert-danger">خطا در بارگذاری لیست پرداخت‌ها</div>');
                    }
                });
            }

            // Add payment form submit
            $('#addPaymentForm').on('submit', function(e) {
                e.preventDefault();
                const packageId = $('#paymentModal').data('package-id');
                const amount = $('#payment_amount').val();
                const paymentMethodId = $('#payment_method_id').val();
                const paymentDate = $('#payment_date').val();
                const notes = $('#payment_notes').val();

                $.ajax({
                    url: `/api/admin/organizations/{{ $organization->id }}/packages/${packageId}/payments`,
                    type: 'POST',
                    data: {
                        amount: amount,
                        payment_method_id: paymentMethodId,
                        payment_date: paymentDate || null,
                        notes: notes || null
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        swal({
                            title: 'موفقیت',
                            text: response.message,
                            type: 'success',
                            padding: '2em'
                        });
                        $('#addPaymentForm')[0].reset();
                        window.onPayment(packageId); // Reload payment info
                        window.datatableApi.refresh();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            let errorMessage = '';
                            for (const key in errors) {
                                errorMessage += errors[key].join('\n') + '\n';
                            }
                            swal({
                                title: 'خطا در اعتبارسنجی',
                                text: errorMessage,
                                type: 'error',
                                padding: '2em'
                            });
                        } else {
                            const message = xhr.responseJSON?.message || 'خطا در ثبت پرداخت';
                            swal({
                                title: 'خطا',
                                text: message,
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            });

            // Delete payment
            function deletePayment(packageId, paymentId) {
                swal({
                    title: 'حذف پرداخت',
                    text: 'آیا مطمئن هستید که می‌خواهید این پرداخت را حذف کنید؟',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'بله، حذف کن',
                    cancelButtonText: 'انصراف',
                    confirmButtonColor: '#d33',
                    padding: '2em'
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: `/api/admin/organizations/{{ $organization->id }}/packages/${packageId}/payments/${paymentId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                            },
                            success: function(response) {
                                swal({
                                    title: 'موفق',
                                    text: response.message,
                                    type: 'success',
                                    padding: '2em'
                                });
                                window.onPayment(packageId); // Reload payment info
                                window.datatableApi.refresh();
                            },
                            error: function() {
                                swal({
                                    title: 'خطا',
                                    text: 'خطا در حذف پرداخت',
                                    type: 'error',
                                    padding: '2em'
                                });
                            }
                        });
                    }
                });
            }

            // Reload payments when switching tabs
            $('#payments-list-tab').on('shown.bs.tab', function() {
                const packageId = $('#paymentModal').data('package-id');
                if (packageId) {
                    loadPaymentsList(packageId);
                }
            });

        });

        function loadAvailablePackages() {
            $.ajax({
                url: `/api/admin/organizations/{{ $organization->id }}/packages/available`,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                },
                success: function(response) {
                    const select = $('#package_id');
                    select.empty();
                    select.append('<option value="">انتخاب کنید...</option>');
                    
                    response.data.forEach(package => {
                        select.append(`<option value="${package.id}" data-price="${package.price}" data-duration="${package.duration_label}">${package.name} - ${package.duration_label} - ${package.formatted_price}</option>`);
                    });
                },
                error: function() {
                    swal({
                        title: 'خطا',
                        text: 'خطا در دریافت لیست پکیج‌ها',
                        type: 'error',
                        padding: '2em'
                    });
                }
            });
        }
    </script>
@endsection
