@extends('organization.layout.master')

@section('title', 'مدیریت قراردادهای ساختمان')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">مدیریت قراردادهای ساختمان</h5>
                    <div class="widget-n">
                        <a href="{{ route('organization.buildings.view') }}" class="btn btn-primary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                            بازگشت به ساختمان‌ها
                        </a>
                    </div>
                </div>
                <div class="widget-content">
                    <div id="building-info" class="mb-4 p-3 bg-light rounded">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">در حال بارگذاری...</span>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-success" id="addContractBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            افزودن قرارداد جدید
                        </button>
                    </div>
                    <div id="contractsListContainer">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">در حال بارگذاری...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Contract Modal -->
<div class="modal fade" id="contractModal" tabindex="-1" role="dialog" aria-labelledby="contractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contractModalLabel">افزودن قرارداد جدید</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="contractForm">
                <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contract_start_date">تاریخ شروع قرارداد <span class="text-danger">*</span></label>
                                <input data-jdp-only-date="true" type="text" class="form-control" id="contract_start_date" name="contract_start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contract_end_date">تاریخ پایان قرارداد <span class="text-danger">*</span></label>
                                <input data-jdp-only-date="true" type="text" class="form-control" id="contract_end_date" name="contract_end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contract_monthly_amount">مبلغ ماهیانه قرارداد <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="contract_monthly_amount" name="monthly_amount" min="0" step="0.01" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contract_annual_amount">مبلغ سالیانه قرارداد</label>
                                <input type="number" class="form-control" id="contract_annual_amount" name="annual_amount" readonly disabled>
                                <small class="form-text text-muted">محاسبه خودکار (مبلغ ماهیانه × 12)</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="payment_method">نحوه دریافت مبلغ قرارداد <span class="text-danger">*</span></label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="">انتخاب کنید</option>
                                    <option value="1">ماهانه بعد از انجام سرویس</option>
                                    <option value="2">2ماه یکبار بعد از انجام سرویس</option>
                                    <option value="3">3 ماه یکبار بعد از انجام سرویس</option>
                                    <option value="4">3 ماه یکبار قبل از انجام سرویس</option>
                                    <option value="5">6ماه یکبار قبل از انجام سرویس</option>
                                    <option value="6">یکساله زمان عقد قرارداد</option>
                                    <option value="custom">وارد کردن</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="custom_payment_method_fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_timing">زمان دریافت <span class="text-danger">*</span></label>
                                    <select class="form-control" id="payment_timing" name="payment_timing">
                                        <option value="">انتخاب کنید</option>
                                        <option value="after_service">بعد از انجام سرویس</option>
                                        <option value="before_service">قبل از انجام سرویس</option>
                                        <option value="at_contract_time">زمان عقد قرارداد</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_frequency_type">نوع فرکانس <span class="text-danger">*</span></label>
                                    <select class="form-control" id="payment_frequency_type" name="payment_frequency_type">
                                        <option value="">انتخاب کنید</option>
                                        <option value="monthly">ماهانه</option>
                                        <option value="yearly">سالانه</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="payment_frequency_value">مقدار فرکانس <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="payment_frequency_value" name="payment_frequency_value" min="1" placeholder="مثال: 3 (برای 3 ماه یکبار)">
                                    <small class="form-text text-muted">تعداد ماه یا سال را وارد کنید</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="previous_debt">بدهی قبلی</label>
                                <input type="number" class="form-control" id="previous_debt" name="previous_debt" min="0" step="0.01" placeholder="0.00" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" id="saveContract">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('page-scripts')
<script>
const buildingId = {{ $buildingId }};
const buildingSlug = '{{ $buildingSlug }}';
let currentContractId = null;

// Load building info
function loadBuildingInfo() {
    $.ajax({
        url: `/api/organization/buildings/${buildingId}`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const building = response.data;
                $('#building-info').html(`
                    <h6>${building.name}</h6>
                    <p class="mb-1"><strong>مدیر/نماینده:</strong> ${building.manager_name}</p>
                    <p class="mb-1"><strong>شماره تماس:</strong> ${building.manager_phone}</p>
                    <p class="mb-0"><strong>آدرس:</strong> ${building.address}</p>
                `);
            }
        },
        error: function(xhr) {
            console.error('Error loading building:', xhr);
        }
    });
}

