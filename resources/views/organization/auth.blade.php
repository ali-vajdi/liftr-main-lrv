@extends('organization.layout.authentication')
@section('title', 'صفحه ورود')

@section('content')
    <div class="form-content">

        <h1 class="">ورود به <a href=""><span class="brand-name">پنل سازمانی لیفتر</span></a></h1>
        <form class="text-left" method="POST" action="{{ route('organization.login') }}">
            @csrf
        <div class="form">

            <div id="username-field" class="field-wrapper input mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-user">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <input id="username" name="username" type="text" class="form-control" placeholder="نام کاربری">
            </div>

            <div id="password-field" class="field-wrapper input mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-lock">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <input type="password" id="password" name="password" class="form-control" placeholder="رمز عبور" required>
            </div>
            <div class="d-sm-flex justify-content-between">
                <div class="field-wrapper toggle-pass">
                    <p class="d-inline-block">نمایش رمز عبور</p>
                    <label class="switch s-primary">
                        <input type="checkbox" id="toggle-password" class="d-none">
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="field-wrapper">
                    <button type="button" id="login-btn" class="btn btn-primary" value="">ورود</button>
                </div>
            </div>

            <div id="error-message" class="alert alert-danger mt-2" style="display: none;">
                <ul id="error-list">
                </ul>
            </div>

            <div class="field-wrapper text-center keep-logged-in">
                <div class="n-chk new-checkbox checkbox-outline-primary">
                    <label class="new-control new-checkbox checkbox-outline-primary">
                        <input type="checkbox" class="new-control-input">
                        <span class="new-control-indicator"></span>مرا به یاد بسپار
                    </label>
                </div>
            </div>
        </div>
    </div>

    @section('page-scripts')
    <script>
        $(document).ready(function() {
            $('#login-btn').click(function() {
                var username = $('#username').val();
                var password = $('#password').val();

                $.ajax({
                    url: '/api/organization/login',
                    type: 'POST',
                    data: {
                        username: username,
                        password: password
                    },
                    success: function(response) {
                        // Store token in localStorage
                        localStorage.setItem('organization_token', response.token);
                        localStorage.setItem('organization_user', JSON.stringify(response.user));

                        // Redirect to dashboard
                        window.location.href = "{{ route('organization.dashboard') }}";
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON;
                        $('#error-list').empty();

                        if (errors && errors.message) {
                            $('#error-list').append('<li>' + errors.message + '</li>');
                        } else if (errors && errors.errors) {
                            $.each(errors.errors, function(key, value) {
                                $('#error-list').append('<li>' + value + '</li>');
                            });
                        } else {
                            $('#error-list').append('<li>خطا در ارتباط با سرور</li>');
                        }

                        $('#error-message').show();
                    }
                });
            });
        });
    </script>
    @endsection
@endsection
