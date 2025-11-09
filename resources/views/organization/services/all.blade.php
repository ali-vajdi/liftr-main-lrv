@extends('organization.layout.master')

@section('title', 'همه سرویس‌ها')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">همه سرویس‌ها - {{ $organization->name }}</h5>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        @include('organization.components.datatable', [
                            'title' => 'لیست همه سرویس‌ها',
                            'apiUrl' => '/api/organization/services/all',
                            'createButton' => false,
                            'columns' => [
                                [
                                    'field' => 'id',
                                    'label' => 'شناسه',
                                    'formatter' => 'function(value) { return value; }',
                                ],
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
                                    'field' => 'status',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        const statuses = {
                                            "pending": `<span class="badge badge-warning">در انتظار</span>`,
                                            "assigned": `<span class="badge badge-info">اختصاص داده شده</span>`,
                                            "completed": `<span class="badge badge-success">تکمیل شده</span>`,
                                            "expired": `<span class="badge badge-danger">منقضی شده</span>`
                                        };
                                        return statuses[value] || value;
                                    }',
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
                            ],
                            'primaryKey' => 'id',
                            'actions' => '
                                // Show details button
                                html += \'<button type="button" class="btn btn-sm btn-info show-details-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده جزئیات">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                                
                                // Assign button (only for pending)
                                if (item.status === "pending") {
                                    html += \'<button type="button" class="btn btn-sm btn-primary assign-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="اختصاص تکنسین">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-check"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>\';
                                    html += \'</button>\';
                                }
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
                                
                                // Handle assign button click
                                $(document).off("click", ".assign-btn").on("click", ".assign-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    if (id && typeof window.onAssign === "function") {
                                        window.onAssign(id);
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

<!-- Assign Technician Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignModalLabel">اختصاص تکنسین</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignForm">
                <div class="modal-body">
                    <input type="hidden" id="service_id" name="service_id">
                    <div class="form-group">
                        <label for="technician_id">تکنسین <span class="text-danger">*</span></label>
                        <select class="form-control" id="technician_id" name="technician_id" required>
                            <option value="">در حال بارگذاری...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="organization_note">یادداشت سازمان</label>
                        <textarea class="form-control" id="organization_note" name="organization_note" rows="4" placeholder="یادداشت سازمان را وارد کنید..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">لغو</button>
                    <button type="button" class="btn btn-primary" id="saveAssign">اختصاص</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script>
let currentServiceId = null;
let technicians = [];
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
    
    // If not found, fetch from API (search all pages)
    $.ajax({
        url: `/api/organization/services/all`,
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
    html += '<tr><th width="30%">شناسه سرویس:</th><td>' + (service.id || '-') + '</td></tr>';
    html += '<tr><th>نام ساختمان:</th><td>' + (service.building ? service.building.name : '-') + '</td></tr>';
    html += '<tr><th>مدیر/نماینده:</th><td>' + (service.building ? service.building.manager_name : '-') + '</td></tr>';
    html += '<tr><th>شماره تماس:</th><td>' + (service.building ? service.building.manager_phone : '-') + '</td></tr>';
    html += '<tr><th>استان:</th><td>' + (service.building && service.building.province ? service.building.province.name : '-') + '</td></tr>';
    html += '<tr><th>شهر:</th><td>' + (service.building && service.building.city ? service.building.city.name : '-') + '</td></tr>';
    html += '<tr><th>ماه سرویس:</th><td>' + (service.service_date_text || '-') + '</td></tr>';
    html += '<tr><th>وضعیت:</th><td>' + (service.status_text || '-') + '</td></tr>';
    html += '</table>';
    html += '</div></div>';
    
    // Assigned Information
    if (service.status === 'assigned' || service.status === 'completed') {
        html += '<div class="card mb-3">';
        html += '<div class="card-header"><h6 class="mb-0">اطلاعات اختصاص</h6></div>';
        html += '<div class="card-body">';
        html += '<table class="table table-bordered">';
        html += '<tr><th width="30%">تکنسین:</th><td>' + (service.technician ? (service.technician.first_name + ' ' + service.technician.last_name) : '-') + '</td></tr>';
        html += '<tr><th>شماره تماس تکنسین:</th><td>' + (service.technician ? service.technician.phone_number : '-') + '</td></tr>';
        html += '<tr><th>تاریخ اختصاص:</th><td>' + (service.assigned_at_jalali || '-') + '</td></tr>';
        if (service.organization_note) {
            html += '<tr><th>یادداشت سازمان:</th><td>' + service.organization_note + '</td></tr>';
        }
        html += '</table>';
        html += '</div></div>';
    }
    
    // Completed Information with Checklist
    if (service.status === 'completed' && service.checklist_data) {
        html += '<div class="card mb-3">';
        html += '<div class="card-header"><h6 class="mb-0">اطلاعات تکمیل و چک‌لیست</h6></div>';
        html += '<div class="card-body">';
        html += '<table class="table table-bordered mb-3">';
        html += '<tr><th width="30%">تاریخ تکمیل:</th><td>' + (service.completed_at_jalali || '-') + '</td></tr>';
        html += '<tr><th>تاریخ ارسال چک‌لیست:</th><td>' + (service.checklist_data.submitted_at || '-') + '</td></tr>';
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
        
        // History
        if (service.checklist_data.history && service.checklist_data.history.length > 0) {
            html += '<h6 class="mt-3 mb-2">تاریخچه تغییرات:</h6>';
            html += '<ul class="list-group">';
            service.checklist_data.history.forEach(function(history) {
                html += '<li class="list-group-item">';
                html += '<strong>عملیات:</strong> ' + history.action + '<br>';
                html += '<strong>تکنسین:</strong> ' + (history.technician_name || '-') + '<br>';
                html += '<strong>تاریخ:</strong> ' + (history.created_at || '-') + '<br>';
                if (history.notes) {
                    html += '<strong>یادداشت:</strong> ' + history.notes;
                }
                html += '</li>';
            });
            html += '</ul>';
        }
        
        html += '</div></div>';
    }
    
    html += '</div>';
    
    $('#serviceDetailsContent').html(html);
    $('#serviceDetailsModal').modal('show');
};

// Define onAssign function
window.onAssign = function(id) {
    const $ = jQuery || window.$;
    currentServiceId = id;
    $('#service_id').val(id);
    
    if (technicians.length === 0) {
        loadTechnicians();
    }
    
    $('#assignModal').modal('show');
};

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
        
        // Load technicians
        loadTechnicians();
        
        // Handle assign form submission
        $(document).on('click', '#saveAssign', function() {
            const technicianId = $('#technician_id').val();
            const organizationNote = $('#organization_note').val();
            
            if (!technicianId) {
                swal({
                    title: 'خطا',
                    text: 'لطفاً تکنسین را انتخاب کنید',
                    type: 'error',
                    padding: '2em'
                });
                return false;
            }
            
            if (!currentServiceId) {
                swal({
                    title: 'خطا',
                    text: 'شناسه سرویس نامعتبر است',
                    type: 'error',
                    padding: '2em'
                });
                return false;
            }
            
            const token = localStorage.getItem('organization_token');
            if (!token) {
                swal({
                    title: 'خطا',
                    text: 'لطفاً مجدداً وارد شوید',
                    type: 'error',
                    padding: '2em'
                });
                return false;
            }
            
            const btn = $(this);
            btn.prop('disabled', true).text('در حال ارسال...');
            
            $.ajax({
                url: `/api/organization/services/${currentServiceId}/assign-technician`,
                type: 'POST',
                data: {
                    technician_id: technicianId,
                    organization_note: organizationNote
                },
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                success: function(response) {
                    if (response.success) {
                        $('#assignModal').modal('hide');
                        $('#assignForm')[0].reset();
                        currentServiceId = null;
                        
                        swal({
                            title: 'موفقیت',
                            text: response.message,
                            type: 'success',
                            padding: '2em'
                        });
                        
                        if (typeof window.datatableApi !== 'undefined' && window.datatableApi.refresh) {
                            window.datatableApi.refresh();
                        }
                    } else {
                        swal({
                            title: 'خطا',
                            text: response.message || 'خطا در اختصاص تکنسین',
                            type: 'error',
                            padding: '2em'
                        });
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMessage = 'خطا در اختصاص تکنسین';
                    
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = 'خطاهای اعتبارسنجی:\n';
                        for (const field in errors) {
                            errorMessage += errors[field][0] + '\n';
                        }
                    } else if (response && response.message) {
                        errorMessage = response.message;
                    }
                    
                    swal({
                        title: 'خطا',
                        text: errorMessage,
                        type: 'error',
                        padding: '2em'
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).text('اختصاص');
                }
            });
            
            return false;
        });
    });
    
})(jQuery || window.jQuery || window.$);

function loadTechnicians() {
    const $ = jQuery || window.$;
    const token = localStorage.getItem('organization_token');
    if (!token) {
        $('#technician_id').html('<option value="">خطا در احراز هویت</option>');
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
                technicians = response.data;
                const select = $('#technician_id');
                select.html('<option value="">انتخاب تکنسین</option>');
                if (technicians.length > 0) {
                    technicians.forEach(function(tech) {
                        select.append(`<option value="${tech.id}">${tech.name} - ${tech.phone_number}</option>`);
                    });
                } else {
                    select.html('<option value="">تکنسینی یافت نشد</option>');
                }
            } else {
                $('#technician_id').html('<option value="">خطا در بارگذاری</option>');
            }
        },
        error: function(xhr) {
            let errorMessage = 'خطا در بارگذاری تکنسین‌ها';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            $('#technician_id').html(`<option value="">${errorMessage}</option>`);
        }
    });
}
</script>
@endsection