// Load contracts list
function loadContracts() {
    $.ajax({
        url: `/api/organization/buildings/${buildingId}/contracts`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                renderContracts(response.data);
            }
        },
        error: function(xhr) {
            console.error('Error loading contracts:', xhr);
            $('#contractsListContainer').html('<div class="alert alert-danger">خطا در بارگذاری قراردادها</div>');
        }
    });
}

// Render contracts list
function renderContracts(contracts) {
    const container = $('#contractsListContainer');
    container.empty();
    
    if (contracts.length === 0) {
        container.html('<div class="alert alert-info">هیچ قراردادی برای این ساختمان تعریف نشده است.</div>');
        return;
    }
    
    contracts.forEach(function(contract) {
        const statusBadge = contract.status === 'active' 
            ? '<span class="badge badge-success">فعال</span>'
            : contract.status === 'finished'
            ? '<span class="badge badge-info">تمام شده</span>'
            : '<span class="badge badge-danger">لغو شده</span>';
        
        const paymentMethodText = getPaymentMethodText(contract);
        
        const contractHtml = `
            <div class="card mb-3 contract-item" data-id="${contract.id}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">قرارداد #${contract.id}</h6>
                    ${statusBadge}
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>تاریخ شروع:</strong> ${contract.contract_start_date_jalali || '-'}</p>
                            <p><strong>تاریخ پایان:</strong> ${contract.contract_end_date_jalali || '-'}</p>
                            <p><strong>مبلغ ماهیانه:</strong> ${formatCurrency(contract.monthly_amount)}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>مبلغ سالیانه:</strong> ${formatCurrency(contract.annual_amount)}</p>
                            <p><strong>نحوه دریافت:</strong> ${paymentMethodText}</p>
                            <p><strong>بدهی قبلی:</strong> ${formatCurrency(contract.previous_debt)}</p>
                        </div>
                    </div>
                    ${contract.status === 'active' ? `
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-info finish-contract-btn mr-2" data-id="${contract.id}">
                                تمام شده
                            </button>
                            <button type="button" class="btn btn-sm btn-danger cancel-contract-btn" data-id="${contract.id}">
                                لغو قرارداد
                            </button>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        container.append(contractHtml);
    });
    
    // Attach event handlers
    $('.finish-contract-btn').on('click', function() {
        const contractId = $(this).data('id');
        updateContractStatus(contractId, 'finished');
    });
    
    $('.cancel-contract-btn').on('click', function() {
        const contractId = $(this).data('id');
        updateContractStatus(contractId, 'cancelled');
    });
}

// Get payment method text
function getPaymentMethodText(contract) {
    if (contract.payment_method) {
        const methods = {
            '1': 'ماهانه بعد از انجام سرویس',
            '2': '2ماه یکبار بعد از انجام سرویس',
            '3': '3 ماه یکبار بعد از انجام سرویس',
            '4': '3 ماه یکبار قبل از انجام سرویس',
            '5': '6ماه یکبار قبل از انجام سرویس',
            '6': 'یکساله زمان عقد قرارداد',
            'custom': 'سفارشی'
        };
        return methods[contract.payment_method] || 'نامشخص';
    }
    return 'نامشخص';
}

// Format currency
function formatCurrency(amount) {
    if (!amount) return '0 ریال';
    const formatted = new Intl.NumberFormat('fa-IR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
    return `${formatted} ریال`;
}

// Update contract status
function updateContractStatus(contractId, status) {
    const statusText = status === 'finished' ? 'تمام شده' : 'لغو شده';
    
    swal({
        title: 'تأیید',
        text: `آیا از ${statusText} کردن این قرارداد اطمینان دارید؟`,
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'انصراف',
        padding: '2em'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: `/api/organization/buildings/${buildingId}/contracts/${contractId}/status`,
                type: 'POST',
                data: { status: status },
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                },
                success: function(response) {
                    if (response.success) {
                        swal({
                            title: 'موفقیت',
                            text: response.message,
                            type: 'success',
                            padding: '2em',
                            timer: 2000
                        });
                        loadContracts();
                    }
                },
                error: function(xhr) {
                    swal({
                        title: 'خطا',
                        text: 'خطا در به‌روزرسانی وضعیت قرارداد',
                        type: 'error',
                        padding: '2em'
                    });
                }
            });
        }
    });
}

