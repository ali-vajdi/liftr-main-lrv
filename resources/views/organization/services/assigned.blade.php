@extends('organization.layout.master')

@section('title', 'سرویس‌های اختصاص داده شده')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">سرویس‌های اختصاص داده شده - <span id="org-name-assigned">...</span></h5>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        @include('organization.components.datatable', [
                            'title' => 'لیست سرویس‌های اختصاص داده شده',
                            'apiUrl' => '/api/organization/services/assigned',
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
                                    'field' => 'building',
                                    'label' => 'استان',
                                    'formatter' => 'function(value) { return value && value.province ? value.province.name : "-"; }',
                                ],
                                [
                                    'field' => 'building',
                                    'label' => 'شهر',
                                    'formatter' => 'function(value) { return value && value.city ? value.city.name : "-"; }',
                                ],
                                [
                                    'field' => 'building',
                                    'label' => 'تعداد آسانسور',
                                    'formatter' => 'function(value) { return value && value.elevators ? value.elevators.length : 0; }',
                                ],
                                [
                                    'field' => 'technician',
                                    'label' => 'تکنسین',
                                    'formatter' => 'function(value) { return value ? value.first_name + " " + value.last_name : "-"; }',
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
                                    'field' => 'assigned_at_jalali',
                                    'label' => 'تاریخ اختصاص',
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
                                // Show button
                                html += \'<button type="button" class="btn btn-sm btn-info show-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                                
                                // Public link button (for assigned and completed services)
                                if (item.building && item.building.slug && item.slug && (item.status === "assigned" || item.status === "completed")) {
                                    const publicUrl = `/buildings/${item.building.slug}/services/${item.slug}`;
                                    html += \'<a href="\' + publicUrl + \'" target="_blank" class="btn btn-sm btn-success public-link-btn mr-1 bs-tooltip" title="مشاهده صفحه عمومی">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-external-link"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>\';
                                    html += \'</a>\';
                                }
                                
                                // Change technician and cancel buttons (only for assigned)
                                if (item.status === "assigned") {
                                    html += \'<button type="button" class="btn btn-sm btn-primary edit-service-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="ویرایش">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>\';
                                    html += \'</button>\';
                                    html += \'<button type="button" class="btn btn-sm btn-warning change-technician-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="تغییر تکنسین">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-x"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="18" y1="8" x2="23" y2="13"></line><line x1="23" y1="8" x2="18" y2="13"></line></svg>\';
                                    html += \'</button>\';
                                    html += \'<button type="button" class="btn btn-sm btn-success resend-checklist-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="ارسال مجدد چک لیست">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>\';
                                    html += \'</button>\';
                                    html += \'<button type="button" class="btn btn-sm btn-danger cancel-service-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="لغو سرویس">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>\';
                                    html += \'</button>\';
                                }
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(document).off("click", ".show-btn").on("click", ".show-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    window.onShow(id);
                                    return false;
                                });
                                
                                // Handle change technician button click
                                $(document).off("click", ".change-technician-btn").on("click", ".change-technician-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    if (id && typeof window.onChangeTechnician === "function") {
                                        window.onChangeTechnician(id);
                                    }
                                    return false;
                                });
                                
                                // Handle edit service button click
                                $(document).off("click", ".edit-service-btn").on("click", ".edit-service-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    if (id && typeof window.onEditService === "function") {
                                        window.onEditService(id);
                                    }
                                    return false;
                                });
                                
                                // Handle cancel service button click
                                $(document).off("click", ".cancel-service-btn").on("click", ".cancel-service-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    if (id && typeof window.onCancelService === "function") {
                                        window.onCancelService(id);
                                    }
                                    return false;
                                });
                                
                                // Handle resend checklist button click
                                $(document).off("click", ".resend-checklist-btn").on("click", ".resend-checklist-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    if (id) {
                                        $("#resendChecklistModal").data("service-id", id).modal("show");
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

<!-- Show Service Details Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1" role="dialog" aria-labelledby="serviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="serviceModalLabel">جزئیات سرویس</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="serviceDetails">
                <!-- Service details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Service Modal -->
<div class="modal fade" id="editServiceModal" tabindex="-1" role="dialog" aria-labelledby="editServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editServiceModalLabel">ویرایش سرویس</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editServiceForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_service_id" name="service_id">
                    <div class="form-group">
                        <label for="edit_visit_date">تاریخ مراجعه <span class="text-danger">*</span></label>
                        <input data-jdp-only-date="true" type="text" class="form-control" id="edit_visit_date" name="visit_date" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_visit_time_range">بازه زمانی مراجعه <span class="text-danger">*</span></label>
                        <select class="form-control" id="edit_visit_time_range" name="visit_time_range" required>
                            <option value="">انتخاب بازه زمانی</option>
                            <option value="06:00 - 08:00">06:00 - 08:00</option>
                            <option value="08:00 - 10:00">08:00 - 10:00</option>
                            <option value="10:00 - 12:00">10:00 - 12:00</option>
                            <option value="12:00 - 14:00">12:00 - 14:00</option>
                            <option value="14:00 - 16:00">14:00 - 16:00</option>
                            <option value="16:00 - 18:00">16:00 - 18:00</option>
                            <option value="18:00 - 20:00">18:00 - 20:00</option>
                            <option value="20:00 - 22:00">20:00 - 22:00</option>
                            <option value="22:00 - 24:00">22:00 - 24:00</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">لغو</button>
                    <button type="button" class="btn btn-primary" id="saveEditService">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Resend Checklist Modal -->
<div class="modal fade" id="resendChecklistModal" tabindex="-1" role="dialog" aria-labelledby="resendChecklistModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resendChecklistModalLabel">ارسال مجدد چک لیست</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <button type="button" class="btn btn-success btn-block" id="resendSmsBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 5px;">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                            ارسال مجدد با اس ام اس
                        </button>
                    </div>
                    <div class="col-md-12 mb-3">
                        <button type="button" class="btn btn-secondary btn-block" id="resendVoiceBtn" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 5px;">
                                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                <line x1="12" y1="19" x2="12" y2="23"></line>
                                <line x1="8" y1="23" x2="16" y2="23"></line>
                            </svg>
                            ارسال مجدد با پیام صوتی
                        </button>
                        <p class="text-muted text-center small mt-2 mb-0">بزودی!</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<!-- Change Technician Modal -->
<div class="modal fade" id="changeTechnicianModal" tabindex="-1" role="dialog" aria-labelledby="changeTechnicianModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeTechnicianModalLabel">تغییر تکنسین</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="changeTechnicianForm">
                <div class="modal-body">
                    <input type="hidden" id="change_service_id" name="service_id">
                    <div class="form-group">
                        <label for="change_technician_id">تکنسین جدید <span class="text-danger">*</span></label>
                        <select class="form-control" id="change_technician_id" name="technician_id" required>
                            <option value="">در حال بارگذاری...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="change_visit_date">تاریخ مراجعه <span class="text-danger">*</span></label>
                        <input data-jdp-only-date="true" type="text" class="form-control" id="change_visit_date" name="visit_date" required>
                    </div>
                    <div class="form-group">
                        <label for="change_visit_time_range">بازه زمانی مراجعه <span class="text-danger">*</span></label>
                        <select class="form-control" id="change_visit_time_range" name="visit_time_range" required>
                            <option value="">انتخاب بازه زمانی</option>
                            <option value="06:00 - 08:00">06:00 - 08:00</option>
                            <option value="08:00 - 10:00">08:00 - 10:00</option>
                            <option value="10:00 - 12:00">10:00 - 12:00</option>
                            <option value="12:00 - 14:00">12:00 - 14:00</option>
                            <option value="14:00 - 16:00">14:00 - 16:00</option>
                            <option value="16:00 - 18:00">16:00 - 18:00</option>
                            <option value="18:00 - 20:00">18:00 - 20:00</option>
                            <option value="20:00 - 22:00">20:00 - 22:00</option>
                            <option value="22:00 - 24:00">22:00 - 24:00</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="change_organization_note">یادداشت شرکت</label>
                        <textarea class="form-control" id="change_organization_note" name="organization_note" rows="4" placeholder="یادداشت شرکت را وارد کنید..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">لغو</button>
                    <button type="button" class="btn btn-warning" id="saveChangeTechnician">تغییر تکنسین</button>
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

window.onShow = function(id) {
    $.ajax({
        url: `/api/organization/services/assigned?page=1`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const service = response.data.find(s => s.id == id);
                if (service) {
                    displayServiceDetails(service);
                }
            }
        },
        error: function(xhr) {
            console.error('Error loading service:', xhr);
        }
    });
};

