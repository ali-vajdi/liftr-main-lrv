@extends('organization.layout.master')

@section('title', 'مدیریت آسانسورها - ' . $building->name)

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">مدیریت آسانسورها - {{ $building->name }}</h5>
                        <div class="widget-n">
                            <a href="{{ route('organization.buildings.view') }}" class="btn btn-primary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                                بازگشت به ساختمان‌ها
                            </a>
                        </div>
                    </div>
                    <div class="widget-content">
                        @include('organization.components.datatable', [
                            'title' => 'آسانسورها',
                            'apiUrl' => '/api/organization/buildings/' . $building->id . '/elevators?all=true',
                            'createButton' => false,
                            'createButtonText' => '',
                            'columns' => [
                                [
                                    'field' => 'id',
                                    'label' => 'شناسه',
                                    'formatter' => 'function(value) { return value; }',
                                ],
                                [
                                    'field' => 'name',
                                    'label' => 'نام آسانسور',
                                    'formatter' => 'function(value) { return value; }',
                                ],
                                [
                                    'field' => 'stops_count',
                                    'label' => 'تعداد توقف',
                                    'formatter' => 'function(value) { return value; }',
                                ],
                                [
                                    'field' => 'capacity',
                                    'label' => 'ظرفیت',
                                    'formatter' => 'function(value) { return value; }',
                                ],
                                [
                                    'field' => 'description',
                                    'label' => 'توضیحات',
                                    'formatter' => 'function(value) { return value || "-"; }',
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
                                // Show button only
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

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">جزئیات آسانسور</h5>
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
                                <th>نام آسانسور</th>
                                <td id="detailName"></td>
                            </tr>
                            <tr>
                                <th>تعداد توقف</th>
                                <td id="detailStopsCount"></td>
                            </tr>
                            <tr>
                                <th>ظرفیت</th>
                                <td id="detailCapacity"></td>
                            </tr>
                            <tr>
                                <th>توضیحات</th>
                                <td id="detailDescription"></td>
                            </tr>
                            <tr>
                                <th>وضعیت</th>
                                <td id="detailStatus"></td>
                            </tr>
                            <tr>
                                <th>تاریخ ایجاد</th>
                                <td id="detailCreatedAt"></td>
                            </tr>
                            <tr>
                                <th>آخرین به‌روزرسانی</th>
                                <td id="detailUpdatedAt"></td>
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

@endsection

@section('page-scripts')
<script>
const buildingId = {{ $building->id }};

$(document).ready(function() {
    // Handle show button click (called by datatable component)
    window.onShow = function(id) {
        $.ajax({
            url: `/api/organization/buildings/${buildingId}/elevators/${id}`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#detailId').text(data.id);
                    $('#detailName').text(data.name);
                    $('#detailStopsCount').text(data.stops_count);
                    $('#detailCapacity').text(data.capacity);
                    $('#detailDescription').text(data.description || '-');
                    $('#detailStatus').html(data.status ? 
                        '<span class="badge badge-success">فعال</span>' : 
                        '<span class="badge badge-danger">غیرفعال</span>'
                    );
                    $('#detailCreatedAt').text(new Date(data.created_at).toLocaleDateString('fa-IR'));
                    $('#detailUpdatedAt').text(new Date(data.updated_at).toLocaleDateString('fa-IR'));
                    $('#detailsModal').modal('show');
                }
            },
            error: function(xhr) {
                console.error('Error loading elevator details:', xhr);
            }
        });
    };
});
</script>
@endsection
