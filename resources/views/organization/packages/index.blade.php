@extends('organization.layout.master')

@section('title', 'پکیج‌های من')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">پکیج‌های من - {{ $organization->name }}</h5>
                    </div>
                    <div class="widget-content">
                        <!-- Package Summary -->
                        @include('organization.packages.partials.package-summary', ['organization' => $organization])

                        @include('organization.components.datatable', [
                            'title' => 'پکیج‌های اختصاص داده شده',
                            'apiUrl' => '/api/organization/packages',
                            'createButton' => false,
                            'hideDefaultActions' => true,
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                ['field' => 'package_name', 'label' => 'نام پکیج'],
                                ['field' => 'package_duration_label', 'label' => 'مدت زمان'],
                                ['field' => 'formatted_price', 'label' => 'قیمت'],
                                [
                                    'field' => 'started_at',
                                    'label' => 'تاریخ شروع',
                                    'formatter' => 'function(value) {
                                        return new Date(value).toLocaleDateString("fa-IR");
                                    }',
                                ],
                                [
                                    'field' => 'expires_at',
                                    'label' => 'تاریخ انقضا',
                                    'formatter' => 'function(value) {
                                        return new Date(value).toLocaleDateString("fa-IR");
                                    }',
                                ],
                                [
                                    'field' => 'is_active',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? `<span class="badge badge-success">فعال</span>` : `<span class="badge badge-danger">غیرفعال</span>`;
                                    }',
                                ],
                                [
                                    'field' => 'has_package_changed',
                                    'label' => 'تغییر پکیج',
                                    'formatter' => 'function(value) {
                                        if (value) {
                                            return `<span class="badge badge-warning">تغییر کرده</span>`;
                                        } else {
                                            return `<span class="badge badge-success">بدون تغییر</span>`;
                                        }
                                    }',
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
                        <h5 class="modal-title" id="detailsModalLabel">جزئیات پکیج</h5>
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
                                        <th>نام پکیج (اختصاص یافته)</th>
                                        <td id="detailAssignedPackageName"></td>
                                    </tr>
                                    <tr>
                                        <th>مدت زمان (اختصاص یافته)</th>
                                        <td id="detailAssignedDuration"></td>
                                    </tr>
                                    <tr>
                                        <th>قیمت (اختصاص یافته)</th>
                                        <td id="detailAssignedPrice"></td>
                                    </tr>
                                    <tr>
                                        <th>نام پکیج (فعلی)</th>
                                        <td id="detailCurrentPackageName"></td>
                                    </tr>
                                    <tr>
                                        <th>مدت زمان (فعلی)</th>
                                        <td id="detailCurrentDuration"></td>
                                    </tr>
                                    <tr>
                                        <th>قیمت (فعلی)</th>
                                        <td id="detailCurrentPrice"></td>
                                    </tr>
                                    <tr>
                                        <th>تغییر کرده است؟</th>
                                        <td id="detailHasChanged"></td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ شروع</th>
                                        <td id="detailStartedAt"></td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ انقضا</th>
                                        <td id="detailExpiresAt"></td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ ایجاد</th>
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
            // Show package details
            window.onShow = function(id) {
                $.ajax({
                    url: `/api/organization/packages/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#detailId').text(data.id);
                        $('#detailAssignedPackageName').text(data.assigned_package_info.name);
                        $('#detailAssignedDuration').text(data.assigned_package_info.duration_label);
                        $('#detailAssignedPrice').text(data.assigned_package_info.formatted_price);

                        if (data.current_package_info) {
                            $('#detailCurrentPackageName').text(data.current_package_info.name);
                            $('#detailCurrentDuration').text(data.current_package_info.duration_label);
                            $('#detailCurrentPrice').text(data.current_package_info.formatted_price);
                            $('#detailHasChanged').html(data.has_package_changed ? '<span class="badge badge-warning">بله</span>' : '<span class="badge badge-success">خیر</span>');
                        } else {
                            $('#detailCurrentPackageName').text('پکیج اصلی حذف شده');
                            $('#detailCurrentDuration').text('-');
                            $('#detailCurrentPrice').text('-');
                            $('#detailHasChanged').html('<span class="badge badge-danger">بله (حذف شده)</span>');
                        }
                        
                        $('#detailStartedAt').text(new Date(data.started_at).toLocaleDateString('fa-IR'));
                        $('#detailExpiresAt').text(new Date(data.expires_at).toLocaleDateString('fa-IR'));
                        $('#detailCreatedAt').text(new Date(data.created_at).toLocaleDateString('fa-IR'));

                        $('#detailsModal').modal('show');
                    },
                    error: function(xhr) {
                        if (xhr.status === 404) {
                            swal({
                                title: 'خطا',
                                text: 'پکیج مورد نظر یافت نشد',
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
                                window.location.href = '/login';
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
