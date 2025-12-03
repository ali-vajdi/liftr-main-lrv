@extends('organization.layout.authentication')
@section('title', 'فراموشی رمز عبور')

@section('content')
    <div class="form-content">

        <h1 class="login-header">فراموشی <a href=""><span class="brand-name">رمز عبور</span></a></h1>
        <form class="text-left login-form" id="forgot-password-form">
            <div class="form">

                <div id="phone_number-field" class="field-wrapper input mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-phone">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <input id="phone_number" name="phone_number" type="text" class="form-control" placeholder="شماره تلفن" required>
                </div>

                <div class="d-sm-flex justify-content-between">
                    <div class="field-wrapper">
                        <a href="{{ route('organization.login') }}" class="forgot-pass-link">بازگشت به صفحه ورود</a>
                    </div>
                    <div class="field-wrapper">
                        <button type="submit" id="submit-btn" class="btn btn-primary" value="">ارسال لینک بازنشانی</button>
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
            $('#forgot-password-form').on('submit', function(e) {
                e.preventDefault();
                
                var phone_number = $('#phone_number').val();
                var submitBtn = $('#submit-btn');
                var originalText = submitBtn.text();

                // Disable button and show loading
                submitBtn.prop('disabled', true).text('در حال ارسال...');
                $('#error-message').hide();
                $('#success-message').hide();

                $.ajax({
                    url: '/api/organization/forgot-password',
                    type: 'POST',
                    data: {
                        phone_number: phone_number
                    },
                    success: function(response) {
                        $('#success-text').text(response.message || 'لینک بازنشانی رمز عبور به شماره تلفن شما ارسال شد.');
                        $('#success-message').show();
                        $('#forgot-password-form')[0].reset();
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

