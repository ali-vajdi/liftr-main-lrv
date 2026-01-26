@extends('organization.layout.master')

@section('title', 'گزارش خرابی')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">گزارش خرابی</h5>
                        <div>
                            <a href="{{ route('organization.damages.create') }}" class="btn btn-primary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                فرم ثبت خرابی
                            </a>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="widget-content-area br-6 px-0">
                        @include('organization.components.datatable', [
                            'title' => 'لیست گزارش‌های خرابی',
                            'apiUrl' => '/api/organization/damages',
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
                            ],
                            'columns' => [
                                [
                                    'field' => 'building',
                                    'label' => 'نام ساختمان',
                                    'formatter' => 'function(value) { return value ? value.name : "-"; }',
                                ],
                                [
                                    'field' => 'report_date_jalali',
                                    'label' => 'تاریخ و زمان اعلام',
                                    'formatter' => 'function(value) { return value || "-"; }',
                                ],
                                [
                                    'field' => 'visit_date_jalali',
                                    'label' => 'تاریخ و زمان مراجعه',
                                    'formatter' => 'function(value) { return value || "-"; }',
                                ],
                                [
                                    'field' => 'technician',
                                    'label' => 'نام تکنسین',
                                    'formatter' => 'function(value) { return value ? value.name : "-"; }',
                                ],
                                [
                                    'field' => 'description',
                                    'label' => 'توضیحات',
                                    'formatter' => 'function(value) { return value || "-"; }',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'hideDefaultActions' => false,
                            'actions' => '
                                const damageId = item.id || "";
                                html += \'<button type="button" class="btn btn-sm btn-primary edit-btn mr-1 bs-tooltip" data-id="\' + damageId + \'" title="ویرایش">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>\';
                                html += \'</button>\';
                                html += \'<button type="button" class="btn btn-sm btn-danger delete-btn mr-1 bs-tooltip" data-id="\' + damageId + \'" title="حذف">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>\';
                                html += \'</button>\';
                            ',
                            'actionHandlers' => '
                                // Handle edit button click
                                $(document).off("click", ".edit-btn").on("click", ".edit-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    if (id && typeof window.onEdit === "function") {
                                        window.onEdit(id);
                                    }
                                    return false;
                                });
                                
                                // Handle delete button click
                                $(document).off("click", ".delete-btn").on("click", ".delete-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    if (id && typeof window.onDelete === "function") {
                                        window.onDelete(id);
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
@endsection

@section('page-scripts')
<style>
/* Fix horizontal spacing issues - apply consistent margins to all sections */
.widget-content {
    padding: 20px;
}

.widget-content > * {
    margin-left: 0;
    margin-right: 0;
}

.widget-content .row {
    margin-left: 0;
    margin-right: 0;
}

.widget-content .row > [class*="col-"] {
    padding-left: 10px;
    padding-right: 10px;
}

.widget-content .table-responsive {
    margin-left: 0;
    margin-right: 0;
}

.widget-content hr {
    margin-left: 0;
    margin-right: 0;
}

.widget-content .card {
    margin-left: 0;
    margin-right: 0;
}

.widget-content .form-group {
    margin-left: 0;
    margin-right: 0;
}
</style>
<script>
// Define onEdit function
window.onEdit = function(id) {
    window.location.href = '{{ route("organization.damages.edit", ":id") }}'.replace(':id', id);
};

// Define onDelete function
window.onDelete = function(id) {
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
        text: 'این گزارش خرابی حذف خواهد شد و قابل بازگشت نیست.',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'خیر',
        padding: '2em'
    }).then(function(result) {
        if (result.value) {
            $.ajax({
                url: `/api/organization/damages/${id}`,
                type: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                success: function(response) {
                    if (response.success) {
                        swal({
                            title: 'موفقیت',
                            text: response.message || 'گزارش خرابی با موفقیت حذف شد',
                            type: 'success',
                            padding: '2em'
                        });
                        
                        if (typeof window.datatableApi !== 'undefined' && window.datatableApi.refresh) {
                            window.datatableApi.refresh();
                        }
                    } else {
                        swal({
                            title: 'خطا',
                            text: response.message || 'خطا در حذف گزارش خرابی',
                            type: 'error',
                            padding: '2em'
                        });
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMessage = 'خطا در حذف گزارش خرابی';
                    
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

// Load buildings for filter
function loadBuildings() {
    const $ = jQuery || window.$;
    const token = localStorage.getItem('organization_token');
    if (!token) {
        return;
    }

    $.ajax({
        url: '/api/organization/damages/buildings',
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
                        select.append(`<option value="${building.id}">${building.name}</option>`);
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

// Wait for jQuery
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Load filters
        loadBuildings();
        loadTechnicians();
    });
    
})(jQuery || window.jQuery || window.$);
</script>
@endsection

