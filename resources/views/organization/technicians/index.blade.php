@extends('organization.layout.master')

@section('title', 'مدیریت تکنیسین‌ها')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">مدیریت تکنیسین‌ها - {{ $organization->name }}</h5>
                    </div>
                    <div class="widget-content">
                        @include('organization.components.datatable', [
                            'title' => 'تکنیسین‌ها',
                            'apiUrl' => '/api/organization/technicians',
                            'createButton' => true,
                            'createButtonText' => 'افزودن تکنیسین جدید',
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                ['field' => 'full_name', 'label' => 'نام و نام خانوادگی'],
                                ['field' => 'national_id', 'label' => 'کد ملی'],
                                ['field' => 'phone_number', 'label' => 'شماره تماس'],
                                [
                                    'field' => 'username',
                                    'label' => 'نام کاربری',
                                    'formatter' => 'function(value) {
                                        return value || "-";
                                    }',
                                ],
                                [
                                    'field' => 'status',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? `<span class="badge badge-success">فعال</span>` : `<span class="badge badge-danger">غیرفعال</span>`;
                                    }',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'actions' => '
                                // Show button
                                html += \'<button type="button" class="btn btn-sm btn-info show-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                                
                                // Set credentials button
                                html += \'<button type="button" class="btn btn-sm btn-warning credentials-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="تنظیم اطلاعات ورود">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-key"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>\';
                                html += \'</button>\';
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(".show-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShow(id);
                                });
                                
                                // Handle credentials button click
                                $(".credentials-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onSetCredentials(id);
                                });
                            ',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Add/Edit Technician -->
        <div class="modal fade" id="technicianModal" tabindex="-1" role="dialog" aria-labelledby="technicianModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="technicianModalLabel">افزودن تکنیسین جدید</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="technicianForm">
                            <div class="form-group">
                                <label for="first_name">نام</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">نام خانوادگی</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                            <div class="form-group">
                                <label for="national_id">کد ملی</label>
                                <input type="text" class="form-control" id="national_id" name="national_id" required>
                            </div>
                            <div class="form-group">
                                <label for="phone_number">شماره تماس</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                            </div>
                            <div class="form-group">
                                <label for="username">نام کاربری (اختیاری)</label>
                                <input type="text" class="form-control" id="username" name="username">
                            </div>
                            <div class="form-group">
                                <label for="password">رمز عبور (اختیاری)</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                            <div class="form-group">
                                <label for="status">وضعیت</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="true">فعال</option>
                                    <option value="false">غیرفعال</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="saveTechnician">ذخیره</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Set Credentials -->
        <div class="modal fade" id="credentialsModal" tabindex="-1" role="dialog" aria-labelledby="credentialsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="credentialsModalLabel">تنظیم اطلاعات ورود</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="credentialsForm">
                            <div class="form-group">
                                <label for="credentials_username">نام کاربری</label>
                                <input type="text" class="form-control" id="credentials_username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="credentials_password">رمز عبور</label>
                                <input type="password" class="form-control" id="credentials_password" name="password" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="saveCredentials">ذخیره</button>
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
                        <h5 class="modal-title" id="detailsModalLabel">جزئیات تکنیسین</h5>
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
                                        <th>نام</th>
                                        <td id="detailFirstName"></td>
                                    </tr>
                                    <tr>
                                        <th>نام خانوادگی</th>
                                        <td id="detailLastName"></td>
                                    </tr>
                                    <tr>
                                        <th>کد ملی</th>
                                        <td id="detailNationalId"></td>
                                    </tr>
                                    <tr>
                                        <th>شماره تماس</th>
                                        <td id="detailPhoneNumber"></td>
                                    </tr>
                                    <tr>
                                        <th>نام کاربری</th>
                                        <td id="detailUsername"></td>
                                    </tr>
                                    <tr>
                                        <th>وضعیت</th>
                                        <td id="detailStatus"></td>
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

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">تأیید حذف</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>آیا از حذف این تکنیسین اطمینان دارید؟</p>
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
        let currentTechnicianId = null;

        // Create new technician
        $('.create-new-button').click(function() {
            currentTechnicianId = null;
            $('#technicianModalLabel').text('افزودن تکنیسین جدید');
            $('#technicianForm')[0].reset();
            $('#password').prop('required', false);
            $('#technicianModal').modal('show');
        });

        $(document).ready(function() {
            // Show technician details
            window.onShow = function(id) {
                $.ajax({
                    url: `/api/organization/technicians/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#detailId').text(data.id);
                        $('#detailFirstName').text(data.first_name);
                        $('#detailLastName').text(data.last_name);
                        $('#detailNationalId').text(data.national_id);
                        $('#detailPhoneNumber').text(data.phone_number);
                        $('#detailUsername').text(data.username || '-');
                        $('#detailStatus').html(data.status ? 
                            '<span class="badge badge-success">فعال</span>' : 
                            '<span class="badge badge-danger">غیرفعال</span>'
                        );
                        $('#detailCreatedAt').text(new Date(data.created_at).toLocaleDateString('fa-IR'));

                        $('#detailsModal').modal('show');
                    },
                    error: function(xhr) {
                        if (xhr.status === 404) {
                            swal({
                                title: 'خطا',
                                text: 'تکنیسین مورد نظر یافت نشد',
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

            // Edit technician
            window.onEdit = function(id) {
                currentTechnicianId = id;
                $('#technicianModalLabel').text('ویرایش تکنیسین');
                
                $.ajax({
                    url: `/api/organization/technicians/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#first_name').val(data.first_name);
                        $('#last_name').val(data.last_name);
                        $('#national_id').val(data.national_id);
                        $('#phone_number').val(data.phone_number);
                        $('#username').val(data.username || '');
                        $('#password').val('');
                        $('#status').val(data.status ? 'true' : 'false');
                        
                        $('#technicianModal').modal('show');
                    },
                    error: function(xhr) {
                        swal({
                            title: 'خطا',
                            text: 'خطا در دریافت اطلاعات تکنیسین',
                            type: 'error',
                            padding: '2em'
                        });
                    }
                });
            };

            // Set credentials
            window.onSetCredentials = function(id) {
                currentTechnicianId = id;
                
                // Get technician data to pre-fill username
                $.ajax({
                    url: `/api/organization/technicians/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        $('#credentials_username').val(data.username || '');
                        $('#credentials_password').val('');
                        $('#credentialsModal').modal('show');
                    },
                    error: function(xhr) {
                        // If error, just show modal with empty form
                        $('#credentialsForm')[0].reset();
                        $('#credentialsModal').modal('show');
                    }
                });
            };

            // Delete technician
            window.onDelete = function(id) {
                currentTechnicianId = id;
                $('#deleteModal').modal('show');
            };

            // Save technician
            $('#saveTechnician').click(function() {
                const formData = {
                    first_name: $('#first_name').val(),
                    last_name: $('#last_name').val(),
                    national_id: $('#national_id').val(),
                    phone_number: $('#phone_number').val(),
                    username: $('#username').val(),
                    password: $('#password').val(),
                    status: $('#status').val()
                };

                const url = currentTechnicianId ? 
                    `/api/organization/technicians/${currentTechnicianId}` : 
                    '/api/organization/technicians';
                const method = currentTechnicianId ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        $('#technicianModal').modal('hide');
                        
                        swal({
                            title: 'موفقیت',
                            text: response.message,
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
                                window.location.href = '/login';
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

            // Save credentials
            $('#saveCredentials').click(function() {
                const formData = {
                    username: $('#credentials_username').val(),
                    password: $('#credentials_password').val()
                };

                $.ajax({
                    url: `/api/organization/technicians/${currentTechnicianId}/credentials`,
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        $('#credentialsModal').modal('hide');
                        
                        swal({
                            title: 'موفقیت',
                            text: response.message,
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
                                window.location.href = '/login';
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

            // Confirm delete
            $('#confirmDelete').click(function() {
                $.ajax({
                    url: `/api/organization/technicians/${currentTechnicianId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        $('#deleteModal').modal('hide');
                        
                        swal({
                            title: 'موفقیت',
                            text: response.message,
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
                                window.location.href = '/login';
                            });
                        } else {
                            swal({
                                title: 'خطا',
                                text: 'خطا در حذف تکنیسین',
                                type: 'error',
                                padding: '2em'
                            });
                        }
                    }
                });
            });

            // Reset form when modal is hidden
            $('#technicianModal').on('hidden.bs.modal', function() {
                $('#technicianForm')[0].reset();
                currentTechnicianId = null;
                $('#technicianModalLabel').text('افزودن تکنیسین جدید');
            });
        });
    </script>
@endsection