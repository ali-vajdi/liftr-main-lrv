@extends('admin.layout.master')
@section('title', 'مدیریت پوزهای بانکی')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="">مدیریت پوزهای بانکی</h5>
                    </div>
                    <div class="widget-content">
                        @include('admin.components.datatable', [
                            'title' => 'پوزهای بانکی',
                            'apiUrl' => '/api/admin/bank-poses',
                            'createButton' => true,
                            'createButtonText' => 'افزودن پوز جدید',
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                ['field' => 'name', 'label' => 'نام پوز'],
                                ['field' => 'description', 'label' => 'توضیحات'],
                                [
                                    'field' => 'bank',
                                    'label' => 'بانک',
                                    'formatter' => 'function(value) {
                                        return value ? value.name : "-";
                                    }',
                                ],
                                [
                                    'field' => 'created_at',
                                    'label' => 'تاریخ ایجاد',
                                    'formatter' => 'function(value) {
                                        return new Date(value).toLocaleDateString("fa-IR");
                                    }',
                                ],
                            ],
                            'filters' => [
                                [
                                    'type' => 'select',
                                    'name' => 'bank_id',
                                    'label' => 'بانک',
                                    'placeholder' => 'همه بانک‌ها',
                                    'options' => [],
                                    'apiUrl' => '/api/admin/banks',
                                    'labelField' => 'name',
                                    'valueField' => 'id',
                                ],
                            ],
                            'primaryKey' => 'id',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for adding/editing poses -->
        <div class="modal fade" id="poseModal" tabindex="-1" role="dialog" aria-labelledby="poseModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="poseModalLabel">افزودن پوز</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="poseForm">
                            <input type="hidden" id="poseId">
                            <div class="form-group">
                                <label for="name">نام پوز</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="description">توضیحات</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="bank_id">بانک</label>
                                <select class="form-control" id="bank_id" name="bank_id" required>
                                    <option value="">انتخاب بانک</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="savePose">ذخیره</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Show Modal -->
        <div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-labelledby="showModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="showModalLabel">جزئیات پوز</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>نام پوز:</strong> <span id="showName"></span></p>
                                <p><strong>بانک:</strong> <span id="showBank"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>تاریخ ایجاد:</strong> <span id="showCreatedAt"></span></p>
                                <p><strong>آخرین ویرایش:</strong> <span id="showUpdatedAt"></span></p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>توضیحات:</strong></p>
                                <p id="showDescription" class="text-justify"></p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal for Delete -->
        <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteConfirmationModalLabel">تایید حذف</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        آیا از حذف این مورد اطمینان دارید؟
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-danger" id="confirmDelete">حذف</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script>
        $(document).ready(function() {
            let currentPoseId = null;

            // Create new pose
            $('.create-new-button').click(function() {
                $('#poseModalLabel').text('افزودن پوز');
                $('#poseForm')[0].reset();
                $('#poseId').val('');
                
                // Load banks for new pose
                $.ajax({
                    url: '/api/admin/banks',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const banks = response.data;
                        const select = $('#bank_id');
                        select.empty().append('<option value="">انتخاب بانک</option>');
                        banks.forEach(bank => {
                            select.append(`<option value="${bank.id}">${bank.name}</option>`);
                        });
                        $('#poseModal').modal('show');
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            swal({
                                title: 'خطای دسترسی',
                                text: 'لطفا مجددا وارد سیستم شوید',
                                type: 'error',
                                padding: '2em'
                            }).then(function() {
                                window.location.href = '/admin/login';
                            });
                        } else {
                            swal({
                                title: 'خطا',
                                text: 'خطا در دریافت لیست بانک‌ها',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            });

            // Save pose (create or update)
            $('#savePose').click(function() {
                const id = $('#poseId').val();
                const name = $('#name').val();
                const description = $('#description').val();
                const bankId = $('#bank_id').val();

                if (!name || !bankId) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا تمام فیلدهای الزامی را پر کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const data = {
                    name: name,
                    description: description,
                    bank_id: bankId
                };

                const url = id ? `/api/admin/bank-poses/${id}` : '/api/admin/bank-poses';
                const method = id ? 'PUT' : 'POST';
                const successMessage = id ? 'پوز با موفقیت ویرایش شد' : 'پوز با موفقیت ایجاد شد';

                $.ajax({
                    url: url,
                    type: method,
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        $('#poseModal').modal('hide');

                        swal({
                            title: 'موفقیت',
                            text: successMessage,
                            type: 'success',
                            padding: '2em'
                        });

                        window.datatableApi.refresh();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            let errorMessage = '';

                            for (const key in errors) {
                                errorMessage += errors[key].join('\n') + '\n';
                            }

                            swal({
                                title: 'خطا در اعتبارسنجی',
                                text: errorMessage,
                                type: 'error',
                                padding: '2em'
                            });
                        } else if (xhr.status === 401) {
                            swal({
                                title: 'خطای دسترسی',
                                text: 'لطفا مجددا وارد سیستم شوید',
                                type: 'error',
                                padding: '2em'
                            }).then(function() {
                                window.location.href = '/admin/login';
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

            // Show pose details
            window.onShow = function(id) {
                $.ajax({
                    url: `/api/admin/bank-poses/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const pose = response.data;
                        
                        $('#showName').text(pose.name);
                        $('#showBank').text(pose.bank ? pose.bank.name : '-');
                        $('#showDescription').text(pose.description || '-');
                        $('#showCreatedAt').text(new Date(pose.created_at).toLocaleDateString('fa-IR'));
                        $('#showUpdatedAt').text(new Date(pose.updated_at).toLocaleDateString('fa-IR'));
                        
                        $('#showModal').modal('show');
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            swal({
                                title: 'خطای دسترسی',
                                text: 'لطفا مجددا وارد سیستم شوید',
                                type: 'error',
                                padding: '2em'
                            }).then(function() {
                                window.location.href = '/admin/login';
                            });
                        } else {
                            swal({
                                title: 'خطا',
                                text: 'خطا در دریافت اطلاعات',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            };

            // Edit pose
            window.onEdit = function(id) {
                // First load banks
                $.ajax({
                    url: '/api/admin/banks',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(banksResponse) {
                        const banks = banksResponse.data;
                        const select = $('#bank_id');
                        select.empty().append('<option value="">انتخاب بانک</option>');
                        banks.forEach(bank => {
                            const option = new Option(bank.name, bank.id);
                            select.append(option);
                        });

                        // Then load pose data
                        $.ajax({
                            url: `/api/admin/bank-poses/${id}`,
                            type: 'GET',
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                            },
                            success: function(response) {
                                const pose = response.data;

                                $('#poseModalLabel').text('ویرایش پوز');
                                $('#poseId').val(pose.id);
                                $('#name').val(pose.name);
                                $('#description').val(pose.description);
                                $('#bank_id').val(pose.bank_id);

                                $('#poseModal').modal('show');
                            },
                            error: function(xhr) {
                                if (xhr.status === 401) {
                                    swal({
                                        title: 'خطای دسترسی',
                                        text: 'لطفا مجددا وارد سیستم شوید',
                                        type: 'error',
                                        padding: '2em'
                                    }).then(function() {
                                        window.location.href = '/admin/login';
                                    });
                                } else {
                                    swal({
                                        title: 'خطا',
                                        text: 'خطا در دریافت اطلاعات',
                                        type: 'error',
                                        padding: '2em'
                                    });
                                }
                            }
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            swal({
                                title: 'خطای دسترسی',
                                text: 'لطفا مجددا وارد سیستم شوید',
                                type: 'error',
                                padding: '2em'
                            }).then(function() {
                                window.location.href = '/admin/login';
                            });
                        } else {
                            swal({
                                title: 'خطا',
                                text: 'خطا در دریافت لیست بانک‌ها',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            };

            // Delete pose
            window.onDelete = function(id) {
                currentPoseId = id;
                $('#deleteConfirmationModal').modal('show');
            };

            // Confirm delete
            $('#confirmDelete').click(function() {
                if (!currentPoseId) return;

                $.ajax({
                    url: `/api/admin/bank-poses/${currentPoseId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function() {
                        $('#deleteConfirmationModal').modal('hide');

                        swal({
                            title: 'موفقیت',
                            text: 'پوز با موفقیت حذف شد',
                            type: 'success',
                            padding: '2em'
                        });

                        window.datatableApi.refresh();
                    },
                    error: function(xhr) {
                        $('#deleteConfirmationModal').modal('hide');

                        if (xhr.status === 401) {
                            swal({
                                title: 'خطای دسترسی',
                                text: 'لطفا مجددا وارد سیستم شوید',
                                type: 'error',
                                padding: '2em'
                            }).then(function() {
                                window.location.href = '/admin/login';
                            });
                        } else if (xhr.status === 422 || xhr.status === 409) {
                            swal({
                                title: 'خطا',
                                text: xhr.responseJSON?.message || 'این مورد قابل حذف نیست زیرا در جای دیگری استفاده شده است',
                                type: 'error',
                                padding: '2em'
                            });
                        } else {
                            swal({
                                title: 'خطا',
                                text: 'خطا در حذف اطلاعات',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection 