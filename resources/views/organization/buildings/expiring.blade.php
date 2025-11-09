@extends('organization.layout.master')

@section('title', 'قراردادهای رو به اتمام')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">قراردادهای رو به اتمام - {{ $organization->name }}</h5>
                </div>
                <div class="widget-content">
                    <div class="alert alert-warning">
                        <strong>توجه:</strong> این صفحه ساختمان‌هایی را نمایش می‌دهد که تاریخ پایان سرویس آن‌ها در 30 روز آینده است.
                    </div>
                    <div class="widget-content widget-content-area br-6">
                        @include('organization.components.datatable', [
                            'title' => 'قراردادهای رو به اتمام',
                            'apiUrl' => '/api/organization/buildings?expiring=true',
                            'createButton' => false,
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
                                    'field' => 'service_end_date_jalali',
                                    'label' => 'تاریخ پایان سرویس',
                                    'formatter' => 'function(value, row) { 
                                        if (!value || !row.service_end_date) return "-";
                                        try {
                                            const endDate = new Date(row.service_end_date);
                                            const today = new Date();
                                            today.setHours(0, 0, 0, 0);
                                            endDate.setHours(0, 0, 0, 0);
                                            const diffTime = endDate - today;
                                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                                            if (diffDays < 0) {
                                                return value + ` <span class="badge badge-danger">منقضی شده</span>`;
                                            }
                                            let badgeClass = "badge-success";
                                            if (diffDays <= 7) {
                                                badgeClass = "badge-danger";
                                            } else if (diffDays <= 15) {
                                                badgeClass = "badge-warning";
                                            }
                                            return value + ` <span class="badge ${badgeClass}">${diffDays} روز باقی مانده</span>`;
                                        } catch (e) {
                                            return value;
                                        }
                                    }',
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
                            <th>تاریخ شروع سرویس</th>
                            <td id="detailServiceStartDate"></td>
                        </tr>
                        <tr>
                            <th>تاریخ پایان سرویس</th>
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
                $('#detailServiceStartDate').text(data.service_start_date_jalali || '-');
                $('#detailServiceEndDate').text(data.service_end_date_jalali || '-');
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
</script>

<!-- Leaflet CSS and JS for map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
@endsection

