@extends('organization.layout.master')

@section('title', 'داشبورد شرکت')

@section('content')
<div class="row layout-top-spacing">
    <!-- Welcome Header -->
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget widget-chart-one" style="border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="widget-heading" style="border-bottom: 1px solid rgba(255,255,255,0.2); padding: 20px 25px;">
                <h5 class="mb-0" style="font-weight: 600; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px; color: white;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    خوش آمدید، <span id="org-name">...</span>
                </h5>
            </div>
        </div>
    </div>

    <!-- SMS Balance - Prominent Card -->
    <div class="col-xl-4 col-lg-6 col-sm-12 layout-spacing">
        <div class="widget widget-chart-one" style="border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">
            <div class="widget-content" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px 25px; color: white; min-height: 200px;">
                <div class="text-center">
                    <div style="margin-bottom: 20px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.9;">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <h6 style="color: rgba(255,255,255,0.9); margin-bottom: 15px; font-size: 14px; font-weight: 500; letter-spacing: 0.5px;">موجودی پیامک</h6>
                    <h2 style="color: white; font-size: 42px; font-weight: 700; margin-bottom: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <span id="sms-balance">0</span>
                    </h2>
                    <p style="color: rgba(255,255,255,0.8); margin-bottom: 20px; font-size: 14px;">تومان</p>
                    <div class="row mt-4" style="border-top: 1px solid rgba(255,255,255,0.25); padding-top: 20px; margin-top: 20px;">
                        <div class="col-4 text-center">
                            <div style="font-size: 24px; font-weight: 700; margin-bottom: 5px;"><span id="sms-total">0</span></div>
                            <div style="font-size: 11px; opacity: 0.85; font-weight: 500;">کل</div>
                        </div>
                        <div class="col-4 text-center">
                            <div style="font-size: 24px; font-weight: 700; margin-bottom: 5px;"><span id="sms-sent">0</span></div>
                            <div style="font-size: 11px; opacity: 0.85; font-weight: 500;">ارسال شده</div>
                        </div>
                        <div class="col-4 text-center">
                            <div style="font-size: 24px; font-weight: 700; margin-bottom: 5px;"><span id="sms-pending">0</span></div>
                            <div style="font-size: 11px; opacity: 0.85; font-weight: 500;">در انتظار</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Package Card -->
    <div class="col-xl-4 col-lg-6 col-sm-12 layout-spacing">
        <div class="widget widget-chart-one" style="border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="widget-heading" style="border-bottom: 1px solid #e0e6ed; padding: 15px 20px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); margin: -1px -1px 0 -1px;">
                <h5 class="mb-0" style="color: white; font-weight: 600;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                        <path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                    پکیج فعلی
                </h5>
            </div>
            <div class="widget-content" style="padding: 30px 25px;" id="package-content">
                <div class="text-center" style="padding: 40px 20px;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">در حال بارگذاری...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Card -->
    <div class="col-xl-4 col-lg-6 col-sm-12 layout-spacing">
        <div class="widget widget-chart-one" style="border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="widget-heading" style="border-bottom: 1px solid #e0e6ed; padding: 15px 20px; background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); margin: -1px -1px 0 -1px;">
                <h5 class="mb-0" style="font-weight: 600; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px; color: white;">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    آمار کلی
                </h5>
            </div>
            <div class="widget-content" style="padding: 25px 20px;">
                <div class="row">
                    <div class="col-6 mb-4">
                        <div class="text-center" style="padding: 15px; background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border-radius: 12px; border: 1px solid #e0e6ed;">
                            <h3 class="mb-2" style="color: #4361ee; font-weight: 700; font-size: 28px;"><span id="users-total">0</span></h3>
                            <p class="text-muted mb-1" style="font-size: 13px; font-weight: 500; margin-bottom: 5px;">کاربر</p>
                            <small class="text-muted" style="font-size: 11px; color: #888ea8;"><span id="users-active">0</span> فعال</small>
                        </div>
                    </div>
                    <div class="col-6 mb-4">
                        <div class="text-center" style="padding: 15px; background: linear-gradient(135deg, #f093fb15 0%, #f5576c15 100%); border-radius: 12px; border: 1px solid #e0e6ed;">
                            <h3 class="mb-2" style="color: #e7515a; font-weight: 700; font-size: 28px;"><span id="technicians-total">0</span></h3>
                            <p class="text-muted mb-1" style="font-size: 13px; font-weight: 500; margin-bottom: 5px;">تکنیسین</p>
                            <small class="text-muted" style="font-size: 11px; color: #888ea8;"><span id="technicians-active">0</span> فعال</small>
                        </div>
                    </div>
                    <div class="col-6 mb-4">
                        <div class="text-center" style="padding: 15px; background: linear-gradient(135deg, #fad96115 0%, #f76b1c15 100%); border-radius: 12px; border: 1px solid #e0e6ed;">
                            <h3 class="mb-2" style="color: #f59e0b; font-weight: 700; font-size: 28px;"><span id="buildings-total">0</span></h3>
                            <p class="text-muted mb-1" style="font-size: 13px; font-weight: 500; margin-bottom: 5px;">ساختمان</p>
                            <small id="buildings-status" class="text-muted" style="font-size: 11px; color: #888ea8;"><span id="buildings-active">0</span> فعال</small>
                        </div>
                    </div>
                    <div class="col-6 mb-4">
                        <div class="text-center" style="padding: 15px; background: linear-gradient(135deg, #4facfe15 0%, #00f2fe15 100%); border-radius: 12px; border: 1px solid #e0e6ed;">
                            <h3 class="mb-2" style="color: #2196f3; font-weight: 700; font-size: 28px;"><span id="services-total">0</span></h3>
                            <p class="text-muted mb-1" style="font-size: 13px; font-weight: 500; margin-bottom: 5px;">سرویس</p>
                            <small class="text-muted" style="font-size: 11px; color: #888ea8;"><span id="services-completed">0</span> تکمیل شده</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Overview -->
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget widget-chart-one" style="border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="widget-heading" style="border-bottom: 1px solid #e0e6ed; padding: 15px 20px; background: linear-gradient(135deg, #00d4aa 0%, #00a085 100%); margin: -1px -1px 0 -1px;">
                <h5 class="mb-0" style="font-weight: 600; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px; color: white;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    وضعیت سرویس‌ها
                </h5>
            </div>
            <div class="widget-content" style="padding: 25px 20px;">
                <div class="row text-center">
                    <div class="col-md-2 col-4 mb-3">
                        <div style="padding: 20px 10px; background: #f1f2f3; border-radius: 12px; border: 2px solid #e0e6ed;">
                            <h4 class="mb-2" style="color: #3b3f5c; font-weight: 700; font-size: 28px;"><span id="services-total-overview">0</span></h4>
                            <small class="text-muted" style="font-size: 12px; font-weight: 500;">کل سرویس‌ها</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div style="padding: 20px 10px; background: linear-gradient(135deg, #fad96115 0%, #f76b1c15 100%); border-radius: 12px; border: 2px solid #f59e0b;">
                            <h4 class="text-warning mb-2" style="font-weight: 700; font-size: 28px;"><span id="services-pending-overview">0</span></h4>
                            <small class="text-muted" style="font-size: 12px; font-weight: 500;">در انتظار</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div style="padding: 20px 10px; background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border-radius: 12px; border: 2px solid #4361ee;">
                            <h4 class="text-primary mb-2" style="font-weight: 700; font-size: 28px;"><span id="services-assigned-overview">0</span></h4>
                            <small class="text-muted" style="font-size: 12px; font-weight: 500;">اختصاص داده شده</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div style="padding: 20px 10px; background: linear-gradient(135deg, #4facfe15 0%, #00f2fe15 100%); border-radius: 12px; border: 2px solid #00d4aa;">
                            <h4 class="text-success mb-2" style="font-weight: 700; font-size: 28px;"><span id="services-completed-overview">0</span></h4>
                            <small class="text-muted" style="font-size: 12px; font-weight: 500;">تکمیل شده</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div style="padding: 20px 10px; background: linear-gradient(135deg, #fa709a15 0%, #fee14015 100%); border-radius: 12px; border: 2px solid #e7515a;">
                            <h4 class="text-danger mb-2" style="font-weight: 700; font-size: 28px;"><span id="services-expired-overview">0</span></h4>
                            <small class="text-muted" style="font-size: 12px; font-weight: 500;">منقضی شده</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div style="padding: 20px 10px; display: flex; align-items: center; justify-content: center; height: 100%;">
                            <a href="{{ route('organization.services.all') }}" class="btn btn-info" style="border-radius: 50px; padding: 10px 25px; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">مشاهده همه</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget widget-chart-one" style="border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="widget-heading" style="border-bottom: 1px solid #e0e6ed; padding: 15px 20px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); margin: -1px -1px 0 -1px;">
                <h5 class="mb-0" style="font-weight: 600; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px; color: white;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    دسترسی سریع
                </h5>
            </div>
            <div class="widget-content" style="padding: 25px 20px;">
                <div class="row">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('organization.services.pending') }}" class="btn btn-outline-warning btn-block" style="border-radius: 12px; padding: 15px 20px; font-weight: 600; border-width: 2px; transition: all 0.3s;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            سرویس‌های در انتظار
                            <span id="pending-badge" class="badge badge-warning" style="margin-right: 5px; padding: 5px 10px; border-radius: 50px; display: none;">0</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('organization.buildings.expiring') }}" class="btn btn-outline-danger btn-block" style="border-radius: 12px; padding: 15px 20px; font-weight: 600; border-width: 2px; transition: all 0.3s;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            قراردادهای رو به اتمام
                            <span id="expiring-badge" class="badge badge-danger" style="margin-right: 5px; padding: 5px 10px; border-radius: 50px; display: none;">0</span>
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('organization.buildings.view') }}" class="btn btn-outline-primary btn-block" style="border-radius: 12px; padding: 15px 20px; font-weight: 600; border-width: 2px; transition: all 0.3s;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                                <path d="M3 21h18"></path>
                                <path d="M5 21V7l8-4v18"></path>
                                <path d="M19 21V11l-6-4"></path>
                            </svg>
                            ساختمان‌ها
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <a href="{{ route('organization.profile') }}" class="btn btn-outline-secondary btn-block" style="border-radius: 12px; padding: 15px 20px; font-weight: 600; border-width: 2px; transition: all 0.3s;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            پروفایل
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-styles')
<style>
.btn-outline-warning:hover, .btn-outline-danger:hover, .btn-outline-primary:hover, .btn-outline-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}
</style>
@endsection

