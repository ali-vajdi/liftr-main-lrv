@extends('organization.layout.master')

@section('title', 'قراردادهای تمام شده')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">قراردادهای تمام شده - <span id="org-name-expired">...</span></h5>
                </div>
                <div class="widget-content">
                    <div class="alert alert-danger mb-4">
                        <strong>توجه:</strong> این صفحه ساختمان‌هایی را نمایش می‌دهد که تاریخ پایان قرارداد آن‌ها گذشته است.
                    </div>
                    <!-- Custom Filters for Expired Contracts -->
                    <div class="card mb-4" style="border: 1px solid #e0e6ed; border-radius: 8px;">
                        <div class="card-body">
                            <h6 class="card-title mb-3" style="font-weight: 600; color: #3b3f5c;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                                فیلترها
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-10">
                                    <label for="statusSelect" class="form-label" style="font-weight: 500; margin-bottom: 8px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 5px;">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="2" x2="12" y2="6"></line>
                                            <line x1="12" y1="18" x2="12" y2="22"></line>
                                            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                            <line x1="2" y1="12" x2="6" y2="12"></line>
                                            <line x1="18" y1="12" x2="22" y2="12"></line>
                                            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                                            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                                        </svg>
                                        وضعیت
                                    </label>
                                    <select id="statusSelect" class="form-control" style="border-radius: 6px; padding: 10px 15px;">
                                        <option value="all" selected>همه</option>
                                        <option value="true">فعال</option>
                                        <option value="false">غیرفعال</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary btn-block" id="applyFiltersBtn" style="border-radius: 6px; padding: 10px 15px; font-weight: 500;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 5px;">
                                            <polyline points="23 4 23 10 17 10"></polyline>
                                            <polyline points="1 20 1 14 7 14"></polyline>
                                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                        </svg>
                                        اعمال
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="widget-content widget-content-area br-6">
                        @include('organization.components.datatable', [
                            'title' => 'قراردادهای تمام شده',
                            'apiUrl' => '/api/organization/buildings?expired=true&status=all',
                            'createButton' => false,
                            'hideDefaultFilters' => true,
                            'columns' => [
                                [
                                    'field' => 'name',
                                    'label' => 'نام ساختمان/پروژه',
                                    'formatter' => 'function(value) { return value; }',
                                ],
                                [
                                    'field' => 'manager_name',
                                    'label' => 'مدیر/نماینده',
                                    'formatter' => 'function(value) { return value; }',
                                ],
                                [
                                    'field' => 'manager_phone',
                                    'label' => 'شماره تماس',
                                    'formatter' => 'function(value) { return value; }',
                                ],
                                [
                                    'field' => 'building_type',
                                    'label' => 'نوع ساختمان',
                                    'formatter' => 'function(value) { 
                                        const types = {
                                            "residential": "مسکونی",
                                            "office": "اداری", 
                                            "commercial": "تجاری"
                                        };
                                        return types[value] || value;
                                    }',
                                ],
                                [
                                    'field' => 'province',
                                    'label' => 'استان',
                                    'formatter' => 'function(value) { return value ? value.name : "-"; }',
                                ],
                                [
                                    'field' => 'city',
                                    'label' => 'شهر',
                                    'formatter' => 'function(value) { return value ? value.name : "-"; }',
                                ],
                                [
                                    'field' => 'contract_end_date_jalali',
                                    'label' => 'تاریخ پایان قرارداد',
                                    'formatter' => 'function(value, row) { 
                                        if (!value || !row.contract || !row.contract.contract_end_date) return "-";
                                        if (row.days_past !== null && row.days_past !== undefined) {
                                            return value + " <span class=\"badge badge-danger\">" + row.days_past + " روز گذشته</span>";
                                        }
                                        return value;
                                    }',
                                ],
                                [
                                    'field' => 'status',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? "<span class=\"badge badge-success\">فعال</span>" : "<span class=\"badge badge-danger\">غیرفعال</span>";
                                    }',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'hideDefaultActions' => true,
                            'actions' => '
                                // Show button
                                html += \'<button type="button" class="btn btn-sm btn-info show-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                                
                                // Location button
                                html += \'<button type="button" class="btn btn-sm btn-warning location-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده موقعیت">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>\';
                                html += \'</button>\';
                                
                                // Toggle status button
                                const statusIcon = item.status ? "x-circle" : "check-circle";
                                const statusTitle = item.status ? "غیرفعال کردن" : "فعال کردن";
                                html += \'<button type="button" class="btn btn-sm btn-secondary toggle-status-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" data-status="\' + (item.status ? "true" : "false") + \'" title="\' + statusTitle + \'">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-\' + statusIcon + \'"><circle cx="12" cy="12" r="10"></circle>\';
                                if (item.status) {
                                    html += \'<line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>\';
                                } else {
                                    html += \'<polyline points="9 11 12 14 22 4"></polyline>\';
                                }
                                html += \'</svg></button>\';
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(".show-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShow(id);
                                });
                                
                                // Handle location button click
                                $(".location-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShowLocation(id);
                                });
                                
                                // Handle toggle status button click
                                $(".toggle-status-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    const currentStatus = $(this).data("status") === "true";
                                    window.onToggleStatus(id, currentStatus);
                                });
                            ',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">جزئیات ساختمان/پروژه</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>نام ساختمان/پروژه</th>
                            <td id="detailName"></td>
                        </tr>
                        <tr>
                            <th>مدیر/نماینده</th>
                            <td id="detailManagerName"></td>
                        </tr>
                        <tr>
                            <th>شماره تماس</th>
                            <td id="detailManagerPhone"></td>
                        </tr>
                        <tr>
                            <th>ایجادکننده</th>
                            <td id="detailCreator"></td>
                        </tr>
                        <tr>
                            <th>نوع ساختمان</th>
                            <td id="detailBuildingType"></td>
                        </tr>
                        <tr>
                            <th>استان</th>
                            <td id="detailProvince"></td>
                        </tr>
                        <tr>
                            <th>شهر</th>
                            <td id="detailCity"></td>
                        </tr>
                        <tr>
                            <th>آدرس</th>
                            <td id="detailAddress"></td>
                        </tr>
                        <tr>
                            <th>تاریخ شروع قرارداد</th>
                            <td id="detailServiceStartDate"></td>
                        </tr>
                        <tr>
                            <th>تاریخ پایان قرارداد</th>
                            <td id="detailServiceEndDate"></td>
                        </tr>
                        <tr>
                            <th>موقعیت انتخابی</th>
                            <td id="detailLocation"></td>
                        </tr>
                        <tr>
                            <th>تعداد آسانسورها</th>
                            <td id="detailElevatorsCount"></td>
                        </tr>
                        <tr>
                            <th>وضعیت</th>
                            <td id="detailStatus"></td>
                        </tr>
                        <tr>
                            <th>تاریخ ایجاد</th>
                            <td id="detailCreatedAt"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<!-- Location Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" role="dialog" aria-labelledby="locationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="locationModalLabel">موقعیت ساختمان/پروژه</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="map" style="height: 400px; width: 100%;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Dates Modal -->
