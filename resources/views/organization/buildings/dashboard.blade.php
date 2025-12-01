@extends('organization.layout.master')

@section('title', 'داشبورد ساختمان')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <!-- Building Information Card -->
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">داشبورد ساختمان</h5>
                    <div class="widget-n">
                        <a href="{{ route('organization.buildings.view') }}" class="btn btn-primary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                            بازگشت به ساختمان‌ها
                        </a>
                    </div>
                </div>
                <div class="widget-content">
                    <div id="building-info" class="p-4">
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
                        
                        <!-- Second Row: Status and Technician Filters -->
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
                                    <label for="technician_id" style="font-weight: 600; margin-bottom: 8px; color: #3b3f5c; font-size: 14px;">تکنیسین</label>
                                    <select class="form-control" id="technician_id" name="technician_id" style="border-radius: 8px; padding: 10px 15px; border: 1px solid #e0e6ed;">
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
                    <h5 class="mb-0">لیست سرویس‌ها</h5>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped" id="services-table">
                                <thead>
                                    <tr>
                                        <th>ماه/سال سرویس</th>
                                        <th>تاریخ ساخت</th>
                                        <th>وضعیت</th>
                                        <th>تکنسین</th>
                                        <th>تاریخ اختصاص</th>
                                        <th>تاریخ تکمیل</th>
                                        <th>تاریخ بازدید</th>
                                        <th>تعداد آسانسورها</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody id="services-tbody">
                                    <tr>
                                        <td colspan="9" class="text-center">
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

