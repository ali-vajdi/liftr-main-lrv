@extends('organization.layout.master')

@section('title', 'سرویس‌های در انتظار')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">سرویس‌های در انتظار - <span id="org-name-pending">...</span></h5>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        @include('organization.components.datatable', [
                            'title' => 'لیست سرویس‌های در انتظار',
                            'apiUrl' => '/api/organization/services/pending',
                            'createButton' => false,
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
                                [
                                    'field' => 'last_service_days_ago',
                                    'label' => 'زمان آخرین سرویس',
                                    'formatter' => 'function(value, row) {
                                        if (value === null || value === undefined) {
                                            return "-";
                                        }
                                        return value + " روزپیش";
                                    }',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'hideDefaultActions' => true,
                            'actions' => '
                                const serviceId = item.id || item.service_id || "";
                                
                                // Check if service is locked (only for pending services where building support period has ended)
                                if (item.status === "pending" && item.is_locked) {
                                    // Show lock button that opens modal
                                    html += \'<button type="button" class="btn btn-sm btn-secondary locked-service-btn mr-1 bs-tooltip" data-id="\' + serviceId + \'" title="پشتیبانی ساختمان به پایان رسیده است">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>\';
                                    html += \'</button>\';
                                } else {
                                    // Show last service details button (if last service exists)
                                    if (item.last_service_id) {
                                        html += \'<button type="button" class="btn btn-sm btn-info show-last-service-btn mr-1 bs-tooltip" data-id="\' + item.last_service_id + \'" title="مشاهده آخرین سرویس">\';
                                        html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                        html += \'</button>\';
                                    }
                                    
                                    // Assign button
                                    html += \'<button type="button" class="btn btn-sm btn-primary assign-btn mr-1 bs-tooltip" data-id="\' + serviceId + \'" title="اختصاص تکنسین">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-check"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>\';
                                    html += \'</button>\';
                                    console.log("Assign button created for service ID:", serviceId);
                                    
                                    // Cancel button (for pending services)
                                    if (item.status !== "completed" && item.status !== "cancelled") {
                                        html += \'<button type="button" class="btn btn-sm btn-danger cancel-service-btn mr-1 bs-tooltip" data-id="\' + serviceId + \'" title="لغو سرویس">\';
                                        html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>\';
                                        html += \'</button>\';
                                    }
                                }
                            ',
                            'actionHandlers' => '
                                // Handle show last service details button click
                                $(document).off("click", ".show-last-service-btn").on("click", ".show-last-service-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    if (id && typeof window.onShowLastService === "function") {
                                        window.onShowLastService(id);
                                    }
                                    return false;
                                });
                                
                                // Handle assign button click - ensure jQuery is available
                                if (typeof jQuery !== "undefined") {
                                    console.log("Setting up assign button handlers");
                                    jQuery(document).off("click", ".assign-btn").on("click", ".assign-btn", function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        const btn = jQuery(this);
                                        const id = btn.data("id") || btn.attr("data-id");
                                        console.log("Assign button clicked - ID:", id, "Button:", btn);
                                        if (id && typeof window.onAssign === "function") {
                                            window.onAssign(id);
                                        } else {
                                            console.error("Error - ID:", id, "onAssign type:", typeof window.onAssign);
                                            alert("خطا در اختصاص تکنسین. لطفاً صفحه را مجدداً بارگذاری کنید.");
                                        }
                                        return false;
                                    });
                                } else {
                                    console.error("jQuery is not available for action handlers");
                                }
                                
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
                            ',
                        ])
                    </div>
                </div>
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

<!-- Last Service Details Modal -->
<div class="modal fade" id="lastServiceDetailsModal" tabindex="-1" role="dialog" aria-labelledby="lastServiceDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lastServiceDetailsModalLabel">جزئیات آخرین سرویس</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="lastServiceDetailsContent">
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
                                    <strong>تعداد سرویس‌های تکمیل شده:</strong>
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

// Define onShowLastService function
window.onShowLastService = function(id) {
    const $ = jQuery || window.$;
    const token = localStorage.getItem('organization_token');
    
    if (!token) {
        alert('لطفاً مجدداً وارد شوید');
        return;
    }
    
    // Show loading
    $('#lastServiceDetailsContent').html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">در حال بارگذاری...</span></div></div>');
    $('#lastServiceDetailsModal').modal('show');
    
    // Fetch service details from API
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
                if (!service) {
                    $('#lastServiceDetailsContent').html('<div class="alert alert-danger">سرویس یافت نشد</div>');
                    return;
                }
                displayLastServiceDetails(service);
            } else {
                $('#lastServiceDetailsContent').html('<div class="alert alert-danger">خطا در بارگذاری اطلاعات</div>');
            }
        },
        error: function(xhr) {
            $('#lastServiceDetailsContent').html('<div class="alert alert-danger">خطا در بارگذاری اطلاعات</div>');
        }
    });
};

