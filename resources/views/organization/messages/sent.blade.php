@extends('organization.layout.master')

@section('title', 'پیام‌های ارسال شده')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">پیام‌های ارسال شده به تکنسین‌ها</h5>
                        <div>
                            <a href="{{ route('organization.messages.view') }}" class="btn btn-info btn-sm mr-2">
                                <i class="fa fa-inbox"></i> پیام‌های دریافتی
                            </a>
                            <button type="button" class="btn btn-primary btn-sm" id="create-message-btn">
                                <i class="fa fa-plus"></i> ارسال پیام جدید
                            </button>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        @include('organization.components.datatable', [
                            'title' => 'پیام‌های ارسال شده',
                            'apiUrl' => '/api/organization/messages/sent',
                            'createButton' => false,
                            'hideDefaultActions' => true,
                            'columns' => [
                                [
                                    'field' => 'receiver',
                                    'label' => 'گیرنده',
                                    'formatter' => 'function(value) {
                                        if (!value) {
                                            return `<span class="badge badge-info">همه تکنسین‌ها</span>`;
                                        }
                                        return value.first_name + " " + value.last_name || "-";
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
                                html += \'<button type="button" class="btn btn-sm btn-info show-message-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده">\';
                                html += \'<i class="fa fa-eye"></i>\';
                                html += \'</button>\';
                            ',
                            'actionHandlers' => '
                                $(document).off("click", ".show-message-btn").on("click", ".show-message-btn", function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    const id = $(this).data("id");
                                    if (id && typeof window.onShowMessage === "function") {
                                        window.onShowMessage(id);
                                    }
                                    return false;
                                });
                            ',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Message Modal -->
<div class="modal fade" id="createMessageModal" tabindex="-1" role="dialog" aria-labelledby="createMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createMessageModalLabel">ارسال پیام جدید</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createMessageForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="message_technician_id">ارسال به <span class="text-danger">*</span></label>
                        <select class="form-control" id="message_technician_id" name="technician_id">
                            <option value="">همه تکنسین‌ها</option>
                        </select>
                        <small class="form-text text-muted">برای ارسال به همه تکنسین‌ها، گزینه "همه تکنسین‌ها" را انتخاب کنید</small>
                    </div>
                    <div class="form-group">
                        <label for="message_subject">عنوان <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="message_subject" name="subject" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="message_content">متن پیام <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message_content" name="message" rows="6" required maxlength="5000"></textarea>
                    </div>
                    <div class="alert alert-danger" id="message-error" style="display: none; border-radius: 8px; margin-top: 15px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">لغو</button>
                    <button type="button" class="btn btn-primary" id="saveMessage">ارسال پیام</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Message Modal -->
<div class="modal fade" id="viewMessageModal" tabindex="-1" role="dialog" aria-labelledby="viewMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewMessageModalLabel">مشاهده پیام</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewMessageContent">
                <!-- Message content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script>
// Wait for jQuery
(function($) {
    'use strict';
    
    let technicians = [];
    
    $(document).ready(function() {
        loadTechnicians();
        
        // Open create message modal
        $('#create-message-btn').on('click', function() {
            $('#createMessageModal').modal('show');
            $('#message-error').hide();
            $('#createMessageForm')[0].reset();
        });
        
        // Handle create message form submission
        $(document).on('click', '#saveMessage', function() {
            const technicianId = $('#message_technician_id').val();
            const subject = $('#message_subject').val();
            const message = $('#message_content').val();
            
            if (!subject) {
                $('#message-error').text('لطفاً عنوان پیام را وارد کنید').show();
                return false;
            }
            
            if (!message) {
                $('#message-error').text('لطفاً متن پیام را وارد کنید').show();
                return false;
            }
            
            $('#message-error').hide();
            
            const token = localStorage.getItem('organization_token');
            if (!token) {
                $('#message-error').text('لطفاً مجدداً وارد شوید').show();
                return false;
            }
            
            const btn = $(this);
            btn.prop('disabled', true).text('در حال ارسال...');
            
            $.ajax({
                url: '/api/organization/messages',
                type: 'POST',
                data: {
                    technician_id: technicianId || null,
                    subject: subject,
                    message: message
                },
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                success: function(response) {
                    if (response.success) {
                        $('#createMessageModal').modal('hide');
                        $('#createMessageForm')[0].reset();
                        
                        swal({
                            title: 'موفقیت',
                            text: response.message,
                            type: 'success',
                            padding: '2em'
                        });
                        
                        if (typeof window.datatableApi !== 'undefined' && window.datatableApi.refresh) {
                            window.datatableApi.refresh();
                        }
                    } else {
                        $('#message-error').text(response.message || 'خطا در ارسال پیام').show();
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    let errorMessage = 'خطا در ارسال پیام';
                    
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        errorMessage = 'لطفا اطلاعات درخواستی را به صورت کامل وارد نمایید:\n';
                        for (const field in errors) {
                            errorMessage += errors[field][0] + '\n';
                        }
                    } else if (response && response.message) {
                        errorMessage = response.message;
                    }
                    
                    $('#message-error').text(errorMessage).show();
                },
                complete: function() {
                    btn.prop('disabled', false).text('ارسال پیام');
                }
            });
            
            return false;
        });
    });
    
    function loadTechnicians() {
        const token = localStorage.getItem('organization_token');
        if (!token) {
            $('#message_technician_id').html('<option value="">خطا در احراز هویت</option>');
            return;
        }

        $.ajax({
            url: '/api/organization/services/technicians',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success && response.data) {
                    technicians = response.data;
                    const select = $('#message_technician_id');
                    
                    select.html('<option value="">همه تکنسین‌ها</option>');
                    
                    if (technicians.length > 0) {
                        technicians.forEach(function(tech) {
                            select.append(`<option value="${tech.id}">${tech.name} - ${tech.phone_number}</option>`);
                        });
                    }
                } else {
                    $('#message_technician_id').html('<option value="">خطا در بارگذاری</option>');
                }
            },
            error: function(xhr) {
                let errorMessage = 'خطا در بارگذاری تکنسین‌ها';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                $('#message_technician_id').html(`<option value="">${errorMessage}</option>`);
            }
        });
    }
    
    window.onShowMessage = function(id) {
        const token = localStorage.getItem('organization_token');
        if (!token) {
            swal({
                title: 'خطا',
                text: 'لطفاً مجدداً وارد شوید',
                type: 'error',
                padding: '2em'
            });
            return;
        }
        
        $.ajax({
            url: `/api/organization/messages/sent/${id}`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.success && response.data) {
                    const message = response.data;
                    let html = `
                        <div class="message-details">
                            <div class="mb-3">
                                <strong>گیرنده:</strong> 
                                ${message.receiver ? (message.receiver.first_name + ' ' + message.receiver.last_name) : '<span class="badge badge-info">همه تکنسین‌ها</span>'}
                            </div>
                            <div class="mb-3">
                                <strong>عنوان:</strong> ${message.subject}
                            </div>
                            <div class="mb-3">
                                <strong>متن پیام:</strong>
                                <div class="mt-2 p-3 bg-light rounded">${message.message.replace(/\n/g, '<br>')}</div>
                            </div>
                            <div class="mb-3">
                                <strong>تاریخ ارسال:</strong> ${message.created_at_jalali || '-'}
                            </div>
                            ${message.service ? `<div class="mb-3"><strong>سرویس مرتبط:</strong> ${message.service.service_date_text || '-'}</div>` : ''}
                        </div>
                    `;
                    $('#viewMessageContent').html(html);
                    $('#viewMessageModal').modal('show');
                }
            },
            error: function(xhr) {
                swal({
                    title: 'خطا',
                    text: 'خطا در بارگذاری پیام',
                    type: 'error',
                    padding: '2em'
                });
            }
        });
    };
    
})(jQuery || window.jQuery || window.$);
</script>
@endsection