<!-- Service Details Modal -->
<div class="modal fade" id="serviceDetailsModal" tabindex="-1" role="dialog" aria-labelledby="serviceDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceDetailsModalLabel">جزئیات سرویس</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="service-details-content">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">در حال بارگذاری...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-styles')
<style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    .info-item {
        display: flex;
        flex-direction: column;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
    }
    .info-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }
    .info-value {
        color: #212529;
        font-size: 1rem;
    }
    .service-details-section {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
    }
    .service-details-section h6 {
        margin-bottom: 0.75rem;
        color: #495057;
        font-weight: 600;
    }
    .elevator-item {
        padding: 0.75rem;
        margin-bottom: 0.75rem;
        background: white;
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
    }
    .elevator-item:last-child {
        margin-bottom: 0;
    }
    .description-item {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        background: white;
        border-right: 3px solid #007bff;
        border-radius: 0.25rem;
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
    const buildingSlug = {!! json_encode($buildingSlug) !!};
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
        if (filters.technician_id) params.append('technician_id', filters.technician_id);
        
        const queryString = params.toString();
        const url = `/api/organization/buildings/${buildingSlug}/dashboard${queryString ? '?' + queryString : ''}`;
        
        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success) {
                    dashboardData = response.data;
                    renderBuildingInfo(dashboardData.building);
                    renderStatistics(dashboardData.statistics, dashboardData.last_service);
                    renderServicesTable(dashboardData.services);
                } else {
                    showError('خطا در بارگذاری اطلاعات داشبورد');
                }
            },
            error: function(xhr) {
                console.error('Dashboard load error:', xhr);
                showError('خطا در بارگذاری اطلاعات داشبورد');
            }
        });
    }

    // Render building information
    function renderBuildingInfo(building) {
        const buildingTypes = {
            'residential': 'مسکونی',
            'office': 'اداری',
            'commercial': 'تجاری'
        };
        
        let html = `
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">نام ساختمان</span>
                    <span class="info-value">${building.name || '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">مدیر/نماینده</span>
                    <span class="info-value">${building.manager_name || '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">شماره تماس</span>
                    <span class="info-value">${building.manager_phone || '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">نوع ساختمان</span>
                    <span class="info-value">${buildingTypes[building.building_type] || building.building_type || '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">استان</span>
                    <span class="info-value">${building.province ? building.province.name : '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">شهر</span>
                    <span class="info-value">${building.city ? building.city.name : '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">آدرس</span>
                    <span class="info-value">${building.address || '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">تعداد آسانسورها</span>
                    <span class="info-value">${building.elevators_count || 0}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">تاریخ شروع قرارداد</span>
                    <span class="info-value">${building.service_start_date_jalali || '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">تاریخ پایان قرارداد</span>
                    <span class="info-value">${building.service_end_date_jalali || '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">وضعیت</span>
                    <span class="info-value">
                        ${building.status ? '<span class="badge badge-success">فعال</span>' : '<span class="badge badge-danger">غیرفعال</span>'}
                    </span>
                </div>
            </div>
        `;
        $('#building-info').html(html);
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
                            lastService.service_date_text || 
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
            $('#services-tbody').html('<tr><td colspan="9" class="text-center">سرویسی یافت نشد</td></tr>');
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
            const technicianName = service.technician ? service.technician.full_name : '-';
            const createdDate = service.created_at_jalali || '-';
            const assignedDate = service.assigned_at_jalali || '-';
            const completedDate = service.completed_at_jalali || '-';
            const visitDate = service.visit_date_jalali || '-';
            const elevatorsCount = service.checklist && service.checklist.elevator_checklists ? 
                service.checklist.elevator_checklists_count : '-';
            
            html += `
                <tr>
                    <td>${service.service_date_text}</td>
                    <td>${createdDate}</td>
                    <td>${statusBadges[service.status] || service.status}</td>
                    <td>${technicianName}</td>
                    <td>${assignedDate}</td>
                    <td>${completedDate}</td>
                    <td>${visitDate}</td>
                    <td>${elevatorsCount}</td>
                    <td>
                        <button class="btn btn-sm btn-info view-service-btn" data-service-id="${service.id}" title="مشاهده جزئیات">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                    </td>
                </tr>
            `;
        });
        $('#services-tbody').html(html);
    }

    // Show service details modal
    $(document).on('click', '.view-service-btn', function() {
        if (!dashboardData) return;
        
        const serviceId = parseInt($(this).data('service-id'));
        const service = dashboardData.services.find(s => s.id === serviceId);
        
        if (!service) {
            $('#service-details-content').html('<div class="alert alert-danger">سرویس یافت نشد</div>');
            return;
        }

        $('#serviceDetailsModal').modal('show');
        renderServiceDetails(service);
    });

    // Render service details
    function renderServiceDetails(service) {
        const statusTexts = {
            'pending': 'در انتظار',
            'assigned': 'اختصاص داده شده',
            'completed': 'تکمیل شده',
            'expired': 'منقضی شده',
            'cancelled': 'لغو شده'
        };

        let html = `
            <div class="service-details-section">
                <h6>اطلاعات کلی سرویس</h6>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">ماه/سال سرویس</span>
                        <span class="info-value">${service.service_date_text}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">وضعیت</span>
                        <span class="info-value">${statusTexts[service.status] || service.status}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">نوع سرویس</span>
                        <span class="info-value">${service.is_manual ? 'دستی' : 'سیستمی'}</span>
                    </div>
                </div>
            </div>
        `;

        if (service.technician) {
            html += `
                <div class="service-details-section">
                    <h6>اطلاعات تکنسین</h6>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">نام تکنسین</span>
                            <span class="info-value">${service.technician.full_name}</span>
                        </div>
                        ${service.technician.phone ? `
                        <div class="info-item">
                            <span class="info-label">شماره تماس</span>
                            <span class="info-value">${service.technician.phone}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        if (service.assigned_at || service.completed_at || service.visit_date) {
            html += `
                <div class="service-details-section">
                    <h6>تاریخ‌ها</h6>
                    <div class="info-grid">
                        ${service.assigned_at_jalali ? `
                        <div class="info-item">
                            <span class="info-label">تاریخ اختصاص</span>
                            <span class="info-value">${service.assigned_at_jalali}</span>
                        </div>
                        ` : ''}
                        ${service.completed_at_jalali ? `
                        <div class="info-item">
                            <span class="info-label">تاریخ تکمیل</span>
                            <span class="info-value">${service.completed_at_jalali}</span>
                        </div>
                        ` : ''}
                        ${service.visit_date_jalali ? `
                        <div class="info-item">
                            <span class="info-label">تاریخ بازدید</span>
                            <span class="info-value">${service.visit_date_jalali}</span>
                        </div>
                        ` : ''}
                        ${service.visit_time_range ? `
                        <div class="info-item">
                            <span class="info-label">بازه زمانی بازدید</span>
                            <span class="info-value">${service.visit_time_range}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        if (service.notes || service.organization_note || service.user_note || service.technician_note) {
            html += `
                <div class="service-details-section">
                    <h6>یادداشت‌ها</h6>
                    <div class="info-grid">
                        ${service.notes ? `
                        <div class="info-item">
                            <span class="info-label">یادداشت عمومی</span>
                            <span class="info-value">${service.notes}</span>
                        </div>
                        ` : ''}
                        ${service.organization_note ? `
                        <div class="info-item">
                            <span class="info-label">یادداشت سازمان</span>
                            <span class="info-value">${service.organization_note}</span>
                        </div>
                        ` : ''}
                        ${service.user_note ? `
                        <div class="info-item">
                            <span class="info-label">یادداشت کاربر</span>
                            <span class="info-value">${service.user_note}</span>
                        </div>
                        ` : ''}
                        ${service.technician_note ? `
                        <div class="info-item">
                            <span class="info-label">یادداشت تکنسین</span>
                            <span class="info-value">${service.technician_note}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        if (service.checklist && service.checklist.elevator_checklists && service.checklist.elevator_checklists.length > 0) {
            html += `
                <div class="service-details-section">
                    <h6>آسانسورهای سرویس شده</h6>
            `;

            service.checklist.elevator_checklists.forEach(function(elevatorChecklist) {
                const elevator = elevatorChecklist.elevator;
                html += `
                    <div class="elevator-item">
                        <h6 style="margin-bottom: 0.5rem;">${elevator ? elevator.name : 'نامشخص'}</h6>
                        ${elevator ? `
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">تعداد توقف</span>
                                <span class="info-value">${elevator.stops_count || '-'}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">ظرفیت</span>
                                <span class="info-value">${elevator.capacity ? elevator.capacity + ' نفر' : '-'}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">وضعیت تایید</span>
                                <span class="info-value">${elevatorChecklist.verified ? '<span class="badge badge-success">تایید شده</span>' : '<span class="badge badge-warning">تایید نشده</span>'}</span>
                            </div>
                        </div>
                        ` : ''}
                        
                        ${elevatorChecklist.descriptions && elevatorChecklist.descriptions.length > 0 ? `
                        <div style="margin-top: 0.75rem;">
                            <strong>توضیحات:</strong>
                            ${elevatorChecklist.descriptions.map(function(desc) {
                                const checklistTitle = desc.checklist ? desc.checklist.title : 'نامشخص';
                                return `
                                    <div class="description-item">
                                        <strong>${checklistTitle}:</strong> 
                                        ${desc.description || '-'}
                                    </div>
                                `;
                            }).join('')}
                        </div>
                        ` : ''}
                    </div>
                `;
            });

            html += `</div>`;
        }

        if (service.checklist && service.checklist.submitted_at_jalali) {
            html += `
                <div class="service-details-section">
                    <h6>اطلاعات چک‌لیست</h6>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">تاریخ ارسال</span>
                            <span class="info-value">${service.checklist.submitted_at_jalali}</span>
                        </div>
                    </div>
                </div>
            `;
        }

        $('#service-details-content').html(html);
    }

    // Show error message
    function showError(message) {
        $('#building-info').html(`<div class="alert alert-danger">${message}</div>`);
        $('#services-tbody').html(`<tr><td colspan="9" class="text-center text-danger">${message}</td></tr>`);
    }

    // Load technicians for filter
    function loadTechnicians() {
        $.ajax({
            url: '/api/organization/technicians',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.data) {
                    const technicianSelect = $('#technician_id');
                    technicianSelect.find('option:not(:first)').remove();
                    response.data.forEach(function(technician) {
                        technicianSelect.append(`<option value="${technician.id}">${technician.full_name || '-'}</option>`);
                    });
                }
            },
            error: function(xhr) {
                console.error('Error loading technicians:', xhr);
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
            technician_id: $('#technician_id').val()
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

    // Initial load
    populateYearDropdown();
    loadTechnicians();
    loadDashboard();
});
</script>
@endsection

