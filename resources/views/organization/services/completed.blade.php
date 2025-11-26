@extends('organization.layout.master')

@section('title', 'سرویس‌های تکمیل شده')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">سرویس‌های تکمیل شده - <span id="org-name-completed">...</span></h5>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        @include('organization.components.datatable', [
                            'title' => 'لیست سرویس‌های تکمیل شده',
                            'apiUrl' => '/api/organization/services/completed',
                            'createButton' => false,
                            'hideDefaultActions' => true,
                            'hideDefaultFilters' => true,
                            'filters' => [
                                [
                                    'name' => 'building_id',
                                    'label' => 'ساختمان',
                                    'type' => 'select',
                                    'options' => []
                                ],
                                [
                                    'name' => 'technician_id',
                                    'label' => 'تکنسین',
                                    'type' => 'select',
                                    'options' => []
                                ],
                                [
                                    'name' => 'month',
                                    'label' => 'ماه',
                                    'type' => 'select',
                                    'options' => [
                                        ['value' => '', 'label' => 'همه ماه‌ها'],
                                        ['value' => '1', 'label' => 'فروردین'],
                                        ['value' => '2', 'label' => 'اردیبهشت'],
                                        ['value' => '3', 'label' => 'خرداد'],
                                        ['value' => '4', 'label' => 'تیر'],
                                        ['value' => '5', 'label' => 'مرداد'],
                                        ['value' => '6', 'label' => 'شهریور'],
                                        ['value' => '7', 'label' => 'مهر'],
                                        ['value' => '8', 'label' => 'آبان'],
                                        ['value' => '9', 'label' => 'آذر'],
                                        ['value' => '10', 'label' => 'دی'],
                                        ['value' => '11', 'label' => 'بهمن'],
                                        ['value' => '12', 'label' => 'اسفند'],
                                    ]
                                ],
                                [
                                    'name' => 'year',
                                    'label' => 'سال',
                                    'type' => 'select',
                                    'options' => []
                                ],
                            ],
                            'columns' => [
                                [
                                    'field' => 'building',
                                    'label' => 'نام ساختمان',
                                    'formatter' => 'function(value) { return value ? value.name : "-"; }',
                                ],
                                [
                                    'field' => 'building',
                                    'label' => 'مدیر/نماینده',
                                    'formatter' => 'function(value) { return value ? value.manager_name : "-"; }',
                                ],
                                [
                                    'field' => 'building',
                                    'label' => 'شماره تماس',
                                    'formatter' => 'function(value) { return value ? value.manager_phone : "-"; }',
                                ],
                                [
                                    'field' => 'service_date_text',
                                    'label' => 'ماه سرویس',
                                    'formatter' => 'function(value) { return value || "-"; }',
                                ],
                                [
                                    'field' => 'service_year',
                                    'label' => 'سال',
                                    'formatter' => 'function(value) { return value || "-"; }',
                                ],
                                [
                                    'field' => 'technician',
                                    'label' => 'تکنسین',
                                    'formatter' => 'function(value) { return value ? value.first_name + " " + value.last_name : "-"; }',
                                ],
                                [
                                    'field' => 'assigned_at_jalali',
                                    'label' => 'تاریخ اختصاص',
                                    'formatter' => 'function(value) { return value || "-"; }',
                                ],
                                [
                                    'field' => 'completed_at_jalali',
                                    'label' => 'تاریخ تکمیل',
                                    'formatter' => 'function(value) { return value || "-"; }',
                                ],
                                [
                                    'field' => 'status',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        const statuses = {
                                            "pending": `<span class="badge badge-warning">در انتظار</span>`,
                                            "assigned": `<span class="badge badge-info">اختصاص داده شده</span>`,
                                            "completed": `<span class="badge badge-success">تکمیل شده</span>`,
                                            "expired": `<span class="badge badge-danger">منقضی شده</span>`,
                                            "cancelled": `<span class="badge badge-secondary">لغو شده</span>`
                                        };
                                        return statuses[value] || value;
                                    }',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'actions' => '
                                // Show details button
                                html += \'<button type="button" class="btn btn-sm btn-info show-details-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده جزئیات">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                            ',
                            'actionHandlers' => '
                                // Handle show details button click
                                $(document).off("click", ".show-details-btn").on("click", ".show-details-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    if (id && typeof window.onShowDetails === "function") {
                                        window.onShowDetails(id);
                                    }
                                    return false;
                                });
                            ',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Service Details Modal -->
