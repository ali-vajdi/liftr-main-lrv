@extends('organization.layout.master')

@section('title', 'پیام‌های دریافتی')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">پیام‌های دریافتی از لیفتر</h5>
                        <div>
                            <a href="{{ route('organization.messages.sent') }}" class="btn btn-info btn-sm mr-2">
                                <i class="fa fa-paper-plane"></i> پیام‌های ارسال شده
                            </a>
                            <button type="button" class="btn btn-primary btn-sm" id="create-message-btn">
                                <i class="fa fa-plus"></i> ارسال پیام به تکنسین
                            </button>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        <div class="mb-3">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary filter-btn active" data-filter="all">همه</button>
                                <button type="button" class="btn btn-sm btn-outline-primary filter-btn" data-filter="unread">خوانده نشده</button>
                                <button type="button" class="btn btn-sm btn-outline-primary filter-btn" data-filter="read">خوانده شده</button>
                            </div>
                        </div>
                        @include('organization.components.datatable', [
                            'title' => 'پیام‌های دریافتی',
                            'apiUrl' => '/api/organization/messages',
                            'createButton' => false,
                            'hideDefaultActions' => true,
                            'columns' => [
                                [
                                    'field' => 'is_read',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? 
                                            `<span class="badge badge-success">خوانده شده</span>` : 
                                            `<span class="badge badge-warning">خوانده نشده</span>`;
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
                                    'label' => 'تاریخ دریافت',
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
    
    let currentFilter = 'all';
    
    $(document).ready(function() {
        // Filter buttons
        $('.filter-btn').on('click', function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            currentFilter = $(this).data('filter');
            
            if (typeof window.datatableApi !== 'undefined' && window.datatableApi.table) {
                const apiUrl = currentFilter === 'all' 
                    ? '/api/organization/messages'
                    : `/api/organization/messages?is_read=${currentFilter === 'read' ? 1 : 0}`;
                
                if (window.datatableApi.setApiUrl) {
                    window.datatableApi.setApiUrl(apiUrl);
                } else {
                    window.location.reload();
                }
            }
        });
        
        // Open create message modal
        $('#create-message-btn').on('click', function() {
            window.location.href = '{{ route("organization.messages.sent") }}';
        });
    });
    
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
            url: `/api/organization/messages/${id}`,
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
                                <strong>فرستنده:</strong> مدیریت سیستم
                            </div>
                            <div class="mb-3">
                                <strong>وضعیت:</strong> 
                                ${message.is_read ? '<span class="badge badge-success">خوانده شده</span>' : '<span class="badge badge-warning">خوانده نشده</span>'}
                            </div>
                            <div class="mb-3">
                                <strong>عنوان:</strong> ${message.subject}
                            </div>
                            <div class="mb-3">
                                <strong>متن پیام:</strong>
                                <div class="mt-2 p-3 bg-light rounded">${message.message.replace(/\n/g, '<br>')}</div>
                            </div>
                            <div class="mb-3">
                                <strong>تاریخ دریافت:</strong> ${message.created_at_jalali || '-'}
                            </div>
                            ${message.read_at_jalali ? `<div class="mb-3"><strong>تاریخ خواندن:</strong> ${message.read_at_jalali}</div>` : ''}
                        </div>
                    `;
                    $('#viewMessageContent').html(html);
                    $('#viewMessageModal').modal('show');
                    
                    // Refresh table to update read status
                    if (typeof window.datatableApi !== 'undefined' && window.datatableApi.refresh) {
                        setTimeout(() => {
                            window.datatableApi.refresh();
                        }, 500);
                    }
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

