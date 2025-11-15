@extends('admin.layout.master')

@section('title', 'حسابداری و تراکنش‌ها')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">حسابداری و تراکنش‌ها</h5>
                    </div>
                    <div class="widget-content">
                        <!-- Summary Cards -->
                        <div id="transactions-summary" class="row mb-4">
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">کل درآمد</h6>
                                        <h3 class="text-success" id="total-income">0 تومان</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">کل هزینه</h6>
                                        <h3 class="text-danger" id="total-expense">0 تومان</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">خالص</h6>
                                        <h3 class="text-primary" id="net-amount">0 تومان</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>نوع تراکنش</label>
                                            <select class="form-control" id="filter-type">
                                                <option value="">همه</option>
                                                <option value="income">دریافت</option>
                                                <option value="expense">پرداخت</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>وضعیت</label>
                                            <select class="form-control" id="filter-status">
                                                <option value="">همه</option>
                                                <option value="completed">تکمیل شده</option>
                                                <option value="pending">در انتظار</option>
                                                <option value="failed">ناموفق</option>
                                                <option value="cancelled">لغو شده</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>منبع</label>
                                            <select class="form-control" id="filter-source">
                                                <option value="">همه</option>
                                                <option value="package">پکیج</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>جستجو</label>
                                            <input type="text" class="form-control" id="filter-search" placeholder="جستجو...">
                                        </div>
                                    </div>
                                </div>
                                <button class="btn btn-primary" onclick="applyFilters()">اعمال فیلتر</button>
                                <button class="btn btn-secondary" onclick="resetFilters()">پاک کردن</button>
                            </div>
                        </div>

                        @include('admin.components.datatable', [
                            'title' => 'لیست تراکنش‌ها',
                            'apiUrl' => '/api/admin/transactions',
                            'createButton' => false,
                            'hideDefaultActions' => true,
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                [
                                    'field' => 'transaction_date',
                                    'label' => 'تاریخ',
                                    'formatter' => 'function(value) {
                                        return new Date(value).toLocaleDateString("fa-IR");
                                    }',
                                ],
                                [
                                    'field' => 'type_text',
                                    'label' => 'نوع',
                                    'formatter' => 'function(value, item) {
                                        var badgeClass = item.type === "income" ? "badge-success" : "badge-danger";
                                        return `<span class="badge ${badgeClass}">${value}</span>`;
                                    }',
                                ],
                                [
                                    'field' => 'formatted_amount',
                                    'label' => 'مبلغ',
                                ],
                                [
                                    'field' => 'payment_method',
                                    'label' => 'روش پرداخت',
                                    'formatter' => 'function(value, item) {
                                        return item.payment_method ? item.payment_method.name + (item.payment_method.is_system ? " <span class=\\"badge badge-info\\">سیستمی</span>" : "") : "-";
                                    }',
                                ],
                                [
                                    'field' => 'source_type_text',
                                    'label' => 'منبع',
                                    'formatter' => 'function(value, item) {
                                        if (item.transactionable_type && item.transactionable_type.includes("PackagePayment")) {
                                            return `<span class="badge badge-primary">پکیج</span>`;
                                        }
                                        return value || "-";
                                    }',
                                ],
                                [
                                    'field' => 'organization',
                                    'label' => 'سازمان',
                                    'formatter' => 'function(value, item) {
                                        return item.organization ? item.organization.name : "-";
                                    }',
                                ],
                                [
                                    'field' => 'status_text',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value, item) {
                                        var badgeClass = item.status_badge_class || "badge-secondary";
                                        return `<span class="badge ${badgeClass}">${value}</span>`;
                                    }',
                                ],
                                [
                                    'field' => 'description',
                                    'label' => 'توضیحات',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'actions' => '
                                // Show button
                                html += \'<button type="button" class="btn btn-sm btn-info show-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(".show-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShow(id);
                                });
                            ',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailsModalLabel">جزئیات تراکنش</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th>شناسه</th>
                                        <td id="detailId"></td>
                                    </tr>
                                    <tr>
                                        <th>نوع</th>
                                        <td id="detailType"></td>
                                    </tr>
                                    <tr>
                                        <th>مبلغ</th>
                                        <td id="detailAmount"></td>
                                    </tr>
                                    <tr>
                                        <th>روش پرداخت</th>
                                        <td id="detailPaymentMethod"></td>
                                    </tr>
                                    <tr>
                                        <th>منبع</th>
                                        <td id="detailSource"></td>
                                    </tr>
                                    <tr>
                                        <th>سازمان</th>
                                        <td id="detailOrganization"></td>
                                    </tr>
                                    <tr>
                                        <th>وضعیت</th>
                                        <td id="detailStatus"></td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ تراکنش</th>
                                        <td id="detailTransactionDate"></td>
                                    </tr>
                                    <tr>
                                        <th>توضیحات</th>
                                        <td id="detailDescription"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script>
        $(document).ready(function() {
            // Load summary on page load
            loadSummary();

            // Show transaction details
            window.onShow = function(id) {
                $.ajax({
                    url: `/api/admin/transactions/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#detailId').text(data.id);
                        $('#detailType').html(`<span class="badge ${data.type === 'income' ? 'badge-success' : 'badge-danger'}">${data.type_text}</span>`);
                        $('#detailAmount').text(data.formatted_amount);
                        $('#detailPaymentMethod').text(data.payment_method ? data.payment_method.name + (data.payment_method.is_system ? ' (سیستمی)' : '') : '-');
                        $('#detailSource').html(`<span class="badge badge-primary">${data.source_type_text}</span>`);
                        $('#detailOrganization').text(data.organization ? data.organization.name : '-');
                        $('#detailStatus').html(`<span class="badge ${data.status_badge_class}">${data.status_text}</span>`);
                        $('#detailTransactionDate').text(new Date(data.transaction_date).toLocaleDateString('fa-IR'));
                        $('#detailDescription').text(data.description || '-');

                        $('#detailsModal').modal('show');
                    },
                    error: function(xhr) {
                        swal({
                            title: 'خطا',
                            text: 'خطا در دریافت اطلاعات تراکنش',
                            type: 'error',
                            padding: '2em'
                        });
                    }
                });
            };

            // Load summary
            function loadSummary() {
                $.ajax({
                    url: '/api/admin/transactions?per_page=1',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        if (response.summary) {
                            $('#total-income').text(response.summary.formatted_total_income);
                            $('#total-expense').text(response.summary.formatted_total_expense);
                            $('#net-amount').text(response.summary.formatted_net_amount);
                        }
                    },
                    error: function() {
                        console.error('Error loading summary');
                    }
                });
            }

            // Apply filters
            window.applyFilters = function() {
                const type = $('#filter-type').val();
                const status = $('#filter-status').val();
                const source = $('#filter-source').val();
                const search = $('#filter-search').val();

                let url = '/api/admin/transactions?';
                const params = [];
                if (type) params.push('type=' + type);
                if (status) params.push('status=' + status);
                if (source) params.push('source_type=' + source);
                if (search) params.push('search=' + encodeURIComponent(search));

                url += params.join('&');
                
                if (window.datatableApi) {
                    window.datatableApi.setApiUrl(url);
                    window.datatableApi.refresh();
                }
                loadSummary();
            };

            // Reset filters
            window.resetFilters = function() {
                $('#filter-type').val('');
                $('#filter-status').val('');
                $('#filter-source').val('');
                $('#filter-search').val('');
                
                if (window.datatableApi) {
                    window.datatableApi.setApiUrl('/api/admin/transactions');
                    window.datatableApi.refresh();
                }
                loadSummary();
            };
        });
    </script>
@endsection

