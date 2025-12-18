@extends('organization.layout.master')

@section('title', 'کاربران شرکت')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">کاربران شرکت - <span id="org-name-users">...</span></h5>
                    </div>
                    <div class="widget-content">
                        @include('organization.components.datatable', [
                            'title' => 'لیست کاربران شرکت',
                            'apiUrl' => '/api/organization/users',
                            'createButton' => true,
                            'createButtonText' => 'افزودن کاربر جدید',
                            'hideDefaultActions' => true,
                            'columns' => [
                                [
                                    'field' => 'name',
                                    'label' => 'نام',
                                    'formatter' => 'function(value, item) {
                                        let html = value || "";
                                        if (item.is_main_user) {
                                            html += ` <span class="badge badge-primary">مدیر عامل</span>`;
                                        }
                                        return html;
                                    }',
                                ],
                                ['field' => 'phone_number', 'label' => 'شماره تلفن'],
                                [
                                    'field' => 'status',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? `<span class="badge badge-success">فعال</span>` : `<span class="badge badge-danger">غیرفعال</span>`;
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
                                
                                // Edit and Delete buttons (only if current user is main user)
                                if (window.currentUserIsMain) {
                                    // Edit button
                                    html += \'<button type="button" class="btn btn-sm btn-primary edit-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="ویرایش">\';
                                    html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>\';
                                    html += \'</button>\';
                                    
                                    // Delete button (don\'t show for main user)
                                    if (!item.is_main_user) {
                                        html += \'<button type="button" class="btn btn-sm btn-danger delete-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="حذف">\';
                                        html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>\';
                                        html += \'</button>\';
                                    }
                                }
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(".show-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShow(id);
                                });
                                
                                // Handle edit button click
                                $(".edit-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onEdit(id);
                                });
                                
                                // Handle delete button click
                                $(".delete-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onDelete(id);
                                });
                            ',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
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
                                        <th>نام</th>
                                        <td id="detailName"></td>
                                    </tr>
                                    <tr>
                                        <th>شماره تلفن</th>
                                        <td id="detailPhone"></td>
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for adding users -->
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
                            <div class="form-group">
                                <label for="name">نام <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="phone_number">شماره تلفن <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                            </div>
                            <div class="form-group">
                                <label for="password">رمز عبور</label>
                                <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
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
    </div>
@endsection

