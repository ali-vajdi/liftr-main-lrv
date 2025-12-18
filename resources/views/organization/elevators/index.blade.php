@extends('organization.layout.master')

@section('title', 'مدیریت آسانسورها')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">مدیریت آسانسورها - <span id="building-name">...</span></h5>
                        <div class="widget-n">
                            <button type="button" class="btn btn-success btn-sm mr-2" id="manageElevatorsBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-up"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
                                مدیریت آسانسورها
                            </button>
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
                            'apiUrl' => '/api/organization/buildings/' . $buildingId . '/elevators?all=true',
                            'createButton' => false,
                            'createButtonText' => '',
                            'columns' => [
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

    <!-- Elevators Management Modal -->
    <div class="modal fade" id="elevatorsManagementModal" tabindex="-1" role="dialog" aria-labelledby="elevatorsManagementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="elevatorsManagementModalLabel">مدیریت آسانسورها</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <button type="button" class="btn btn-success" id="addElevatorBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            افزودن آسانسور جدید
                        </button>
                    </div>
                    <div id="elevatorsListContainer">
                        <!-- Elevators list will be dynamically generated here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Elevator Form Modal (Add/Edit) -->
    <div class="modal fade" id="elevatorFormModal" tabindex="-1" role="dialog" aria-labelledby="elevatorFormModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="elevatorFormModalLabel">افزودن آسانسور</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="elevatorForm">
                    <div class="modal-body">
                        <input type="hidden" id="elevator_id" name="elevator_id">
                        <div class="form-group">
                            <label for="elevator_name">نام آسانسور <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="elevator_name" name="name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="elevator_stops_count">تعداد توقف <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="elevator_stops_count" name="stops_count" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="elevator_capacity">ظرفیت <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="elevator_capacity" name="capacity" min="1" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="elevator_status">وضعیت <span class="text-danger">*</span></label>
                            <select class="form-control" id="elevator_status" name="status" required>
                                <option value="true">فعال</option>
                                <option value="false">غیرفعال</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="elevator_description">توضیحات</label>
                            <textarea class="form-control" id="elevator_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary" id="saveElevatorBtn">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('page-scripts')
<script>
const buildingId = {{ $buildingId }};

$(document).ready(function() {
    // Load building data
    var token = localStorage.getItem('organization_token');
    if (token) {
        $.ajax({
            url: '/api/organization/buildings/' + buildingId,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.data) {
                    $('#building-name').text(response.data.name);
                }
            },
            error: function(xhr) {
                console.error('Error loading building:', xhr);
            }
        });
    }
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

    // Handle manage elevators button click
    $('#manageElevatorsBtn').on('click', function() {
        openElevatorsManagementModal();
    });

    // Handle add elevator button
    $('#addElevatorBtn').on('click', function() {
        openElevatorFormModal();
    });

    // Handle elevator form submit
    $('#elevatorForm').on('submit', function(e) {
        e.preventDefault();
        
        const elevatorId = $('#elevator_id').val();
        const formData = {
            name: $('#elevator_name').val(),
            stops_count: parseInt($('#elevator_stops_count').val()),
            capacity: parseInt($('#elevator_capacity').val()),
            status: $('#elevator_status').val() === 'true',
            description: $('#elevator_description').val() || null
        };
        
        const url = elevatorId 
            ? `/api/organization/buildings/${buildingId}/elevators/${elevatorId}`
            : `/api/organization/buildings/${buildingId}/elevators`;
        const method = elevatorId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            type: method,
            data: formData,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
            },
            success: function(response) {
                if (response.success) {
                    $('#elevatorFormModal').modal('hide');
                    swal({
                        title: 'موفقیت',
                        text: response.message,
                        type: 'success',
                        padding: '2em',
                        timer: 2000
                    });
                    loadElevatorsList();
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
                        text: 'خطا در ذخیره اطلاعات آسانسور',
                        type: 'error',
                        padding: '2em'
                    });
                }
            }
        });
    });
});

// Open elevators management modal
function openElevatorsManagementModal() {
    loadElevatorsList();
    $('#elevatorsManagementModal').modal('show');
}

