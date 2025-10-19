@extends('admin.layout.master')

@section('title', 'مدیریت بانک‌ها')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="">مدیریت بانک‌ها</h5>
                    </div>
                    <div class="widget-content">
                        @include('admin.components.datatable', [
                            'title' => 'بانک‌ها',
                            'apiUrl' => '/api/admin/banks',
                            'createButton' => true,
                            'createButtonText' => 'افزودن بانک جدید',
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                ['field' => 'name', 'label' => 'نام بانک'],
                                ['field' => 'branch_name', 'label' => 'نام شعبه'],
                                ['field' => 'account_number', 'label' => 'شماره حساب'],
                                [
                                    'field' => 'created_at',
                                    'label' => 'تاریخ ایجاد',
                                    'formatter' => 'function(value) {
                                        return new Date(value).toLocaleDateString("fa-IR");
                                    }',
                                ],
                            ],
                            'primaryKey' => 'id',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for adding/editing banks -->
        <div class="modal fade" id="bankModal" tabindex="-1" role="dialog" aria-labelledby="bankModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bankModalLabel">افزودن بانک</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="bankForm">
                            <input type="hidden" id="bankId">
                            <div class="form-group">
                                <label for="name">نام بانک</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="branch_name">نام شعبه</label>
                                <input type="text" class="form-control" id="branch_name" name="branch_name" required>
                            </div>
                            <div class="form-group">
                                <label for="account_number">شماره حساب</label>
                                <input type="text" class="form-control" id="account_number" name="account_number" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="saveBank">ذخیره</button>
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
            let currentBankId = null;

            // Create new bank
            $('.create-new-button').click(function() {
                $('#bankModalLabel').text('افزودن بانک');
                $('#bankForm')[0].reset();
                $('#bankId').val('');
                $('#bankModal').modal('show');
            });

            // Save bank (create or update)
            $('#saveBank').click(function() {
                const id = $('#bankId').val();
                const name = $('#name').val();
                const branchName = $('#branch_name').val();
                const accountNumber = $('#account_number').val();

                if (!name) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا نام بانک را وارد کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const data = {
                    name: name,
                    branch_name: branchName,
                    account_number: accountNumber
                };

                const url = id ? `/api/admin/banks/${id}` : '/api/admin/banks';
                const method = id ? 'PUT' : 'POST';
                const successMessage = id ? 'بانک با موفقیت ویرایش شد' : 'بانک با موفقیت ایجاد شد';

                $.ajax({
                    url: url,
                    type: method,
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        $('#bankModal').modal('hide');

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

            // Edit bank
            window.onEdit = function(id) {
                $.ajax({
                    url: `/api/admin/banks/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const bank = response.data;

                        $('#bankModalLabel').text('ویرایش بانک');
                        $('#bankId').val(bank.id);
                        $('#name').val(bank.name);
                        $('#branch_name').val(bank.branch_name);
                        $('#account_number').val(bank.account_number);

                        $('#bankModal').modal('show');
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

            // Delete bank
            window.onDelete = function(id) {
                currentBankId = id;
                $('#deleteConfirmationModal').modal('show');
            };

            // Confirm delete
            $('#confirmDelete').click(function() {
                if (!currentBankId) return;

                $.ajax({
                    url: `/api/admin/banks/${currentBankId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function() {
                        $('#deleteConfirmationModal').modal('hide');

                        swal({
                            title: 'موفقیت',
                            text: 'بانک با موفقیت حذف شد',
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