@extends('organization.layout.master')

@section('title', 'پروفایل شرکت')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">پروفایل شرکت - <span id="org-name-profile">...</span></h5>
                    </div>
                    <div class="widget-content">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">اطلاعات شرکت</h5>
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editOrganizationModal">
                                            <i class="fa fa-edit"></i> ویرایش
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th width="200">نام شرکت</th>
                                                        <td id="org-name-table">...</td>
                                                    </tr>
                                                    <tr>
                                                        <th>آدرس</th>
                                                        <td id="org-address">...</td>
                                                    </tr>
                                                    <tr>
                                                        <th>وضعیت</th>
                                                        <td id="org-status">...</td>
                                                    </tr>
                                                    <tr>
                                                        <th>تاریخ ایجاد</th>
                                                        <td id="org-created">...</td>
                                                    </tr>
                                                    <tr>
                                                        <th>آخرین بروزرسانی</th>
                                                        <td id="org-updated">...</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">لوگو شرکت</h5>
                                    </div>
                                    <div class="card-body text-center" id="org-logo-container">
                                        <div class="text-muted">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">در حال بارگذاری...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Profile Section -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">پروفایل کاربر</h5>
                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editUserProfileModal">
                                            <i class="fa fa-edit"></i> ویرایش
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th width="200">نام</th>
                                                        <td id="user-name">...</td>
                                                    </tr>
                                                    <tr>
                                                        <th>شماره تلفن</th>
                                                        <td id="user-phone">...</td>
                                                    </tr>
                                                    <tr>
                                                        <th>وضعیت</th>
                                                        <td id="user-status">...</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Package Statistics -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">آمار اشتراک‌ها</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">کل اشتراک‌ها</h6>
                                                    <h4 class="text-info"><span id="packages-total">0</span></h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">اشتراک‌های فعال</h6>
                                                    <h4 class="text-success"><span id="packages-active">0</span></h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">کل روزهای باقی‌مانده</h6>
                                                    <h4 class="text-warning"><span id="packages-remaining-days">0</span></h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">کل مبلغ پرداخت شده</h6>
                                                    <h4 class="text-primary"><span id="packages-total-amount">0</span> تومان</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Organization Modal -->
    <div class="modal fade" id="editOrganizationModal" tabindex="-1" role="dialog" aria-labelledby="editOrganizationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editOrganizationModalLabel">ویرایش اطلاعات شرکت</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editOrganizationForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit-org-name">نام شرکت <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-org-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-org-address">آدرس</label>
                            <textarea class="form-control" id="edit-org-address" name="address" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit-org-logo">لوگو شرکت</label>
                            <input type="file" class="form-control-file" id="edit-org-logo" name="logo" accept="image/jpeg,image/png,image/jpg">
                            <small class="form-text text-muted">فرمت‌های مجاز: JPG, PNG (حداکثر 2 مگابایت)</small>
                            <div id="org-logo-preview" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Profile Modal -->
    <div class="modal fade" id="editUserProfileModal" tabindex="-1" role="dialog" aria-labelledby="editUserProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserProfileModalLabel">ویرایش پروفایل کاربر</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editUserProfileForm">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit-user-name">نام <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-user-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-current-password">رمز عبور فعلی</label>
                            <input type="password" class="form-control" id="edit-current-password" name="current_password">
                            <small class="form-text text-muted">برای تغییر رمز عبور، رمز عبور فعلی را وارد کنید.</small>
                        </div>
                        <div class="form-group">
                            <label for="edit-new-password">رمز عبور جدید</label>
                            <input type="password" class="form-control" id="edit-new-password" name="new_password">
                        </div>
                        <div class="form-group">
                            <label for="edit-new-password-confirmation">تکرار رمز عبور جدید</label>
                            <input type="password" class="form-control" id="edit-new-password-confirmation" name="new_password_confirmation">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
