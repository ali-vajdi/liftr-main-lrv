@extends('organization.layout.master')

@section('title', 'تراکنش‌ها')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">تراکنش‌ها</h5>
                    </div>
                    <div class="widget-content">
                        @include('organization.components.datatable', [
                            'title' => 'لیست تراکنش‌ها',
                            'apiUrl' => '/api/organization/transactions',
                            'createButton' => false,
                            'hideDefaultActions' => true,
                            'filters' => [
                                [
                                    'name' => 'type',
                                    'label' => 'نوع تراکنش',
                                    'type' => 'select',
                                    'placeholder' => 'همه',
                                    'options' => [
                                        ['value' => 'expense', 'label' => 'پرداخت'],
                                    ],
                                ],
                                [
                                    'name' => 'status',
                                    'label' => 'وضعیت',
                                    'type' => 'select',
                                    'placeholder' => 'همه',
                                    'options' => [
                                        ['value' => 'completed', 'label' => 'تکمیل شده'],
                                        ['value' => 'pending', 'label' => 'در انتظار'],
                                        ['value' => 'failed', 'label' => 'ناموفق'],
                                        ['value' => 'cancelled', 'label' => 'لغو شده'],
                                    ],
                                ],
                                [
                                    'name' => 'source_type',
                                    'label' => 'منبع',
                                    'type' => 'select',
                                    'placeholder' => 'همه',
                                    'options' => [
                                        ['value' => 'package', 'label' => 'اشتراک'],
                                    ],
                                ],
                            ],
                            'columns' => [
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
                                            return `<span class="badge badge-primary">اشتراک</span>`;
                                        }
                                        return value || "-";
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
                                        <th>وضعیت</th>
                                        <td id="detailStatus"></td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ تراکنش</th>
                                        <td id="detailTransactionDate"></td>
                                    </tr>
                                    <tr>
                                        <th>شماره مرجع</th>
                                        <td id="detailReferenceNumber"></td>
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
            // Show transaction details
            window.onShow = function(id) {
                const token = localStorage.getItem('organization_token');
                if (!token) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا مجددا وارد سیستم شوید',
                        type: 'error',
                        padding: '2em'
                    }).then(function() {
                        window.location.href = '/organization/login';
                    });
                    return;
                }

                $.ajax({
                    url: `/api/organization/transactions/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#detailType').html(`<span class="badge ${data.type === 'income' ? 'badge-success' : 'badge-danger'}">${data.type_text}</span>`);
                        $('#detailAmount').text(data.formatted_amount);
                        $('#detailPaymentMethod').text(data.payment_method ? data.payment_method.name + (data.payment_method.is_system ? ' (سیستمی)' : '') : '-');
                        $('#detailSource').html(`<span class="badge badge-primary">${data.source_type_text}</span>`);
                        $('#detailStatus').html(`<span class="badge ${data.status_badge_class}">${data.status_text}</span>`);
                        $('#detailTransactionDate').text(new Date(data.transaction_date).toLocaleDateString('fa-IR'));
                        $('#detailReferenceNumber').text(data.reference_number || '-');
                        $('#detailDescription').text(data.description || '-');

                        $('#detailsModal').modal('show');
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            swal({
                                title: 'خطا',
                                text: 'لطفا مجددا وارد سیستم شوید',
                                type: 'error',
                                padding: '2em'
                            }).then(function() {
                                window.location.href = '/organization/login';
                            });
                        } else {
                            swal({
                                title: 'خطا',
                                text: 'خطا در دریافت اطلاعات تراکنش',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            };
        });
    </script>
@endsection