// Load elevators list
function loadElevatorsList() {
    $.ajax({
        url: `/api/organization/buildings/${buildingId}/elevators?all=true`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const elevators = response.data || [];
                renderElevatorsList(elevators);
            }
        },
        error: function(xhr) {
            console.error('Error loading elevators:', xhr);
            renderElevatorsList([]);
        }
    });
}

// Render elevators list
function renderElevatorsList(elevators) {
    const container = $('#elevatorsListContainer');
    container.empty();
    
    if (elevators.length === 0) {
        container.html('<div class="alert alert-info">هیچ آسانسوری تعریف نشده است. برای افزودن آسانسور جدید روی دکمه "افزودن آسانسور جدید" کلیک کنید.</div>');
        return;
    }
    
    elevators.forEach(function(elevator, index) {
        const elevatorId = elevator.id;
        const elevatorHtml = `
            <div class="card mb-3 elevator-item" data-id="${elevatorId}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="mb-1">${elevator.name}</h6>
                            <p class="mb-1 text-muted">
                                <small>تعداد توقف: ${elevator.stops_count} | ظرفیت: ${elevator.capacity}</small>
                            </p>
                            <p class="mb-0">
                                <span class="badge ${elevator.status ? 'badge-success' : 'badge-danger'}">
                                    ${elevator.status ? 'فعال' : 'غیرفعال'}
                                </span>
                            </p>
                            ${elevator.description ? `<p class="mt-2 mb-0"><small>${elevator.description}</small></p>` : ''}
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-sm btn-primary edit-elevator-btn mr-2" data-id="${elevatorId}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                ویرایش
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-elevator-btn" data-id="${elevatorId}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                حذف
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.append(elevatorHtml);
    });
    
    // Attach event handlers
    $('.edit-elevator-btn').on('click', function() {
        const elevatorId = $(this).data('id');
        editElevator(elevatorId);
    });
    
    $('.delete-elevator-btn').on('click', function() {
        const elevatorId = $(this).data('id');
        deleteElevator(elevatorId);
    });
}

// Open elevator form modal (for add/edit)
function openElevatorFormModal(elevatorId = null) {
    $('#elevatorForm')[0].reset();
    $('#elevator_id').val(elevatorId || '');
    
    if (elevatorId) {
        $('#elevatorFormModalLabel').text('ویرایش آسانسور');
        
        // Load elevator data from API
        $.ajax({
            url: `/api/organization/buildings/${buildingId}/elevators/${elevatorId}`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
            },
            success: function(response) {
                if (response.success) {
                    const elevator = response.data;
                    $('#elevator_name').val(elevator.name);
                    $('#elevator_stops_count').val(elevator.stops_count);
                    $('#elevator_capacity').val(elevator.capacity);
                    $('#elevator_status').val(elevator.status ? 'true' : 'false');
                    $('#elevator_description').val(elevator.description || '');
                }
            },
            error: function(xhr) {
                console.error('Error loading elevator:', xhr);
                swal({
                    title: 'خطا',
                    text: 'خطا در بارگذاری اطلاعات آسانسور',
                    type: 'error',
                    padding: '2em'
                });
            }
        });
    } else {
        $('#elevatorFormModalLabel').text('افزودن آسانسور');
    }
    
    $('#elevatorFormModal').modal('show');
}

// Edit elevator
function editElevator(elevatorId) {
    openElevatorFormModal(elevatorId);
}

// Delete elevator
function deleteElevator(elevatorId) {
    swal({
        title: 'تأیید حذف',
        text: 'آیا از حذف این آسانسور اطمینان دارید؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'انصراف',
        padding: '2em'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: `/api/organization/buildings/${buildingId}/elevators/${elevatorId}`,
                type: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                },
                success: function(response) {
                    if (response.success) {
                        swal({
                            title: 'موفقیت',
                            text: response.message,
                            type: 'success',
                            padding: '2em',
                            timer: 2000
                        });
                        loadElevatorsList();
                        // Reload datatable
                        if (typeof window.datatableApi !== 'undefined' && window.datatableApi.refresh) {
                            window.datatableApi.refresh();
                        }
                    }
                },
                error: function(xhr) {
                    swal({
                        title: 'خطا',
                        text: 'خطا در حذف آسانسور',
                        type: 'error',
                        padding: '2em'
                    });
                }
            });
        }
    });
}
</script>
@endsection