<script>
$(document).ready(function() {
    var token = localStorage.getItem('organization_token');
    var currentOrgData = null;
    var currentUserData = null;

    // Load user profile data
    function loadUserProfile() {
        if (!token) return;

        $.ajax({
            url: '/api/organization/profile',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.data) {
                    currentUserData = response.data;
                    $('#user-name').text(response.data.name || '-');
                    $('#user-phone').text(response.data.phone_number || '-');
                    
                    var statusHtml = response.data.status 
                        ? '<span class="badge badge-success">فعال</span>'
                        : '<span class="badge badge-danger">غیرفعال</span>';
                    $('#user-status').html(statusHtml);

                    // Set form values
                    $('#edit-user-name').val(response.data.name);
                }
            },
            error: function(xhr) {
                console.error('Error loading user profile:', xhr);
                if (xhr.status === 401) {
                    localStorage.removeItem('organization_token');
                    localStorage.removeItem('organization_user');
                    window.location.href = '/login';
                }
            }
        });
    }

    // Load organization data from API
    getOrganizationData(function(org, error) {
        if (error) {
            console.error('Error loading organization:', error);
            return;
        }

        if (!org) {
            console.error('No organization data received');
            return;
        }

        currentOrgData = org;

        // Set organization name
        $('#org-name-profile').text(org.name);
        $('#org-name-table').text(org.name);
        $('#org-address').text(org.address || '-');
        
        // Set status
        var statusHtml = org.status 
            ? '<span class="badge badge-success">فعال</span>'
            : '<span class="badge badge-danger">غیرفعال</span>';
        $('#org-status').html(statusHtml);

        // Set dates (convert from ISO to Jalali format)
        if (org.created_at) {
            var createdDate = new Date(org.created_at);
            $('#org-created').text(createdDate.toLocaleDateString('fa-IR') + ' ' + createdDate.toLocaleTimeString('fa-IR'));
        }
        if (org.updated_at) {
            var updatedDate = new Date(org.updated_at);
            $('#org-updated').text(updatedDate.toLocaleDateString('fa-IR') + ' ' + updatedDate.toLocaleTimeString('fa-IR'));
        }

        // Set logo
        var logoHtml = '';
        if (org.logo) {
            // Logo path already starts with /storage/, so use it directly
            var logoUrl = org.logo.startsWith('/') ? org.logo : '/' + org.logo;
            logoHtml = '<img src="' + logoUrl + '" ' +
                      'alt="لوگو ' + org.name + '" ' +
                      'class="img-fluid" ' +
                      'style="max-width: 200px; max-height: 200px;">';
        } else {
            logoHtml = '<div class="text-muted">' +
                      '<i class="fa fa-image fa-3x"></i>' +
                      '<p class="mt-2">هیچ لوگویی آپلود نشده است</p>' +
                      '</div>';
        }
        $('#org-logo-container').html(logoHtml);

        // Set form values
        $('#edit-org-name').val(org.name);
        $('#edit-org-address').val(org.address || '');
    });

    // Load user profile
    loadUserProfile();

    // Load packages data
    if (token) {
        $.ajax({
            url: '/api/organization/packages',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.data && response.data.length > 0) {
                    var total = response.data.length;
                    var active = response.data.filter(function(pkg) {
                        return pkg.is_active;
                    }).length;
                    var remainingDays = response.data
                        .filter(function(pkg) { return pkg.is_active; })
                        .reduce(function(sum, pkg) { return sum + (pkg.remaining_days || 0); }, 0);
                    var totalAmount = response.data.reduce(function(sum, pkg) {
                        return sum + (parseFloat(pkg.package_price || 0));
                    }, 0);

                    $('#packages-total').text(total);
                    $('#packages-active').text(active);
                    $('#packages-remaining-days').text(remainingDays);
                    $('#packages-total-amount').text(parseFloat(totalAmount).toLocaleString('fa-IR'));
                }
            },
            error: function(xhr) {
                console.error('Error loading packages:', xhr);
            }
        });
    }

    // Handle organization logo preview
    $('#edit-org-logo').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#org-logo-preview').html('<img src="' + e.target.result + '" class="img-fluid" style="max-width: 200px; max-height: 200px;">');
            };
            reader.readAsDataURL(file);
        }
    });

    // Handle edit organization form submission
    $('#editOrganizationForm').on('submit', function(e) {
        e.preventDefault();

        var formData = new FormData();
        formData.append('name', $('#edit-org-name').val());
        formData.append('address', $('#edit-org-address').val());
        
        var logoFile = $('#edit-org-logo')[0].files[0];
        if (logoFile) {
            formData.append('logo', logoFile);
        }

        $.ajax({
            url: '/api/organization/organization',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'Authorization': 'Bearer ' + token,
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                swal({
                    title: 'موفقیت',
                    text: response.message || 'اطلاعات شرکت با موفقیت بروزرسانی شد',
                    type: 'success',
                    padding: '2em'
                }).then(function() {
                    $('#editOrganizationModal').modal('hide');
                    // Update organization data without reload
                    if (response.data) {
                        currentOrgData = response.data;
                        $('#org-name-profile').text(response.data.name);
                        $('#org-name-table').text(response.data.name);
                        $('#org-address').text(response.data.address || '-');
                        
                        // Update logo
                        var logoHtml = '';
                        if (response.data.logo) {
                            var logoUrl = response.data.logo.startsWith('/') ? response.data.logo : '/' + response.data.logo;
                            logoHtml = '<img src="' + logoUrl + '" ' +
                                      'alt="لوگو ' + response.data.name + '" ' +
                                      'class="img-fluid" ' +
                                      'style="max-width: 200px; max-height: 200px;">';
                        } else {
                            logoHtml = '<div class="text-muted">' +
                                      '<i class="fa fa-image fa-3x"></i>' +
                                      '<p class="mt-2">هیچ لوگویی آپلود نشده است</p>' +
                                      '</div>';
                        }
                        $('#org-logo-container').html(logoHtml);
                    } else {
                        // Reload page if data not in response
                        location.reload();
                    }
                });
            },
            error: function(xhr) {
                var errorMessage = 'خطا در بروزرسانی اطلاعات شرکت';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                swal({
                    title: 'خطا',
                    text: errorMessage,
                    type: 'error',
                    padding: '2em'
                });
            }
        });
    });

    // Handle edit user profile form submission
    $('#editUserProfileForm').on('submit', function(e) {
        e.preventDefault();

        var name = $('#edit-user-name').val();
        var currentPassword = $('#edit-current-password').val();
        var newPassword = $('#edit-new-password').val();
        var newPasswordConfirmation = $('#edit-new-password-confirmation').val();

        if (!name) {
            swal({
                title: 'خطا',
                text: 'لطفا نام را وارد کنید',
                type: 'error',
                padding: '2em'
            });
            return;
        }

        if (newPassword && !currentPassword) {
            swal({
                title: 'خطا',
                text: 'برای تغییر رمز عبور، رمز عبور فعلی را وارد کنید',
                type: 'error',
                padding: '2em'
            });
            return;
        }

        if (newPassword !== newPasswordConfirmation) {
            swal({
                title: 'خطا',
                text: 'رمز عبور جدید و تکرار آن مطابقت ندارند',
                type: 'error',
                padding: '2em'
            });
            return;
        }

        var data = {
            name: name
        };

        if (currentPassword) {
            data.current_password = currentPassword;
        }

        if (newPassword) {
            data.new_password = newPassword;
            data.new_password_confirmation = newPasswordConfirmation;
        }

        $.ajax({
            url: '/api/organization/profile',
            type: 'PUT',
            data: JSON.stringify(data),
            headers: {
                'Authorization': 'Bearer ' + token,
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            success: function(response) {
                swal({
                    title: 'موفقیت',
                    text: response.message || 'پروفایل با موفقیت بروزرسانی شد',
                    type: 'success',
                    padding: '2em'
                }).then(function() {
                    $('#editUserProfileModal').modal('hide');
                    // Clear password fields
                    $('#edit-current-password').val('');
                    $('#edit-new-password').val('');
                    $('#edit-new-password-confirmation').val('');
                    // Reload user profile
                    loadUserProfile();
                });
            },
            error: function(xhr) {
                var errorMessage = 'خطا در بروزرسانی پروفایل';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                swal({
                    title: 'خطا',
                    text: errorMessage,
                    type: 'error',
                    padding: '2em'
                });
            }
        });
    });

    // Reset modals when closed
    $('#editOrganizationModal').on('hidden.bs.modal', function() {
        $('#org-logo-preview').html('');
        $('#edit-org-logo').val('');
        if (currentOrgData) {
            $('#edit-org-name').val(currentOrgData.name);
            $('#edit-org-address').val(currentOrgData.address || '');
        }
    });

    $('#editUserProfileModal').on('hidden.bs.modal', function() {
        $('#edit-current-password').val('');
        $('#edit-new-password').val('');
        $('#edit-new-password-confirmation').val('');
        if (currentUserData) {
            $('#edit-user-name').val(currentUserData.name);
        }
    });
});
</script>
@endsection