// Handle add contract button
$('#addContractBtn').on('click', function() {
    currentContractId = null;
    $('#contractForm')[0].reset();
    $('#contractModalLabel').text('افزودن قرارداد جدید');
    $('#custom_payment_method_fields').hide();
    $('#contractModal').modal('show');
});

// Initialize date pickers
jalaliDatepicker.startWatch({
    selector: '#contract_start_date',
    date: true,
    time: false,
    format: 'YYYY/MM/DD',
    separatorChars: { date: '/' },
    persianDigits: false,
    autoShow: true,
    autoHide: true,
    container: 'body',
    zIndex: 10000,
});

jalaliDatepicker.startWatch({
    selector: '#contract_end_date',
    date: true,
    time: false,
    format: 'YYYY/MM/DD',
    separatorChars: { date: '/' },
    persianDigits: false,
    autoShow: true,
    autoHide: true,
    container: 'body',
    zIndex: 10000,
});

// Calculate annual amount
$('#contract_monthly_amount').on('input', function() {
    const monthlyAmount = parseFloat($(this).val()) || 0;
    const annualAmount = monthlyAmount * 12;
    $('#contract_annual_amount').val(annualAmount.toFixed(2));
});

// Handle payment method change
$('#payment_method').on('change', function() {
    const value = $(this).val();
    if (value === 'custom') {
        $('#custom_payment_method_fields').show();
        $('#payment_timing').prop('required', true);
        $('#payment_frequency_type').prop('required', true);
        $('#payment_frequency_value').prop('required', true);
    } else {
        $('#custom_payment_method_fields').hide();
        $('#payment_timing').prop('required', false);
        $('#payment_frequency_type').prop('required', false);
        $('#payment_frequency_value').prop('required', false);
    }
});

// Handle save contract
$('#saveContract').on('click', function() {
    const formData = {
        contract_start_date: $('#contract_start_date').val(),
        contract_end_date: $('#contract_end_date').val(),
        monthly_amount: $('#contract_monthly_amount').val(),
        payment_method: $('#payment_method').val(),
        previous_debt: $('#previous_debt').val() || 0
    };
    
    if (formData.payment_method === 'custom') {
        formData.payment_timing = $('#payment_timing').val();
        formData.payment_frequency_type = $('#payment_frequency_type').val();
        formData.payment_frequency_value = $('#payment_frequency_value').val();
        
        if (!formData.payment_timing || !formData.payment_frequency_type || !formData.payment_frequency_value) {
            swal({
                title: 'خطا',
                text: 'لطفاً تمام فیلدهای روش پرداخت سفارشی را پر کنید',
                type: 'error',
                padding: '2em'
            });
            return;
        }
    }
    
    if (!formData.contract_start_date || !formData.contract_end_date || !formData.monthly_amount || !formData.payment_method) {
        swal({
            title: 'خطا',
            text: 'لطفاً تمام فیلدهای الزامی را پر کنید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    $.ajax({
        url: `/api/organization/buildings/${buildingId}/contracts`,
        type: 'POST',
        data: formData,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                $('#contractModal').modal('hide');
                swal({
                    title: 'موفقیت',
                    text: response.message,
                    type: 'success',
                    padding: '2em'
                });
                loadContracts();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'خطاهای اعتبارسنجی:\n';
                for (const field in errors) {
                    errorMessage += errors[field][0] + '\n';
                }
                swal({
                    title: 'خطا',
                    text: errorMessage,
                    type: 'error',
                    padding: '2em'
                });
            } else {
                swal({
                    title: 'خطا',
                    text: xhr.responseJSON?.message || 'خطا در ذخیره قرارداد',
                    type: 'error',
                    padding: '2em'
                });
            }
        }
    });
});

// Load data on page load
$(document).ready(function() {
    loadBuildingInfo();
    loadContracts();
});
</script>
@endsection