<div class="modal fade" id="editDatesModal" tabindex="-1" role="dialog" aria-labelledby="editDatesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDatesModalLabel">ویرایش تاریخ قرارداد</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editDatesForm">
                <div class="modal-body">
                    <input type="hidden" id="editDatesBuildingId" name="building_id">
                    <div class="form-group">
                        <label for="editServiceStartDate">تاریخ شروع قرارداد <span class="text-danger">*</span></label>
                        <input data-jdp-only-date="true" type="text" class="form-control" id="editServiceStartDate" name="service_start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="editServiceEndDate">تاریخ پایان قرارداد <span class="text-danger">*</span></label>
                        <input data-jdp-only-date="true" type="text" class="form-control" id="editServiceEndDate" name="service_end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('page-scripts')
<script>
let map = null;

// Show building details
window.onShow = function(id) {
    $.ajax({
        url: `/api/organization/buildings/${id}`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#detailName').text(data.name);
                $('#detailManagerName').text(data.manager_name);
                $('#detailManagerPhone').text(data.manager_phone);
                $('#detailCreator').text(data.organization_user ? 
                    data.organization_user.first_name + ' ' + data.organization_user.last_name : 
                    'نامشخص'
                );
                const buildingTypes = {
                    'residential': 'مسکونی',
                    'office': 'اداری',
                    'commercial': 'تجاری'
                };
                $('#detailBuildingType').text(buildingTypes[data.building_type] || data.building_type);
                $('#detailProvince').text(data.province ? data.province.name : '-');
                $('#detailCity').text(data.city ? data.city.name : '-');
                $('#detailAddress').text(data.address);
                $('#detailServiceStartDate').text(data.contract_start_date_jalali || (data.contract && data.contract.contract_start_date_jalali) || '-');
                $('#detailServiceEndDate').text(data.contract_end_date_jalali || (data.contract && data.contract.contract_end_date_jalali) || '-');
                $('#detailLocation').text(
                    data.selected_latitude && data.selected_longitude 
                        ? `${data.selected_latitude}, ${data.selected_longitude}`
                        : 'تعریف نشده'
                );
                $('#detailElevatorsCount').text(data.elevators_count || 0);
                $('#detailStatus').html(data.status ? 
                    '<span class="badge badge-success">فعال</span>' : 
                    '<span class="badge badge-danger">غیرفعال</span>'
                );
                $('#detailCreatedAt').text(new Date(data.created_at).toLocaleDateString('fa-IR'));

                $('#detailsModal').modal('show');
            }
        },
        error: function(xhr) {
            console.error('Error loading building details:', xhr);
        }
    });
};

