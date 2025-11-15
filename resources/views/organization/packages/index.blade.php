@extends('organization.layout.master')

@section('title', 'پکیج‌های من')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0 text-white">پکیج‌های من - <span id="org-name-packages">...</span></h5>
                    </div>
                    <div class="widget-content">
                        <!-- Package Summary -->
                        <div id="package-summary-container">
                            <div class="text-center p-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">در حال بارگذاری...</span>
                                </div>
                            </div>
                        </div>

                        @include('organization.components.datatable', [
                            'title' => 'پکیج‌های اختصاص داده شده',
                            'apiUrl' => '/api/organization/packages',
                            'createButton' => false,
                            'hideDefaultActions' => true,
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                ['field' => 'package_name', 'label' => 'نام پکیج'],
                                ['field' => 'package_duration_label', 'label' => 'مدت زمان'],
                                ['field' => 'formatted_price', 'label' => 'قیمت کل'],
                                [
                                    'field' => 'payment_status_text',
                                    'label' => 'وضعیت پرداخت',
                                    'formatter' => 'function(value, row) {
                                        var badgeClass = row.payment_status_badge_class || "badge-secondary";
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
                                [
                                    'field' => 'use_periods',
                                    'label' => 'استفاده از دوره',
                                    'formatter' => 'function(value) {
                                        return value ? `<span class="badge badge-info">بله</span>` : `<span class="badge badge-secondary">خیر</span>`;
                                    }',
                                ],
                                [
                                    'field' => 'total_periods',
                                    'label' => 'تعداد دوره‌ها',
                                    'formatter' => 'function(value, row) {
                                        if (row.use_periods && value) {
                                            return `<span class="badge badge-primary">${value} دوره</span>`;
                                        }
                                        return "-";
                                    }',
                                ],
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
                                    'field' => 'remaining_days',
                                    'label' => 'روزهای باقی‌مانده',
                                    'formatter' => 'function(value) {
                                        if (value === null || value === undefined) return "-";
                                        var badgeClass = value <= 0 ? "badge-danger" : (value <= 7 ? "badge-warning" : "badge-success");
                                        return `<span class="badge ${badgeClass}">${value} روز</span>`;
                                    }',
                                ],
                                [
                                    'field' => 'is_active',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? `<span class="badge badge-success">فعال</span>` : `<span class="badge badge-danger">غیرفعال</span>`;
                                    }',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'actions' => '
                                // Show button
                                html += \'<button type="button" class="btn btn-sm btn-info show-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(".show-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShow(id);
                                });
                            ',
                        ])
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
                        <ul class="nav nav-tabs" id="packageDetailTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">اطلاعات پکیج</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="payment-tab" data-toggle="tab" href="#payment" role="tab">وضعیت پرداخت</a>
                            </li>
                            <li class="nav-item" id="periods-tab-item" style="display: none;">
                                <a class="nav-link" id="periods-tab" data-toggle="tab" href="#periods" role="tab">دوره‌های پرداخت</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content mt-3" id="packageDetailTabContent">
                            <!-- Package Info Tab -->
                            <div class="tab-pane fade show active" id="info" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th>شناسه</th>
                                                <td id="detailId"></td>
                                            </tr>
                                            <tr>
                                                <th>نام پکیج (اختصاص یافته)</th>
                                                <td id="detailAssignedPackageName"></td>
                                            </tr>
                                            <tr>
                                                <th>مدت زمان (اختصاص یافته)</th>
                                                <td id="detailAssignedDuration"></td>
                                            </tr>
                                            <tr>
                                                <th>قیمت (اختصاص یافته)</th>
                                                <td id="detailAssignedPrice"></td>
                                            </tr>
                                            <tr>
                                                <th>نام پکیج (فعلی)</th>
                                                <td id="detailCurrentPackageName"></td>
                                            </tr>
                                            <tr>
                                                <th>مدت زمان (فعلی)</th>
                                                <td id="detailCurrentDuration"></td>
                                            </tr>
                                            <tr>
                                                <th>قیمت (فعلی)</th>
                                                <td id="detailCurrentPrice"></td>
                                            </tr>
                                            <tr>
                                                <th>تغییر کرده است؟</th>
                                                <td id="detailHasChanged"></td>
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
                                                <th>تاریخ ایجاد</th>
                                                <td id="detailCreatedAt"></td>
                                            </tr>
                                            <tr>
                                                <th>استفاده از دوره</th>
                                                <td id="detailUsePeriods"></td>
                                            </tr>
                                            <tr id="detailPeriodDaysRow" style="display: none;">
                                                <th>روزهای هر دوره</th>
                                                <td id="detailPeriodDays"></td>
                                            </tr>
                                            <tr id="detailTotalPeriodsRow" style="display: none;">
                                                <th>تعداد کل دوره‌ها</th>
                                                <td id="detailTotalPeriods"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Payment Info Tab -->
                            <div class="tab-pane fade" id="payment" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th>وضعیت پرداخت</th>
                                                <td id="detailPaymentStatus"></td>
                                            </tr>
                                            <tr>
                                                <th>قیمت کل</th>
                                                <td id="detailTotalAmount"></td>
                                            </tr>
                                            <tr>
                                                <th>مبلغ پرداخت شده</th>
                                                <td id="detailPaidAmount"></td>
                                            </tr>
                                            <tr>
                                                <th>مبلغ باقی‌مانده</th>
                                                <td id="detailRemainingAmount"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Periods Tab -->
                            <div class="tab-pane fade" id="periods" role="tabpanel">
                                <div id="periodsListContainer">
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
    </div>
