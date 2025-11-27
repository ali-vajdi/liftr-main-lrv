@extends('organization.layout.master')

@section('title', 'داشبورد تکنیسین')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <!-- Technician Information Card -->
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">داشبورد تکنیسین</h5>
                    <div class="widget-n">
                        <a href="{{ route('organization.technicians.view') }}" class="btn btn-primary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                            بازگشت به تکنیسین‌ها
                        </a>
                    </div>
                </div>
                <div class="widget-content">
                    <div id="technician-info" class="p-4">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">در حال بارگذاری...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading" style="border-bottom: 2px solid #e0e6ed; padding: 20px 25px; background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);">
                    <h5 class="mb-0" style="font-weight: 600; color: #3b3f5c;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 10px; color: #667eea;">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        فیلترها
                    </h5>
                </div>
                <div class="widget-content" style="padding: 30px 25px;">
                    <form id="dashboard-filters">
                        <!-- First Row: Date Filters -->
                        <div class="row mb-4">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="date_from" style="font-weight: 600; margin-bottom: 8px; color: #3b3f5c; font-size: 14px;">از تاریخ</label>
                                    <input type="text" class="form-control" id="date_from" name="date_from" placeholder="1403/01/01" style="border-radius: 8px; padding: 10px 15px; border: 1px solid #e0e6ed;">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="date_to" style="font-weight: 600; margin-bottom: 8px; color: #3b3f5c; font-size: 14px;">تا تاریخ</label>
                                    <input type="text" class="form-control" id="date_to" name="date_to" placeholder="1403/12/29" style="border-radius: 8px; padding: 10px 15px; border: 1px solid #e0e6ed;">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="service_year" style="font-weight: 600; margin-bottom: 8px; color: #3b3f5c; font-size: 14px;">سال سرویس</label>
                                    <select class="form-control" id="service_year" name="service_year" style="border-radius: 8px; padding: 10px 15px; border: 1px solid #e0e6ed;">
                                        <option value="">همه</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="service_month" style="font-weight: 600; margin-bottom: 8px; color: #3b3f5c; font-size: 14px;">ماه سرویس</label>
                                    <select class="form-control" id="service_month" name="service_month" style="border-radius: 8px; padding: 10px 15px; border: 1px solid #e0e6ed;">
                                        <option value="">همه</option>
                                        <option value="1">فروردین</option>
                                        <option value="2">اردیبهشت</option>
                                        <option value="3">خرداد</option>
                                        <option value="4">تیر</option>
                                        <option value="5">مرداد</option>
                                        <option value="6">شهریور</option>
                                        <option value="7">مهر</option>
                                        <option value="8">آبان</option>
                                        <option value="9">آذر</option>
                                        <option value="10">دی</option>
                                        <option value="11">بهمن</option>
                                        <option value="12">اسفند</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Second Row: Status and Building Filters -->
                        <div class="row mb-4">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="service_status" style="font-weight: 600; margin-bottom: 8px; color: #3b3f5c; font-size: 14px;">وضعیت سرویس</label>
                                    <select class="form-control" id="service_status" name="service_status" style="border-radius: 8px; padding: 10px 15px; border: 1px solid #e0e6ed;">
                                        <option value="">همه</option>
                                        <option value="pending">در انتظار</option>
                                        <option value="assigned">اختصاص داده شده</option>
                                        <option value="completed">تکمیل شده</option>
                                        <option value="expired">منقضی شده</option>
                                        <option value="cancelled">لغو شده</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <div class="form-group mb-0">
                                    <label for="building_id" style="font-weight: 600; margin-bottom: 8px; color: #3b3f5c; font-size: 14px;">ساختمان</label>
                                    <select class="form-control" id="building_id" name="building_id" style="border-radius: 8px; padding: 10px 15px; border: 1px solid #e0e6ed;">
                                        <option value="">همه</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div style="padding-top: 15px; border-top: 2px solid #e0e6ed; margin-top: 10px;">
                                    <button type="submit" class="btn btn-primary" style="border-radius: 8px; padding: 12px 30px; font-weight: 600; margin-left: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                        </svg>
                                        اعمال فیلتر
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="reset-filters" style="border-radius: 8px; padding: 12px 30px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                        پاک کردن فیلترها
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="col-xl-3 col-lg-6 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">کل سرویس‌ها</h5>
                </div>
                <div class="widget-content">
                    <div class="text-center p-4">
                        <h2 id="total-services" class="mb-0">-</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">سرویس‌های تکمیل شده</h5>
                </div>
                <div class="widget-content">
                    <div class="text-center p-4">
                        <h2 id="completed-services" class="mb-0 text-success">-</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">سرویس‌های در انتظار</h5>
                </div>
                <div class="widget-content">
                    <div class="text-center p-4">
                        <h2 id="pending-services" class="mb-0 text-warning">-</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">روز از آخرین سرویس</h5>
                </div>
                <div class="widget-content">
                    <div class="text-center p-4">
                        <h2 id="last-service-days" class="mb-0 text-info">-</h2>
                        <small id="last-service-date" class="text-muted">-</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services List -->
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">آخرین سرویس‌ها</h5>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped" id="services-table">
                                <thead>
                                    <tr>
                                        <th>ماه/سال سرویس</th>
                                        <th>ساختمان</th>
                                        <th>آدرس</th>
                                        <th>وضعیت</th>
                                        <th>تاریخ اختصاص</th>
                                        <th>تاریخ تکمیل</th>
                                        <th>تاریخ بازدید</th>
                                    </tr>
                                </thead>
                                <tbody id="services-tbody">
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">در حال بارگذاری...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
        border-right: 3px solid #007bff;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .info-value {
        font-size: 1rem;
        color: #212529;
        font-weight: 600;
    }
    
    /* Filter Form Styles */
    #dashboard-filters .form-control {
        transition: all 0.3s ease;
    }
    #dashboard-filters .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        outline: none;
    }
    #dashboard-filters .form-control:hover {
        border-color: #cbd5e0;
    }
    #dashboard-filters .btn-primary {
        transition: all 0.3s ease;
    }
    #dashboard-filters .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    }
    #dashboard-filters .btn-secondary {
        transition: all 0.3s ease;
    }
    #dashboard-filters .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
    }