// Show building location on map
window.onShowLocation = function(id) {
    $.ajax({
        url: `/api/organization/buildings/${id}`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                if (data.selected_latitude && data.selected_longitude) {
                    showMap(data.selected_latitude, data.selected_longitude, data.name);
                } else {
                    swal({
                        title: 'اطلاع',
                        text: 'موقعیت برای این ساختمان تعریف نشده است',
                        type: 'info',
                        padding: '2em'
                    });
                }
            }
        },
        error: function(xhr) {
            console.error('Error loading building location:', xhr);
        }
    });
};

// Show map
function showMap(lat, lng, title) {
    $('#locationModal').modal('show');
    
    // Initialize map after modal is shown
    $('#locationModal').on('shown.bs.modal', function() {
        if (map) {
            map.remove();
        }
        
        map = L.map('map').setView([lat, lng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        L.marker([lat, lng]).addTo(map)
            .bindPopup(title)
            .openPopup();
    });
}

// Load organization name
getOrganizationData(function(org, error) {
    if (!error && org) {
        $('#org-name-expired').text(org.name);
    }
});


// Toggle status function
window.onToggleStatus = function(id, currentStatus) {
    const newStatus = !currentStatus;
    
    swal({
        title: 'تأیید تغییر وضعیت',
        text: 'آیا از ' + (newStatus ? 'فعال' : 'غیرفعال') + ' کردن این ساختمان اطمینان دارید؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'انصراف',
        padding: '2em'
    }).then((result) => {
        if (result.value) {
            // Get current building data first
            $.ajax({
                url: `/api/organization/buildings/${id}`,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                },
                success: function(response) {
                    if (response.success) {
                        const building = response.data;
                        // Prepare update data with all required fields
                        const updateData = {
                            name: building.name,
                            manager_name: building.manager_name,
                            manager_phone: building.manager_phone,
                            building_type: building.building_type,
                            province_id: building.province_id,
                            city_id: building.city_id,
                            address: building.address,
                            service_start_date: building.contract_start_date_jalali || (building.contract && building.contract.contract_start_date_jalali) || '',
                            service_end_date: building.contract_end_date_jalali || (building.contract && building.contract.contract_end_date_jalali) || '',
                            status: newStatus ? 'true' : 'false',
                            elevators_count: building.elevators_count || 0,
                            monthly_amount: building.monthly_amount || '',
                            selected_latitude: building.selected_latitude,
                            selected_longitude: building.selected_longitude,
                            _method: 'PUT'
                        };
                        
                        $.ajax({
                            url: `/api/organization/buildings/${id}`,
                            type: 'PUT',
                            data: updateData,
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                            },
                            success: function(updateResponse) {
                                if (updateResponse.success) {
                                    swal({
                                        title: 'موفقیت',
                                        text: 'وضعیت ساختمان با موفقیت تغییر کرد',
                                        type: 'success',
                                        padding: '2em',
                                        timer: 2000
                                    });
                                    // Reload datatable
                                    $('.refresh-button').click();
                                }
                            },
                            error: function(xhr) {
                                if (xhr.status === 422) {
                                    const errors = xhr.responseJSON.errors;
                                    let errorMessage = 'خطاهای اعتبارسنجی:\n';
                                    for (const field in errors) {
                                        errorMessage += errors[field][0] + '\n';
                                    }
                                    swal({
                                        title: 'خطا',
                                        text: errorMessage,
                                        type: 'error',
                                        padding: '2em'
                                    });
                                } else {
                                    swal({
                                        title: 'خطا',
                                        text: 'خطا در تغییر وضعیت',
                                        type: 'error',
                                        padding: '2em'
                                    });
                                }
                            }
                        });
                    }
                },
                error: function(xhr) {
                    swal({
                        title: 'خطا',
                        text: 'خطا در بارگذاری اطلاعات ساختمان',
                        type: 'error',
                        padding: '2em'
                    });
                }
            });
        }
    });
};


// Handle filter changes and update datatable
$(document).ready(function() {
    // Store custom parameters globally for datatable to use
    window.expiredFilters = {
        status: 'all'
    };
    
    // Intercept AJAX calls to add custom parameters (only for buildings API with expired)
    const originalAjax = $.ajax;
    const expiredAjaxWrapper = function(options) {
        // Only intercept if it's a buildings API call with expired parameter
        if (options && typeof options === 'object' && options.url) {
            const url = typeof options.url === 'string' ? options.url : '';
            if (url.includes('/api/organization/buildings') && url.includes('expired=true')) {
                if (!options.data) options.data = {};
                // Always add expired
                options.data.expired = 'true';
                // Add status only if not 'all'
                if (window.expiredFilters.status && window.expiredFilters.status !== 'all') {
                    options.data.status = window.expiredFilters.status;
                }
            }
        }
        return originalAjax.apply(this, arguments);
    };
    
    // Replace $.ajax only for this page
    $.ajax = expiredAjaxWrapper;
    
    // Apply filters button
    $('#applyFiltersBtn').on('click', function() {
        const status = $('#statusSelect').val();
        
        // Update global filters
        window.expiredFilters.status = status;
        
        // Trigger datatable reload by clicking refresh button
        $('.refresh-button').click();
    });
});
</script>

<!-- Leaflet CSS and JS for map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
@endsection

