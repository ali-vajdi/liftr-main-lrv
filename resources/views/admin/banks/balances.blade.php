@extends('admin.layout.master')

@section('title', 'موجودی بانک‌ها')

@section('content')
<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1">موجودی بانک‌ها و صندوق</h4>
                        <p class="text-dark mb-0">مدیریت و مشاهده موجودی تمام حساب‌های بانکی و صندوق</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshData()">
                            <i class="fas fa-sync-alt"></i> بروزرسانی
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overall Statistics -->
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-dark small mb-2">موجودی فعلی کل</div>
                <div class="h4 text-primary mb-0" id="total-current-balance">۰</div>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-dark small mb-2">ورودی تایید شده</div>
                <div class="h4 text-success mb-0" id="total-incoming-confirmed">۰</div>
                <div class="small text-dark mt-1">امروز: <span id="today-incoming">۰</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-dark small mb-2">خروجی تایید شده</div>
                <div class="h4 text-danger mb-0" id="total-outgoing-confirmed">۰</div>
                <div class="small text-dark mt-1">امروز: <span id="today-outgoing">۰</span></div>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-dark small mb-2">در انتظار تایید</div>
                <div class="h4 text-warning mb-0" id="total-pending">۰</div>
                <div class="small text-dark mt-1" id="pending-count">۰ تراکنش</div>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-dark small mb-2">موجودی پیش‌بینی</div>
                <div class="h4 text-info mb-0" id="total-projected-balance">۰</div>
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-6 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="text-dark small mb-2">تعداد حساب‌ها</div>
                <div class="h4 text-dark mb-0" id="total-banks">۰</div>
                <div class="small text-dark mt-1" id="banks-breakdown">بانک: ۰ | صندوق: ۰</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-filter ml-2"></i>فیلترها</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label small text-dark">نوع حساب</label>
                        <select class="form-control form-control-sm" id="account-type-filter">
                            <option value="">همه</option>
                            <option value="bank">بانک</option>
                            <option value="cash">صندوق</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label small text-dark">وضعیت موجودی</label>
                        <select class="form-control form-control-sm" id="balance-status-filter">
                            <option value="">همه</option>
                            <option value="positive">مثبت</option>
                            <option value="negative">منفی</option>
                            <option value="zero">صفر</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label small text-dark">جستجو</label>
                        <input type="text" class="form-control form-control-sm" id="search-filter" placeholder="نام بانک، شعبه یا شماره حساب...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label small text-dark">مرتب‌سازی</label>
                        <select class="form-control form-control-sm" id="sort-filter">
                            <option value="name">بر اساس نام</option>
                            <option value="balance">بر اساس موجودی</option>
                            <option value="pending">بر اساس در انتظار</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bank/Cash Cards View -->
<div class="row" id="bank-balances-container">
    <!-- Cards will be loaded here -->
</div>

