@extends('admin.layout.master')

@section('title', 'مدیریت پیامک‌ها')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="">مدیریت پیامک‌ها</h5>
                    </div>
                    <div class="widget-content">
                        @include('admin.components.datatable', [
                            'title' => 'پیامک‌ها',
                            'apiUrl' => '/api/admin/sms',
                            'createButton' => true,
                            'createButtonText' => 'افزودن پیامک جدید',
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                [
                                    'field' => 'organization',
                                    'label' => 'سازمان',
                                    'formatter' => 'function(value) {
                                        if (value && value.name) {
                                            return `<span class="badge badge-info">${value.name}</span>`;
                                        }
                                        return `<span class="badge badge-secondary">سیستم</span>`;
                                    }',
                                ],
                                ['field' => 'phone_number', 'label' => 'شماره تلفن'],
                                [
                                    'field' => 'message',
                                    'label' => 'پیام',
                                    'formatter' => 'function(value) {
                                        if (value && value.length > 50) {
                                            return value.substring(0, 50) + "...";
                                        }
                                        return value || "";
                                    }',
                                ],
                                [
                                    'field' => 'cost',
                                    'label' => 'هزینه',
                                    'formatter' => 'function(value) {
                                        return value ? 
                                            `<span class="badge badge-warning">${parseFloat(value).toLocaleString("fa-IR")} تومان</span>` : 
                                            `<span class="badge badge-secondary">0 تومان</span>`;
                                    }',
                                ],
                                [
                                    'field' => 'status',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        switch(value) {
                                            case "pending":
                                                return `<span class="badge badge-warning">در انتظار</span>`;
                                            case "sent":
                                                return `<span class="badge badge-success">ارسال شده</span>`;
                                            case "failed":
                                                return `<span class="badge badge-danger">ناموفق</span>`;
                                            default:
                                                return `<span class="badge badge-secondary">نامشخص</span>`;
                                        }
                                    }',
                                ],
                                [
                                    'field' => 'created_at',
                                    'label' => 'تاریخ ایجاد',
                                    'formatter' => 'function(value) {
                                        return new Date(value).toLocaleDateString("fa-IR");
                                    }',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'actions' => '
                                // Show button
                                html += \'<button type="button" class="btn btn-sm btn-info show-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                                
                                // Edit button
                                html += \'<button type="button" class="btn btn-sm btn-primary edit-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="ویرایش">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>\';
                                html += \'</button>\';
                                
                                // Delete button
                                html += \'<button type="button" class="btn btn-sm btn-danger delete-btn bs-tooltip" data-id="\' + item.id + \'" title="حذف">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>\';
                                html += \'</button>\';
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(".show-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShow(id);
                                });
                                
                                // Handle edit button click
                                $(".edit-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onEdit(id);
                                });
                                
                                // Handle delete button click
                                $(".delete-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onDelete(id);
                                });
                            ',
                            'filters' => [
                                [
                                    'type' => 'select',
                                    'name' => 'organization_id',
                                    'label' => 'سازمان',
                                    'apiUrl' => '/api/admin/organizations',
                                    'optionValue' => 'id',
                                    'optionLabel' => 'name',
                                    'includeNull' => true,
                                    'nullLabel' => 'سیستم',
                                    'nullValue' => 'null',
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'status',
                                    'label' => 'وضعیت',
                                    'options' => [
                                        ['value' => '', 'label' => 'همه'],
                                        ['value' => 'pending', 'label' => 'در انتظار'],
                                        ['value' => 'sent', 'label' => 'ارسال شده'],
                                        ['value' => 'failed', 'label' => 'ناموفق'],
                                    ],
                                ],
                            ],
                        ])
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for adding/editing SMS -->
        <div class="modal fade" id="smsModal" tabindex="-1" role="dialog" aria-labelledby="smsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="smsModalLabel">افزودن پیامک</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="smsForm">
                            <input type="hidden" id="smsId">
                            <div class="form-group">
                                <label for="organization_id">سازمان</label>
                                <select class="form-control" id="organization_id" name="organization_id">
                                    <option value="">سیستم (بدون سازمان)</option>
                                </select>
                                <small class="form-text text-muted">اگر خالی بماند، پیامک به عنوان پیامک سیستم محسوب می‌شود</small>
                            </div>
                            <div class="form-group">
                                <label for="phone_number">شماره تلفن <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                            </div>
                            <div class="form-group">
                                <label for="message">متن پیامک <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="status">وضعیت</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="pending">در انتظار</option>
                                    <option value="sent">ارسال شده</option>
                                    <option value="failed">ناموفق</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>هزینه محاسبه شده</label>
                                <div id="calculatedCost" class="alert alert-info mb-0">
                                    <span id="costValue">0 تومان</span>
                                    <small class="d-block mt-1" id="costNote">برای پیامک سیستم هزینه 0 تومان است</small>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="saveSms">ذخیره</button>
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
                        <h5 class="modal-title" id="detailsModalLabel">جزئیات پیامک</h5>
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
                                        <th>سازمان</th>
                                        <td id="detailOrganization"></td>
                                    </tr>
                                    <tr>
                                        <th>شماره تلفن</th>
                                        <td id="detailPhoneNumber"></td>
                                    </tr>
                                    <tr>
                                        <th>متن پیامک</th>
                                        <td id="detailMessage"></td>
                                    </tr>
                                    <tr>
                                        <th>هزینه</th>
                                        <td id="detailCost"></td>
                                    </tr>
                                    <tr>
                                        <th>وضعیت</th>
                                        <td id="detailStatus"></td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ ایجاد</th>
                                        <td id="detailCreatedAt"></td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ ارسال</th>
                                        <td id="detailSentAt"></td>
                                    </tr>
                                    <tr id="detailErrorRow" style="display: none;">
                                        <th>پیام خطا</th>
                                        <td id="detailErrorMessage"></td>
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

    @section('page-scripts')
    <script>
        $(document).ready(function() {
            // Load organizations for select
            $.ajax({
                url: '/api/admin/organizations',
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                },
                success: function(response) {
                    const select = $('#organization_id');
                    response.data.forEach(function(org) {
                        select.append(`<option value="${org.id}">${org.name}</option>`);
                    });
                }
            });

            // Calculate cost when organization changes
            $('#organization_id').change(function() {
                calculateCost();
            });

            function calculateCost() {
                const organizationId = $('#organization_id').val();
                if (organizationId) {
                    $.ajax({
                        url: `/api/admin/organizations/${organizationId}`,
                        type: 'GET',
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                        },
                        success: function(response) {
                            const org = response.data;
                            const cost = org.sms_cost_per_message || 0;
                            $('#costValue').text(parseFloat(cost).toLocaleString('fa-IR') + ' تومان');
                            $('#costNote').text(`هزینه هر پیامک برای سازمان "${org.name}" برابر ${parseFloat(cost).toLocaleString('fa-IR')} تومان است`);
                        },
                        error: function() {
                            $('#costValue').text('0 تومان');
                            $('#costNote').text('خطا در دریافت اطلاعات سازمان');
                        }
                    });
                } else {
                    $('#costValue').text('0 تومان');
                    $('#costNote').text('برای پیامک سیستم هزینه 0 تومان است');
                }
            }

            // Show SMS details
            window.onShow = function(id) {
                $.ajax({
                    url: `/api/admin/sms/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#detailId').text(data.id);
                        $('#detailOrganization').html(data.organization ? 
                            `<span class="badge badge-info">${data.organization.name}</span>` : 
                            '<span class="badge badge-secondary">سیستم</span>'
                        );
                        $('#detailPhoneNumber').text(data.phone_number);
                        $('#detailMessage').text(data.message);
                        $('#detailCost').html(data.cost ? 
                            `<span class="badge badge-warning">${parseFloat(data.cost).toLocaleString('fa-IR')} تومان</span>` : 
                            '<span class="badge badge-secondary">0 تومان</span>'
                        );
                        
                        let statusHtml = '';
                        switch(data.status) {
                            case 'pending':
                                statusHtml = '<span class="badge badge-warning">در انتظار</span>';
                                break;
                            case 'sent':
                                statusHtml = '<span class="badge badge-success">ارسال شده</span>';
                                break;
                            case 'failed':
                                statusHtml = '<span class="badge badge-danger">ناموفق</span>';
                                break;
                            default:
                                statusHtml = '<span class="badge badge-secondary">نامشخص</span>';
                        }
                        $('#detailStatus').html(statusHtml);
                        
                        $('#detailCreatedAt').text(new Date(data.created_at).toLocaleDateString('fa-IR'));
                        $('#detailSentAt').text(data.sent_at ? new Date(data.sent_at).toLocaleDateString('fa-IR') : 'ارسال نشده');
                        
                        if (data.error_message) {
                            $('#detailErrorRow').show();
                            $('#detailErrorMessage').text(data.error_message);
                        } else {
                            $('#detailErrorRow').hide();
                        }

                        $('#detailsModal').modal('show');
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
                                text: 'خطا در دریافت اطلاعات',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            };

            // Create new SMS
            $('.create-new-button').click(function() {
                $('#smsModalLabel').text('افزودن پیامک');
                $('#smsForm')[0].reset();
                $('#smsId').val('');
                $('#organization_id').val('');
                calculateCost();
                $('#smsModal').modal('show');
            });

            // Edit SMS
            window.onEdit = function(id) {
                $.ajax({
                    url: `/api/admin/sms/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const sms = response.data;

                        $('#smsModalLabel').text('ویرایش پیامک');
                        $('#smsId').val(sms.id);
                        $('#organization_id').val(sms.organization_id || '');
                        $('#phone_number').val(sms.phone_number);
                        $('#message').val(sms.message);
                        $('#status').val(sms.status);
                        calculateCost();

                        $('#smsModal').modal('show');
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
                                text: 'خطا در دریافت اطلاعات',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            };

            // Save SMS (create or update)
            $('#saveSms').click(function() {
                const id = $('#smsId').val();
                const organizationId = $('#organization_id').val() || null;
                const phoneNumber = $('#phone_number').val();
                const message = $('#message').val();
                const status = $('#status').val();

                if (!phoneNumber || !message) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا شماره تلفن و متن پیامک را وارد کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const data = {
                    organization_id: organizationId,
                    phone_number: phoneNumber,
                    message: message,
                    status: status,
                };

                const url = id ? `/api/admin/sms/${id}` : '/api/admin/sms';
                const method = id ? 'PUT' : 'POST';
                const successMessage = id ? 'پیامک با موفقیت ویرایش شد' : 'پیامک با موفقیت ایجاد شد';

                $.ajax({
                    url: url,
                    type: method,
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        $('#smsModal').modal('hide');

                        swal({
                            title: 'موفقیت',
                            text: successMessage,
                            type: 'success',
                            padding: '2em'
                        });

                        window.datatableApi.refresh();
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
                            const errors = xhr.responseJSON?.errors || {};
                            let errorMessage = 'خطا در ذخیره اطلاعات';
                            if (Object.keys(errors).length > 0) {
                                errorMessage = Object.values(errors).flat().join('<br>');
                            }
                            swal({
                                title: 'خطا',
                                html: errorMessage,
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            });

            // Delete SMS
            window.onDelete = function(id) {
                swal({
                    title: 'آیا مطمئن هستید؟',
                    text: 'این عمل قابل بازگشت نیست!',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'بله، حذف کن',
                    cancelButtonText: 'انصراف',
                    padding: '2em'
                }).then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: `/api/admin/sms/${id}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                                'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                            },
                            success: function() {
                                swal({
                                    title: 'موفقیت',
                                    text: 'پیامک با موفقیت حذف شد',
                                    type: 'success',
                                    padding: '2em'
                                });

                                window.datatableApi.refresh();
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
                                        text: 'خطا در حذف پیامک',
                                        type: 'error',
                                        padding: '2em'
                                    });
                                }
                            }
                        });
                    }
                });
            };
        });
    </script>
    @endsection
@endsection

