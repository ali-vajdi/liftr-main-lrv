@extends('admin.layout.master')

@section('title', 'مدیریت کاربران سازمان')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="">مدیریت کاربران سازمان: {{ $organization->name }}</h5>
                        <div class="mt-2">
                            <a href="{{ route('admin.organizations.view') }}" class="btn btn-secondary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                                بازگشت به لیست سازمان‌ها
                            </a>
                        </div>
                    </div>
                    <div class="widget-content">
                        @include('admin.components.datatable', [
                            'title' => 'کاربران سازمان',
                            'apiUrl' => '/api/admin/organizations/' . $organization->id . '/users',
                            'createButton' => true,
                            'createButtonText' => 'افزودن کاربر جدید',
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                ['field' => 'name', 'label' => 'نام'],
                                ['field' => 'phone_number', 'label' => 'شماره تلفن'],
                                [
                                    'field' => 'username',
                                    'label' => 'نام کاربری',
                                    'formatter' => 'function(value) {
                                        return value || "تعین نشده";
                                    }',
                                ],
                                [
                                    'field' => 'status',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? 
                                            `<span class="badge badge-success">فعال</span>` : 
                                            `<span class="badge badge-danger">غیرفعال</span>`;
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
                            'primaryKey' => 'id',
                            'actions' => '
                                // Show button
                                html += \'<button type="button" class="btn btn-sm btn-info show-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                                
                                // Credentials button
                                html += \'<button type="button" class="btn btn-sm btn-primary credentials-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="تنظیم نام کاربری و رمز عبور">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-key"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>\';
                                html += \'</button>\';
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(".show-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShow(id);
                                });
                                
                                // Handle credentials button click
                                $(".credentials-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onCredentials(id);
                                });
                            ',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for adding/editing users -->
        <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">افزودن کاربر</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="userForm">
                            <input type="hidden" id="userId">
                            <div class="form-group">
                                <label for="name">نام <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="phone_number">شماره تلفن <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                            </div>
                            <div class="form-group">
                                <label for="username">نام کاربری</label>
                                <input type="text" class="form-control" id="username" name="username">
                            </div>
                            <div class="form-group">
                                <label for="password">رمز عبور</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <small class="form-text text-muted">حداقل 6 کاراکتر</small>
                            </div>
                            <div class="form-group">
                                <label for="status">وضعیت <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="1">فعال</option>
                                    <option value="0">غیرفعال</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="saveUser">ذخیره</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Credentials Modal -->
        <div class="modal fade" id="credentialsModal" tabindex="-1" role="dialog" aria-labelledby="credentialsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="credentialsModalLabel">تنظیم نام کاربری و رمز عبور</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="credentialsForm">
                            <input type="hidden" id="credentialsUserId">
                            <div class="form-group">
                                <label for="credentialsUsername">نام کاربری <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="credentialsUsername" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="credentialsPassword">رمز عبور <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="credentialsPassword" name="password" required>
                                <small class="form-text text-muted">حداقل 6 کاراکتر</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="saveCredentials">ذخیره</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailsModalLabel">جزئیات کاربر</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>شناسه</th>
                                        <td id="detailId"></td>
                                    </tr>
                                    <tr>
                                        <th>نام</th>
                                        <td id="detailName"></td>
                                    </tr>
                                    <tr>
                                        <th>شماره تلفن</th>
                                        <td id="detailPhoneNumber"></td>
                                    </tr>
                                    <tr>
                                        <th>نام کاربری</th>
                                        <td id="detailUsername"></td>
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
                                        <th>آخرین ویرایش</th>
                                        <td id="detailUpdatedAt"></td>
                                    </tr>
                                </tbody>
                            </table>
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
            let currentUserId = null;
            const organizationId = {{ $organization->id }};

            // Show user details
            window.onShow = function(id) {
                $.ajax({
                    url: `/api/admin/organizations/${organizationId}/users/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#detailId').text(data.id);
                        $('#detailName').text(data.name);
                        $('#detailPhoneNumber').text(data.phone_number);
                        $('#detailUsername').text(data.username || 'تعین نشده');
                        $('#detailStatus').html(data.status ? 
                            '<span class="badge badge-success">فعال</span>' : 
                            '<span class="badge badge-danger">غیرفعال</span>'
                        );
                        $('#detailCreatedAt').text(new Date(data.created_at).toLocaleDateString('fa-IR'));
                        $('#detailUpdatedAt').text(new Date(data.updated_at).toLocaleDateString('fa-IR'));

                        $('#detailsModal').modal('show');
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

            // Set credentials
            window.onCredentials = function(id) {
                $.ajax({
                    url: `/api/admin/organizations/${organizationId}/users/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        $('#credentialsUserId').val(data.id);
                        $('#credentialsUsername').val(data.username || '');
                        $('#credentialsPassword').val('');
                        $('#credentialsModal').modal('show');
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

            // Create new user
            $('.create-new-button').click(function() {
                $('#userModalLabel').text('افزودن کاربر');
                $('#userForm')[0].reset();
                $('#userId').val('');
                $('#userModal').modal('show');
            });

            // Save user (create or update)
            $('#saveUser').click(function() {
                const id = $('#userId').val();
                const name = $('#name').val();
                const phoneNumber = $('#phone_number').val();
                const username = $('#username').val();
                const password = $('#password').val();
                const status = $('#status').val() === '1' ? true : false;

                if (!name || !phoneNumber) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا نام و شماره تلفن را وارد کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const data = {
                    name: name,
                    phone_number: phoneNumber,
                    username: username,
                    password: password,
                    status: status
                };

                const url = id ? `/api/admin/organizations/${organizationId}/users/${id}` : `/api/admin/organizations/${organizationId}/users`;
                const method = id ? 'PUT' : 'POST';
                const successMessage = id ? 'کاربر با موفقیت ویرایش شد' : 'کاربر با موفقیت ایجاد شد';

                $.ajax({
                    url: url,
                    type: method,
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        $('#userModal').modal('hide');

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

            // Save credentials
            $('#saveCredentials').click(function() {
                const userId = $('#credentialsUserId').val();
                const username = $('#credentialsUsername').val();
                const password = $('#credentialsPassword').val();

                if (!username || !password) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا نام کاربری و رمز عبور را وارد کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const data = {
                    username: username,
                    password: password
                };

                $.ajax({
                    url: `/api/admin/organizations/${organizationId}/users/${userId}/credentials`,
                    type: 'POST',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        $('#credentialsModal').modal('hide');

                        swal({
                            title: 'موفقیت',
                            text: 'نام کاربری و رمز عبور با موفقیت تنظیم شد',
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

            // Edit user
            window.onEdit = function(id) {
                $.ajax({
                    url: `/api/admin/organizations/${organizationId}/users/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const user = response.data;

                        $('#userModalLabel').text('ویرایش کاربر');
                        $('#userId').val(user.id);
                        $('#name').val(user.name);
                        $('#phone_number').val(user.phone_number);
                        $('#username').val(user.username || '');
                        $('#password').val('');
                        $('#status').val(user.status ? '1' : '0');

                        $('#userModal').modal('show');
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

            // Delete user
            window.onDelete = function(id) {
                currentUserId = id;
                $('#deleteConfirmationModal').modal('show');
            };

            // Confirm delete
            $('#confirmDelete').click(function() {
                if (!currentUserId) return;

                $.ajax({
                    url: `/api/admin/organizations/${organizationId}/users/${currentUserId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function() {
                        $('#deleteConfirmationModal').modal('hide');

                        swal({
                            title: 'موفقیت',
                            text: 'کاربر با موفقیت حذف شد',
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