<!-- Bank Details Modal -->
<div class="modal fade" id="bankDetailsModal" tabindex="-1" role="dialog" aria-labelledby="bankDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="bankDetailsModalLabel">
                    <i class="fas fa-university ml-2"></i>جزئیات تراکنش‌ها
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Bank/Pose Header Statistics -->
                <div class="row mb-4" id="bank-pose-header" style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e9ecef;">
                    <!-- Filled by JS -->
                </div>
                <!-- Bank Summary -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card bg-light text-dark shadow-sm border-0">
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3 mb-2">
                                        <div class="h4 mb-1 text-primary" id="modal-current-balance">۰</div>
                                        <div class="small text-dark">موجودی فعلی</div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="h4 mb-1 text-success" id="modal-incoming-confirmed">۰</div>
                                        <div class="small text-dark">ورودی تایید شده</div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="h4 mb-1 text-danger" id="modal-outgoing-confirmed">۰</div>
                                        <div class="small text-dark">خروجی تایید شده</div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <div class="h4 mb-1 text-warning" id="modal-pending">۰</div>
                                        <div class="small text-dark">در انتظار</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Filters -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label small text-dark">نوع تراکنش</label>
                                        <select class="form-control form-control-sm" id="transaction-type-filter">
                                            <option value="">همه</option>
                                            <option value="incoming">ورودی</option>
                                            <option value="outgoing">خروجی</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label small text-dark">روش پرداخت</label>
                                        <select class="form-control form-control-sm" id="modal-payment-filter">
                                            <option value="">همه</option>
                                            <option value="sheba">شبا</option>
                                            <option value="pose">پوز</option>
                                            <option value="transfer">انتقال</option>
                                            <option value="cash">نقدی</option>
                                            <option value="combined">ترکیبی</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label small text-dark">وضعیت</label>
                                        <select class="form-control form-control-sm" id="transaction-status-filter">
                                            <option value="">همه</option>
                                            <option value="confirmed">تایید شده</option>
                                            <option value="pending">در انتظار</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <label class="form-label small text-dark">بانک</label>
                                        <select class="form-control form-control-sm" id="transaction-bank-filter">
                                            <option value="">همه</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label small text-dark">جستجو</label>
                                        <input type="text" class="form-control form-control-sm" id="transaction-search" placeholder="توضیحات، کاربر، شماره شبا یا نام پوز...">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">لیست تراکنش‌ها</h6>
                                <div class="d-flex gap-2">
                                    <span class="badge badge-info" id="transactions-count">۰ تراکنش</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm mb-0" id="transactions-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th>تاریخ</th>
                                                <th>نوع</th>
                                                <th>روش پرداخت</th>
                                                <th>جزئیات پرداخت</th>
                                                <th>مبلغ</th>
                                                <th>وضعیت</th>
                                                <th>کاربر</th>
                                                <th>توضیحات</th>
                                            </tr>
                                        </thead>
                                        <tbody id="transactions-tbody">
                                            <!-- Transactions will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times ml-1"></i>بستن
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script>
$(document).ready(function() {
    if (!localStorage.getItem('admin_token')) {
        window.location.href = '/admin/login';
        return;
    }
    
    $.ajaxSetup({
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('admin_token'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    });

    // Initialize
    loadBankBalances();
    setupEventListeners();

    function setupEventListeners() {
        // Filters
        $('#account-type-filter, #balance-status-filter, #sort-filter').change(applyFilters);
        $('#search-filter').on('input', applyFilters);

        // Modal filters
        $('#transaction-type-filter, #modal-payment-filter, #transaction-status-filter, #transaction-bank-filter').change(filterTransactions);
        $('#transaction-search').on('input', filterTransactions);
    }

    function loadBankBalances() {
        showLoading();
        $.ajax({
            url: '/api/admin/bank-balances',
            method: 'GET',
            success: function(response) {
                hideLoading();
                updateOverallTotals(response.overall_totals, response.banks);
                renderBankCards(response.banks);
                storeOriginalData(response.banks);
            },
            error: function(xhr) {
                hideLoading();
                if (xhr.status === 401) {
                    window.location.href = '/admin/login';
                } else {
                    showError('خطا در بارگذاری موجودی بانک‌ها');
                }
            }
        });
    }

    function updateOverallTotals(totals, banks) {
        $('#total-current-balance').text(formatCurrency(totals.total_current_balance));
        $('#total-incoming-confirmed').text(formatCurrency(totals.total_incoming_confirmed));
        $('#total-outgoing-confirmed').text(formatCurrency(totals.total_outgoing_confirmed));
        $('#total-pending').text(formatCurrency(totals.total_pending));
        $('#total-projected-balance').text(formatCurrency(totals.total_projected_balance));
        $('#total-banks').text(banks.length);
        
        // Additional stats
        const bankCount = banks.filter(b => !b.is_cash).length;
        const cashCount = banks.filter(b => b.is_cash).length;
        $('#banks-breakdown').text(`بانک: ${bankCount} | صندوق: ${cashCount}`);
        
        // Today's transactions
        $('#today-incoming').text(formatCurrency(totals.today_incoming || 0));
        $('#today-outgoing').text(formatCurrency(totals.today_outgoing || 0));
        $('#pending-count').text(`${totals.pending_count || 0} تراکنش`);
    }

    function renderBankCards(banks) {
        const container = $('#bank-balances-container');
        container.empty();
        
        if (banks.length === 0) {
            container.html('<div class="col-12 text-center py-5"><i class="fas fa-inbox fa-3x text-dark mb-3"></i><p class="text-dark">هیچ حساب‌بانکی یافت نشد</p></div>');
            return;
        }

        banks.forEach(function(bank) {
            const card = createBankCard(bank);
            container.append(card);
        });
    }

    function createBankCard(bank) {
        const isCash = bank.is_cash;
        const iconClass = isCash ? 'fas fa-money-bill-wave' : 'fas fa-university';
        const cardClass = bank.current_balance >= 0 ? 'border-success' : 'border-danger';
        const balanceClass = bank.current_balance >= 0 ? 'text-success' : 'text-danger';
        
        const lastTransactionDate = bank.last_transaction_date ? formatDate(bank.last_transaction_date) : 'بدون تراکنش';

        return `
            <div class="col-12 mb-3 bank-card" data-bank-id="${bank.id}" data-account-type="${isCash ? 'cash' : 'bank'}">
                <div class="card ${cardClass} shadow-sm bg-white">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <!-- Bank Info -->
                            <div class="col-lg-3 col-md-4 mb-3 mb-md-0">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <i class="${iconClass} fa-2x ${isCash ? 'text-success' : 'text-primary'}"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1 text-dark">${bank.name}</h5>
                                        <div class="small text-dark">${bank.branch_name || 'صندوق'} - ${bank.account_number}</div>
                                        <div class="small text-dark">${bank.moderator || 'بدون مسئول'}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Balance Info -->
                            <div class="col-lg-6 col-md-5 mb-3 mb-md-0">
                                <div class="row text-center">
                                    <div class="col-3">
                                        <div class="h5 ${balanceClass} mb-1">${formatCurrency(bank.current_balance)}</div>
                                        <div class="small text-dark">موجودی فعلی</div>
                                    </div>
                                    <div class="col-3">
                                        <div class="h5 text-warning mb-1">${formatCurrency(bank.total_pending)}</div>
                                        <div class="small text-dark">در انتظار</div>
                                        <div class="small text-dark">${bank.pending_count || 0} تراکنش</div>
                                    </div>
                                    <div class="col-3">
                                        <div class="h5 text-info mb-1">${formatCurrency(bank.projected_balance)}</div>
                                        <div class="small text-dark">پیش‌بینی</div>
                                    </div>
                                    <div class="col-3">
                                        <div class="h6 text-dark mb-1">${bank.total_transactions || 0}</div>
                                        <div class="small text-dark">کل تراکنش</div>
                                        <div class="small text-dark">${lastTransactionDate}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Breakdown Info -->
                            <div class="col-lg-2 col-md-2 mb-3 mb-md-0">
                                ${!isCash ? `
                                    <div class="small text-dark mb-2">جزئیات:</div>
                                    <div class="small">
                                        <div class="mb-1">
                                            <span class="text-success">شبا:</span> ${formatCurrency(bank.breakdowns.sheba.current_balance)}
                                        </div>
                                        <div class="mb-1">
                                            <span class="text-info">پوز:</span> ${formatCurrency(bank.breakdowns.pose.current_balance)}
                                        </div>
                                        <div>
                                            <span class="text-warning">انتقال:</span> ${formatCurrency(bank.breakdowns.transfer.current_balance)}
                                        </div>
                                    </div>
                                ` : `
                                    <div class="small text-dark mb-2">نقدی:</div>
                                    <div class="small text-success">${formatCurrency(bank.current_balance)}</div>
                                `}
                            </div>
                            
                            <!-- Actions -->
                            <div class="col-lg-1 col-md-1 text-center">
                                <div class="btn-group-vertical btn-group-sm">
                                    <button class="btn btn-outline-primary mb-1" onclick="showBankDetails('${bank.id}')" title="جزئیات">
                                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Today's Activity -->
                        <div class="row mt-3 pt-3 border-top">
                            <div class="col-12">
                                <div class="bg-light rounded p-2">
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="small text-success">امروز ورودی: ${formatCurrency(bank.today_incoming || 0)}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-danger">امروز خروجی: ${formatCurrency(bank.today_outgoing || 0)}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-info">در انتظار: ${bank.pending_count || 0} تراکنش</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-dark">آخرین تراکنش: ${lastTransactionDate}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    function applyFilters() {
        const accountType = $('#account-type-filter').val();
        const balanceStatus = $('#balance-status-filter').val();
        const search = $('#search-filter').val().toLowerCase();
        const sortBy = $('#sort-filter').val();

        let filteredBanks = originalBanksData.filter(function(bank) {
            let show = true;

            // Account type filter
            if (accountType && bank.is_cash !== (accountType === 'cash')) {
                show = false;
            }

            // Search filter
            if (search && !bank.name.toLowerCase().includes(search) && 
                !bank.branch_name?.toLowerCase().includes(search) && 
                !bank.account_number.includes(search)) {
                show = false;
            }

            // Balance status filter
            if (balanceStatus) {
                if (balanceStatus === 'positive' && bank.current_balance <= 0) show = false;
                if (balanceStatus === 'negative' && bank.current_balance >= 0) show = false;
                if (balanceStatus === 'zero' && bank.current_balance !== 0) show = false;
            }

            return show;
        });

        // Sort banks
        if (sortBy === 'name') {
            filteredBanks.sort((a, b) => a.name.localeCompare(b.name));
        } else if (sortBy === 'balance') {
            filteredBanks.sort((a, b) => b.current_balance - a.current_balance);
        } else if (sortBy === 'pending') {
            filteredBanks.sort((a, b) => b.total_pending - a.total_pending);
        }

        renderBankCards(filteredBanks);
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('fa-IR').format(amount) + ' ریال';
    }

    function showLoading() {
        $('#bank-balances-container').html('<div class="col-12 text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-dark"></i><p class="mt-2 text-dark">در حال بارگذاری...</p></div>');
    }

    function hideLoading() {
        // Loading will be replaced by actual content
    }

    function showError(message) {
        alert(message);
    }

    // Global functions
    window.refreshData = function() {
        loadBankBalances();
    };

    window.showBankDetails = function(bankId) {
        $.ajax({
            url: `/api/admin/bank-balances/${bankId}`,
            method: 'GET',
            success: function(response) {
                showBankDetailsModal(response);
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    window.location.href = '/admin/login';
                } else {
                    showError('خطا در بارگذاری جزئیات بانک');
                }
            }
        });
    };

    // Store original data for filtering
    let originalBanksData = [];

    function storeOriginalData(banks) {
        originalBanksData = banks;
    }

    function showBankDetailsModal(data) {
        const bank = data.bank;
        const balance = data.balance_summary;
        const availableBanks = data.available_banks;
        
        // Fill header with bank/pose details and stats
        let headerHtml = '';
        if (bank.is_cash) {
            headerHtml = `
                <div class="col-12">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-money-bill-wave fa-2x text-success ml-2"></i>
                        <h4 class="mb-0">صندوق نقدی</h4>
                        <span class="ml-3 text-dark">کد: ${bank.account_number}</span>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3"><span class="text-dark small">ورودی تایید شده:</span> <span class="text-success">${formatCurrency(balance.incoming_confirmed)}</span></div>
                        <div class="col-md-3"><span class="text-dark small">خروجی تایید شده:</span> <span class="text-danger">${formatCurrency(balance.outgoing_confirmed)}</span></div>
                        <div class="col-md-3"><span class="text-dark small">در انتظار:</span> <span class="text-warning">${formatCurrency(balance.total_pending)}</span></div>
                        <div class="col-md-3"><span class="text-dark small">پیش‌بینی:</span> <span class="text-info">${formatCurrency(balance.projected_balance)}</span></div>
                    </div>
                </div>
            `;
        } else {
            headerHtml = `
                <div class="col-12">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-university fa-2x text-primary ml-2"></i>
                        <h4 class="mb-0">${bank.name}</h4>
                        <span class="ml-3 text-dark">شعبه: ${bank.branch_name || '-'}</span>
                        <span class="ml-3 text-dark">شماره حساب: ${bank.account_number}</span>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3"><span class="text-dark small">ورودی تایید شده:</span> <span class="text-success">${formatCurrency(balance.incoming_confirmed)}</span></div>
                        <div class="col-md-3"><span class="text-dark small">خروجی تایید شده:</span> <span class="text-danger">${formatCurrency(balance.outgoing_confirmed)}</span></div>
                        <div class="col-md-3"><span class="text-dark small">در انتظار:</span> <span class="text-warning">${formatCurrency(balance.total_pending)}</span></div>
                        <div class="col-md-3"><span class="text-dark small">پیش‌بینی:</span> <span class="text-info">${formatCurrency(balance.projected_balance)}</span></div>
                    </div>
                </div>
            `;
        }
        $('#bank-pose-header').html(headerHtml);
        
        $('#modal-current-balance').text(formatCurrency(balance.current_balance));
        $('#modal-incoming-confirmed').text(formatCurrency(balance.incoming_confirmed));
        $('#modal-outgoing-confirmed').text(formatCurrency(balance.outgoing_confirmed));
        $('#modal-pending').text(formatCurrency(balance.total_pending));
        
        // Populate bank filter
        const bankFilter = $('#transaction-bank-filter');
        bankFilter.empty().append('<option value="">همه</option>');
        availableBanks.forEach(function(bank) {
            bankFilter.append(`<option value="${bank.id}">${bank.name} - ${bank.branch_name}</option>`);
        });
        
        renderTransactions(data.transactions);
        $('#bankDetailsModal').modal('show');
    }

    function renderTransactions(transactions) {
        const tbody = $('#transactions-tbody');
        tbody.empty();
        
        if (transactions.length === 0) {
            tbody.html('<tr><td colspan="9" class="text-center py-4"><i class="fas fa-inbox text-dark mr-2"></i>هیچ تراکنشی یافت نشد</td></tr>');
            $('#transactions-count').text('۰ تراکنش');
            return;
        }

        $('#transactions-count').text(`${transactions.length} تراکنش`);
        
        transactions.forEach(function(transaction) {
            const row = createTransactionRow(transaction);
            tbody.append(row);
        });
    }

    function createTransactionRow(transaction) {
        const typeClass = transaction.type === 'incoming' ? 'text-success' : 'text-danger';
        const typeIcon = transaction.type === 'incoming' ? 'fas fa-arrow-down' : 'fas fa-arrow-up';
        const statusClass = transaction.status === 'confirmed' ? 'badge-success' : 'badge-warning';
        const paymentTypeText = getPaymentTypeText(transaction.payment_type);
        
        // Get payment details with Sheba/Pose information
        let paymentDetails = '-';
        if (transaction.payment_type === 'sheba' && transaction.sheba_number) {
            // Find the ShebaNumber record to get the title
            const shebaTitle = transaction.sheba_title || 'شبا';
            paymentDetails = `${shebaTitle}: ${transaction.sheba_number}`;
        } else if (transaction.payment_type === 'pose' && transaction.bank_pose) {
            paymentDetails = `پوز: ${transaction.bank_pose.name}`;
        } else if (transaction.payment_type === 'transfer' && transaction.bank) {
            paymentDetails = `انتقال: ${transaction.bank.name}`;
        } else if (transaction.payment_type === 'cash') {
            paymentDetails = 'نقدی';
        } else if (transaction.payment_type === 'combined' && transaction.combined_transactions) {
            paymentDetails = `ترکیبی: ${transaction.combined_transactions.length} تراکنش`;
        }
        
        let description = transaction.description || '-';

        return `
            <tr class="transaction-row" data-type="${transaction.type}" data-payment-type="${transaction.payment_type}" data-status="${transaction.status}" data-bank-id="${transaction.bank_id || ''}">
                <td>
                    <div class="small">${formatDate(transaction.created_at)}</div>
                    <div class="small text-dark">${formatTime(transaction.created_at)}</div>
                </td>
                <td>
                    <span class="badge ${transaction.type === 'incoming' ? 'badge-success' : 'badge-danger'}">
                        <i class="${typeIcon} ml-1"></i>${transaction.type === 'incoming' ? 'ورودی' : 'خروجی'}
                    </span>
                </td>
                <td>
                    <span class="badge badge-info">${paymentTypeText}</span>
                </td>
                <td>
                    <div class="small">${paymentDetails}</div>
                </td>
                <td class="${typeClass} font-weight-bold">${formatCurrency(transaction.amount)}</td>
                <td>
                    <span class="badge ${statusClass}">
                        ${transaction.status === 'confirmed' ? 'تایید شده' : 'در انتظار'}
                    </span>
                </td>
                <td>
                    <div class="small">${transaction.user ? transaction.user.name : '-'}</div>
                    <div class="small text-dark">${transaction.user ? transaction.user.phone_number : '-'}</div>
                </td>
                <td>
                    <div class="small">${description}</div>
                </td>
            </tr>
        `;
    }

    function filterTransactions() {
        const type = $('#transaction-type-filter').val();
        const paymentType = $('#modal-payment-filter').val();
        const status = $('#transaction-status-filter').val();
        const bankId = $('#transaction-bank-filter').val();
        const search = $('#transaction-search').val().toLowerCase();

        $('.transaction-row').each(function() {
            const $row = $(this);
            let show = true;

            if (type && $row.data('type') !== type) show = false;
            if (paymentType && $row.data('payment-type') !== paymentType) show = false;
            if (status && $row.data('status') !== status) show = false;
            if (bankId && $row.data('bank-id') !== bankId) show = false;
            if (search && !$row.text().toLowerCase().includes(search)) show = false;

            $row.toggle(show);
        });
    }

    function getPaymentTypeText(paymentType) {
        const types = {
            'cash': 'نقدی',
            'sheba': 'شبا',
            'pose': 'پوز',
            'transfer': 'انتقال',
            'combined': 'ترکیبی'
        };
        return types[paymentType] || paymentType;
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('fa-IR');
    }

    function formatTime(dateString) {
        return new Date(dateString).toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
    }

});
</script>
@endsection