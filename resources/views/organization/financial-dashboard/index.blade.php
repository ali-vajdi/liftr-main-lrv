@extends('organization.layout.master')

@section('title', 'داشبورد مالی')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">داشبورد مالی</h5>
                        <div>
                            <button type="button" class="btn btn-info btn-sm mr-2" id="exportPdfBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                خروجی PDF
                            </button>
                            <button type="button" class="btn btn-success btn-sm mr-2" id="addPaymentBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                فرم پرداختی های ساختمان
                            </button>
                            <button type="button" class="btn btn-danger btn-sm mr-2" id="addExpenseBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                فرم هزینه قطعات و خرابی ها
                            </button>
                            <a href="{{ route('organization.buildings.view') }}" class="btn btn-primary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                                بازگشت به ساختمان‌ها
                            </a>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <!-- Building and Contract Info -->
                    <div class="mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <!-- Building Info -->
                                <div id="building-info">
                                    <div class="text-center py-3">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">در حال بارگذاری...</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contract Info -->
                                <div id="contract-info" style="display: none;">
                                    <hr class="my-3">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4" id="summaryCards">
                        <div class="col-md-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6 class="card-title">مجموع بدهکاری</h6>
                                    <h3 id="totalDebits">0 ریال</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">مجموع بستانکاری</h6>
                                    <h3 id="totalCredits">0 ریال</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">مانده نهایی</h6>
                                    <h3 id="balance">0 ریال</h3>
                                    <small id="balanceNote">(مثبت = بدهکار، منفی = بستانکار)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Records Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="financialRecordsTable">
                            <thead>
                                <tr>
                                    <th>تاریخ</th>
                                    <th>شرح</th>
                                    <th>بدهکاری</th>
                                    <th>بستانکاری</th>
                                    <th>مانده</th>
                                    <th>توضیحات بیشتر</th>
                                </tr>
                            </thead>
                            <tbody id="recordsTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">در حال بارگذاری...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal (Credit) -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">فرم پرداختی های ساختمان</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addPaymentForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="payment_date">تاریخ <span class="text-danger">*</span></label>
                        <input data-jdp-only-date="true" type="text" class="form-control" id="payment_date" name="transaction_date" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_description">شرح <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="payment_description" name="description" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_amount">مبلغ پرداختی <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="payment_amount" name="amount" min="0.01" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_extra_descriptions">توضیحات اضافه</label>
                        <textarea class="form-control" id="payment_extra_descriptions" name="extra_descriptions" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-success">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Expense Modal (Debit) -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">فرم هزینه قطعات و خرابی ها</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addExpenseForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="expense_date">تاریخ <span class="text-danger">*</span></label>
                        <input data-jdp-only-date="true" type="text" class="form-control" id="expense_date" name="transaction_date" required>
                    </div>
                    <div class="form-group">
                        <label for="expense_description">شرح <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="expense_description" name="description" required>
                    </div>
                    <div class="form-group">
                        <label for="expense_amount">مبلغ هزینه <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="expense_amount" name="amount" min="0.01" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="expense_extra_descriptions">توضیحات اضافه</label>
                        <textarea class="form-control" id="expense_extra_descriptions" name="extra_descriptions" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-danger">ذخیره</button>
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
                    <div class="mb-0">
                        <h6 class="mb-3 text-primary">${building.name}</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">مدیر/نماینده</small>
                                <span class="font-weight-medium">${building.manager_name || '-'}</span>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block">شماره تماس</small>
                                <span class="font-weight-medium">${building.manager_phone || '-'}</span>
                            </div>
                            <div class="col-12 mb-0">
                                <small class="text-muted d-block">آدرس</small>
                                <span class="font-weight-medium">${building.address || '-'}</span>
                            </div>
                        </div>
                    </div>
                `);
            }
        },
        error: function(xhr) {
            console.error('Error loading building:', xhr);
        }
    });
}

// Load financial dashboard data
function loadFinancialDashboard() {
    $.ajax({
        url: `/api/organization/buildings/${buildingId}/financial-dashboard`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                
                // Update summary cards
                const balance = data.balance || 0;
                // Show balance with + or - sign (positive = بستانکار, negative = بدهکار)
                const balanceSign = balance > 0 ? '+' : (balance < 0 ? '-' : '');
                const balanceDisplay = balance !== 0 ? `${balanceSign}${formatCurrency(Math.abs(balance))}` : formatCurrency(0);
                $('#balance').text(balanceDisplay);
                $('#balanceNote').text('').removeClass('text-white text-success text-warning');
                $('#pendingAmount').text(formatCurrency(data.pending_amount || 0));
                $('#totalDebits').text(formatCurrency(data.total_debits || 0));
                $('#totalCredits').text(formatCurrency(data.total_credits || 0));
                
                // Render contract info
                renderContractInfo(data.contract);
                
                // Render records table
                renderRecordsTable(data.records || []);
            }
        },
        error: function(xhr) {
            console.error('Error loading financial dashboard:', xhr);
            $('#recordsTableBody').html('<tr><td colspan="6" class="text-center text-danger">خطا در بارگذاری داده‌ها</td></tr>');
        }
    });
}

// Render contract info
function renderContractInfo(contract) {
    const contractInfoDiv = $('#contract-info');
    
    if (!contract) {
        contractInfoDiv.hide();
        return;
    }
    
    const paymentMethodTexts = {
        '1': 'بعد از هر سرویس (ماهانه)',
        '2': 'بعد از هر 2 سرویس',
        '3': 'بعد از هر 3 سرویس',
        '4': 'قبل از هر 3 سرویس',
        '5': 'قبل از هر 6 سرویس',
        '6': 'قبل از هر 12 سرویس (سالانه)',
        'custom': 'سفارشی'
    };
    
    const statusBadge = contract.is_active 
        ? '<span class="badge badge-success">فعال</span>' 
        : (contract.status === 'finished' 
            ? '<span class="badge badge-warning">تمام شده</span>' 
            : '<span class="badge badge-danger">لغو شده</span>');
    
    const contractTitle = contract.contract_number 
        ? `قرارداد شماره ${contract.contract_number}` 
        : 'قرارداد';
    
    const paymentMethodText = paymentMethodTexts[contract.payment_method] || 'نامشخص';
    
    let html = `
        <div class="mb-0">
            <h6 class="mb-3 text-primary">${contractTitle} ${statusBadge}</h6>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block">تاریخ شروع</small>
                    <span class="font-weight-medium">${contract.contract_start_date_jalali || '-'}</span>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block">تاریخ پایان</small>
                    <span class="font-weight-medium">${contract.contract_end_date_jalali || '-'}</span>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block">مبلغ ماهیانه</small>
                    <span class="font-weight-medium">${formatCurrency(contract.monthly_amount || 0)}</span>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block">مبلغ سالیانه</small>
                    <span class="font-weight-medium">${formatCurrency(contract.annual_amount || 0)}</span>
                </div>
                <div class="col-md-6 mb-2">
                    <small class="text-muted d-block">بدهی قبلی</small>
                    <span class="font-weight-medium">${formatCurrency(contract.previous_debt || 0)}</span>
                </div>
                <div class="col-md-6 mb-0">
                    <small class="text-muted d-block">روش پرداخت</small>
                    <span class="font-weight-medium">${paymentMethodText}</span>
                </div>
            </div>
        </div>
    `;
    
    contractInfoDiv.html(html).show();
}

// Render records table
function renderRecordsTable(records) {
    const tbody = $('#recordsTableBody');
    tbody.empty();
    
    if (records.length === 0) {
        tbody.html('<tr><td colspan="6" class="text-center">هیچ تراکنش مالی یافت نشد</td></tr>');
        return;
    }
    
    // Records come from API ordered by ID descending
    records.forEach(function(record) {
        const debitAmount = record.debit !== null ? formatCurrency(record.debit) : '-';
        const creditAmount = record.credit !== null ? formatCurrency(record.credit) : '-';
        const balanceAmount = record.balance || 0;
        // Show balance with + or - sign (positive = بستانکار, negative = بدهکار)
        const balanceSign = balanceAmount > 0 ? '+' : (balanceAmount < 0 ? '-' : '');
        const balanceDisplay = balanceAmount !== 0 ? `${balanceSign}${formatCurrency(Math.abs(balanceAmount))}` : formatCurrency(0);
        
        const row = `
            <tr>
                <td>${record.transaction_date_jalali || '-'}</td>
                <td>${record.description || '-'}</td>
                <td>${debitAmount}</td>
                <td>${creditAmount}</td>
                <td>${balanceDisplay}</td>
                <td>${record.extra_descriptions || '-'}</td>
            </tr>
        `;
        tbody.append(row);
    });
}

// Initialize Jalali DatePicker for payment form
jalaliDatepicker.startWatch({
    selector: '#payment_date',
    date: true,
    time: false,
    hasSecond: false,
    showSelectTimeBtnAlways: false,
    format: 'YYYY/MM/DD',
    separatorChars: {
        date: '/',
        between: ' ',
    },
    persianDigits: false,
    autoShow: true,
    autoHide: true,
    hideAfterChange: true,
    showTodayBtn: true,
    showEmptyBtn: true,
    showCloseBtn: true,
    useDropDownYears: true,
    container: 'body',
    zIndex: 10000,
});

// Initialize Jalali DatePicker for expense form
jalaliDatepicker.startWatch({
    selector: '#expense_date',
    date: true,
    time: false,
    hasSecond: false,
    showSelectTimeBtnAlways: false,
    format: 'YYYY/MM/DD',
    separatorChars: {
        date: '/',
        between: ' ',
    },
    persianDigits: false,
    autoShow: true,
    autoHide: true,
    hideAfterChange: true,
    showTodayBtn: true,
    showEmptyBtn: true,
    showCloseBtn: true,
    useDropDownYears: true,
    container: 'body',
    zIndex: 10000,
});

// Handle add payment button (Credit)
$('#addPaymentBtn').on('click', function() {
    $('#addPaymentForm')[0].reset();
    $('#payment_date').val('');
    $('#addPaymentModal').modal('show');
});

// Handle add expense button (Debit)
$('#addExpenseBtn').on('click', function() {
    $('#addExpenseForm')[0].reset();
    $('#expense_date').val('');
    $('#addExpenseModal').modal('show');
});

// Handle add payment form submission (Credit)
$('#addPaymentForm').on('submit', function(e) {
    e.preventDefault();
    
    const transactionDate = $('#payment_date').val();
    const description = $('#payment_description').val();
    const amount = parseFloat($('#payment_amount').val());
    const extraDescriptions = $('#payment_extra_descriptions').val();
    
    if (!transactionDate || !description || !amount) {
        swal({
            title: 'خطا',
            text: 'لطفاً فیلدهای الزامی را پر کنید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    // Send Jalali date string directly - backend will convert it
    const formData = {
        type: 'credit', // Credit for building payments
        amount: amount,
        transaction_type: 'manual_income',
        description: description,
        extra_descriptions: extraDescriptions,
        transaction_date: transactionDate, // Send Jalali date string (Y/m/d format)
    };
    
    $.ajax({
        url: `/api/organization/buildings/${buildingId}/financial-transactions`,
        type: 'POST',
        data: formData,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                $('#addPaymentModal').modal('hide');
                swal({
                    title: 'موفقیت',
                    text: response.message,
                    type: 'success',
                    padding: '2em',
                    timer: 2000
                });
                loadFinancialDashboard();
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            let errorMessage = 'خطا در ثبت تراکنش';
            if (response?.errors) {
                const errors = Object.values(response.errors).flat();
                errorMessage = errors.join('\n');
            } else if (response?.message) {
                errorMessage = response.message;
            }
            swal({
                title: 'خطا',
                text: errorMessage,
                type: 'error',
                padding: '2em'
            });
        }
    });
});

// Handle add expense form submission (Debit)
$('#addExpenseForm').on('submit', function(e) {
    e.preventDefault();
    
    const transactionDate = $('#expense_date').val();
    const description = $('#expense_description').val();
    const amount = parseFloat($('#expense_amount').val());
    const extraDescriptions = $('#expense_extra_descriptions').val();
    
    if (!transactionDate || !description || !amount) {
        swal({
            title: 'خطا',
            text: 'لطفاً فیلدهای الزامی را پر کنید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    // Send Jalali date string directly - backend will convert it
    const formData = {
        type: 'debit', // Debit for expenses
        amount: amount,
        transaction_type: 'manual_payment',
        description: description,
        extra_descriptions: extraDescriptions,
        transaction_date: transactionDate, // Send Jalali date string (Y/m/d format)
    };
    
    $.ajax({
        url: `/api/organization/buildings/${buildingId}/financial-transactions`,
        type: 'POST',
        data: formData,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                $('#addExpenseModal').modal('hide');
                swal({
                    title: 'موفقیت',
                    text: response.message,
                    type: 'success',
                    padding: '2em',
                    timer: 2000
                });
                loadFinancialDashboard();
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            let errorMessage = 'خطا در ثبت تراکنش';
            if (response?.errors) {
                const errors = Object.values(response.errors).flat();
                errorMessage = errors.join('\n');
            } else if (response?.message) {
                errorMessage = response.message;
            }
            swal({
                title: 'خطا',
                text: errorMessage,
                type: 'error',
                padding: '2em'
            });
        }
    });
});

// Format currency
function formatCurrency(amount) {
    if (!amount) return '0 ریال';
    const formatted = new Intl.NumberFormat('fa-IR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
    return `${formatted} ریال`;
}

// Handle PDF export button
$('#exportPdfBtn').on('click', function() {
    const token = localStorage.getItem('organization_token');
    if (!token) {
        swal({
            title: 'خطا',
            text: 'لطفاً ابتدا وارد سیستم شوید',
            type: 'error',
            padding: '2em'
        });
        return;
    }

    // Use building slug for the API call (route supports both ID and slug, but slug is preferred)
    const buildingIdentifier = buildingSlug || buildingId;
    const url = `/api/organization/buildings/${buildingIdentifier}/financial-dashboard/export-pdf`;
    
    // Use fetch to download PDF with authentication
    fetch(url, {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/pdf'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('خطا در دریافت فایل PDF');
        }
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `صورتحساب_مالی_${buildingSlug}_${new Date().toLocaleDateString('fa-IR')}.pdf`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    })
    .catch(error => {
        console.error('Error:', error);
        swal({
            title: 'خطا',
            text: 'خطا در دریافت فایل PDF. لطفاً دوباره تلاش کنید.',
            type: 'error',
            padding: '2em'
        });
    });
});

// Load data on page load
$(document).ready(function() {
    loadBuildingInfo();
    loadFinancialDashboard();
});
</script>
@endsection
