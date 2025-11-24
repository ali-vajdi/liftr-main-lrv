@extends('organization.layout.authentication')
@section('title', 'صفحه ورود')

@section('content')
    <div class="form-content">

        <h1 class="">ورود به <a href=""><span class="brand-name">پنل شرکتی لیفتر</span></a></h1>
        <form class="text-left" method="POST" action="{{ route('organization.login') }}">
            @csrf
        <div class="form">

            <div id="phone_number-field" class="field-wrapper input mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-phone">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                </svg>
                <input id="phone_number" name="phone_number" type="text" class="form-control" placeholder="شماره تلفن" required>
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
                var phone_number = $('#phone_number').val();
                var password = $('#password').val();

                $.ajax({
                    url: '/api/organization/login',
                    type: 'POST',
                    data: {
                        phone_number: phone_number,
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
