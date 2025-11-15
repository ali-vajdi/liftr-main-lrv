@extends('organization.layout.master')

@section('title', 'پروفایل شرکت')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">پروفایل شرکت - <span id="org-name-profile">...</span></h5>
                    </div>
                    <div class="widget-content">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">اطلاعات شرکت</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th width="200">نام شرکت</th>
                                                        <td id="org-name-table">...</td>
                                                    </tr>
                                                    <tr>
                                                        <th>آدرس</th>
                                                        <td id="org-address">...</td>
                                                    </tr>
                                                    <tr>
                                                        <th>وضعیت</th>
                                                        <td id="org-status">...</td>
                                                    </tr>
                                                    <tr>
                                                        <th>تاریخ ایجاد</th>
                                                        <td id="org-created">...</td>
                                                    </tr>
                                                    <tr>
                                                        <th>آخرین بروزرسانی</th>
                                                        <td id="org-updated">...</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">لوگو شرکت</h5>
                                    </div>
                                    <div class="card-body text-center" id="org-logo-container">
                                        <div class="text-muted">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">در حال بارگذاری...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Package Statistics -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">آمار پکیج‌ها</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">کل پکیج‌ها</h6>
                                                    <h4 class="text-info"><span id="packages-total">0</span></h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">پکیج‌های فعال</h6>
                                                    <h4 class="text-success"><span id="packages-active">0</span></h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">کل روزهای باقی‌مانده</h6>
                                                    <h4 class="text-warning"><span id="packages-remaining-days">0</span></h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">کل مبلغ پرداخت شده</h6>
                                                    <h4 class="text-primary"><span id="packages-total-amount">0</span> تومان</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
<script>
$(document).ready(function() {
    // Load organization data from API
    getOrganizationData(function(org, error) {
        if (error) {
            console.error('Error loading organization:', error);
            return;
        }

        if (!org) {
            console.error('No organization data received');
            return;
        }

        // Set organization name
        $('#org-name-profile').text(org.name);
        $('#org-name-table').text(org.name);
        $('#org-address').text(org.address || '-');
        
        // Set status
        var statusHtml = org.status 
            ? '<span class="badge badge-success">فعال</span>'
            : '<span class="badge badge-danger">غیرفعال</span>';
        $('#org-status').html(statusHtml);

        // Set dates (convert from ISO to Jalali format)
        if (org.created_at) {
            var createdDate = new Date(org.created_at);
            $('#org-created').text(createdDate.toLocaleDateString('fa-IR') + ' ' + createdDate.toLocaleTimeString('fa-IR'));
        }
        if (org.updated_at) {
            var updatedDate = new Date(org.updated_at);
            $('#org-updated').text(updatedDate.toLocaleDateString('fa-IR') + ' ' + updatedDate.toLocaleTimeString('fa-IR'));
        }

        // Set logo
        var logoHtml = '';
        if (org.logo) {
            logoHtml = '<img src="{{ asset('') }}' + org.logo + '" ' +
                      'alt="لوگو ' + org.name + '" ' +
                      'class="img-fluid" ' +
                      'style="max-width: 200px; max-height: 200px;">';
        } else {
            logoHtml = '<div class="text-muted">' +
                      '<i class="fa fa-image fa-3x"></i>' +
                      '<p class="mt-2">هیچ لوگویی آپلود نشده است</p>' +
                      '</div>';
        }
        $('#org-logo-container').html(logoHtml);
    });

    // Load packages data
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
                    var total = response.data.length;
                    var active = response.data.filter(function(pkg) {
                        return pkg.is_active;
                    }).length;
                    var remainingDays = response.data
                        .filter(function(pkg) { return pkg.is_active; })
                        .reduce(function(sum, pkg) { return sum + (pkg.remaining_days || 0); }, 0);
                    var totalAmount = response.data.reduce(function(sum, pkg) {
                        return sum + (parseFloat(pkg.package_price || 0));
                    }, 0);

                    $('#packages-total').text(total);
                    $('#packages-active').text(active);
                    $('#packages-remaining-days').text(remainingDays);
                    $('#packages-total-amount').text(parseFloat(totalAmount).toLocaleString('fa-IR'));
                }
            },
            error: function(xhr) {
                console.error('Error loading packages:', xhr);
            }
        });
    }
});
</script>
@endsection