// Define onEditService function
window.onEditService = function(id) {
    currentServiceId = id;
    $('#edit_service_id').val(id);
    
    // Load service data to populate visit_date and visit_time_range
    const token = localStorage.getItem('organization_token');
    if (token) {
        $.ajax({
            url: `/api/organization/services/assigned?page=1`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success) {
                    const service = response.data.find(s => s.id == id);
                    if (service) {
                        if (service.visit_date_jalali) {
                            $('#edit_visit_date').val(service.visit_date_jalali);
                        }
                        if (service.visit_time_range) {
                            $('#edit_visit_time_range').val(service.visit_time_range);
                        }
                    }
                }
            }
        });
    }
    
    $('#editServiceModal').modal('show');
};

// Define onChangeTechnician function
window.onChangeTechnician = function(id) {
    currentServiceId = id;
    $('#change_service_id').val(id);
    
    // Reset form
    $('#change_organization_note').val('');
    $('#change_visit_date').val('');
    $('#change_visit_time_range').val('');
    $('#change_technician_id').html('<option value="">در حال بارگذاری...</option>');
    
    // Load service data to populate visit_date and visit_time_range
    const token = localStorage.getItem('organization_token');
    if (token) {
        $.ajax({
            url: `/api/organization/services/assigned?page=1`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success) {
                    const service = response.data.find(s => s.id == id);
                    if (service) {
                        if (service.visit_date_jalali) {
                            $('#change_visit_date').val(service.visit_date_jalali);
                        }
                        if (service.visit_time_range) {
                            $('#change_visit_time_range').val(service.visit_time_range);
                        }
                        if (service.organization_note) {
                            $('#change_organization_note').val(service.organization_note);
                        }
                    }
                }
            }
        });
    }
    
    // Load technicians if not already loaded, then show modal
    if (technicians.length === 0) {
        loadTechnicians(function() {
            // Populate change technician select after loading
            const select = $('#change_technician_id');
            select.html('<option value="">انتخاب تکنسین</option>');
            technicians.forEach(function(tech) {
                select.append(`<option value="${tech.id}">${tech.name} - ${tech.phone_number}</option>`);
            });
            $('#changeTechnicianModal').modal('show');
        });
    } else {
        // Populate change technician select
        const select = $('#change_technician_id');
        select.html('<option value="">انتخاب تکنسین</option>');
        technicians.forEach(function(tech) {
            select.append(`<option value="${tech.id}">${tech.name} - ${tech.phone_number}</option>`);
        });
        $('#changeTechnicianModal').modal('show');
    }
};