@section('page-scripts')
    <script>
        $(document).ready(function() {
            // Store current user's main status
            window.currentUserIsMain = false;
            
            // Function to update currentUserIsMain from API response
            function updateCurrentUserIsMain() {
                $.ajax({
                    url: '/api/organization/users',
                    type: 'GET',
                    data: { per_page: 1 },
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        window.currentUserIsMain = response.current_user_is_main || false;
                    },
                    error: function(xhr) {
                        console.error('Error loading user status:', xhr);
                    }
                });
            }
            
            // Load current user's main status from API
            updateCurrentUserIsMain();
            
            // Show user details
            window.onShow = function(id) {
                $.ajax({
                    url: `/api/organization/users/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#detailName').text(data.name);
                        $('#detailPhone').text(data.phone_number);
                        $('#detailStatus').html(data.status ? '<span class="badge badge-success">فعال</span>' : '<span class="badge badge-danger">غیرفعال</span>');
                        $('#detailCreatedAt').text(new Date(data.created_at).toLocaleDateString('fa-IR'));

                        $('#detailsModal').modal('show');
                    },
                    error: function(xhr) {
                        if (xhr.status === 404) {
                            swal({
                                title: 'خطا',
                                text: 'کاربر مورد نظر یافت نشد',
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
                                window.location.href = '/login';
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

            // Load organization name
            getOrganizationData(function(org, error) {
                if (!error && org) {
                    $('#org-name-users').text(org.name);
                }
            });

            // Create new user
            $('.create-new-button').click(function() {
                $('#userModalLabel').text('افزودن کاربر');
                $('#userForm')[0].reset();
                $('#userForm').data('user-id', null);
                $('#password').closest('.form-group').show();
                $('#password').prop('required', true);
                $('#userModal').modal('show');
            });
            
            // Edit user
            window.onEdit = function(id) {
                if (!window.currentUserIsMain) {
                    swal({
                        title: 'خطا',
                        text: 'شما اجازه ویرایش کاربران را ندارید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }
                
                $.ajax({
                    url: `/api/organization/users/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#userModalLabel').text('ویرایش کاربر');
                        $('#name').val(data.name);
                        $('#phone_number').val(data.phone_number);
                        $('#status').val(data.status ? '1' : '0');
                        $('#password').val('');
                        $('#password').closest('.form-group').show();
                        $('#password').prop('required', false);
                        $('#password').next('small').text('در صورت خالی بودن، رمز عبور تغییر نخواهد کرد');
                        $('#userForm').data('user-id', id);
                        
                        $('#userModal').modal('show');
                    },
                    error: function(xhr) {
                        if (xhr.status === 404) {
                            swal({
                                title: 'خطا',
                                text: 'کاربر مورد نظر یافت نشد',
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
                                window.location.href = '/login';
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
                if (!window.currentUserIsMain) {
                    swal({
                        title: 'خطا',
                        text: 'شما اجازه حذف کاربران را ندارید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }
                
                swal({
                    title: 'آیا مطمئن هستید؟',
                    text: 'این عمل قابل بازگشت نیست',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'بله، حذف شود',
                    cancelButtonText: 'انصراف',
                    padding: '2em'
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: `/api/organization/users/${id}`,
                            type: 'DELETE',
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                            },
                            success: function(response) {
                                swal({
                                    title: 'موفقیت',
                                    text: response.message || 'کاربر با موفقیت حذف شد',
                                    type: 'success',
                                    padding: '2em'
                                });
                                
                                // Update current user's main status and refresh table
                                updateCurrentUserIsMain();
                                window.datatableApi.refresh();
                            },
                            error: function(xhr) {
                                if (xhr.status === 404) {
                                    swal({
                                        title: 'خطا',
                                        text: 'کاربر مورد نظر یافت نشد',
                                        type: 'error',
                                        padding: '2em'
                                    });
                                } else if (xhr.status === 403) {
                                    swal({
                                        title: 'خطای دسترسی',
                                        text: xhr.responseJSON?.message || 'شما اجازه حذف این کاربر را ندارید',
                                        type: 'error',
                                        padding: '2em'
                                    });
                                } else if (xhr.status === 422) {
                                    swal({
                                        title: 'خطا',
                                        text: xhr.responseJSON?.message || 'نمی‌توانید این کاربر را حذف کنید',
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
                                        window.location.href = '/login';
                                    });
                                } else {
                                    swal({
                                        title: 'خطا',
                                        text: xhr.responseJSON?.message || 'خطا در حذف کاربر',
                                        type: 'error',
                                        padding: '2em'
                                    });
                                }
                            }
                        });
                    }
                });
            };

            // Save user (create or update)
            $('#saveUser').click(function() {
                const userId = $('#userForm').data('user-id');
                const name = $('#name').val();
                const phoneNumber = $('#phone_number').val();
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
                
                // Check if password is required (for new users)
                if (!userId && !password) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا رمز عبور را وارد کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }
                
                // Check permission for edit
                if (userId && !window.currentUserIsMain) {
                    swal({
                        title: 'خطا',
                        text: 'شما اجازه ویرایش کاربران را ندارید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const data = {
                    name: name,
                    phone_number: phoneNumber,
                    status: status
                };
                
                // Only include password if provided
                if (password) {
                    data.password = password;
                }

                const url = userId ? `/api/organization/users/${userId}` : '/api/organization/users';
                const method = userId ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        $('#userModal').modal('hide');

                        swal({
                            title: 'موفقیت',
                            text: response.message || (userId ? 'کاربر با موفقیت به‌روزرسانی شد' : 'کاربر با موفقیت ایجاد شد'),
                            type: 'success',
                            padding: '2em'
                        });

                        // Update current user's main status and refresh table
                        updateCurrentUserIsMain();
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
                                title: 'اطلاعات را به صورت صحیح وارد نمایید',
                                text: errorMessage,
                                type: 'error',
                                padding: '2em'
                            });
                        } else if (xhr.status === 403) {
                            swal({
                                title: 'خطای دسترسی',
                                text: xhr.responseJSON?.message || 'شما اجازه انجام این عمل را ندارید',
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
                                window.location.href = '/login';
                            });
                        } else {
                            swal({
                                title: 'خطا',
                                text: xhr.responseJSON?.message || 'خطا در ذخیره اطلاعات',
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
