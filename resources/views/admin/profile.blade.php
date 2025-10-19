@extends('admin.layout.master')
@section('title', 'پروفایل')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="">ویرایش پروفایل</h5>
                    </div>
                    <div class="widget-content">
                        <form id="profileForm" class="mt-4">
                            <div class="form-group">
                                <label for="fullName">نام کامل</label>
                                <input type="text" class="form-control" id="fullName" name="full_name" required>
                            </div>
                            <div class="form-group">
                                <label for="username">نام کاربری</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="currentPassword">رمز عبور فعلی</label>
                                <input type="password" class="form-control" id="currentPassword" name="current_password">
                                <small class="form-text text-muted">برای تغییر رمز عبور، رمز عبور فعلی را وارد کنید.</small>
                            </div>
                            <div class="form-group">
                                <label for="newPassword">رمز عبور جدید</label>
                                <input type="password" class="form-control" id="newPassword" name="new_password">
                            </div>
                            <div class="form-group">
                                <label for="newPasswordConfirmation">تکرار رمز عبور جدید</label>
                                <input type="password" class="form-control" id="newPasswordConfirmation" name="new_password_confirmation">
                            </div>
                            <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script>
        $(document).ready(function() {
            // Load current profile data
            $.ajax({
                url: '/api/admin/profile',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                },
                success: function(response) {
                    const data = response.data;
                    $('#fullName').val(data.full_name);
                    $('#username').val(data.username);
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

            // Handle form submission
            $('#profileForm').on('submit', function(e) {
                e.preventDefault();

                const fullName = $('#fullName').val();
                const username = $('#username').val();
                const currentPassword = $('#currentPassword').val();
                const newPassword = $('#newPassword').val();
                const newPasswordConfirmation = $('#newPasswordConfirmation').val();

                if (!fullName || !username) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا تمام فیلدهای الزامی را پر کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                if (newPassword && !currentPassword) {
                    swal({
                        title: 'خطا',
                        text: 'برای تغییر رمز عبور، رمز عبور فعلی را وارد کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                if (newPassword !== newPasswordConfirmation) {
                    swal({
                        title: 'خطا',
                        text: 'رمز عبور جدید و تکرار آن مطابقت ندارند',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const data = {
                    full_name: fullName,
                    username: username
                };

                if (currentPassword) {
                    data.current_password = currentPassword;
                }

                if (newPassword) {
                    data.new_password = newPassword;
                    data.new_password_confirmation = newPasswordConfirmation;
                }

                $.ajax({
                    url: '/api/admin/profile',
                    type: 'PUT',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        swal({
                            title: 'موفقیت',
                            text: 'اطلاعات پروفایل با موفقیت بروزرسانی شد',
                            type: 'success',
                            padding: '2em'
                        }).then(function() {
                            // Clear password fields
                            $('#currentPassword').val('');
                            $('#newPassword').val('');
                            $('#newPasswordConfirmation').val('');
                        });
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
                                text: 'خطا در بروزرسانی اطلاعات',
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