</style>
@endsection

@section('page-scripts')
<script>
$(document).ready(function() {
    const technicianId = {!! json_encode($technicianId) !!};
    const token = localStorage.getItem('organization_token');
    let dashboardData = null; // Store dashboard data globally

    // Load dashboard data
    function loadDashboard(filters = {}) {
        const params = new URLSearchParams();
        if (filters.date_from) params.append('date_from', filters.date_from);
        if (filters.date_to) params.append('date_to', filters.date_to);
        if (filters.service_year) params.append('service_year', filters.service_year);
        if (filters.service_month) params.append('service_month', filters.service_month);
        if (filters.service_status) params.append('service_status', filters.service_status);
        if (filters.building_id) params.append('building_id', filters.building_id);
        
        const queryString = params.toString();
        const url = `/api/organization/technicians/${technicianId}/dashboard${queryString ? '?' + queryString : ''}`;
        
        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success) {
                    dashboardData = response.data;
                    renderTechnicianInfo(dashboardData.technician);
                    renderStatistics(dashboardData.statistics, dashboardData.last_service);
                    renderServicesTable(dashboardData.services);
                } else {
                    showError('خطا در بارگذاری اطلاعات داشبورد');
                }
            },
            error: function(xhr) {
                console.error('Dashboard load error:', xhr);
                if (xhr.status === 401) {
                    swal({
                        title: 'خطای دسترسی',
                        text: 'لطفا مجددا وارد سیستم شوید',
                        type: 'error',
                        padding: '2em'
                    }).then(function() {
                        window.location.href = '/login';
                    });
                } else {
                    showError('خطا در بارگذاری اطلاعات داشبورد');
                }
            }
        });
    }

    // Render technician information
    function renderTechnicianInfo(technician) {
        let html = `
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">نام و نام خانوادگی</span>
                    <span class="info-value">${technician.full_name || '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">کد ملی</span>
                    <span class="info-value">${technician.national_id || '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">شماره تماس</span>
                    <span class="info-value">${technician.phone_number || '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">وضعیت</span>
                    <span class="info-value">
                        ${technician.status ? '<span class="badge badge-success">فعال</span>' : '<span class="badge badge-danger">غیرفعال</span>'}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">رمز عبور</span>
                    <span class="info-value">
                        ${technician.has_credentials ? '<span class="badge badge-success">تعریف شده</span>' : '<span class="badge badge-warning">تعریف نشده</span>'}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">تاریخ ایجاد</span>
                    <span class="info-value">${technician.created_at || '-'}</span>
                </div>
            </div>
        `;
        $('#technician-info').html(html);
    }

    // Render statistics
    function renderStatistics(stats, lastService) {
        $('#total-services').text(stats.total_services);
        $('#completed-services').text(stats.completed_services);
        $('#pending-services').text(stats.pending_services);
        
        if (lastService && lastService.days_since_text) {
            // Show formatted text like "5 روز پیش" or "امروز"
            $('#last-service-days').text(lastService.days_since_text);
            
            // Show date with month name
            const dateText = lastService.completed_at_jalali_with_month || 
                            lastService.completed_at_jalali || 
                            '-';
            $('#last-service-date').text(dateText);
        } else {
            $('#last-service-days').text('-');
            $('#last-service-date').text('سرویس تکمیل شده‌ای وجود ندارد');
        }
    }

    // Render services table
    function renderServicesTable(services) {
        if (!services || services.length === 0) {
            $('#services-tbody').html('<tr><td colspan="7" class="text-center">سرویسی یافت نشد</td></tr>');
            return;
        }

        const statusBadges = {
            'pending': '<span class="badge badge-warning">در انتظار</span>',
            'assigned': '<span class="badge badge-info">اختصاص داده شده</span>',
            'completed': '<span class="badge badge-success">تکمیل شده</span>',
            'expired': '<span class="badge badge-danger">منقضی شده</span>',
            'cancelled': '<span class="badge badge-secondary">لغو شده</span>'
        };

        let html = '';
        services.forEach(function(service) {
            const buildingName = service.building ? service.building.name : '-';
            const buildingAddress = service.building ? 
                (service.building.address || (service.building.city ? service.building.city + ' - ' + (service.building.province || '') : '')) : '-';
            const assignedDate = service.assigned_at_jalali || '-';
            const completedDate = service.completed_at_jalali || '-';
            const visitDate = service.visit_date_jalali || '-';
            
            html += `
                <tr>
                    <td>${service.service_date_text || '-'}</td>
                    <td>${buildingName}</td>
                    <td>${buildingAddress}</td>
                    <td>${statusBadges[service.status] || service.status_text || '-'}</td>
                    <td>${assignedDate}</td>
                    <td>${completedDate}</td>
                    <td>${visitDate}</td>
                </tr>
            `;
        });
        $('#services-tbody').html(html);
    }

    // Show error message
    function showError(message) {
        swal({
            title: 'خطا',
            text: message,
            type: 'error',
            padding: '2em'
        });
    }

    // Load buildings for filter
    function loadBuildings() {
        $.ajax({
            url: '/api/organization/buildings',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success && response.data) {
                    const buildingSelect = $('#building_id');
                    buildingSelect.find('option:not(:first)').remove();
                    response.data.forEach(function(building) {
                        buildingSelect.append(`<option value="${building.id}">${building.name || '-'}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error loading buildings:', xhr);
            }
        });
    }

    // Populate year dropdown
    function populateYearDropdown() {
        const select = $('#service_year');
        // Get current Jalali year (approximate conversion)
        const now = new Date();
        const gregorianYear = now.getFullYear();
        const currentYear = gregorianYear - 621; // Approximate Jalali year
        const startYear = 1395; // Start from 1395
        
        select.html('<option value="">همه</option>');
        
        // Add years from 1395 to current year
        for (let year = startYear; year <= currentYear; year++) {
            select.append(`<option value="${year}">${year}</option>`);
        }
    }

    // Handle filter form submission
    $('#dashboard-filters').on('submit', function(e) {
        e.preventDefault();
        const filters = {
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val(),
            service_year: $('#service_year').val(),
            service_month: $('#service_month').val(),
            service_status: $('#service_status').val(),
            building_id: $('#building_id').val()
        };
        loadDashboard(filters);
    });

    // Handle reset filters
    $('#reset-filters').on('click', function() {
        $('#dashboard-filters')[0].reset();
        loadDashboard({});
    });

    // Initialize Persian date pickers
    if (typeof jalaliDatepicker !== 'undefined' && jalaliDatepicker) {
        // Initialize date_from picker
        jalaliDatepicker.startWatch({
            selector: '#date_from',
            date: true,
            time: false,
            hasSecond: false,
            showSelectTimeBtnAlways: false,
            format: 'YYYY/MM/DD',
            separatorChars: {
                date: '/',
                between: ' ',
            },
            persianDigits: false,
            autoShow: true,
            autoHide: true,
            hideAfterChange: true,
            showTodayBtn: true,
            showEmptyBtn: true,
            showCloseBtn: true,
            useDropDownYears: true,
            container: 'body',
            zIndex: 10000,
            minDate: {year: 1400, month: 1, day: 1},
            maxDate: 'today'
        });

        // Initialize date_to picker
        jalaliDatepicker.startWatch({
            selector: '#date_to',
            date: true,
            time: false,
            hasSecond: false,
            showSelectTimeBtnAlways: false,
            format: 'YYYY/MM/DD',
            separatorChars: {
                date: '/',
                between: ' ',
            },
            persianDigits: false,
            autoShow: true,
            autoHide: true,
            hideAfterChange: true,
            showTodayBtn: true,
            showEmptyBtn: true,
            showCloseBtn: true,
            useDropDownYears: true,
            container: 'body',
            zIndex: 10000,
            minDate: {year: 1400, month: 1, day: 1},
            maxDate: 'today'
        });
    }
    
    // Load dashboard on page load
    populateYearDropdown();
    loadBuildings();
    loadDashboard();
});
</script>
@endsection

