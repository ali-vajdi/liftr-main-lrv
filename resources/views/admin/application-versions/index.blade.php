@extends('admin.layout.master')

@section('title', 'مدیریت نسخه‌های اپلیکیشن')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="">مدیریت نسخه‌های اپلیکیشن</h5>
                    </div>
                    <div class="widget-content">
                        @include('admin.components.datatable', [
                            'title' => 'نسخه‌های اپلیکیشن',
                            'apiUrl' => '/api/admin/application-versions',
                            'createButton' => true,
                            'createButtonText' => 'افزودن نسخه جدید',
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                [
                                    'field' => 'platform',
                                    'label' => 'پلتفرم',
                                    'formatter' => 'function(value) {
                                        return value === "web" ? 
                                            `<span class="badge badge-primary">وب</span>` : 
                                            `<span class="badge badge-success">اندروید</span>`;
                                    }',
                                ],
                                ['field' => 'version', 'label' => 'نسخه'],
                                [
                                    'field' => 'force_update',
                                    'label' => 'بروزرسانی اجباری',
                                    'formatter' => 'function(value) {
                                        return value ? 
                                            `<span class="badge badge-danger">بله</span>` : 
                                            `<span class="badge badge-secondary">خیر</span>`;
                                    }',
                                ],
                                [
                                    'field' => 'description',
                                    'label' => 'توضیحات',
                                    'formatter' => 'function(value) {
                                        return value ? (value.length > 50 ? value.substring(0, 50) + "..." : value) : "-";
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

        <!-- Modal for adding/editing application version -->
        <div class="modal fade" id="versionModal" tabindex="-1" role="dialog" aria-labelledby="versionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="versionModalLabel">افزودن نسخه</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="versionForm">
                            <input type="hidden" id="versionId">
                            <div class="form-group">
                                <label for="platform">پلتفرم</label>
                                <select class="form-control" id="platform" name="platform" required>
                                    <option value="">انتخاب کنید</option>
                                    <option value="web">وب</option>
                                    <option value="android">اندروید</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="version">نسخه</label>
                                <input type="text" class="form-control" id="version" name="version" placeholder="مثال: 1.0.0" required>
                                <small class="form-text text-muted">
                                    نسخه را به فرمت semantic versioning وارد کنید (مثال: 1.0.0, 1.2.3)
                                </small>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="force_update" name="force_update">
                                    <label class="form-check-label" for="force_update">
                                        بروزرسانی اجباری
                                    </label>
                                    <small class="form-text text-muted d-block mt-1">
                                        در صورت فعال بودن، کاربران باید حتماً به این نسخه بروزرسانی کنند
                                    </small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">توضیحات</label>
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="توضیحات مربوط به این نسخه (اختیاری)"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="saveVersion">ذخیره</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Modal -->
        <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailsModalLabel">جزئیات نسخه</h5>
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
                                        <th>پلتفرم</th>
                                        <td id="detailPlatform"></td>
                                    </tr>
                                    <tr>
                                        <th>نسخه</th>
                                        <td id="detailVersion"></td>
                                    </tr>
                                    <tr>
                                        <th>بروزرسانی اجباری</th>
                                        <td id="detailForceUpdate"></td>
                                    </tr>
                                    <tr>
                                        <th>توضیحات</th>
                                        <td id="detailDescription"></td>
                                    </tr>
                                    <tr>
                                        <th>تاریخ ایجاد</th>
                                        <td id="detailCreatedAt"></td>
                                    </tr>
                                    <tr>
                                        <th>آخرین ویرایش</th>
                                        <td id="detailUpdatedAt"></td>
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

        <!-- Confirmation Modal for Delete -->
        <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteConfirmationModalLabel">تایید حذف</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        آیا از حذف این مورد اطمینان دارید؟
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-danger" id="confirmDelete">حذف</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script>
        $(document).ready(function() {
            let currentVersionId = null;

            // Show version details
            window.onShow = function(id) {
                $.ajax({
                    url: `/api/admin/application-versions/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#detailId').text(data.id);
                        $('#detailPlatform').html(data.platform === 'web' ? 
                            '<span class="badge badge-primary">وب</span>' : 
                            '<span class="badge badge-success">اندروید</span>'
                        );
                        $('#detailVersion').text(data.version);
                        $('#detailForceUpdate').html(data.force_update ? 
                            '<span class="badge badge-danger">بله</span>' : 
                            '<span class="badge badge-secondary">خیر</span>'
                        );
                        $('#detailDescription').text(data.description || '-');
                        $('#detailCreatedAt').text(new Date(data.created_at).toLocaleDateString('fa-IR'));
                        $('#detailUpdatedAt').text(new Date(data.updated_at).toLocaleDateString('fa-IR'));

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

            // Create new version
            $('.create-new-button').click(function() {
                $('#versionModalLabel').text('افزودن نسخه');
                $('#versionForm')[0].reset();
                $('#versionId').val('');
                $('#force_update').prop('checked', false);
                $('#versionModal').modal('show');
            });

            // Save version (create or update)
            $('#saveVersion').click(function() {
                const id = $('#versionId').val();
                const platform = $('#platform').val();
                const version = $('#version').val();
                const forceUpdate = $('#force_update').is(':checked');
                const description = $('#description').val();

                if (!platform || !version) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا تمام فیلدهای الزامی را پر کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const data = {
                    platform: platform,
                    version: version,
                    force_update: forceUpdate,
                    description: description || null
                };

                const url = id ? `/api/admin/application-versions/${id}` : '/api/admin/application-versions';
                const method = id ? 'PUT' : 'POST';
                const successMessage = id ? 'نسخه با موفقیت ویرایش شد' : 'نسخه با موفقیت ثبت شد';

                $.ajax({
                    url: url,
                    type: method,
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        $('#versionModal').modal('hide');
                        
                        swal({
                            title: 'موفقیت',
                            text: successMessage,
                            type: 'success',
                            padding: '2em'
                        });

                        window.datatableApi.refresh();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            let errorMessage = '';

                            for (const key in errors) {
                                errorMessage += errors[key].join('\n') + '\n';
                            }

                            swal({
                                title: 'خطا در اعتبارسنجی',
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
                                text: 'خطا در ذخیره اطلاعات',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            });

            // Edit version
            window.onEdit = function(id) {
                $.ajax({
                    url: `/api/admin/application-versions/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const version = response.data;

                        $('#versionModalLabel').text('ویرایش نسخه');
                        $('#versionId').val(version.id);
                        $('#platform').val(version.platform);
                        $('#version').val(version.version);
                        $('#force_update').prop('checked', version.force_update || false);
                        $('#description').val(version.description || '');

                        $('#versionModal').modal('show');
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

            // Delete version
            window.onDelete = function(id) {
                currentVersionId = id;
                $('#deleteConfirmationModal').modal('show');
            };

            // Confirm delete
            $('#confirmDelete').click(function() {
                if (!currentVersionId) return;

                $.ajax({
                    url: `/api/admin/application-versions/${currentVersionId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function() {
                        $('#deleteConfirmationModal').modal('hide');

                        swal({
                            title: 'موفقیت',
                            text: 'نسخه با موفقیت حذف شد',
                            type: 'success',
                            padding: '2em'
                        });

                        window.datatableApi.refresh();
                    },
                    error: function(xhr) {
                        $('#deleteConfirmationModal').modal('hide');

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
                                text: 'خطا در حذف اطلاعات',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            });
        });
    </script>
@endsection