@endsection

@section('page-scripts')
    <script>
        $(document).ready(function() {
            // Show package details
            window.onShow = function(id) {
                $.ajax({
                    url: `/api/organization/packages/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        // Package Info Tab
                        $('#detailId').text(data.id);
                        $('#detailAssignedPackageName').text(data.assigned_package_info.name);
                        $('#detailAssignedDuration').text(data.assigned_package_info.duration_label);
                        $('#detailAssignedPrice').text(data.assigned_package_info.formatted_price);

                        if (data.current_package_info) {
                            $('#detailCurrentPackageName').text(data.current_package_info.name);
                            $('#detailCurrentDuration').text(data.current_package_info.duration_label);
                            $('#detailCurrentPrice').text(data.current_package_info.formatted_price);
                            $('#detailHasChanged').html(data.has_package_changed ? '<span class="badge badge-warning">بله</span>' : '<span class="badge badge-success">خیر</span>');
                        } else {
                            $('#detailCurrentPackageName').text('پکیج اصلی حذف شده');
                            $('#detailCurrentDuration').text('-');
                            $('#detailCurrentPrice').text('-');
                            $('#detailHasChanged').html('<span class="badge badge-danger">بله (حذف شده)</span>');
                        }
                        
                        $('#detailStartedAt').text(new Date(data.started_at).toLocaleDateString('fa-IR'));
                        $('#detailExpiresAt').text(new Date(data.expires_at).toLocaleDateString('fa-IR'));
                        $('#detailCreatedAt').text(new Date(data.created_at).toLocaleDateString('fa-IR'));
                        
                        // Period settings
                        const usePeriods = data.use_periods || false;
                        $('#detailUsePeriods').html(usePeriods ? '<span class="badge badge-info">بله</span>' : '<span class="badge badge-secondary">خیر</span>');
                        
                        if (usePeriods) {
                            $('#detailPeriodDaysRow').show();
                            $('#detailTotalPeriodsRow').show();
                            $('#detailPeriodDays').text(data.period_days ? data.period_days + ' روز' : '30 روز (پیش‌فرض)');
                            $('#detailTotalPeriods').text(data.total_periods || 'نامشخص');
                            $('#periods-tab-item').show();
                        } else {
                            $('#detailPeriodDaysRow').hide();
                            $('#detailTotalPeriodsRow').hide();
                            $('#periods-tab-item').hide();
                        }
                        
                        // Payment Info Tab
                        $('#detailPaymentStatus').html(`<span class="badge ${data.payment_status_badge_class || 'badge-secondary'}">${data.payment_status_text || 'نامشخص'}</span>`);
                        $('#detailTotalAmount').text(data.formatted_price || '0 تومان');
                        $('#detailPaidAmount').text(data.formatted_total_paid_amount || '0 تومان');
                        $('#detailRemainingAmount').text(data.formatted_remaining_amount || '0 تومان');
                        
                        // Periods Tab
                        if (usePeriods && data.periods && data.periods.length > 0) {
                            renderPeriodsList(data.periods);
                        } else {
                            $('#periodsListContainer').html('<div class="alert alert-info text-center">این پکیج از دوره استفاده نمی‌کند</div>');
                        }
                        
                        // Reset tabs to first tab
                        $('#packageDetailTabs a[href="#info"]').tab('show');
                        
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
                                window.location.href = '/login';
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
            
            // Render periods list
            function renderPeriodsList(periods) {
                let html = '<div class="table-responsive"><table class="table table-bordered table-hover">';
                html += '<thead class="thead-light"><tr><th>دوره</th><th>روزها</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ شروع</th><th>تاریخ پایان</th><th>تاریخ پرداخت</th></tr></thead><tbody>';
                
                periods.forEach(function(period) {
                    const isPaid = period.is_paid;
                    const isCurrent = period.is_current;
                    const startDate = new Date(period.start_date).toLocaleDateString('fa-IR');
                    const endDate = new Date(period.end_date).toLocaleDateString('fa-IR');
                    const paidAt = period.paid_at ? new Date(period.paid_at).toLocaleDateString('fa-IR') : '-';
                    
                    html += `<tr class="${isPaid ? 'table-success' : isCurrent ? 'table-warning' : ''}">
                        <td><strong>دوره ${period.period_number + 1}</strong>${isCurrent ? ' <span class="badge badge-warning">فعلی</span>' : ''}</td>
                        <td>${period.days} روز</td>
                        <td><strong class="text-primary">${parseFloat(period.amount).toLocaleString('fa-IR')} تومان</strong></td>
                        <td>
                            ${isPaid ? 
                                '<span class="badge badge-success"><i class="fa fa-check-circle"></i> پرداخت شده</span>' : 
                                '<span class="badge badge-danger"><i class="fa fa-times-circle"></i> پرداخت نشده</span>'
                            }
                        </td>
                        <td>${startDate}</td>
                        <td>${endDate}</td>
                        <td>${paidAt}</td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                $('#periodsListContainer').html(html);
            }
        });

        // Load organization name
        getOrganizationData(function(org, error) {
            if (!error && org) {
                $('#org-name-packages').text(org.name);
            }
        });

        // Load package summary
        var token = localStorage.getItem('organization_token');
        if (token) {
            $.ajax({
                url: '/api/organization/packages',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                success: function(response) {
                    if (response.data && response.data.length > 0) {
                        var packages = response.data;
                        var activePackages = packages.filter(function(pkg) { return pkg.is_active; });
                        var totalRemainingDays = activePackages.reduce(function(sum, pkg) { 
                            return sum + (pkg.remaining_days || 0); 
                        }, 0);
                        var totalAmountPaid = activePackages.reduce(function(sum, pkg) { 
                            return sum + (parseFloat(pkg.total_paid_amount || 0)); 
                        }, 0);
                        var totalRemainingAmount = activePackages.reduce(function(sum, pkg) { 
                            return sum + (parseFloat(pkg.remaining_amount || 0)); 
                        }, 0);
                        var totalPackagePrice = activePackages.reduce(function(sum, pkg) { 
                            return sum + (parseFloat(pkg.package_price || 0)); 
                        }, 0);
                        var averageDaysPerPackage = activePackages.length > 0 ? 
                            Math.round(totalRemainingDays / activePackages.length * 10) / 10 : 0;
                        
                        var longestPackage = activePackages.length > 0 ? 
                            activePackages.sort(function(a, b) { 
                                return (b.remaining_days || 0) - (a.remaining_days || 0); 
                            })[0] : null;
                        var shortestPackage = activePackages.length > 0 ? 
                            activePackages.sort(function(a, b) { 
                                return (a.remaining_days || 0) - (b.remaining_days || 0); 
                            })[0] : null;
                        var latestExpiry = activePackages.length > 0 ? 
                            new Date(Math.max.apply(null, activePackages.map(function(pkg) { 
                                return new Date(pkg.expires_at); 
                            }))) : null;

                        var html = '';
                        if (activePackages.length > 0) {
                            html = '<div class="row mb-4">' +
                                '<div class="col-12">' +
                                '<div class="card border-success">' +
                                '<div class="card-header bg-success text-white">' +
                                '<h5 class="mb-0"><i class="fa fa-check-circle"></i> پکیج‌های فعال شما (' + activePackages.length + ' پکیج)</h5>' +
                                '</div>' +
                                '<div class="card-body">' +
                                '<div class="row">' +
                                '<div class="col-md-2"><div class="text-center"><h6 class="text-muted">کل روزهای باقی‌مانده</h6><h4 class="text-warning">' + totalRemainingDays + ' روز</h4></div></div>' +
                                '<div class="col-md-2"><div class="text-center"><h6 class="text-muted">میانگین روزها</h6><h4 class="text-info">' + averageDaysPerPackage + ' روز</h4></div></div>' +
                                '<div class="col-md-2"><div class="text-center"><h6 class="text-muted">کل مبلغ پرداخت شده</h6><h4 class="text-success">' + parseFloat(totalAmountPaid).toLocaleString('fa-IR') + ' تومان</h4></div></div>' +
                                '<div class="col-md-2"><div class="text-center"><h6 class="text-muted">کل مبلغ باقی‌مانده</h6><h4 class="text-danger">' + parseFloat(totalRemainingAmount).toLocaleString('fa-IR') + ' تومان</h4></div></div>' +
                                '<div class="col-md-2"><div class="text-center"><h6 class="text-muted">بیشترین روز باقی‌مانده</h6><h4 class="text-primary">' + (longestPackage ? longestPackage.remaining_days + ' روز' : '-') + '</h4></div></div>' +
                                '<div class="col-md-2"><div class="text-center"><h6 class="text-muted">کمترین روز باقی‌مانده</h6><h4 class="text-secondary">' + (shortestPackage ? shortestPackage.remaining_days + ' روز' : '-') + '</h4></div></div>' +
                                '</div></div></div></div></div>';
                        } else {
                            html = '<div class="row mb-4"><div class="col-12"><div class="card border-warning">' +
                                '<div class="card-header bg-warning text-dark"><h5 class="mb-0"><i class="fa fa-exclamation-triangle"></i> بدون پکیج فعال</h5></div>' +
                                '<div class="card-body text-center"><h4 class="text-warning">شما در حال حاضر پکیج فعالی ندارید</h4>' +
                                '<p class="text-muted">برای اطلاع از پکیج‌های خود، با مدیر سیستم تماس بگیرید</p></div></div></div></div>';
                        }

                        if (packages.length > 0) {
                            var stats = {
                                total: packages.length,
                                active: activePackages.length,
                                expired: packages.filter(function(pkg) { return !pkg.is_active; }).length
                            };
                            var activeRate = stats.total > 0 ? Math.round((stats.active / stats.total) * 100 * 10) / 10 : 0;
                            var avgAmount = stats.total > 0 ? Math.round(totalPackagePrice / stats.total) : 0;

                            html += '<div class="row mb-4"><div class="col-12"><div class="card border-info">' +
                                '<div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fa fa-chart-bar"></i> آمار کلی پکیج‌های شما</h5></div>' +
                                '<div class="card-body">' +
                                '<div class="row">' +
                                '<div class="col-md-3"><div class="text-center"><h6 class="text-muted">کل پکیج‌ها</h6><h4 class="text-info">' + stats.total + '</h4></div></div>' +
                                '<div class="col-md-3"><div class="text-center"><h6 class="text-muted">پکیج‌های فعال</h6><h4 class="text-success">' + stats.active + '</h4></div></div>' +
                                '<div class="col-md-3"><div class="text-center"><h6 class="text-muted">پکیج‌های منقضی</h6><h4 class="text-danger">' + stats.expired + '</h4></div></div>' +
                                '<div class="col-md-3"><div class="text-center"><h6 class="text-muted">نرخ فعال بودن</h6><h4 class="text-primary">' + activeRate + '%</h4></div></div>' +
                                '</div>' +
                                '<div class="row mt-3">' +
                                '<div class="col-md-4"><div class="text-center"><h6 class="text-muted">کل مبلغ پکیج‌ها</h6><h4 class="text-primary">' + parseFloat(totalPackagePrice).toLocaleString('fa-IR') + ' تومان</h4></div></div>' +
                                '<div class="col-md-4"><div class="text-center"><h6 class="text-muted">کل مبلغ پرداخت شده</h6><h4 class="text-success">' + parseFloat(totalAmountPaid).toLocaleString('fa-IR') + ' تومان</h4></div></div>' +
                                '<div class="col-md-4"><div class="text-center"><h6 class="text-muted">کل مبلغ باقی‌مانده</h6><h4 class="text-danger">' + parseFloat(totalRemainingAmount).toLocaleString('fa-IR') + ' تومان</h4></div></div>' +
                                '</div></div></div></div></div>';
                        }

                        $('#package-summary-container').html(html);
                    }
                },
                error: function(xhr) {
                    console.error('Error loading packages:', xhr);
                }
            });
        }
    </script>
@endsection