<div class="modal fade" id="serviceDetailsModal" tabindex="-1" role="dialog" aria-labelledby="serviceDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceDetailsModalLabel">جزئیات سرویس</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="serviceDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
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

@section('page-scripts')
<script>
let allServicesData = {};

// Define onShowDetails function
window.onShowDetails = function(id) {
    const $ = jQuery || window.$;
    const token = localStorage.getItem('organization_token');
    
    if (!token) {
        alert('لطفاً مجدداً وارد شوید');
        return;
    }
    
    // Show loading
    $('#serviceDetailsContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">در حال بارگذاری...</span></div></div>');
    $('#serviceDetailsModal').modal('show');
    
    // Try to get from current page data first
    let service = allServicesData[id];
    
    if (service) {
        displayServiceDetails(service);
        return;
    }
    
    // If not found, fetch from API
    $.ajax({
        url: `/api/organization/services/completed`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        data: {
            per_page: 1000 // Get more results to find the service
        },
        success: function(response) {
            if (response.success && response.data) {
                service = response.data.find(s => s.id == id);
                if (!service) {
                    $('#serviceDetailsContent').html('<div class="alert alert-danger">سرویس یافت نشد</div>');
                    return;
                }
                displayServiceDetails(service);
            } else {
                $('#serviceDetailsContent').html('<div class="alert alert-danger">خطا در بارگذاری اطلاعات</div>');
            }
        },
        error: function(xhr) {
            $('#serviceDetailsContent').html('<div class="alert alert-danger">خطا در بارگذاری اطلاعات</div>');
        }
    });
};

