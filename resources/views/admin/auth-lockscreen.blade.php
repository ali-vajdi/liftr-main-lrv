@extends('admin.layout.authentication')
@section('title', 'قفل صفحه')

@section('content')
    <div class="form-content">
        <div class="d-flex user-meta">
            <div class="">
                <p class="user-name">نام کاربر</p>
            </div>
        </div>

        <form class="text-left" >
            @csrf
        <div class="form">
            <div id="password-field" class="field-wrapper input mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-lock">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <input type="password" id="password" name="password" class="form-control" placeholder="رمز عبور" required>
                <input type="hidden" id="username" name="username">
            </div>
            <div class="d-sm-flex justify-content-between gap-2">
                <div class="field-wrapper toggle-pass mr-4">
                    <p class="d-inline-block">نمایش رمز عبور</p>
                    <label class="switch s-primary">
                        <input type="checkbox" id="toggle-password" class="d-none">
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="field-wrapper">
                    <button type="button" id="unlock-btn" class="btn btn-primary" value="">باز کردن قفل</button>
                </div>
            </div>

            <div id="error-message" class="alert alert-danger mt-2" style="display: none;">
                <ul id="error-list">
                </ul>
            </div>
        </div>

        <div class="terms-conditions">
            <p>یا می‌توانید با کاربر دیگری <a href="javascript:void(0)" id="logout-link">وارد شوید</a></p>
        </div>
    </div>

    @section('page-scripts')
        <script>
            $(document).ready(function () {
                // Set user name from localStorage
                var user = JSON.parse(localStorage.getItem('admin_user'));
                if (user) {
                    $('.user-name').text(user.full_name);
                    $('#username').val(user.username);
                } else {
                    // If no user in localStorage, redirect to login
                    window.location.href = "{{ route('admin.login') }}";
                }

                $('#unlock-btn').click(function () {
                    var password = $('#password').val();
                    var username = $('#username').val();

                    $.ajax({
                        url: '/api/admin/unlock-screen',
                        type: 'POST',
                        data: {
                            password: password,
                            username: username
                        },
                        success: function (response) {
                            // Store the new token
                            localStorage.setItem('admin_token', response.token);
                            localStorage.setItem('admin_user', JSON.stringify(response.user));

                            // Remove lock state from localStorage
                            localStorage.removeItem('screen_locked');
                            window.location.href = "{{ route('admin.dashboard') }}";
                        },
                        error: function (xhr) {
                            var errors = xhr.responseJSON;
                            $('#error-list').empty();

                            if (errors && errors.message) {
                                $('#error-list').append('<li>' + errors.message + '</li>');
                            } else if (errors && errors.errors) {
                                $.each(errors.errors, function (key, value) {
                                    $('#error-list').append('<li>' + value + '</li>');
                                });
                            } else {
                                $('#error-list').append('<li>خطا در ارتباط با سرور</li>');
                            }

                            $('#error-message').show();
                        }
                    });
                });

                $('#logout-link').click(function (e) {
                    e.preventDefault();

                    // Try to logout via API if token exists
                    var token = localStorage.getItem('admin_token');
                    if (token) {
                        $.ajax({
                            url: '/api/admin/logout',
                            type: 'POST',
                            headers: {
                                'Authorization': 'Bearer ' + token
                            },
                            complete: function () {
                                // Whether successful or not, clear storage and redirect
                                clearStorageAndRedirect();
                            }
                        });
                    } else {
                        // If no token, just clear storage and redirect
                        clearStorageAndRedirect();
                    }

                    function clearStorageAndRedirect() {
                        // Clear all relevant localStorage items
                        localStorage.removeItem('admin_token');
                        localStorage.removeItem('admin_user');
                        localStorage.removeItem('screen_locked');

                        // Redirect to login page
                        window.location.href = "{{ route('admin.login') }}";
                    }
                });
            });
        </script>
    @endsection
@endsection