// Define onCancelService function
window.onCancelService = function(id) {
    const token = localStorage.getItem('organization_token');
    
    if (!token) {
        swal({
            title: 'خطا',
            text: 'لطفاً مجدداً وارد شوید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    swal({
        title: 'آیا مطمئن هستید؟',
        text: 'با لغو این سرویس، تکنسین حذف شده و سرویس به حالت در انتظار بازمی‌گردد.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'بله، لغو کن',
        cancelButtonText: 'خیر',
        padding: '2em'
    }).then(function(result) {
        if (result.value) {
            $.ajax({
                url: `/api/organization/services/${id}/cancel`,
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                success: function(response) {
                    if (response.success) {
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
                            text: response.message || 'خطا در لغو سرویس',
                            type: 'error',
                            padding: '2em'
                        });
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMessage = 'خطا در لغو سرویس';
                    
                    if (response && response.message) {
                        errorMessage = response.message;
                    }
                    
                    swal({
                        title: 'خطا',
                        text: errorMessage,
                        type: 'error',
                        padding: '2em'
                    });
                }
            });
        }
    });
};

function displayServiceDetails(service) {
    const building = service.building || {};
    const technician = service.technician || {};
    const elevators = building.elevators || [];
    
    let elevatorsList = '';
    if (elevators.length > 0) {
        elevators.forEach(function(elevator) {
            elevatorsList += `<li>${elevator.name} - ${elevator.stops_count} توقف - ظرفیت: ${elevator.capacity}</li>`;
        });
    } else {
        elevatorsList = '<li>آسانسوری ثبت نشده است</li>';
    }
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6>اطلاعات ساختمان</h6>
                <p><strong>نام:</strong> ${building.name || '-'}</p>
                <p><strong>مدیر/نماینده:</strong> ${building.manager_name || '-'}</p>
                <p><strong>شماره تماس:</strong> ${building.manager_phone || '-'}</p>
                <p><strong>نوع:</strong> ${building.building_type || '-'}</p>
            </div>
            <div class="col-md-6">
                <h6>موقعیت</h6>
                <p><strong>استان:</strong> ${building.province ? building.province.name : '-'}</p>
                <p><strong>شهر:</strong> ${building.city ? building.city.name : '-'}</p>
                <p><strong>آدرس:</strong> ${building.address || '-'}</p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h6>اطلاعات سرویس</h6>
                <p><strong>ماه:</strong> ${service.service_date_text || '-'}</p>
                <p><strong>سال:</strong> ${service.service_year || '-'}</p>
                <p><strong>وضعیت:</strong> <span class="badge badge-info">${service.status_text || service.status}</span></p>
                <p><strong>تعداد بازدید:</strong> <span class="badge badge-info"><i class="fas fa-eye"></i> ${service.view_count || 0}</span></p>
                <p><strong>تاریخ اختصاص:</strong> ${service.assigned_at_jalali || '-'}</p>
                ${service.visit_date_jalali ? `<p><strong>تاریخ مراجعه:</strong> ${service.visit_date_jalali}</p>` : ''}
                ${service.visit_time_range ? `<p><strong>بازه زمانی مراجعه:</strong> ${service.visit_time_range}</p>` : ''}
            </div>
            <div class="col-md-6">
                <h6>تکنسین</h6>
                <p><strong>نام:</strong> ${technician.first_name || '-'} ${technician.last_name || ''}</p>
                <p><strong>شماره تماس:</strong> ${technician.phone_number || '-'}</p>
            </div>
        </div>
        ${service.organization_note ? `<hr><div class="row"><div class="col-12"><h6>یادداشت شرکت</h6><p>${service.organization_note}</p></div></div>` : ''}
        ${service.technician_note ? `<hr><div class="row"><div class="col-12"><h6>یادداشت تکنسین</h6><p>${service.technician_note}</p></div></div>` : ''}
        ${service.user_note ? `<hr><div class="row"><div class="col-12"><h6>یادداشت کاربر</h6><p>${service.user_note}</p></div></div>` : ''}
        ${service.views && service.views.length > 0 ? (function() {
            let viewsHtml = '<hr><div class="row"><div class="col-12"><h6>جزئیات بازدیدها</h6><div class="table-responsive"><table class="table table-bordered table-sm"><thead><tr><th>ردیف</th><th>تاریخ و زمان</th><th>نوع دستگاه</th><th>مرورگر</th><th>سیستم عامل</th></tr></thead><tbody>';
            const deviceTypeText = {'mobile': 'موبایل', 'tablet': 'تبلت', 'desktop': 'دسکتاپ', 'unknown': 'نامشخص'};
            service.views.forEach(function(view, index) {
                viewsHtml += '<tr><td>' + (index + 1) + '</td><td>' + (view.viewed_at || '-') + '</td><td><span class="badge badge-secondary">' + (deviceTypeText[view.device_type] || view.device_type || '-') + '</span></td><td>' + (view.browser || '-') + '</td><td>' + (view.platform || '-') + '</td></tr>';
            });
            viewsHtml += '</tbody></table></div></div></div>';
            return viewsHtml;
        })() : ''}
        <hr>
        <div class="row">
            <div class="col-12">
                <h6>آسانسورها</h6>
                <ul>
                    ${elevatorsList}
                </ul>
            </div>
        </div>
    `;
    
    $('#serviceDetails').html(html);
    $('#serviceModal').modal('show');
}

// Load technicians
function loadTechnicians(callback) {
    const token = localStorage.getItem('organization_token');
    if (!token) {
        $('#change_technician_id').html('<option value="">خطا در احراز هویت</option>');
        if (callback) callback();
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
                const changeSelect = $('#change_technician_id');
                
                if (changeSelect.length > 0) {
                    changeSelect.html('<option value="">انتخاب تکنسین</option>');
                    
                    if (technicians.length > 0) {
                        technicians.forEach(function(tech) {
                            changeSelect.append(`<option value="${tech.id}">${tech.name} - ${tech.phone_number}</option>`);
                        });
                    } else {
                        changeSelect.html('<option value="">تکنسینی یافت نشد</option>');
                    }
                }
            } else {
                if ($('#change_technician_id').length > 0) {
                    $('#change_technician_id').html('<option value="">خطا در بارگذاری</option>');
                }
            }
            if (callback) callback();
        },
        error: function(xhr) {
            let errorMessage = 'خطا در بارگذاری تکنسین‌ها';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            if ($('#change_technician_id').length > 0) {
                $('#change_technician_id').html(`<option value="">${errorMessage}</option>`);
            }
            if (callback) callback();
        }
    });
}

// Handle change technician form submission
$(document).ready(function() {
    // Initialize JalaliDatePicker for edit_visit_date
    jalaliDatepicker.startWatch({
        selector: '#edit_visit_date',
        date: true,
        time: false,
        hasSecond: false,
        showSelectTimeBtnAlways:false,
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
        minDate:"today",
        maxDate:"attr"
    });
    
    // Initialize JalaliDatePicker for change_visit_date
    jalaliDatepicker.startWatch({
        selector: '#change_visit_date',
        date: true,
        time: false,
        hasSecond: false,
        showSelectTimeBtnAlways:false,
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
        minDate:"today",
        maxDate:"attr"
    });
    
    // Clear form fields when edit service modal is shown
    $('#editServiceModal').on('show.bs.modal', function() {
        // Fields will be populated by onEditService function
    });
    
    // Clear form fields when change technician modal is shown
    $('#changeTechnicianModal').on('show.bs.modal', function() {
        $('#change_visit_date').val('');
        $('#change_visit_time_range').val('');
        $('#change_organization_note').val('');
    });
    
    // Handle edit service form submission
    $(document).on('click', '#saveEditService', function() {
        const visitDate = $('#edit_visit_date').val();
        const visitTimeRange = $('#edit_visit_time_range').val();
        
        if (!visitDate) {
            swal({
                title: 'خطا',
                text: 'لطفاً تاریخ مراجعه را وارد کنید',
                type: 'error',
                padding: '2em'
            });
            return false;
        }
        
        if (!visitTimeRange) {
            swal({
                title: 'خطا',
                text: 'لطفاً بازه زمانی مراجعه را انتخاب کنید',
                type: 'error',
                padding: '2em'
            });
            return false;
        }
        
        if (!currentServiceId) {
            swal({
                title: 'خطا',
                text: 'سرویس نامعتبر است',
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
            url: `/api/organization/services/${currentServiceId}/update-visit`,
            type: 'POST',
            data: {
                visit_date: visitDate,
                visit_time_range: visitTimeRange
            },
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    $('#editServiceModal').modal('hide');
                    $('#editServiceForm')[0].reset();
                    $('#edit_visit_date').val('');
                    $('#edit_visit_time_range').val('');
                    currentServiceId = null;
                    
                    swal({
                        title: 'موفقیت',
                        text: response.message,
                        type: 'success',
                        padding: '2em'
                    });
                    
                    // Reload datatable
                    if (typeof window.datatableApi !== 'undefined' && window.datatableApi.refresh) {
                        window.datatableApi.refresh();
                    }
                } else {
                    swal({
                        title: 'خطا',
                        text: response.message || 'خطا در ویرایش سرویس',
                        type: 'error',
                        padding: '2em'
                    });
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'خطا در ویرایش سرویس';
                
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
                btn.prop('disabled', false).text('ذخیره');
            }
        });
    });
    
    $(document).on('click', '#saveChangeTechnician', function() {
        const technicianId = $('#change_technician_id').val();
        const organizationNote = $('#change_organization_note').val();
        
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
                text: 'سرویس نامعتبر است',
                type: 'error',
                padding: '2em'
            });
            return false;
        }
        
        const visitDate = $('#change_visit_date').val();
        const visitTimeRange = $('#change_visit_time_range').val();
        
        if (!visitDate) {
            swal({
                title: 'خطا',
                text: 'لطفاً تاریخ مراجعه را وارد کنید',
                type: 'error',
                padding: '2em'
            });
            return false;
        }
        
        if (!visitTimeRange) {
            swal({
                title: 'خطا',
                text: 'لطفاً بازه زمانی مراجعه را انتخاب کنید',
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
            url: `/api/organization/services/${currentServiceId}/change-technician`,
            type: 'POST',
            data: {
                technician_id: technicianId,
                organization_note: organizationNote || '',
                visit_date: visitDate,
                visit_time_range: visitTimeRange
            },
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    $('#changeTechnicianModal').modal('hide');
                    $('#changeTechnicianForm')[0].reset();
                    $('#change_visit_date').val('');
                    $('#change_visit_time_range').val('');
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
                        text: response.message || 'خطا در تغییر تکنسین',
                        type: 'error',
                        padding: '2em'
                    });
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'خطا در تغییر تکنسین';
                
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
                btn.prop('disabled', false).text('تغییر تکنسین');
            }
        });
        
        return false;
    });
    
    // Handle resend SMS button click
    $('#resendSmsBtn').on('click', function() {
        const serviceId = $('#resendChecklistModal').data('service-id');
        if (!serviceId) {
            return;
        }
        
        const token = localStorage.getItem('organization_token');
        if (!token) {
            swal({
                title: 'خطا',
                text: 'لطفاً مجدداً وارد شوید',
                type: 'error',
                padding: '2em'
            });
            return;
        }
        
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> در حال ارسال...');
        
        $.ajax({
            url: `/api/organization/services/${serviceId}/resend-checklist-sms`,
            type: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success) {
                    $('#resendChecklistModal').modal('hide');
                    swal({
                        title: 'موفقیت',
                        text: response.message,
                        type: 'success',
                        padding: '2em'
                    });
                } else {
                    swal({
                        title: 'خطا',
                        text: response.message || 'خطا در ارسال پیامک',
                        type: 'error',
                        padding: '2em'
                    });
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'خطا در ارسال پیامک';
                
                if (response && response.message) {
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
                btn.prop('disabled', false).html('<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 5px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> ارسال مجدد با اس ام اس');
            }
        });
    });
    
    // Load technicians on page load
    loadTechnicians();
    
    // Load buildings for filter
    loadBuildingsForFilter();
    
    // Load technicians for filter
    loadTechniciansForFilter();
    
    // Populate year dropdown
    populateYearDropdown();
});

// Load buildings for filter
function loadBuildingsForFilter() {
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

// Load technicians for filter
function loadTechniciansForFilter() {
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

// Populate year dropdown
function populateYearDropdown() {
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
        $('#org-name-assigned').text(org.name);
    }
});
</script>
@endsection

