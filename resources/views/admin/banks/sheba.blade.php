@extends('admin.layout.master')
@section('title', 'مدیریت شماره‌های شبا')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="">مدیریت شماره‌های شبا</h5>
                    </div>
                    <div class="widget-content">
                        @include('admin.components.datatable', [
                            'title' => 'شماره‌های شبا',
                            'apiUrl' => '/api/admin/sheba-numbers',
                            'createButton' => true,
                            'createButtonText' => 'افزودن شماره شبا جدید',
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                ['field' => 'sheba_number', 'label' => 'شماره شبا'],
                                ['field' => 'title', 'label' => 'عنوان'],
                                [
                                    'field' => 'bank',
                                    'label' => 'بانک',
                                    'formatter' => 'function(value) {
                                        return value ? value.name : "-";
                                    }',
                                ],
                                ['field' => 'description', 'label' => 'توضیحات'],
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
                                    'options' => collect(\App\Models\Bank::all())->map(function($bank) {
                                        return [
                                            'value' => $bank->id,
                                            'label' => $bank->name
                                        ];
                                    })->toArray()
                                ],
                            ],
                            'primaryKey' => 'id',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for adding/editing sheba numbers -->
        <div class="modal fade" id="shebaModal" tabindex="-1" role="dialog" aria-labelledby="shebaModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="shebaModalLabel">افزودن شماره شبا</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="shebaForm">
                            <input type="hidden" id="shebaId">
                            <div class="form-group">
                                <label for="sheba_number">شماره شبا</label>
                                <input type="text" class="form-control" id="sheba_number" name="sheba_number" required>
                            </div>
                            <div class="form-group">
                                <label for="title">عنوان</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="form-group">
                                <label for="bank_id">بانک</label>
                                <select class="form-control" id="bank_id" name="bank_id">
                                    <option value="">انتخاب کنید</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="description">توضیحات</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="saveSheba">ذخیره</button>
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
            let currentShebaId = null;

            // Load banks for select
            function loadBanks() {
                $.ajax({
                    url: '/api/admin/banks',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const banks = response.data;
                        const select = $('#bank_id');
                        select.empty();
                        select.append('<option value="">انتخاب کنید</option>');
                        banks.forEach(function(bank) {
                            select.append(`<option value="${bank.id}">${bank.name}</option>`);
                        });
                    }
                });
            }

            // Load banks on page load
            loadBanks();

            // Create new sheba
            $('.create-new-button').click(function() {
                $('#shebaModalLabel').text('افزودن شماره شبا');
                $('#shebaForm')[0].reset();
                $('#shebaId').val('');
                $('#shebaModal').modal('show');
            });

            // Save sheba (create or update)
            $('#saveSheba').click(function() {
                const id = $('#shebaId').val();
                const shebaNumber = $('#sheba_number').val();
                const title = $('#title').val();
                const bankId = $('#bank_id').val();
                const description = $('#description').val();

                if (!shebaNumber || !title) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا فیلدهای اجباری را پر کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const data = {
                    sheba_number: shebaNumber,
                    title: title,
                    bank_id: bankId || null,
                    description: description
                };

                const url = id ? `/api/admin/sheba-numbers/${id}` : '/api/admin/sheba-numbers';
                const method = id ? 'PUT' : 'POST';
                const successMessage = id ? 'شماره شبا با موفقیت ویرایش شد' : 'شماره شبا با موفقیت ایجاد شد';

                $.ajax({
                    url: url,
                    type: method,
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        $('#shebaModal').modal('hide');

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

            // Edit sheba
            window.onEdit = function(id) {
                $.ajax({
                    url: `/api/admin/sheba-numbers/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const sheba = response.data;

                        $('#shebaModalLabel').text('ویرایش شماره شبا');
                        $('#shebaId').val(sheba.id);
                        $('#sheba_number').val(sheba.sheba_number);
                        $('#title').val(sheba.title);
                        $('#bank_id').val(sheba.bank_id);
                        $('#description').val(sheba.description);

                        $('#shebaModal').modal('show');
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

            // Delete sheba
            window.onDelete = function(id) {
                currentShebaId = id;
                $('#deleteConfirmationModal').modal('show');
            };

            // Confirm delete
            $('#confirmDelete').click(function() {
                if (!currentShebaId) return;

                $.ajax({
                    url: `/api/admin/sheba-numbers/${currentShebaId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function() {
                        $('#deleteConfirmationModal').modal('hide');

                        swal({
                            title: 'موفقیت',
                            text: 'شماره شبا با موفقیت حذف شد',
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