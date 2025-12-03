@extends('organization.layout.authentication')
@section('title', 'بازنشانی رمز عبور')

@section('content')
    <div class="form-content">

        <h1 class="login-header">بازنشانی <a href=""><span class="brand-name">رمز عبور</span></a></h1>
        <form class="text-left login-form" id="reset-password-form">
            <input type="hidden" id="token" name="token" value="{{ $token }}">
            <input type="hidden" id="phone_number" name="phone_number" value="{{ $phone_number }}">
            
            <div class="form">

                <div id="password-field" class="field-wrapper input mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-lock">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" id="password" name="password" class="form-control" placeholder="رمز عبور جدید" required>
                </div>

                <div id="password_confirmation-field" class="field-wrapper input mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-lock">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="تکرار رمز عبور جدید" required>
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
                        <button type="submit" id="submit-btn" class="btn btn-primary" value="">تغییر رمز عبور</button>
                    </div>
                </div>

                <div id="success-message" class="alert alert-success mt-2" style="display: none;">
                    <span id="success-text"></span>
                </div>

                <div id="error-message" class="alert alert-danger mt-2" style="display: none;">
                    <ul id="error-list">
                    </ul>
                </div>
            </div>
        </form>
    </div>

    @section('page-scripts')
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#toggle-password').change(function() {
                var passwordField = $('#password');
                var passwordConfirmationField = $('#password_confirmation');
                
                if ($(this).is(':checked')) {
                    passwordField.attr('type', 'text');
                    passwordConfirmationField.attr('type', 'text');
                } else {
                    passwordField.attr('type', 'password');
                    passwordConfirmationField.attr('type', 'password');
                }
            });

            $('#reset-password-form').on('submit', function(e) {
                e.preventDefault();
                
                var password = $('#password').val();
                var password_confirmation = $('#password_confirmation').val();
                var token = $('#token').val();
                var phone_number = $('#phone_number').val();
                var submitBtn = $('#submit-btn');
                var originalText = submitBtn.text();

                // Disable button and show loading
                submitBtn.prop('disabled', true).text('در حال تغییر...');
                $('#error-message').hide();
                $('#success-message').hide();

                $.ajax({
                    url: '/api/organization/reset-password',
                    type: 'POST',
                    data: {
                        token: token,
                        phone_number: phone_number,
                        password: password,
                        password_confirmation: password_confirmation
                    },
                    success: function(response) {
                        $('#success-text').text(response.message || 'رمز عبور با موفقیت تغییر یافت.');
                        $('#success-message').show();
                        
                        // Redirect to login page after 3 seconds
                        setTimeout(function() {
                            window.location.href = "{{ route('organization.login') }}";
                        }, 3000);
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON;
                        $('#error-list').empty();

                        if (errors && errors.message) {
                            $('#error-list').append('<li>' + errors.message + '</li>');
                        } else if (errors && errors.errors) {
                            $.each(errors.errors, function(key, value) {
                                if (Array.isArray(value)) {
                                    $.each(value, function(i, msg) {
                                        $('#error-list').append('<li>' + msg + '</li>');
                                    });
                                } else {
                                    $('#error-list').append('<li>' + value + '</li>');
                                }
                            });
                        } else {
                            $('#error-list').append('<li>خطا در ارتباط با سرور</li>');
                        }

                        $('#error-message').show();
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });
        });
    </script>
    @endsection
@endsection

