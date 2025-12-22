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
                            <button type="button" class="btn btn-success btn-sm mr-2" id="addTransactionBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                افزودن تراکنش
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
                    <!-- Building Info -->
                    <div id="building-info" class="mb-4 p-3 bg-light rounded">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">در حال بارگذاری...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row mb-4" id="summaryCards">
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">موجودی حساب</h6>
                                    <h3 id="balance">0 ریال</h3>
                                    <small id="balanceNote">(مثبت = بدهکار، منفی = بستانکار)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title">در انتظار پرداخت</h6>
                                    <h3 id="pendingAmount">0 ریال</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6 class="card-title">کل بدهی‌ها</h6>
                                    <h3 id="totalDebits">0 ریال</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title">کل پرداخت‌ها</h6>
                                    <h3 id="totalCredits">0 ریال</h3>
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
                                    <th>نوع</th>
                                    <th>نوع تراکنش</th>
                                    <th>مبلغ</th>
                                    <th>توضیحات</th>
                                    <th>ماه/سال سرویس</th>
                                    <th>وضعیت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody id="recordsTableBody">
                                <tr>
                                    <td colspan="8" class="text-center">
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

<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن تراکنش مالی</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addTransactionForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="transaction_type">نوع تراکنش <span class="text-danger">*</span></label>
                        <select class="form-control" id="transaction_type" name="transaction_type" required>
                            <option value="">انتخاب کنید</option>
                            <option value="manual_income">درآمد دستی (پرداخت از ساختمان)</option>
                            <option value="manual_payment">پرداخت دستی (پرداخت به ساختمان)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="transaction_amount">مبلغ <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="transaction_amount" name="amount" min="0.01" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="transaction_description">توضیحات</label>
                        <textarea class="form-control" id="transaction_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="transaction_date">تاریخ تراکنش</label>
                        <input type="date" class="form-control" id="transaction_date" name="transaction_date">
                        <small class="form-text text-muted">در صورت خالی بودن، تاریخ امروز استفاده می‌شود</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">ذخیره</button>
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
                $('#balance').text(formatCurrency(Math.abs(balance)));
                if (balance > 0) {
                    $('#balanceNote').text('(بدهکار)').removeClass('text-white').addClass('text-warning');
                } else if (balance < 0) {
                    $('#balanceNote').text('(بستانکار)').removeClass('text-white').addClass('text-success');
                } else {
                    $('#balanceNote').text('(صفر)').removeClass('text-white text-warning text-success');
                }
                $('#pendingAmount').text(formatCurrency(data.pending_amount || 0));
                $('#totalDebits').text(formatCurrency(data.total_debits || 0));
                $('#totalCredits').text(formatCurrency(data.total_credits || 0));
                
                // Render records table
                renderRecordsTable(data.records || []);
            }
        },
        error: function(xhr) {
            console.error('Error loading financial dashboard:', xhr);
            $('#recordsTableBody').html('<tr><td colspan="8" class="text-center text-danger">خطا در بارگذاری داده‌ها</td></tr>');
        }
    });
}

// Render records table
function renderRecordsTable(records) {
    const tbody = $('#recordsTableBody');
    tbody.empty();
    
    if (records.length === 0) {
        tbody.html('<tr><td colspan="8" class="text-center">هیچ تراکنش مالی یافت نشد</td></tr>');
        return;
    }
    
    records.forEach(function(record) {
        const typeBadge = record.type === 'debit' 
            ? '<span class="badge badge-danger">بدهکار</span>'
            : '<span class="badge badge-success">بستانکار</span>';
        const pendingBadge = record.is_pending
            ? '<span class="badge badge-warning">در انتظار</span>'
            : '<span class="badge badge-success">پرداخت شده</span>';
        const amountClass = record.type === 'debit' ? 'text-danger' : 'text-success';
        const amountSign = record.type === 'debit' ? '-' : '+';
        
        const row = `
            <tr>
                <td>${record.transaction_date_jalali || record.created_at_jalali || '-'}</td>
                <td>${typeBadge}</td>
                <td>${record.transaction_type_text}</td>
                <td class="${amountClass}">${amountSign} ${formatCurrency(record.amount)}</td>
                <td>${record.description || '-'}</td>
                <td>${record.service_date_text || '-'}</td>
                <td>${pendingBadge}</td>
                <td>
                    ${record.is_pending ? `
                        <button type="button" class="btn btn-sm btn-primary mark-paid-btn" data-record-id="${record.id}" title="علامت‌گذاری به عنوان پرداخت شده">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </button>
                    ` : ''}
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    // Attach event handlers
    $('.mark-paid-btn').on('click', function() {
        const recordId = $(this).data('record-id');
        markRecordAsPaid(recordId);
    });
}

// Mark record as paid
function markRecordAsPaid(recordId) {
    swal({
        title: 'آیا مطمئن هستید؟',
        text: 'این تراکنش به عنوان پرداخت شده علامت‌گذاری خواهد شد',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'خیر',
        padding: '2em'
    }).then((result) => {
        if (result.value) {
            $.ajax({
                url: `/api/organization/financial-records/${recordId}/pending-status`,
                type: 'POST',
                data: { is_pending: false },
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
                        loadFinancialDashboard();
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    swal({
                        title: 'خطا',
                        text: response?.message || 'خطا در به‌روزرسانی وضعیت',
                        type: 'error',
                        padding: '2em'
                    });
                }
            });
        }
    });
}

// Handle add transaction button
$('#addTransactionBtn').on('click', function() {
    $('#addTransactionForm')[0].reset();
    $('#addTransactionModal').modal('show');
});

// Handle add transaction form submission
$('#addTransactionForm').on('submit', function(e) {
    e.preventDefault();
    
    const transactionType = $('#transaction_type').val();
    const amount = parseFloat($('#transaction_amount').val());
    const description = $('#transaction_description').val();
    const transactionDate = $('#transaction_date').val();
    
    // Determine type based on transaction_type
    const type = transactionType === 'manual_income' ? 'credit' : 'debit';
    
    const formData = {
        type: type,
        amount: amount,
        transaction_type: transactionType,
        description: description,
        transaction_date: transactionDate || null,
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
                $('#addTransactionModal').modal('hide');
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

// Load data on page load
$(document).ready(function() {
    loadBuildingInfo();
    loadFinancialDashboard();
});
</script>
@endsection
