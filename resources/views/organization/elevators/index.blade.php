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
                            'apiUrl' => '/api/organization/buildings/' . $building->id . '/elevators',
                            'createButton' => true,
                            'createButtonText' => 'افزودن آسانسور جدید',
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
                                    'field' => 'status',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? `<span class="badge badge-success">فعال</span>` : `<span class="badge badge-danger">غیرفعال</span>`;
                                    }',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'actions' => '',
                            'actionHandlers' => '',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="elevatorModal" tabindex="-1" role="dialog" aria-labelledby="elevatorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="elevatorModalLabel">افزودن آسانسور جدید</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="elevatorForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">نام آسانسور <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="stops_count">تعداد توقف <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="stops_count" name="stops_count" min="1" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="capacity">ظرفیت <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="capacity" name="capacity" min="1" required>
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="saveElevator">ذخیره</button>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">تایید حذف</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    آیا از حذف این آسانسور اطمینان دارید؟
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
let currentElevatorId = null;
const buildingId = {{ $building->id }};

// Handle create button click
$('.create-new-button').click(function() {
    currentElevatorId = null;
    $('#elevatorForm')[0].reset();
    $('#elevatorModalLabel').text('افزودن آسانسور جدید');
    $('#elevatorModal').modal('show');
});

$(document).ready(function() {
    // Handle edit button click (called by datatable component)
    window.onEdit = function(id) {
        currentElevatorId = id;
        
        $.ajax({
            url: `/api/organization/buildings/${buildingId}/elevators/${id}`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#name').val(data.name);
                    $('#stops_count').val(data.stops_count);
                    $('#capacity').val(data.capacity);
                    $('#status').val(data.status ? 'true' : 'false');
                    
                    $('#elevatorModalLabel').text('ویرایش آسانسور');
                    $('#elevatorModal').modal('show');
                }
            },
            error: function(xhr) {
                console.error('Error loading elevator for edit:', xhr);
            }
        });
    };

    // Handle delete button click (called by datatable component)
    window.onDelete = function(id) {
        currentElevatorId = id;
        $('#deleteModal').modal('show');
    };

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

    // Handle save button click
    $('#saveElevator').on('click', function() {
        const formData = new FormData($('#elevatorForm')[0]);
        const data = Object.fromEntries(formData.entries());
        
        const url = currentElevatorId 
            ? `/api/organization/buildings/${buildingId}/elevators/${currentElevatorId}`
            : `/api/organization/buildings/${buildingId}/elevators`;
        
        const method = currentElevatorId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            type: method,
            data: data,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
            },
            success: function(response) {
                if (response.success) {
                    $('#elevatorModal').modal('hide');
                    
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

    // Handle delete confirmation
    $('#confirmDelete').on('click', function() {
        if (currentElevatorId) {
            $.ajax({
                url: `/api/organization/buildings/${buildingId}/elevators/${currentElevatorId}`,
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
});
</script>
@endsection
