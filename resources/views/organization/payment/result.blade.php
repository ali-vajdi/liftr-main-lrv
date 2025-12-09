@extends('organization.layout.master')

@section('title', 'نتیجه پرداخت')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">
                            <i class="fa fa-{{ $success ? 'check-circle' : 'times-circle' }}"></i> نتیجه پرداخت
                        </h5>
                    </div>
                    <div class="widget-content">
                        <div class="text-center p-5">
                            @if($success)
                                <div class="mb-4">
                                    <i class="fa fa-check-circle text-success" style="font-size: 80px;"></i>
                                </div>
                                <h3 class="text-success mb-3">پرداخت با موفقیت انجام شد</h3>
                                <p class="text-muted mb-4">{{ $message ?? 'پرداخت شما با موفقیت انجام و تایید شد.' }}</p>
                            @else
                                <div class="mb-4">
                                    <i class="fa fa-times-circle text-danger" style="font-size: 80px;"></i>
                                </div>
                                <h3 class="text-danger mb-3">پرداخت ناموفق</h3>
                                <p class="text-muted mb-4">{{ $message ?? 'پرداخت شما انجام نشد یا لغو شده است.' }}</p>
                            @endif

                            @if(isset($trackingCode) && $trackingCode)
                                <div class="card bg-light mb-4" style="max-width: 500px; margin: 0 auto;">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">کد پیگیری تراکنش:</h6>
                                        <div class="input-group">
                                            <input type="text" class="form-control text-center font-weight-bold" 
                                                   id="tracking-code" value="{{ $trackingCode }}" readonly 
                                                   style="font-size: 18px; letter-spacing: 2px;">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" onclick="copyTrackingCode()">
                                                    <i class="fa fa-copy"></i> کپی
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4">
                                <button type="button" class="btn btn-primary btn-lg" onclick="continueToPage()">
                                    <i class="fa fa-arrow-left"></i> بازگشت به {{ $redirectTo === 'dashboard' ? 'داشبورد' : 'صفحه پرداخت' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
<script>
    function copyTrackingCode() {
        var input = document.getElementById('tracking-code');
        input.select();
        input.setSelectionRange(0, 99999); // For mobile devices
        document.execCommand('copy');
        
        // Show feedback
        var btn = event.target.closest('button');
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa fa-check"></i> کپی شد';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        
        setTimeout(function() {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    }

    function continueToPage() {
        @if($redirectTo === 'dashboard')
            window.location.href = '{{ route("organization.dashboard") }}';
        @else
            window.location.href = '{{ route("organization.payment.page") }}';
        @endif
    }
</script>
@endsection

