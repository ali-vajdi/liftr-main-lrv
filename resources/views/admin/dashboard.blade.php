@extends('admin.layout.master')
@section('title', 'داشبورد')

@section('content')
<div class="row layout-top-spacing">
    <!-- Faraz SMS Balance Card -->
    <div class="col-xl-4 col-lg-6 col-sm-12 layout-spacing">
        <div class="widget widget-chart-one" style="border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">
            <div class="widget-content" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px 25px; color: white; min-height: 200px;">
                <div class="text-center">
                    <div style="margin-bottom: 20px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.9;">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    <h6 style="color: rgba(255,255,255,0.9); margin-bottom: 15px; font-size: 14px; font-weight: 500; letter-spacing: 0.5px;">موجودی پیامک فراز</h6>
                    <h2 style="color: white; font-size: 42px; font-weight: 700; margin-bottom: 10px; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <span id="sms-balance">0</span>
                    </h2>
                    <p style="color: rgba(255,255,255,0.8); margin-bottom: 20px; font-size: 14px;">ریال</p>
                    <div class="row mt-4" style="border-top: 1px solid rgba(255,255,255,0.25); padding-top: 20px; margin-top: 20px;">
                        <div class="col-6 text-center">
                            <div style="font-size: 18px; font-weight: 600; margin-bottom: 5px; color: rgba(255,255,255,0.9);"><span id="yesterday-credit">0</span></div>
                            <div style="font-size: 11px; opacity: 0.85; font-weight: 500;">موجودی دیروز</div>
                        </div>
                        <div class="col-6 text-center">
                            <div style="font-size: 12px; font-weight: 600; margin-bottom: 5px; color: rgba(255,255,255,0.9);"><span id="last-update">-</span></div>
                            <div style="font-size: 11px; opacity: 0.85; font-weight: 500;">آخرین بروزرسانی</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Organizations Statistics Card -->
    <div class="col-xl-4 col-lg-6 col-sm-12 layout-spacing">
        <div class="widget widget-chart-one" style="border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="widget-heading" style="border-bottom: 1px solid #e0e6ed; padding: 15px 20px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); margin: -1px -1px 0 -1px;">
                <h5 class="mb-0" style="color: white; font-weight: 600;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    شرکت‌ها
                </h5>
            </div>
            <div class="widget-content" style="padding: 25px 20px;">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div style="padding: 15px; background: linear-gradient(135deg, #f093fb15 0%, #f5576c15 100%); border-radius: 12px; border: 1px solid #e0e6ed;">
                            <h3 class="mb-2" style="color: #00e396; font-weight: 700; font-size: 32px;"><span id="organizations-active">0</span></h3>
                            <p class="text-muted mb-0" style="font-size: 13px; font-weight: 500;">فعال</p>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div style="padding: 15px; background: linear-gradient(135deg, #fa709a15 0%, #fee14015 100%); border-radius: 12px; border: 1px solid #e0e6ed;">
                            <h3 class="mb-2" style="color: #e74c3c; font-weight: 700; font-size: 32px;"><span id="organizations-inactive">0</span></h3>
                            <p class="text-muted mb-0" style="font-size: 13px; font-weight: 500;">غیرفعال</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div style="padding: 15px; background: #f1f2f3; border-radius: 12px; border: 1px solid #e0e6ed;">
                            <h4 class="mb-1" style="color: #3b3f5c; font-weight: 700; font-size: 24px;"><span id="organizations-total">0</span></h4>
                            <p class="text-muted mb-0" style="font-size: 12px; font-weight: 500;">کل شرکت‌ها</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Users Statistics Card -->
    <div class="col-xl-4 col-lg-6 col-sm-12 layout-spacing">
        <div class="widget widget-chart-one" style="border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="widget-heading" style="border-bottom: 1px solid #e0e6ed; padding: 15px 20px; background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); margin: -1px -1px 0 -1px;">
                <h5 class="mb-0" style="font-weight: 600; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px; color: white;">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    کاربران اپلیکیشن
                </h5>
            </div>
            <div class="widget-content" style="padding: 30px 25px;">
                <div class="text-center">
                    <h3 class="mb-2" style="color: #4361ee; font-weight: 700; font-size: 48px;"><span id="application-users-total">0</span></h3>
                    <p class="text-muted mb-0" style="font-size: 16px; font-weight: 500;">تعداد کاربران اپلیکیشن</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Total SMS Credit Card -->
    <div class="col-xl-4 col-lg-6 col-sm-12 layout-spacing">
        <div class="widget widget-chart-one" style="border: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="widget-heading" style="border-bottom: 1px solid #e0e6ed; padding: 15px 20px; background: linear-gradient(135deg, #00d4aa 0%, #00a085 100%); margin: -1px -1px 0 -1px;">
                <h5 class="mb-0" style="font-weight: 600; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px; color: white;">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    اعتبار پیامکی
                </h5>
            </div>
            <div class="widget-content" style="padding: 30px 25px;">
                <div class="text-center">
                    <h3 class="mb-2" style="color: #00d4aa; font-weight: 700; font-size: 48px;"><span id="total-sms-credit">0</span></h3>
                    <p class="text-muted mb-0" style="font-size: 16px; font-weight: 500;">اعتبار پیامکی باقی مانده کل شرکت‌ها</p>
                    <p class="text-muted mt-2 mb-0" style="font-size: 14px; font-weight: 500;">تومان</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script>
    $(document).ready(function() {
        // Fetch dashboard data
        $.ajax({
            url: '/api/admin/dashboard-data',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
            },
            success: function(response) {
                const data = response.data;
                
                // Display Faraz SMS Balance
                if (data.faraz_sms_balance) {
                    const balance = data.faraz_sms_balance;
                    $('#sms-balance').text(formatNumber(balance.credit));
                    $('#yesterday-credit').text(formatNumber(balance.yesterday_credit));
                    
                    if (balance.updated_at) {
                        $('#last-update').text(formatDate(balance.updated_at));
                    } else {
                        $('#last-update').text('-');
                    }
                } else {
                    $('#sms-balance').text('0');
                    $('#yesterday-credit').text('0');
                    $('#last-update').text('-');
                }
                
                // Display Statistics
                if (data.statistics) {
                    // Organizations statistics
                    if (data.statistics.organizations) {
                        $('#organizations-total').text(formatNumber(data.statistics.organizations.total));
                        $('#organizations-active').text(formatNumber(data.statistics.organizations.active || 0));
                        $('#organizations-inactive').text(formatNumber(data.statistics.organizations.inactive || 0));
                    }
                    
                    // Application users
                    if (data.statistics.application_users) {
                        $('#application-users-total').text(formatNumber(data.statistics.application_users.total));
                    }
                    
                    // Total SMS credit
                    if (data.statistics.total_sms_credit !== undefined) {
                        $('#total-sms-credit').text(formatNumber(data.statistics.total_sms_credit));
                    }
                }
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
                        text: 'خطا در دریافت اطلاعات داشبورد',
                        type: 'error',
                        padding: '2em'
                    });
                }
            }
        });
        
        // Helper function to format numbers
        function formatNumber(num) {
            if (num === null || num === undefined || num === '-') return '0';
            return parseFloat(num).toLocaleString('fa-IR');
        }
        
        // Helper function to format date
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('fa-IR', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    });
</script>
@endsection