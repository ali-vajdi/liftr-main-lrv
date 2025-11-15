@extends('admin.layout.master')

@section('title', 'مدیریت شرکت‌ها')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">مدیریت شرکت‌ها</h5>
                            <a href="{{ route('admin.packages.view') }}" class="btn btn-info btn-sm">
                                <i class="fa fa-package"></i> مدیریت تعرفه‌ها
                            </a>
                        </div>
                    </div>
                    <div class="widget-content">
                        @include('admin.components.datatable', [
                            'title' => 'شرکت‌ها',
                            'apiUrl' => '/api/admin/organizations',
                            'createButton' => true,
                            'createButtonText' => 'افزودن شرکت جدید',
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                ['field' => 'name', 'label' => 'نام شرکت'],
                                ['field' => 'address', 'label' => 'آدرس'],
                                [
                                    'field' => 'logo',
                                    'label' => 'لوگو',
                                    'formatter' => 'function(value) {
                                        if (value) {
                                            return `<img src="${value}" alt="Logo" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">`;
                                        }
                                        return "بدون لوگو";
                                    }',
                                ],
                                [
                                    'field' => 'status',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? 
                                            `<span class="badge badge-success">فعال</span>` : 
                                            `<span class="badge badge-danger">غیرفعال</span>`;
                                    }',
                                ],
                                [
                                    'field' => 'sms_balance',
                                    'label' => 'موجودی پیامک',
                                    'formatter' => 'function(value) {
                                        return value ? 
                                            `<span class="badge badge-info">${parseFloat(value).toLocaleString("fa-IR")} تومان</span>` : 
                                            `<span class="badge badge-secondary">0 تومان</span>`;
                                    }',
                                ],
                                [
                                    'field' => 'sms_cost_per_message',
                                    'label' => 'هزینه هر پیامک',
                                    'formatter' => 'function(value) {
                                        return value ? 
                                            `<span class="badge badge-warning">${parseFloat(value).toLocaleString("fa-IR")} تومان</span>` : 
                                            `<span class="badge badge-secondary">0 تومان</span>`;
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
                                
                                // Users button
                                html += \'<button type="button" class="btn btn-sm btn-warning users-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مدیریت کاربران">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>\';
                                html += \'</button>\';
                                
                                // Packages button
                                html += \'<button type="button" class="btn btn-sm btn-success packages-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مدیریت پکیج‌ها">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-package"><path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>\';
                                html += \'</button>\';
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(".show-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShow(id);
                                });
                                
                                // Handle users button click
                                $(".users-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.location.href = "/admin/organizations/" + id + "/users";
                                });
                                
                                // Handle packages button click
                                $(".packages-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.location.href = "/admin/organizations/" + id + "/packages";
                                });
                            ',
                        ])
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for adding/editing organizations -->
        <div class="modal fade" id="organizationModal" tabindex="-1" role="dialog" aria-labelledby="organizationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="organizationModalLabel">افزودن شرکت</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="organizationForm">
                            <input type="hidden" id="organizationId">
                            <div class="form-group">
                                <label for="name">نام شرکت <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="address">آدرس</label>
                                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="logo">لوگو</label>
                                <input type="file" class="form-control" id="logo" name="logo" accept="image/jpeg,image/png,image/jpg">
                                <small class="form-text text-muted">فرمت‌های مجاز: JPG, PNG - حداکثر 2MB</small>
                                <div id="logoPreview" class="mt-2" style="display: none;">
                                    <img id="logoPreviewImg" src="" alt="پیش‌نمایش لوگو" style="max-width: 100px; max-height: 100px; border-radius: 4px;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="status">وضعیت <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="1">فعال</option>
                                    <option value="0">غیرفعال</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sms_balance">موجودی پیامک (تومان)</label>
                                <input type="number" class="form-control" id="sms_balance" name="sms_balance" min="0" step="0.01" value="0">
                                <small class="form-text text-muted">موجودی اولیه پنل پیامک برای این سازمان</small>
                            </div>
                            <div class="form-group">
                                <label for="sms_cost_per_message">هزینه هر پیامک (تومان)</label>
                                <input type="number" class="form-control" id="sms_cost_per_message" name="sms_cost_per_message" min="0" step="0.01" value="0">
                                <small class="form-text text-muted">هزینه هر پیامک برای این سازمان (مثال: 15 تومان)</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="saveOrganization">ذخیره</button>
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
                        <h5 class="modal-title" id="detailsModalLabel">جزئیات شرکت</h5>
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
                                        <th>نام شرکت</th>
                                        <td id="detailName"></td>
                                    </tr>
                                    <tr>
                                        <th>آدرس</th>
                                        <td id="detailAddress"></td>
                                    </tr>
                                    <tr>
                                        <th>لوگو</th>
                                        <td id="detailLogo"></td>
                                    </tr>
                                    <tr>
                                        <th>وضعیت</th>
                                        <td id="detailStatus"></td>
                                    </tr>
                                    <tr>
                                        <th>موجودی پیامک</th>
                                        <td id="detailSmsBalance"></td>
                                    </tr>
                                    <tr>
                                        <th>هزینه هر پیامک</th>
                                        <td id="detailSmsCostPerMessage"></td>
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
            let currentOrganizationId = null;

            // Show organization details
            window.onShow = function(id) {
                $.ajax({
                    url: `/api/admin/organizations/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#detailId').text(data.id);
                        $('#detailName').text(data.name);
                        $('#detailAddress').text(data.address || 'ثبت نشده');
                        $('#detailLogo').html(data.logo ? 
                            `<img src="${data.logo}" alt="Logo" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">` : 
                            'بدون لوگو'
                        );
                        $('#detailStatus').html(data.status ? 
                            '<span class="badge badge-success">فعال</span>' : 
                            '<span class="badge badge-danger">غیرفعال</span>'
                        );
                        $('#detailSmsBalance').html(data.sms_balance ? 
                            `<span class="badge badge-info">${parseFloat(data.sms_balance).toLocaleString('fa-IR')} تومان</span>` : 
                            '<span class="badge badge-secondary">0 تومان</span>'
                        );
                        $('#detailSmsCostPerMessage').html(data.sms_cost_per_message ? 
                            `<span class="badge badge-warning">${parseFloat(data.sms_cost_per_message).toLocaleString('fa-IR')} تومان</span>` : 
                            '<span class="badge badge-secondary">0 تومان</span>'
                        );
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

            // Create new organization
            $('.create-new-button').click(function() {
                $('#organizationModalLabel').text('افزودن شرکت');
                $('#organizationForm')[0].reset();
                $('#organizationId').val('');
                $('#logoPreview').hide();
                $('#organizationModal').modal('show');
            });

            // Logo preview functionality
            $('#logo').change(function() {
                const file = this.files[0];
                if (file) {
                    // Check file type
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                    if (!allowedTypes.includes(file.type)) {
                        swal({
                            title: 'خطا',
                            text: 'فرمت فایل باید JPG یا PNG باشد',
                            type: 'error',
                            padding: '2em'
                        });
                        $(this).val(''); // Clear the input
                        $('#logoPreview').hide();
                        return;
                    }
                    
                    // Check file size (2MB = 2 * 1024 * 1024 bytes)
                    if (file.size > 2 * 1024 * 1024) {
                        swal({
                            title: 'خطا',
                            text: 'حجم فایل نمی‌تواند بیش از 2 مگابایت باشد',
                            type: 'error',
                            padding: '2em'
                        });
                        $(this).val(''); // Clear the input
                        $('#logoPreview').hide();
                        return;
                    }
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#logoPreviewImg').attr('src', e.target.result);
                        $('#logoPreview').show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#logoPreview').hide();
                }
            });

            // Save organization (create or update)
            $('#saveOrganization').click(function() {
                const id = $('#organizationId').val();
                const name = $('#name').val();
                const address = $('#address').val();
                const status = $('#status').val() === '1' ? true : false;

                if (!name) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا نام شرکت را وارد کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                // Create FormData for file upload
                const formData = new FormData();
                formData.append('name', name);
                formData.append('address', address);
                formData.append('status', status);
                formData.append('sms_balance', $('#sms_balance').val() || 0);
                formData.append('sms_cost_per_message', $('#sms_cost_per_message').val() || 0);
                
                // Add logo file if selected
                const logoFile = $('#logo')[0].files[0];
                if (logoFile) {
                    formData.append('logo', logoFile);
                }

                const url = id ? `/api/admin/organizations/${id}` : '/api/admin/organizations';
                const method = id ? 'POST' : 'POST'; // Use POST for both with _method for PUT
                if (id) {
                    formData.append('_method', 'PUT');
                }
                const successMessage = id ? 'شرکت با موفقیت ویرایش شد' : 'شرکت با موفقیت ایجاد شد';

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        $('#organizationModal').modal('hide');

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

            // Edit organization
            window.onEdit = function(id) {
                $.ajax({
                    url: `/api/admin/organizations/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const organization = response.data;

                        $('#organizationModalLabel').text('ویرایش شرکت');
                        $('#organizationId').val(organization.id);
                        $('#name').val(organization.name);
                        $('#address').val(organization.address);
                        $('#status').val(organization.status ? '1' : '0');
                        $('#sms_balance').val(organization.sms_balance || 0);
                        $('#sms_cost_per_message').val(organization.sms_cost_per_message || 0);
                        
                        // Show current logo if exists
                        if (organization.logo) {
                            $('#logoPreviewImg').attr('src', organization.logo);
                            $('#logoPreview').show();
                        } else {
                            $('#logoPreview').hide();
                        }
                        
                        // Clear file input
                        $('#logo').val('');

                        $('#organizationModal').modal('show');
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

            // Delete organization
            window.onDelete = function(id) {
                currentOrganizationId = id;
                $('#deleteConfirmationModal').modal('show');
            };

            // Confirm delete
            $('#confirmDelete').click(function() {
                if (!currentOrganizationId) return;

                $.ajax({
                    url: `/api/admin/organizations/${currentOrganizationId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function() {
                        $('#deleteConfirmationModal').modal('hide');

                        swal({
                            title: 'موفقیت',
                            text: 'شرکت با موفقیت حذف شد',
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
                        } else if (xhr.status === 422 || xhr.status === 409) {
                            swal({
                                title: 'خطا',
                                text: xhr.responseJSON?.message || 'این مورد قابل حذف نیست زیرا در جای دیگری استفاده شده است',
                                type: 'error',
                                padding: '2em'
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