function displayServiceDetails(service) {
    
    let html = '<div class="service-details">';
    
    // Basic Information
    html += '<div class="card mb-3">';
    html += '<div class="card-header"><h6 class="mb-0">اطلاعات پایه</h6></div>';
    html += '<div class="card-body">';
    html += '<table class="table table-bordered">';
    html += '<tr><th width="30%">نام ساختمان:</th><td>' + (service.building ? service.building.name : '-') + '</td></tr>';
    html += '<tr><th>مدیر/نماینده:</th><td>' + (service.building ? service.building.manager_name : '-') + '</td></tr>';
    html += '<tr><th>شماره تماس:</th><td>' + (service.building ? service.building.manager_phone : '-') + '</td></tr>';
    html += '<tr><th>استان:</th><td>' + (service.building && service.building.province ? service.building.province.name : '-') + '</td></tr>';
    html += '<tr><th>شهر:</th><td>' + (service.building && service.building.city ? service.building.city.name : '-') + '</td></tr>';
    html += '<tr><th>ماه سرویس:</th><td>' + (service.service_date_text || '-') + '</td></tr>';
    html += '<tr><th>وضعیت:</th><td>' + (service.status_text || '-') + '</td></tr>';
    html += '<tr><th>تعداد بازدید:</th><td><span class="badge badge-info"><i class="fas fa-eye"></i> ' + (service.view_count || 0) + '</span></td></tr>';
    html += '</table>';
    html += '</div></div>';
    
    // View Details
    if (service.views && service.views.length > 0) {
        html += '<div class="card mb-3">';
        html += '<div class="card-header"><h6 class="mb-0">جزئیات بازدیدها</h6></div>';
        html += '<div class="card-body">';
        html += '<div class="table-responsive">';
        html += '<table class="table table-bordered table-sm">';
        html += '<thead>';
        html += '<tr>';
        html += '<th>ردیف</th>';
        html += '<th>تاریخ و زمان</th>';
        html += '<th>نوع دستگاه</th>';
        html += '<th>مرورگر</th>';
        html += '<th>سیستم عامل</th>';
        html += '</tr>';
        html += '</thead>';
        html += '<tbody>';
        service.views.forEach(function(view, index) {
            const deviceTypeText = {
                'mobile': 'موبایل',
                'tablet': 'تبلت',
                'desktop': 'دسکتاپ',
                'unknown': 'نامشخص'
            };
            html += '<tr>';
            html += '<td>' + (index + 1) + '</td>';
            html += '<td>' + (view.viewed_at || '-') + '</td>';
            html += '<td><span class="badge badge-secondary">' + (deviceTypeText[view.device_type] || view.device_type || '-') + '</span></td>';
            html += '<td>' + (view.browser || '-') + '</td>';
            html += '<td>' + (view.platform || '-') + '</td>';
            html += '</tr>';
        });
        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        html += '</div></div>';
    }
    
    // Assigned Information
    if (service.status === 'assigned' || service.status === 'completed') {
        html += '<div class="card mb-3">';
        html += '<div class="card-header"><h6 class="mb-0">اطلاعات اختصاص</h6></div>';
        html += '<div class="card-body">';
        html += '<table class="table table-bordered">';
        html += '<tr><th width="30%">تکنسین:</th><td>' + (service.technician ? (service.technician.first_name + ' ' + service.technician.last_name) : '-') + '</td></tr>';
        html += '<tr><th>شماره تماس تکنسین:</th><td>' + (service.technician ? service.technician.phone_number : '-') + '</td></tr>';
        html += '<tr><th>تاریخ اختصاص:</th><td>' + (service.assigned_at_jalali || '-') + '</td></tr>';
        if (service.visit_date_jalali) {
            html += '<tr><th>تاریخ مراجعه:</th><td>' + service.visit_date_jalali + '</td></tr>';
        }
        if (service.visit_time_range) {
            html += '<tr><th>بازه زمانی مراجعه:</th><td>' + service.visit_time_range + '</td></tr>';
        }
        if (service.organization_note) {
            html += '<tr><th>یادداشت شرکت:</th><td>' + service.organization_note + '</td></tr>';
        }
        if (service.technician_note) {
            html += '<tr><th>یادداشت تکنسین:</th><td>' + service.technician_note + '</td></tr>';
        }
        if (service.user_note) {
            html += '<tr><th>یادداشت مدیر:</th><td>' + service.user_note + '</td></tr>';
        }
        html += '</table>';
        html += '</div></div>';
    }
    
    // Completed Information with Checklist
    if (service.status === 'completed' && service.checklist_data) {
        html += '<div class="card mb-3">';
        html += '<div class="card-header"><h6 class="mb-0">اطلاعات تکمیل</h6></div>';
        html += '<div class="card-body">';
        html += '<table class="table table-bordered mb-3">';
        html += '<tr><th width="30%">تاریخ تکمیل:</th><td>' + (service.completed_at_jalali || '-') + '</td></tr>';
        html += '<tr><th>تاریخ ارسال چک‌لیست:</th><td>' + (service.checklist_data.submitted_at || '-') + '</td></tr>';
        if (service.technician_note) {
            html += '<tr><th>یادداشت تکنسین:</th><td>' + service.technician_note + '</td></tr>';
        }
        html += '</table>';
        
        // Elevators Checklist
        if (service.checklist_data.elevators && service.checklist_data.elevators.length > 0) {
            html += '<h6 class="mt-3 mb-2">چک‌لیست آسانسورها:</h6>';
            service.checklist_data.elevators.forEach(function(elevator, index) {
                html += '<div class="card mb-2">';
                html += '<div class="card-header"><strong>آسانسور: ' + (elevator.elevator_name || elevator.elevator_id) + '</strong></div>';
                html += '<div class="card-body">';
                html += '<p><strong>وضعیت:</strong> ' + (elevator.verified ? '<span class="badge badge-success">تایید شده</span>' : '<span class="badge badge-danger">تایید نشده</span>') + '</p>';
                
                if (elevator.descriptions && elevator.descriptions.length > 0) {
                    html += '<h6 class="mt-2 mb-2">توضیحات:</h6>';
                    html += '<ul>';
                    elevator.descriptions.forEach(function(desc) {
                        html += '<li>';
                        html += '<strong>' + (desc.checklist_title || desc.title) + ':</strong> ';
                        html += desc.description || '-';
                        html += '</li>';
                    });
                    html += '</ul>';
                }
                html += '</div></div>';
            });
        }
        
        // Signatures
        html += '<h6 class="mt-3 mb-2">امضاها:</h6>';
        html += '<div class="row">';
        if (service.checklist_data.manager_signature) {
            html += '<div class="col-md-6 mb-3">';
            html += '<div class="card">';
            html += '<div class="card-header"><strong>امضای مدیر</strong></div>';
            html += '<div class="card-body text-center">';
            html += '<p><strong>نام:</strong> ' + service.checklist_data.manager_signature.name + '</p>';
            if (service.checklist_data.manager_signature.signature) {
                html += '<img src="' + service.checklist_data.manager_signature.signature + '" class="img-fluid" style="max-height: 150px;" alt="امضای مدیر">';
            }
            html += '</div></div></div>';
        }
        if (service.checklist_data.technician_signature) {
            html += '<div class="col-md-6 mb-3">';
            html += '<div class="card">';
            html += '<div class="card-header"><strong>امضای تکنسین</strong></div>';
            html += '<div class="card-body text-center">';
            html += '<p><strong>نام:</strong> ' + service.checklist_data.technician_signature.name + '</p>';
            if (service.checklist_data.technician_signature.signature) {
                html += '<img src="' + service.checklist_data.technician_signature.signature + '" class="img-fluid" style="max-height: 150px;" alt="امضای تکنسین">';
            }
            html += '</div></div></div>';
        }
        html += '</div>';
        
        html += '</div></div>';
    }
    
    html += '</div>';
    
    $('#serviceDetailsContent').html(html);
    $('#serviceDetailsModal').modal('show');
}

