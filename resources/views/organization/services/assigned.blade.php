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
                                            "completed": `<span class="badge badge-success">تکمیل شده</span>`
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
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(".show-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShow(id);
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

<script>
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
                <p><strong>تاریخ اختصاص:</strong> ${service.assigned_at_jalali || '-'}</p>
            </div>
            <div class="col-md-6">
                <h6>تکنسین</h6>
                <p><strong>نام:</strong> ${technician.first_name || '-'} ${technician.last_name || ''}</p>
                <p><strong>شماره تماس:</strong> ${technician.phone_number || '-'}</p>
            </div>
        </div>
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

// Load organization name
getOrganizationData(function(org, error) {
    if (!error && org) {
        $('#org-name-assigned').text(org.name);
    }
});
</script>
@endsection

