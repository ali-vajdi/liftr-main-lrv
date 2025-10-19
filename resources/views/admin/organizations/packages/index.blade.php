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
                            ],
                            'primaryKey' => 'id',
                            'actions' => '
                                // Show button
                                html += \'<button type="button" class="btn btn-sm btn-info show-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                                
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
                swal({
                    title: 'غیرفعال کردن پکیج',
                    text: 'آیا می‌خواهید این پکیج را غیرفعال کنید؟',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'بله، غیرفعال کن',
                    cancelButtonText: 'انصراف',
                    confirmButtonColor: '#f39c12',
                    padding: '2em'
                }).then((result) => {
                    if (result.value) {
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
                                swal({
                                    title: 'موفق',
                                    text: 'پکیج با موفقیت غیرفعال شد',
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
                    }
                });
            };

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
