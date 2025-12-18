@extends('admin.layout.master')

@section('title', 'مدیریت پیام‌ها')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">مدیریت پیام‌ها</h5>
                            <button type="button" class="btn btn-primary btn-sm create-new-button">
                                <i class="fa fa-plus"></i> ارسال پیام جدید
                            </button>
                        </div>
                    </div>
                    <div class="widget-content">
                        @include('admin.components.datatable', [
                            'title' => 'پیام‌های ارسال شده',
                            'apiUrl' => '/api/admin/messages',
                            'createButton' => false,
                            'hideDefaultActions' => true,
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                [
                                    'field' => 'receiver',
                                    'label' => 'گیرنده',
                                    'formatter' => 'function(value) {
                                        if (!value) {
                                            return `<span class="badge badge-info">همه سازمان‌ها</span>`;
                                        }
                                        return value.name || "-";
                                    }',
                                ],
                                ['field' => 'subject', 'label' => 'عنوان'],
                                [
                                    'field' => 'message',
                                    'label' => 'متن پیام',
                                    'formatter' => 'function(value) {
                                        if (!value) return "-";
                                        return value.length > 50 ? value.substring(0, 50) + "..." : value;
                                    }',
                                ],
                                [
                                    'field' => 'created_at_jalali',
                                    'label' => 'تاریخ ارسال',
                                    'formatter' => 'function(value) { return value || "-"; }',
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

        <!-- Modal for creating message -->
        <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="messageModalLabel">ارسال پیام جدید</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="messageForm">
                            <div class="form-group">
                                <label for="organization_id">ارسال به <span class="text-danger">*</span></label>
                                <select class="form-control" id="organization_id" name="organization_id">
                                    <option value="">همه سازمان‌ها</option>
                                </select>
                                <small class="form-text text-muted">برای ارسال به همه سازمان‌ها، گزینه "همه سازمان‌ها" را انتخاب کنید</small>
                            </div>
                            <div class="form-group">
                                <label for="subject">عنوان <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="message">متن پیام <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="6" required maxlength="5000"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="saveMessage">ارسال پیام</button>
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
                        <h5 class="modal-title" id="detailsModalLabel">جزئیات پیام</h5>
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
                                        <th>گیرنده</th>
                                        <td id="detailReceiver"></td>
                                    </tr>
                                    <tr>
                                        <th>عنوان</th>
                                        <td id="detailSubject"></td>
                                    </tr>
                                    <tr>
                                        <th>متن پیام</th>
                                        <td id="detailMessage"></td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ ارسال</th>
                                        <td id="detailCreatedAt"></td>
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
            let organizations = [];

            // Load organizations
            function loadOrganizations() {
                const token = localStorage.getItem('admin_token');
                if (!token) {
                    $('#organization_id').html('<option value="">خطا در احراز هویت</option>');
                    return;
                }

                $.ajax({
                    url: '/api/admin/organizations?per_page=1000',
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    success: function(response) {
                        if (response.data && Array.isArray(response.data)) {
                            organizations = response.data;
                            const select = $('#organization_id');
                            
                            select.html('<option value="">همه سازمان‌ها</option>');
                            
                            if (organizations.length > 0) {
                                organizations.forEach(function(org) {
                                    select.append(`<option value="${org.id}">${org.name}</option>`);
                                });
                            }
                        } else {
                            $('#organization_id').html('<option value="">خطا در بارگذاری</option>');
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'خطا در بارگذاری سازمان‌ها';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        $('#organization_id').html(`<option value="">${errorMessage}</option>`);
                    }
                });
            }

            loadOrganizations();

            // Create new message
            $('.create-new-button').click(function() {
                $('#messageModalLabel').text('ارسال پیام جدید');
                $('#messageForm')[0].reset();
                $('#messageModal').modal('show');
            });

            // Save message
            $('#saveMessage').click(function() {
                const organizationId = $('#organization_id').val();
                const subject = $('#subject').val();
                const message = $('#message').val();

                if (!subject) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا عنوان پیام را وارد کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                if (!message) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا متن پیام را وارد کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const token = localStorage.getItem('admin_token');
                if (!token) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا مجددا وارد سیستم شوید',
                        type: 'error',
                        padding: '2em'
                    }).then(function() {
                        window.location.href = '/admin/login';
                    });
                    return;
                }

                $.ajax({
                    url: '/api/admin/messages',
                    type: 'POST',
                    data: {
                        organization_id: organizationId || null,
                        subject: subject,
                        message: message
                    },
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#messageModal').modal('hide');
                            $('#messageForm')[0].reset();

                            swal({
                                title: 'موفقیت',
                                text: response.message,
                                type: 'success',
                                padding: '2em'
                            });

                            window.datatableApi.refresh();
                        } else {
                            swal({
                                title: 'خطا',
                                text: response.message || 'خطا در ارسال پیام',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            let errorMessage = '';

                            for (const key in errors) {
                                errorMessage += errors[key].join('\n') + '\n';
                            }

                            swal({
                                title: 'اطلاعات را به صورت صحیح وارد نمایید',
                                text: errorMessage,
                                type: 'error',
                                padding: '2em'
                            });
                        } else if (xhr.status === 401) {
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
                                text: xhr.responseJSON?.message || 'خطا در ارسال پیام',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            });

            // Show message details
            window.onShow = function(id) {
                const token = localStorage.getItem('admin_token');
                if (!token) {
                    swal({
                        title: 'خطای دسترسی',
                        text: 'لطفا مجددا وارد سیستم شوید',
                        type: 'error',
                        padding: '2em'
                    }).then(function() {
                        window.location.href = '/admin/login';
                    });
                    return;
                }

                $.ajax({
                    url: `/api/admin/messages/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + token
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            const message = response.data;
                            
                            $('#detailId').text(message.id);
                            $('#detailReceiver').html(
                                message.receiver ? 
                                    message.receiver.name : 
                                    '<span class="badge badge-info">همه سازمان‌ها</span>'
                            );
                            $('#detailSubject').text(message.subject);
                            $('#detailMessage').html(message.message.replace(/\n/g, '<br>'));
                            $('#detailCreatedAt').text(message.created_at_jalali || '-');

                            $('#detailsModal').modal('show');
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
                                text: 'خطا در دریافت اطلاعات',
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
