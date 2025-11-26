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
                                        <td colspan="8" class="text-center">
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
</style>
@endsection

@section('page-scripts')
<script>
$(document).ready(function() {
    const buildingSlug = {!! json_encode($buildingSlug) !!};
    const token = localStorage.getItem('organization_token');
    let dashboardData = null; // Store dashboard data globally

    // Load dashboard data
    function loadDashboard() {
        $.ajax({
            url: `/api/organization/buildings/${buildingSlug}/dashboard`,
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
                    <span class="info-label">تاریخ شروع سرویس</span>
                    <span class="info-value">${building.service_start_date_jalali || '-'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">تاریخ پایان سرویس</span>
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
            $('#services-tbody').html('<tr><td colspan="8" class="text-center">سرویسی یافت نشد</td></tr>');
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
            const assignedDate = service.assigned_at_jalali || '-';
            const completedDate = service.completed_at_jalali || '-';
            const visitDate = service.visit_date_jalali || '-';
            const elevatorsCount = service.checklist && service.checklist.elevator_checklists ? 
                service.checklist.elevator_checklists_count : '-';
            
            html += `
                <tr>
                    <td>${service.service_date_text}</td>
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
        $('#services-tbody').html(`<tr><td colspan="8" class="text-center text-danger">${message}</td></tr>`);
    }

    // Initial load
    loadDashboard();
});
</script>
@endsection