@section('page-scripts')
<script>
$(document).ready(function() {
    // Load dashboard data from API
    getDashboardData(function(data, error) {
        if (error) {
            console.error('Error loading dashboard:', error);
            return;
        }

        if (!data) {
            console.error('No data received');
            return;
        }

        var org = data.organization;
        var stats = data.statistics;

        // Set organization name
        $('#org-name').text(org.name);

        // Set SMS statistics
        $('#sms-balance').text(parseFloat(stats.sms.balance).toLocaleString('fa-IR'));
        $('#sms-total').text(stats.sms.total);
        $('#sms-sent').text(stats.sms.sent);
        $('#sms-pending').text(stats.sms.pending);

        // Set package data
        var packageHtml = '';
        if (stats.current_package) {
            var pkg = stats.current_package;
            var expiresDate = new Date(pkg.expires_at);
            // Convert to Jalali date (simplified - you may want to use a proper Jalali library)
            packageHtml = '<div class="text-center">' +
                '<div style="margin-bottom: 20px;">' +
                '<h4 class="mb-2" style="color: #3b3f5c; font-weight: 700; font-size: 22px;">' + pkg.package_name + '</h4>' +
                '<p class="text-muted mb-3" style="font-size: 14px; color: #888ea8;">' + pkg.package_duration_label + '</p>' +
                '</div>' +
                '<div class="mb-4">' +
                '<span class="badge ' + pkg.status_badge_class + '" style="padding: 10px 20px; font-size: 14px; font-weight: 600; border-radius: 50px;">' +
                pkg.remaining_days + ' روز باقی‌مانده' +
                '</span>' +
                '</div>' +
                '<div style="background: #f1f2f3; padding: 15px; border-radius: 8px; margin-top: 20px;">' +
                '<p class="text-muted mb-0" style="font-size: 13px; margin-bottom: 5px;">تاریخ انقضا</p>' +
                '<p class="mb-0" style="color: #3b3f5c; font-weight: 600; font-size: 15px;">' +
                expiresDate.toLocaleDateString('fa-IR') +
                '</p>' +
                '</div>' +
                '</div>';
        } else {
            packageHtml = '<div class="text-center" style="padding: 40px 20px;">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#888ea8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 15px; opacity: 0.5;">' +
                '<path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>' +
                '</svg>' +
                '<p class="text-muted mb-3" style="font-size: 14px;">هیچ پکیج فعالی ندارید</p>' +
                '<a href="{{ route('organization.packages.view') }}" class="btn btn-sm btn-primary" style="border-radius: 50px; padding: 8px 20px;">مشاهده پکیج‌ها</a>' +
                '</div>';
        }
        $('#package-content').html(packageHtml);

        // Set user statistics
        $('#users-total').text(stats.users.total);
        $('#users-active').text(stats.users.active);

        // Set technician statistics
        $('#technicians-total').text(stats.technicians.total);
        $('#technicians-active').text(stats.technicians.active);

        // Set building statistics
        $('#buildings-total').text(stats.buildings.total);
        $('#buildings-active').text(stats.buildings.active);
        if (stats.buildings.expiring_soon > 0) {
            $('#buildings-status').html('<span class="text-danger" style="font-weight: 600;">' + stats.buildings.expiring_soon + ' در حال انقضا</span>');
        }

        // Set service statistics
        $('#services-total').text(stats.services.total);
        $('#services-completed').text(stats.services.completed);
        $('#services-total-overview').text(stats.services.total);
        $('#services-pending-overview').text(stats.services.pending);
        $('#services-assigned-overview').text(stats.services.assigned);
        $('#services-completed-overview').text(stats.services.completed);
        $('#services-expired-overview').text(stats.services.expired);

        // Set badge counts
        if (stats.services.pending > 0) {
            $('#pending-badge').text(stats.services.pending).show();
        }
        if (stats.buildings.expiring_soon > 0) {
            $('#expiring-badge').text(stats.buildings.expiring_soon).show();
        }
    });
});
</script>
@endsection