function displayLastServiceDetails(service) {
    const $ = jQuery || window.$;
    
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
    
    $('#lastServiceDetailsContent').html(html);
    $('#lastServiceDetailsModal').modal('show');
}

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

// Define onAssign function immediately to ensure it's available when datatable loads
window.onAssign = function(id) {
    console.log("onAssign called with ID:", id);
    if (!id) {
        console.error("No service ID provided");
        alert("خطا: سرویس نامعتبر است");
        return;
    }
    
    // Ensure jQuery is available
    if (typeof jQuery === 'undefined' && typeof $ === 'undefined') {
        console.error("jQuery is not available");
        alert("خطا: jQuery بارگذاری نشده است");
        return;
    }
    
    const $ = jQuery || window.$;
    
    currentServiceId = id;
    $('#service_id').val(id);
    
    // Reload technicians when modal opens (in case they weren't loaded initially)
    if (technicians.length === 0) {
        console.log("Loading technicians...");
        loadTechnicians();
    }
    
    console.log("Showing assign modal for service:", id);
    $('#assignModal').modal('show');
};

// Wait for jQuery to be available
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Load technicians on page load
        loadTechnicians();
        
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
        
        // Also reload when modal is shown (fallback)
        $('#assignModal').on('show.bs.modal', function() {
            if (technicians.length === 0) {
                loadTechnicians();
            }
            // Clear form fields
            $('#visit_date').val('');
            $('#visit_time_range').val('');
            $('#organization_note').val('');
        });
        
        // Additional event handler as fallback (event delegation)
        $(document).on('click', '.assign-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const id = $(this).data('id') || $(this).attr('data-id');
            console.log("Fallback handler: Assign button clicked for service ID:", id);
            if (id && typeof window.onAssign === 'function') {
                window.onAssign(id);
            } else {
                console.error("Error: ID =", id, ", onAssign exists =", typeof window.onAssign === 'function');
            }
            return false;
        });
        
        // Handle assign button click using event delegation (works even if modal is dynamically created)
        $(document).on('click', '#saveAssign', function() {
        console.log("saveAssign button clicked");
        const technicianId = $('#technician_id').val();
        const organizationNote = $('#organization_note').val();
        console.log("Technician ID:", technicianId);
        console.log("Organization Note:", organizationNote);
        console.log("Current Service ID:", currentServiceId);
        
        if (!technicianId) {
            swal({
                title: 'خطا',
                text: 'لطفاً تکنسین را انتخاب کنید',
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
        
        // Disable button to prevent double submission
        const btn = $(this);
        btn.prop('disabled', true).text('در حال ارسال...');
        
        console.log("Sending AJAX request to assign technician");
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
                console.log("Success response:", response);
                if (response.success) {
                    $('#assignModal').modal('hide');
                    $('#assignForm')[0].reset();
                    $('#organization_note').val('');
                    $('#visit_date').val('');
                    $('#visit_time_range').val('');
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
                        text: response.message || 'خطا در اختصاص تکنسین',
                        type: 'error',
                        padding: '2em'
                    });
                }
            },
            error: function(xhr) {
                console.error("Error response:", xhr);
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
                } else if (response && response.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join('\n');
                } else if (xhr.status === 401) {
                    errorMessage = 'احراز هویت نامعتبر. لطفاً مجدداً وارد شوید.';
                } else if (xhr.status === 404) {
                    errorMessage = 'سرویس یافت نشد';
                } else if (xhr.status === 400) {
                    errorMessage = 'این سرویس قبلاً اختصاص داده شده است';
                }
                
                swal({
                    title: 'خطا',
                    text: errorMessage,
                    type: 'error',
                    padding: '2em'
                });
            },
            complete: function() {
                // Re-enable button
                btn.prop('disabled', false).text('اختصاص');
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
    // Ensure jQuery is available
    if (typeof jQuery === 'undefined' && typeof $ === 'undefined') {
        console.error('jQuery is not available');
        return;
    }
    
    const $ = jQuery || window.$;
    const token = localStorage.getItem('organization_token');
    if (!token) {
        console.error('No authentication token found');
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
            console.log('Technicians response:', response);
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
                console.error('Invalid response format:', response);
                $('#technician_id').html('<option value="">خطا در بارگذاری</option>');
            }
        },
        error: function(xhr) {
            console.error('Error loading technicians:', xhr);
            let errorMessage = 'خطا در بارگذاری تکنسین‌ها';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 401) {
                errorMessage = 'احراز هویت نامعتبر';
            }
            $('#technician_id').html(`<option value="">${errorMessage}</option>`);
        }
    });
}

// Load buildings for filter
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

// Populate year dropdown
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

// Wait for jQuery
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Load filters
        loadBuildings();
        loadTechniciansForFilter();
        populateYearDropdown();
    });
    
})(jQuery || window.jQuery || window.$);

// Load organization name
getOrganizationData(function(org, error) {
    if (!error && org) {
        $('#org-name-pending').text(org.name);
    }
});
</script>
@endsection

