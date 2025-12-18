@extends('organization.layout.master')

@section('title', 'همه سرویس‌ها')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">همه سرویس‌ها - <span id="org-name-all-services">...</span></h5>
                        <button type="button" class="btn btn-primary btn-sm" id="add-service-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 5px;">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            افزودن سرویس
                        </button>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        @include('organization.components.datatable', [
                            'title' => 'لیست همه سرویس‌ها',
                            'apiUrl' => '/api/organization/services/all',
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
                                    'name' => 'status',
                                    'label' => 'وضعیت',
                                    'type' => 'select',
                                    'options' => [
                                        ['value' => '', 'label' => 'همه وضعیت‌ها'],
                                        ['value' => 'pending', 'label' => 'در انتظار'],
                                        ['value' => 'assigned', 'label' => 'اختصاص داده شده'],
                                        ['value' => 'completed', 'label' => 'انجام شده'],
                                        ['value' => 'expired', 'label' => 'منقضی شده'],
                                        ['value' => 'cancelled', 'label' => 'لغو شده'],
                                    ]
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
                                    'field' => 'status',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        const statuses = {
                                            "pending": `<span class="badge badge-warning">در انتظار</span>`,
                                            "assigned": `<span class="badge badge-info">اختصاص داده شده</span>`,
                                            "completed": `<span class="badge badge-success">انجام شده</span>`,
                                            "expired": `<span class="badge badge-danger">منقضی شده</span>`,
                                            "cancelled": `<span class="badge badge-secondary">لغو شده</span>`
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
                                // Check if service is locked (only for pending services where building support period has ended)
                                if (item.status === "pending" && item.is_locked) {
                                    // Show lock button that opens modal
                                    html += \'<button type="button" class="btn btn-sm btn-secondary locked-service-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="پشتیبانی ساختمان به پایان رسیده است">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>\';
                                    html += \'</button>\';
                                } else {
                                    // Show details button
                                    html += \'<button type="button" class="btn btn-sm btn-info show-details-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده جزئیات">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                    html += \'</button>\';
                                    
                                    // Manager page link (for assigned and completed services)
                                    if (item.slug && (item.status === "assigned" || item.status === "completed")) {
                                        const managerUrl = `/d/${item.slug}`;
                                        html += \'<a href="\' + managerUrl + \'" target="_blank" class="btn btn-sm btn-secondary manager-page-btn mr-1 bs-tooltip" title="صفحه مدیر ساختمان">\';
                                        html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-external-link"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>\';
                                        html += \'</a>\';
                                    }
                                    
                                    // Assign button (only for pending)
                                    if (item.status === "pending") {
                                        html += \'<button type="button" class="btn btn-sm btn-primary assign-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="اختصاص تکنسین">\';
                                        html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-check"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>\';
                                        html += \'</button>\';
                                    }
                                    
                                    // Change technician and edit buttons (only for assigned)
                                    if (item.status === "assigned") {
                                        html += \'<button type="button" class="btn btn-sm btn-primary edit-service-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="ویرایش">\';
                                        html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>\';
                                        html += \'</button>\';
                                        html += \'<button type="button" class="btn btn-sm btn-warning change-technician-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="تغییر تکنسین">\';
                                        html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-x"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="18" y1="8" x2="23" y2="13"></line><line x1="23" y1="8" x2="18" y2="13"></line></svg>\';
                                        html += \'</button>\';
                                    }
                                    
                                    // Resend checklist button (for completed services)
                                    if (item.status === "completed") {
                                        html += \'<button type="button" class="btn btn-sm btn-success resend-checklist-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="ارسال مجدد برگه سرویس">\';
                                        html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>\';
                                        html += \'</button>\';
                                    }
                                    
                                    // Print PDF button (for completed services)
                                    if (item.status === "completed") {
                                        html += \'<button type="button" class="btn btn-sm btn-primary print-pdf-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="چاپ PDF">\';
                                        html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>\';
                                        html += \'</button>\';
                                    }
                                    
                                    // Cancel button (for all services except completed and cancelled)
                                    if (item.status !== "completed" && item.status !== "cancelled") {
                                        html += \'<button type="button" class="btn btn-sm btn-danger cancel-service-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="لغو سرویس">\';
                                        html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>\';
                                        html += \'</button>\';
                                    }
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
                                
                                // Handle locked service button click
                                $(document).off("click", ".locked-service-btn").on("click", ".locked-service-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    if (id) {
                                        $("#lockedServiceModal").data("service-id", id).modal("show");
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
                                
                                // Handle print PDF button click
                                $(document).off("click", ".print-pdf-btn").on("click", ".print-pdf-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    if (id && typeof window.onPrintPdf === "function") {
                                        window.onPrintPdf(id);
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
                        <label for="visit_date">تاریخ مراجعه <span class="text-danger">*</span></label>
                        <input data-jdp-only-date="true" type="text" class="form-control" id="visit_date" name="visit_date" required>
                    </div>
                    <div class="form-group">
                        <label for="visit_time_range">بازه زمانی مراجعه <span class="text-danger">*</span></label>
                        <select class="form-control" id="visit_time_range" name="visit_time_range" required>
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
                        <label for="organization_note">یادداشت شرکت</label>
                        <textarea class="form-control" id="organization_note" name="organization_note" rows="4" placeholder="یادداشت شرکت را وارد کنید..."></textarea>
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

<!-- Add Service Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" role="dialog" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServiceModalLabel">افزودن سرویس جدید</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addServiceForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add_building_id">ساختمان <span class="text-danger">*</span></label>
                        <select class="form-control" id="add_building_id" name="building_id" required>
                            <option value="">در حال بارگذاری...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add_service_month">ماه <span class="text-danger">*</span></label>
                        <select class="form-control" id="add_service_month" name="service_month" required>
                            <option value="">انتخاب ماه</option>
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
                    <div class="form-group">
                        <label for="add_service_year">سال <span class="text-danger">*</span></label>
                        <select class="form-control" id="add_service_year" name="service_year" required>
                            <option value="">انتخاب سال</option>
                        </select>
                    </div>
                    <div class="alert alert-danger" id="add-service-error" style="display: none; border-radius: 8px; margin-top: 15px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">لغو</button>
                    <button type="button" class="btn btn-primary" id="saveAddService">افزودن سرویس</button>
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
                <h5 class="modal-title" id="resendChecklistModalLabel">ارسال مجدد برگه سرویس</h5>
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

<!-- Locked Service Modal -->
<div class="modal fade" id="lockedServiceModal" tabindex="-1" role="dialog" aria-labelledby="lockedServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lockedServiceModalLabel">سرویس قفل شده</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-4">
                    <p class="mb-0">تاریخ پشتیبانی ساختمان برای این سرویس به پایان رسیده است. در صورت تمایل به عدم تولید سرویس برای این ساختمان، وضعیت ساختمان را غیرفعال کنید.</p>
                </div>
                
                <!-- Building Information -->
                <div id="buildingInfoSection" class="mb-4" style="display: none;">
                    <div class="card border-info shadow-sm">
                        <div class="card-header bg-info text-white" style="direction: rtl;">
                            <h6 class="mb-0">اطلاعات پشتیبانی ساختمان</h6>
                        </div>
                        <div class="card-body" style="direction: rtl;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>نام ساختمان:</strong>
                                    <div id="info-building-name" class="text-muted">-</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>تاریخ شروع قرارداد:</strong>
                                    <div id="info-service-start-date" class="text-muted">-</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>تاریخ پایان قرارداد:</strong>
                                    <div id="info-service-end-date" class="text-muted">-</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>تعداد سرویس‌های انجام شده:</strong>
                                    <div id="info-completed-count" class="text-muted">-</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>آخرین سرویس:</strong>
                                    <div id="info-last-service" class="text-muted">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="card border-right-danger shadow-sm" style="border-right: 4px solid #dc3545; cursor: pointer; direction: rtl;" onclick="$('#cancelLockedService').click();">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1" style="text-align: right;">
                                        <h6 class="mb-1 font-weight-bold text-danger" style="text-align: right;">لغو سرویس</h6>
                                        <p class="mb-0 text-muted small" style="font-size: 0.875rem; line-height: 1.5; text-align: right;">این سرویس لغو شده و دیگر قابل استفاده نخواهد بود.</p>
                                    </div>
                                    <div class="ml-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-block d-none" id="cancelLockedService">لغو سرویس</button>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <div class="card border-right-warning shadow-sm" style="border-right: 4px solid #ffc107; cursor: pointer; direction: rtl;" onclick="$('#revertLockedService').click();">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1" style="text-align: right;">
                                        <h6 class="mb-1 font-weight-bold text-warning" style="text-align: right;">فعال کردن پشتیبانی این سرویس</h6>
                                        <p class="mb-0 text-muted small" style="font-size: 0.875rem; line-height: 1.5; text-align: right;">این سرویس به حالت دستی تبدیل شده و دیگر قفل نخواهد بود. می‌توانید به صورت دستی برای این سرویس تکنسین اختصاص دهید.</p>
                                    </div>
                                    <div class="ml-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-unlock">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-warning btn-block d-none" id="revertLockedService">فعال کردن پشتیبانی این سرویس</button>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <div class="card border-right-secondary shadow-sm" style="border-right: 4px solid #6c757d; cursor: pointer; direction: rtl;" onclick="$('#cancelBuildingAndService').click();">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1" style="text-align: right;">
                                        <h6 class="mb-1 font-weight-bold text-secondary" style="text-align: right;">غیرفعال کردن ساختمان و لغو سرویس</h6>
                                        <p class="mb-0 text-muted small" style="font-size: 0.875rem; line-height: 1.5; text-align: right;">ساختمان غیرفعال شده و این سرویس لغو می‌شود. دیگر سرویسی برای این ساختمان تولید نخواهد شد.</p>
                                    </div>
                                    <div class="ml-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-building">
                                            <path d="M3 21h18"></path>
                                            <path d="M5 21V7l8-4v18"></path>
                                            <path d="M19 21V11l-6-4"></path>
                                            <line x1="9" y1="9" x2="9" y2="9"></line>
                                            <line x1="9" y1="12" x2="9" y2="12"></line>
                                            <line x1="9" y1="15" x2="9" y2="15"></line>
                                            <line x1="9" y1="18" x2="9" y2="18"></line>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary btn-block d-none" id="cancelBuildingAndService">غیرفعال کردن ساختمان و لغو سرویس</button>
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
    html += '<tr><th width="30%">نام ساختمان:</th><td>' + (service.building ? service.building.name : '-') + '</td></tr>';
    html += '<tr><th>مدیر/نماینده:</th><td>' + (service.building ? service.building.manager_name : '-') + '</td></tr>';
    html += '<tr><th>شماره تماس:</th><td>' + (service.building ? service.building.manager_phone : '-') + '</td></tr>';
    html += '<tr><th>استان:</th><td>' + (service.building && service.building.province ? service.building.province.name : '-') + '</td></tr>';
    html += '<tr><th>شهر:</th><td>' + (service.building && service.building.city ? service.building.city.name : '-') + '</td></tr>';
    html += '<tr><th>ماه سرویس:</th><td>' + (service.service_date_text || '-') + '</td></tr>';
    html += '<tr><th>وضعیت:</th><td>' + (service.status_text || '-') + '</td></tr>';
    html += '<tr><th>دفعات بازدید برگه سرویس توسط مدیرساختمان:</th><td><span class="badge badge-info"><i class="fas fa-eye"></i> ' + (service.view_count || 0) + '</span></td></tr>';
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
                        html += '<strong>' + (desc.checklist_title || desc.title) + '</strong> ';
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

// Define onEditService function
window.onEditService = function(id) {
    const $ = jQuery || window.$;
    currentServiceId = id;
    $('#edit_service_id').val(id);
    
    // Load service data to populate visit_date and visit_time_range
    const token = localStorage.getItem('organization_token');
    if (token) {
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
    const $ = jQuery || window.$;
    currentServiceId = id;
    $('#change_service_id').val(id);
    
    if (technicians.length === 0) {
        loadTechnicians();
    } else {
        // Populate change technician select
        const select = $('#change_technician_id');
        select.html('<option value="">انتخاب تکنسین</option>');
        technicians.forEach(function(tech) {
            select.append(`<option value="${tech.id}">${tech.name} - ${tech.phone_number}</option>`);
        });
    }
    
    $('#changeTechnicianModal').modal('show');
};

// Define onCancelService function
window.onCancelService = function(id) {
    const $ = jQuery || window.$;
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
        text: 'با لغو این سرویس، تکنسین (در صورت وجود) حذف شده و سرویس لغو می‌شود.',
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
        
        // Load technicians and buildings
        loadTechnicians();
        loadBuildings();
        
        // Load filters
        loadBuildingsForFilter();
        loadTechniciansForFilter();
        
        // Populate year dropdown
        populateYearDropdown();
        
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
        
        // Initialize JalaliDatePicker for visit_date
        jalaliDatepicker.startWatch({
            selector: '#visit_date',
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
        
        // Clear form fields when assign modal is shown
        $('#assignModal').on('show.bs.modal', function() {
            $('#visit_date').val('');
            $('#visit_time_range').val('');
            $('#organization_note').val('');
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
        
        // Open add service modal
        $('#add-service-btn').on('click', function() {
            $('#addServiceModal').modal('show');
            $('#add-service-error').hide();
            $('#addServiceForm')[0].reset();
        });
        
        // Handle add service form submission
        $(document).on('click', '#saveAddService', function() {
            const buildingId = $('#add_building_id').val();
            const serviceMonth = $('#add_service_month').val();
            const serviceYear = $('#add_service_year').val();
            
            if (!buildingId) {
                $('#add-service-error').text('لطفاً ساختمان را انتخاب کنید').show();
                return false;
            }
            
            if (!serviceMonth) {
                $('#add-service-error').text('لطفاً ماه را انتخاب کنید').show();
                return false;
            }
            
            if (!serviceYear) {
                $('#add-service-error').text('لطفاً سال را انتخاب کنید').show();
                return false;
            }
            
            $('#add-service-error').hide();
            
            const token = localStorage.getItem('organization_token');
            if (!token) {
                $('#add-service-error').text('لطفاً مجدداً وارد شوید').show();
                return false;
            }
            
            const btn = $(this);
            btn.prop('disabled', true).text('در حال ایجاد...');
            
            $.ajax({
                url: '/api/organization/services',
                type: 'POST',
                data: {
                    building_id: buildingId,
                    service_month: serviceMonth,
                    service_year: serviceYear
                },
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                success: function(response) {
                    if (response.success) {
                        $('#addServiceModal').modal('hide');
                        $('#addServiceForm')[0].reset();
                        
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
                        $('#add-service-error').text(response.message || 'خطا در ایجاد سرویس').show();
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMessage = 'خطا در ایجاد سرویس';
                    
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = 'خطاهای اعتبارسنجی:\n';
                        for (const field in errors) {
                            errorMessage += errors[field][0] + '\n';
                        }
                    } else if (response && response.message) {
                        errorMessage = response.message;
                    }
                    
                    $('#add-service-error').text(errorMessage).show();
                },
                complete: function() {
                    btn.prop('disabled', false).text('افزودن سرویس');
                }
            });
            
            return false;
        });
        
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
                    text: 'سرویس نامعتبر است',
                    type: 'error',
                    padding: '2em'
                });
                return false;
            }
            
            const visitDate = $('#visit_date').val();
            const visitTimeRange = $('#visit_time_range').val();
            
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
                url: `/api/organization/services/${currentServiceId}/assign-technician`,
                type: 'POST',
                data: {
                    technician_id: technicianId,
                    organization_note: organizationNote,
                    visit_date: visitDate,
                    visit_time_range: visitTimeRange
                },
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                success: function(response) {
                    if (response.success) {
                        $('#assignModal').modal('hide');
                        $('#assignForm')[0].reset();
                        $('#visit_date').val('');
                        $('#visit_time_range').val('');
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
        
        // Handle change technician form submission
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
                    organization_note: organizationNote
                },
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                success: function(response) {
                    if (response.success) {
                        $('#changeTechnicianModal').modal('hide');
                        $('#changeTechnicianForm')[0].reset();
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
        
        // Handle locked service modal buttons
        let currentLockedServiceId = null;
        
        $('#lockedServiceModal').on('show.bs.modal', function() {
            currentLockedServiceId = $(this).data('service-id');
            
            // Load building information
            if (currentLockedServiceId) {
                const token = localStorage.getItem('organization_token');
                if (token) {
                    // Show loading
                    $('#buildingInfoSection').hide();
                    
                    $.ajax({
                        url: `/api/organization/services/${currentLockedServiceId}/building-info`,
                        type: 'GET',
                        headers: {
                            'Authorization': 'Bearer ' + token
                        },
                        success: function(response) {
                            if (response.success && response.data) {
                                const data = response.data;
                                
                                // Display building information
                                $('#info-building-name').text(data.building_name || '-');
                                $('#info-service-start-date').text(data.service_start_date_jalali || '-');
                                $('#info-service-end-date').text(data.service_end_date_jalali || '-');
                                $('#info-completed-count').text(data.completed_services_count || '0');
                                
                                if (data.last_service_days_ago !== null && data.last_service_days_ago !== undefined) {
                                    $('#info-last-service').text(data.last_service_date_jalali + ' (' + data.last_service_days_ago + ' روز پیش)');
                                } else {
                                    $('#info-last-service').text('-');
                                }
                                
                                $('#buildingInfoSection').show();
                            }
                        },
                        error: function(xhr) {
                            console.error('Error loading building information:', xhr);
                            $('#buildingInfoSection').hide();
                        }
                    });
                }
            }
        });
        
        // Cancel service button
        $('#cancelLockedService').on('click', function() {
            if (!currentLockedServiceId) {
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
            
            swal({
                title: 'آیا مطمئن هستید؟',
                text: 'این سرویس لغو خواهد شد.',
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
                        url: `/api/organization/services/${currentLockedServiceId}/cancel`,
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + token
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#lockedServiceModal').modal('hide');
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
        });
        
        // Revert service button (set is_manual to true)
        $('#revertLockedService').on('click', function() {
            if (!currentLockedServiceId) {
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
            
            swal({
                title: 'آیا مطمئن هستید؟',
                text: 'پشتیبانی این سرویس فعال شده و به حالت دستی تبدیل خواهد شد. دیگر قفل نخواهد بود و می‌توانید به صورت دستی برای این سرویس تکنسین اختصاص دهید.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'بله، فعال کن',
                cancelButtonText: 'خیر',
                padding: '2em'
            }).then(function(result) {
                if (result.value) {
                    $.ajax({
                        url: `/api/organization/services/${currentLockedServiceId}/revert`,
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + token
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#lockedServiceModal').modal('hide');
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
                                    text: response.message || 'خطا در تبدیل سرویس',
                                    type: 'error',
                                    padding: '2em'
                                });
                            }
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            let errorMessage = 'خطا در تبدیل سرویس';
                            
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
        });
        
        
        // Cancel building and service button
        $('#cancelBuildingAndService').on('click', function() {
            if (!currentLockedServiceId) {
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
            
            swal({
                title: 'آیا مطمئن هستید؟',
                text: 'ساختمان غیرفعال شده و سرویس لغو خواهد شد.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'بله، انجام بده',
                cancelButtonText: 'خیر',
                padding: '2em'
            }).then(function(result) {
                if (result.value) {
                    $.ajax({
                        url: `/api/organization/services/${currentLockedServiceId}/cancel-building`,
                        type: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + token
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#lockedServiceModal').modal('hide');
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
                                    text: response.message || 'خطا در انجام عملیات',
                                    type: 'error',
                                    padding: '2em'
                                });
                            }
                        },
                        error: function(xhr) {
                            const response = xhr.responseJSON;
                            let errorMessage = 'خطا در انجام عملیات';
                            
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
        });
    });
    
})(jQuery || window.jQuery || window.$);

function loadTechnicians() {
    const $ = jQuery || window.$;
    const token = localStorage.getItem('organization_token');
    if (!token) {
        $('#technician_id').html('<option value="">خطا در احراز هویت</option>');
        $('#change_technician_id').html('<option value="">خطا در احراز هویت</option>');
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
                const changeSelect = $('#change_technician_id');
                
                select.html('<option value="">انتخاب تکنسین</option>');
                changeSelect.html('<option value="">انتخاب تکنسین</option>');
                
                if (technicians.length > 0) {
                    technicians.forEach(function(tech) {
                        select.append(`<option value="${tech.id}">${tech.name} - ${tech.phone_number}</option>`);
                        changeSelect.append(`<option value="${tech.id}">${tech.name} - ${tech.phone_number}</option>`);
                    });
                } else {
                    select.html('<option value="">تکنسینی یافت نشد</option>');
                    changeSelect.html('<option value="">تکنسینی یافت نشد</option>');
                }
            } else {
                $('#technician_id').html('<option value="">خطا در بارگذاری</option>');
                $('#change_technician_id').html('<option value="">خطا در بارگذاری</option>');
            }
        },
        error: function(xhr) {
            let errorMessage = 'خطا در بارگذاری تکنسین‌ها';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            $('#technician_id').html(`<option value="">${errorMessage}</option>`);
            $('#change_technician_id').html(`<option value="">${errorMessage}</option>`);
        }
    });
}

function loadBuildings() {
    const $ = jQuery || window.$;
    const token = localStorage.getItem('organization_token');
    if (!token) {
        $('#add_building_id').html('<option value="">خطا در احراز هویت</option>');
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
                buildings = response.data;
                const select = $('#add_building_id');
                
                select.html('<option value="">انتخاب ساختمان</option>');
                
                if (buildings.length > 0) {
                    buildings.forEach(function(building) {
                        select.append(`<option value="${building.id}">${building.name} - ${building.manager_name}</option>`);
                    });
                } else {
                    select.html('<option value="">ساختمانی یافت نشد</option>');
                }
            } else {
                $('#add_building_id').html('<option value="">خطا در بارگذاری</option>');
            }
        },
        error: function(xhr) {
            let errorMessage = 'خطا در بارگذاری ساختمان‌ها';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            $('#add_building_id').html(`<option value="">${errorMessage}</option>`);
        }
    });
}

function populateYearDropdown() {
    const $ = jQuery || window.$;
    const select = $('#add_service_year');
    const filterSelect = $('.filter-control[data-filter-name="year"]');
    
    // Get current Jalali year from server or use approximate
    // Calculate approximate Jalali year from Gregorian
    const now = new Date();
    const gregorianYear = now.getFullYear();
    // Approximate conversion: Jalali year ≈ Gregorian year - 621
    const currentYear = gregorianYear - 621;
    const startYear = 1395; // Start from 1395 as requested
    
    select.html('<option value="">انتخاب سال</option>');
    if (filterSelect.length) {
        filterSelect.html('<option value="">همه سال‌ها</option>');
    }
    
    // Add years from 1395 to current year
    for (let year = startYear; year <= currentYear; year++) {
        select.append(`<option value="${year}">${year}</option>`);
        if (filterSelect.length) {
            filterSelect.append(`<option value="${year}">${year}</option>`);
        }
    }
    
    // Set current year as default
    select.val(currentYear);
    
    // Read URL parameters and set filters
    const urlParams = new URLSearchParams(window.location.search);
    const monthParam = urlParams.get('month');
    const yearParam = urlParams.get('year');
    
    // Function to apply URL parameters to filters
    function applyUrlFilters() {
        if (monthParam) {
            const monthSelect = $('.filter-control[data-filter-name="month"]');
            if (monthSelect.length && monthSelect.find('option').length > 1) {
                monthSelect.val(monthParam);
                if (typeof window.datatableApi !== 'undefined' && window.datatableApi.setFilter) {
                    window.datatableApi.setFilter('month', monthParam);
                }
            }
        }
        
        if (yearParam) {
            const yearSelect = $('.filter-control[data-filter-name="year"]');
            if (yearSelect.length && yearSelect.find('option').length > 1) {
                yearSelect.val(yearParam);
                if (typeof window.datatableApi !== 'undefined' && window.datatableApi.setFilter) {
                    window.datatableApi.setFilter('year', yearParam);
                }
            }
        }
    }
    
    // Wait for datatable to be initialized, then set filters
    // Try multiple times to ensure filters are populated
    let attempts = 0;
    const maxAttempts = 10;
    const checkInterval = setInterval(function() {
        attempts++;
        const monthSelect = $('.filter-control[data-filter-name="month"]');
        const yearSelect = $('.filter-control[data-filter-name="year"]');
        
        // Check if filters are ready (have options populated)
        const monthReady = monthSelect.length > 0 && monthSelect.find('option').length > 1;
        const yearReady = yearSelect.length > 0 && yearSelect.find('option').length > 1;
        const datatableReady = typeof window.datatableApi !== 'undefined' && window.datatableApi.setFilter;
        
        if ((monthReady || !monthParam) && (yearReady || !yearParam) && datatableReady) {
            clearInterval(checkInterval);
            applyUrlFilters();
        } else if (attempts >= maxAttempts) {
            clearInterval(checkInterval);
            // Try to apply anyway
            applyUrlFilters();
        }
    }, 200);
}

// Load buildings for filter
function loadBuildingsForFilter() {
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

// Load technicians for filter
function loadTechniciansForFilter() {
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

// Load organization name
getOrganizationData(function(org, error) {
    if (!error && org) {
        $('#org-name-all-services').text(org.name);
    }
});

// Define onPrintPdf function
window.onPrintPdf = function(id) {
    const $ = jQuery || window.$;
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
    
    const btn = $(`.print-pdf-btn[data-id="${id}"]`);
    const originalHtml = btn.html();
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    
    $.ajax({
        url: `/api/organization/services/${id}/pdf-download-url`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success && response.data && response.data.download_url) {
                // Open download URL in new tab
                window.open(response.data.download_url, '_blank');
            } else {
                swal({
                    title: 'خطا',
                    text: response.message || 'خطا در ایجاد لینک دانلود',
                    type: 'error',
                    padding: '2em'
                });
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            let errorMessage = 'خطا در ایجاد لینک دانلود';
            
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
            btn.prop('disabled', false).html(originalHtml);
        }
    });
};

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
</script>
@endsection

