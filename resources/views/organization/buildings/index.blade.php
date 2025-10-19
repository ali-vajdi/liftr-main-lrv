@extends('organization.layout.master')

@section('title', 'مدیریت ساختمان‌ها/پروژه‌ها')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">مدیریت ساختمان‌ها/پروژه‌ها - {{ $organization->name }}</h5>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        @include('organization.components.datatable', [
                            'title' => 'ساختمان‌ها/پروژه‌ها',
                            'apiUrl' => '/api/organization/buildings',
                            'createButton' => true,
                            'createButtonText' => 'افزودن ساختمان/پروژه جدید',
                            'columns' => [
                                [
                                    'field' => 'id',
                                    'label' => 'شناسه',
                                    'formatter' => 'function(value) { return value; }',
                                ],
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
                                    'field' => 'organization_user',
                                    'label' => 'ایجادکننده',
                                    'formatter' => 'function(value) { return value ? value.first_name + " " + value.last_name : "-"; }',
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
                                    'field' => 'service_day_of_month',
                                    'label' => 'روز سرویس',
                                    'formatter' => 'function(value) { return value ? value + " ماه" : "-"; }',
                                ],
                                [
                                    'field' => 'status',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? `<span class="badge badge-success">فعال</span>` : `<span class="badge badge-danger">غیرفعال</span>`;
                                    }',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'actions' => '
                                // Show button
                                html += \'<button type="button" class="btn btn-sm btn-info show-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                                
                                // Location button
                                html += \'<button type="button" class="btn btn-sm btn-warning location-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده موقعیت">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-map-pin"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>\';
                                html += \'</button>\';
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
                            ',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="buildingModal" tabindex="-1" role="dialog" aria-labelledby="buildingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buildingModalLabel">افزودن ساختمان/پروژه جدید</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="buildingForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">نام ساختمان/پروژه <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manager_name">نام و نام خانوادگی مدیر/نماینده <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="manager_name" name="manager_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manager_phone">شماره تماس مدیر/نماینده <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="manager_phone" name="manager_phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="building_type">نوع ساختمان <span class="text-danger">*</span></label>
                                <select class="form-control" id="building_type" name="building_type" required>
                                    <option value="">انتخاب کنید</option>
                                    <option value="residential">مسکونی</option>
                                    <option value="office">اداری</option>
                                    <option value="commercial">تجاری</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="province_id">استان <span class="text-danger">*</span></label>
                                <select class="form-control" id="province_id" name="province_id" required>
                                    <option value="">انتخاب کنید</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city_id">شهر <span class="text-danger">*</span></label>
                                <select class="form-control" id="city_id" name="city_id" required>
                                    <option value="">ابتدا استان را انتخاب کنید</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="service_day_of_month">روز سرویس (روز ماه)</label>
                                <input type="number" class="form-control" id="service_day_of_month" name="service_day_of_month" min="1" max="31">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">وضعیت <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="true">فعال</option>
                                    <option value="false">غیرفعال</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">آدرس متنی <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>موقعیت انتخابی</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="selected_latitude" name="selected_latitude" placeholder="عرض جغرافیایی" readonly>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="selected_longitude" name="selected_longitude" placeholder="طول جغرافیایی" readonly>
                            </div>
                        </div>
                        <small class="form-text text-muted">روی نقشه کلیک کنید تا موقعیت را انتخاب کنید</small>
                    </div>
                    <div class="form-group">
                        <label>انتخاب موقعیت روی نقشه</label>
                        <div id="locationMap" style="height: 300px; width: 100%; border: 1px solid #ddd; border-radius: 4px;"></div>
                        <small class="form-text text-muted">نقشه بر اساس شهر انتخابی بارگذاری می‌شود</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" id="saveBuilding">ذخیره</button>
                </div>
            </form>
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
                            <th>شناسه</th>
                            <td id="detailId"></td>
                        </tr>
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
                            <th>روز سرویس</th>
                            <td id="detailServiceDay"></td>
                        </tr>
                        <tr>
                            <th>موقعیت انتخابی</th>
                            <td id="detailLocation"></td>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">تأیید حذف</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                آیا از حذف این ساختمان/پروژه اطمینان دارید؟
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">حذف</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-scripts')
<script>
let currentBuildingId = null;
let map = null;
let locationMap = null;

// Handle create button click
$('.create-new-button').click(function() {
    currentBuildingId = null;
    $('#buildingForm')[0].reset();
    $('#buildingModalLabel').text('افزودن ساختمان/پروژه جدید');
    $('#buildingModal').modal('show');
    
    // Clear the location map
    if (locationMap) {
        locationMap.remove();
        locationMap = null;
    }
});