// Wait for jQuery
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Store services data when datatable loads
        if (typeof window.datatableApi !== 'undefined' && window.datatableApi.table) {
            window.datatableApi.table.on('draw', function() {
                // Store current page data
                window.datatableApi.table.rows().every(function() {
                    const data = this.data();
                    if (data && data.id) {
                        allServicesData[data.id] = data;
                    }
                });
            });
        }
        
        // Load filters
        loadBuildings();
        loadTechnicians();
        
        // Populate year dropdown
        populateYearDropdown();
    });
    
})(jQuery || window.jQuery || window.$);

function loadBuildings() {
    const $ = jQuery || window.$;
    const token = localStorage.getItem('organization_token');
    if (!token) {
        return;
    }

    $.ajax({
        url: '/api/organization/buildings?per_page=1000',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success && response.data) {
                const select = $('.filter-control[data-filter-name="building_id"]');
                select.html('<option value="">همه ساختمان‌ها</option>');
                
                if (response.data.length > 0) {
                    response.data.forEach(function(building) {
                        select.append(`<option value="${building.id}">${building.name} - ${building.manager_name}</option>`);
                    });
                }
            }
        },
        error: function(xhr) {
            console.error('Error loading buildings:', xhr);
        }
    });
}

function loadTechnicians() {
    const $ = jQuery || window.$;
    const token = localStorage.getItem('organization_token');
    if (!token) {
        return;
    }

    $.ajax({
        url: '/api/organization/services/technicians',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success && response.data) {
                const select = $('.filter-control[data-filter-name="technician_id"]');
                select.html('<option value="">همه تکنسین‌ها</option>');
                
                if (response.data.length > 0) {
                    response.data.forEach(function(tech) {
                        select.append(`<option value="${tech.id}">${tech.name} - ${tech.phone_number}</option>`);
                    });
                }
            }
        },
        error: function(xhr) {
            console.error('Error loading technicians:', xhr);
        }
    });
}

function populateYearDropdown() {
    const $ = jQuery || window.$;
    const select = $('.filter-control[data-filter-name="year"]');
    
    // Get current Jalali year from server or use approximate
    const now = new Date();
    const gregorianYear = now.getFullYear();
    // Approximate conversion: Jalali year ≈ Gregorian year - 621
    const currentYear = gregorianYear - 621;
    const startYear = 1395;
    
    select.html('<option value="">همه سال‌ها</option>');
    
    // Add years from 1395 to current year
    for (let year = startYear; year <= currentYear; year++) {
        select.append(`<option value="${year}">${year}</option>`);
    }
}

// Load organization name
getOrganizationData(function(org, error) {
    if (!error && org) {
        $('#org-name-completed').text(org.name);
    }
});
</script>
@endsection

