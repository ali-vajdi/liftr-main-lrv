@extends('organization.layout.master')

@section('title', 'سرویس‌های در انتظار')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">سرویس‌های در انتظار - {{ $organization->name }}</h5>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        @include('organization.components.datatable', [
                            'title' => 'لیست سرویس‌های در انتظار',
                            'apiUrl' => '/api/organization/services/pending',
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
                                            "completed": `<span class="badge badge-success">تکمیل شده</span>`
                                        };
                                        return statuses[value] || value;
                                    }',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'actions' => '
                                // Assign button
                                const serviceId = item.id || item.service_id || "";
                                html += \'<button type="button" class="btn btn-sm btn-primary assign-btn mr-1 bs-tooltip" data-id="\' + serviceId + \'" title="اختصاص تکنسین">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-check"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>\';
                                html += \'</button>\';
                                console.log("Assign button created for service ID:", serviceId);
                            ',
                            'actionHandlers' => '
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

// Define onAssign function immediately to ensure it's available when datatable loads
window.onAssign = function(id) {
    console.log("onAssign called with ID:", id);
    if (!id) {
        console.error("No service ID provided");
        alert("خطا: شناسه سرویس نامعتبر است");
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
        
        // Also reload when modal is shown (fallback)
        $('#assignModal').on('show.bs.modal', function() {
            if (technicians.length === 0) {
                loadTechnicians();
            }
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
        console.log("Technician ID:", technicianId);
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
        
        // Disable button to prevent double submission
        const btn = $(this);
        btn.prop('disabled', true).text('در حال ارسال...');
        
        console.log("Sending AJAX request to assign technician");
        $.ajax({
            url: `/api/organization/services/${currentServiceId}/assign-technician`,
            type: 'POST',
            data: {
                technician_id: technicianId
            },
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                console.log("Success response:", response);
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
</script>
@endsection