$(document).ready(function() {
    // Load provinces on page load
    loadProvinces();
    
    // Handle province change
    $('#province_id').on('change', function() {
        const provinceId = $(this).val();
        if (provinceId) {
            loadCities(provinceId);
        } else {
            $('#city_id').html('<option value="">ابتدا استان را انتخاب کنید</option>');
        }
    });
    
    // Handle city change
    $('#city_id').on('change', function() {
        const cityId = $(this).val();
        if (cityId) {
            loadCityLocation(cityId);
            initializeLocationMap();
        }
    });
    
    // Handle edit button click (called by datatable component)
    window.onEdit = function(id) {
        currentBuildingId = id;
        
        $.ajax({
            url: `/api/organization/buildings/${id}`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#name').val(data.name);
                    $('#manager_name').val(data.manager_name);
                    $('#manager_phone').val(data.manager_phone);
                    $('#building_type').val(data.building_type);
                    $('#province_id').val(data.province_id);
                    $('#address').val(data.address);
                    $('#service_day_of_month').val(data.service_day_of_month);
                    $('#status').val(data.status ? 'true' : 'false');
                    $('#selected_latitude').val(data.selected_latitude);
                    $('#selected_longitude').val(data.selected_longitude);
                    
                // Load cities for selected province
                if (data.province_id) {
                    loadCities(data.province_id);
                    setTimeout(() => {
                        $('#city_id').val(data.city_id);
                        // Initialize map after city is loaded
                        setTimeout(() => {
                            initializeLocationMap();
                        }, 100);
                    }, 500);
                }
                    
                    $('#buildingModalLabel').text('ویرایش ساختمان/پروژه');
                    $('#buildingModal').modal('show');
                }
            },
            error: function(xhr) {
                console.error('Error loading building for edit:', xhr);
            }
        });
    };

    // Handle delete button click (called by datatable component)
    window.onDelete = function(id) {
        currentBuildingId = id;
        $('#deleteModal').modal('show');
    };

    // Handle save button click
    $('#saveBuilding').on('click', function() {
        const formData = new FormData($('#buildingForm')[0]);
        const data = Object.fromEntries(formData.entries());
        
        const url = currentBuildingId 
            ? `/api/organization/buildings/${currentBuildingId}`
            : '/api/organization/buildings';
        
        const method = currentBuildingId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            type: method,
            data: data,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
            },
            success: function(response) {
                if (response.success) {
                    $('#buildingModal').modal('hide');
                    
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
                        text: 'خطا در ذخیره اطلاعات',
                        type: 'error',
                        padding: '2em'
                    });
                }
            }
        });
    });
});

// Load provinces
function loadProvinces() {
    $.ajax({
        url: '/api/organization/provinces',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const options = '<option value="">انتخاب کنید</option>';
                response.data.forEach(function(province) {
                    $('#province_id').append(`<option value="${province.id}">${province.name}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading provinces:', xhr);
        }
    });
}

// Load cities by province
function loadCities(provinceId) {
    $.ajax({
        url: '/api/organization/cities-by-province',
        type: 'GET',
        data: { province_id: provinceId },
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                $('#city_id').html('<option value="">انتخاب کنید</option>');
                response.data.forEach(function(city) {
                    $('#city_id').append(`<option value="${city.id}" data-lat="${city.latitude}" data-lng="${city.longitude}">${city.name}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading cities:', xhr);
        }
    });
}

// Load city location
function loadCityLocation(cityId) {
    const selectedOption = $('#city_id option:selected');
    const lat = selectedOption.data('lat');
    const lng = selectedOption.data('lng');
    
    if (lat && lng) {
        $('#selected_latitude').val(lat);
        $('#selected_longitude').val(lng);
    }
}

// Initialize location map for selection
function initializeLocationMap() {
    const lat = parseFloat($('#selected_latitude').val());
    const lng = parseFloat($('#selected_longitude').val());
    
    if (isNaN(lat) || isNaN(lng)) {
        return;
    }
    
    // Remove existing map if it exists
    if (locationMap) {
        locationMap.remove();
    }
    
    // Initialize map
    locationMap = L.map('locationMap').setView([lat, lng], 13);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(locationMap);
    
    // Add marker for current location
    const marker = L.marker([lat, lng]).addTo(locationMap);
    
    // Add click handler to map
    locationMap.on('click', function(e) {
        const newLat = e.latlng.lat;
        const newLng = e.latlng.lng;
        
        // Update coordinate inputs
        $('#selected_latitude').val(newLat.toFixed(8));
        $('#selected_longitude').val(newLng.toFixed(8));
        
        // Update marker position
        marker.setLatLng([newLat, newLng]);
        
        // Update marker popup
        marker.bindPopup(`موقعیت انتخابی: ${newLat.toFixed(6)}, ${newLng.toFixed(6)}`).openPopup();
    });
    
    // Add marker popup for current location
    marker.bindPopup(`موقعیت شهر: ${lat.toFixed(6)}, ${lng.toFixed(6)}`).openPopup();
}

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
                $('#detailId').text(data.id);
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
                $('#detailServiceDay').text(data.service_day_of_month ? data.service_day_of_month + ' ماه' : 'تعریف نشده');
                $('#detailLocation').text(
                    data.selected_latitude && data.selected_longitude 
                        ? `${data.selected_latitude}, ${data.selected_longitude}`
                        : 'تعریف نشده'
                );
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


// Handle delete confirmation
$('#confirmDelete').on('click', function() {
    if (currentBuildingId) {
        $.ajax({
            url: `/api/organization/buildings/${currentBuildingId}`,
            type: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteModal').modal('hide');
                    // Reload datatable
                    if (typeof window.datatableApi !== 'undefined' && window.datatableApi.refresh) {
                        window.datatableApi.refresh();
                    }
                    // Show success message
                    swal({
                        title: 'موفقیت',
                        text: response.message,
                        type: 'success',
                        padding: '2em'
                    });
                }
            },
            error: function(xhr) {
                swal({
                    title: 'خطا',
                    text: 'خطا در حذف اطلاعات',
                    type: 'error',
                    padding: '2em'
                });
            }
        });
    }
});
</script>

<!-- Leaflet CSS and JS for map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
@endsection

