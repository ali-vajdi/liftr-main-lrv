@extends('admin.layout.master')

@section('title', 'مدیریت تعرفه‌ها')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="">مدیریت تعرفه‌ها</h5>
                    </div>
                    <div class="widget-content">
                        @include('admin.components.datatable', [
                            'title' => 'تعرفه‌ها',
                            'apiUrl' => '/api/admin/packages',
                            'createButton' => true,
                            'createButtonText' => 'افزودن تعرفه جدید',
                            'columns' => [
                                ['field' => 'id', 'label' => 'شناسه'],
                                ['field' => 'name', 'label' => 'نام تعرفه'],
                                ['field' => 'duration_label', 'label' => 'مدت زمان'],
                                ['field' => 'formatted_price', 'label' => 'قیمت'],
                                [
                                    'field' => 'use_periods',
                                    'label' => 'دوره‌های پرداخت',
                                    'formatter' => 'function(value) {
                                        return value ? 
                                            `<span class="badge badge-info">فعال</span>` : 
                                            `<span class="badge badge-secondary">غیرفعال</span>`;
                                    }',
                                ],
                                [
                                    'field' => 'is_public',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? 
                                            `<span class="badge badge-success">عمومی</span>` : 
                                            `<span class="badge badge-warning">خصوصی</span>`;
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

        <!-- Modal for adding/editing package -->
        <div class="modal fade" id="packageModal" tabindex="-1" role="dialog" aria-labelledby="packageModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="packageModalLabel">افزودن تعرفه</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="packageForm">
                            <input type="hidden" id="packageId">
                            <div class="form-group">
                                <label for="name">نام تعرفه</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="duration_days">مدت زمان (روز)</label>
                                <input type="number" class="form-control" id="duration_days" name="duration_days" min="1" required>
                            </div>
                            <div class="form-group">
                                <label for="duration_label">برچسب مدت</label>
                                <input type="text" class="form-control" id="duration_label" name="duration_label" placeholder="مثال: 1 ماه، 15 روز، 6 ماه، 1 سال" required>
                            </div>
                            <div class="form-group">
                                <label for="price">قیمت (تومان)</label>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="is_public">وضعیت عمومی</label>
                                <select class="form-control" id="is_public" name="is_public" required>
                                    <option value="true">عمومی</option>
                                    <option value="false">خصوصی</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="use_periods" name="use_periods">
                                    <label class="form-check-label" for="use_periods">
                                        استفاده از دوره‌های پرداخت
                                    </label>
                                    <small class="form-text text-muted d-block mt-1">
                                        در صورت فعال بودن، پکیج به چند دوره تقسیم می‌شود و کاربر باید برای هر دوره جداگانه پرداخت کند
                                    </small>
                                </div>
                            </div>
                            <div class="form-group" id="period_days_group" style="display: none;">
                                <label for="period_days">تعداد روزهای هر دوره</label>
                                <input type="number" class="form-control" id="period_days" name="period_days" min="1" placeholder="مثال: 30 برای دوره‌های ماهانه">
                                <small class="form-text text-muted">
                                    تعداد روزهایی که هر دوره شامل می‌شود (مثال: 30 برای دوره‌های ماهانه)
                                </small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary" id="savePackage">ذخیره</button>
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
                        <h5 class="modal-title" id="detailsModalLabel">جزئیات تعرفه</h5>
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
                                        <th>نام تعرفه</th>
                                        <td id="detailName"></td>
                                    </tr>
                                    <tr>
                                        <th>مدت زمان</th>
                                        <td id="detailDuration"></td>
                                    </tr>
                                    <tr>
                                        <th>قیمت</th>
                                        <td id="detailPrice"></td>
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
                                        <th>آخرین ویرایش</th>
                                        <td id="detailUpdatedAt"></td>
                                    </tr>
                                    <tr>
                                        <th>استفاده از دوره‌ها</th>
                                        <td id="detailUsePeriods"></td>
                                    </tr>
                                    <tr id="detailPeriodDaysRow" style="display: none;">
                                        <th>تعداد روزهای هر دوره</th>
                                        <td id="detailPeriodDays"></td>
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
            let currentPackageId = null;

            // Toggle period_days field based on use_periods checkbox
            $('#use_periods').change(function() {
                if ($(this).is(':checked')) {
                    $('#period_days_group').slideDown();
                    $('#period_days').prop('required', true);
                } else {
                    $('#period_days_group').slideUp();
                    $('#period_days').prop('required', false);
                    $('#period_days').val('');
                }
            });

            // Show package details
            window.onShow = function(id) {
                $.ajax({
                    url: `/api/admin/packages/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const data = response.data;
                        
                        $('#detailId').text(data.id);
                        $('#detailName').text(data.name);
                        $('#detailDuration').text(data.duration_label);
                        $('#detailPrice').text(data.formatted_price);
                        $('#detailStatus').html(data.is_public ? 
                            '<span class="badge badge-success">عمومی</span>' : 
                            '<span class="badge badge-warning">خصوصی</span>'
                        );
                        $('#detailCreatedAt').text(new Date(data.created_at).toLocaleDateString('fa-IR'));
                        $('#detailUpdatedAt').text(new Date(data.updated_at).toLocaleDateString('fa-IR'));
                        $('#detailUsePeriods').html(data.use_periods ? 
                            '<span class="badge badge-info">فعال</span>' : 
                            '<span class="badge badge-secondary">غیرفعال</span>'
                        );
                        if (data.use_periods && data.period_days) {
                            $('#detailPeriodDaysRow').show();
                            $('#detailPeriodDays').text(data.period_days + ' روز');
                        } else {
                            $('#detailPeriodDaysRow').hide();
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

            // Create new package
            $('.create-new-button').click(function() {
                $('#packageModalLabel').text('افزودن تعرفه');
                $('#packageForm')[0].reset();
                $('#packageId').val('');
                $('#use_periods').prop('checked', false);
                $('#period_days_group').hide();
                $('#period_days').prop('required', false);
                $('#packageModal').modal('show');
            });

            // Save package (create or update)
            $('#savePackage').click(function() {
                const id = $('#packageId').val();
                const name = $('#name').val();
                const durationDays = $('#duration_days').val();
                const durationLabel = $('#duration_label').val();
                const price = $('#price').val();
                const isPublic = $('#is_public').val();

                if (!name || !durationDays || !durationLabel || !price) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا تمام فیلدهای الزامی را پر کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const usePeriods = $('#use_periods').is(':checked');
                const periodDays = $('#period_days').val();

                if (usePeriods && !periodDays) {
                    swal({
                        title: 'خطا',
                        text: 'لطفا تعداد روزهای هر دوره را وارد کنید',
                        type: 'error',
                        padding: '2em'
                    });
                    return;
                }

                const data = {
                    name: name,
                    duration_days: parseInt(durationDays),
                    duration_label: durationLabel,
                    price: parseFloat(price),
                    is_public: isPublic,
                    use_periods: usePeriods ? 1 : 0, // Send as 1 or 0 instead of boolean
                    period_days: usePeriods ? parseInt(periodDays) : null
                };

                const url = id ? `/api/admin/packages/${id}` : '/api/admin/packages';
                const method = id ? 'PUT' : 'POST';
                const successMessage = id ? 'تعرفه با موفقیت ویرایش شد' : 'تعرفه با موفقیت ثبت شد';

                $.ajax({
                    url: url,
                    type: method,
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        $('#packageModal').modal('hide');
                        
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

            // Edit package
            window.onEdit = function(id) {
                $.ajax({
                    url: `/api/admin/packages/${id}`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function(response) {
                        const package = response.data;

                        $('#packageModalLabel').text('ویرایش تعرفه');
                        $('#packageId').val(package.id);
                        $('#name').val(package.name);
                        $('#duration_days').val(package.duration_days);
                        $('#duration_label').val(package.duration_label);
                        $('#price').val(package.price);
                        $('#is_public').val(package.is_public ? 'true' : 'false');
                        $('#use_periods').prop('checked', package.use_periods || false);
                        $('#period_days').val(package.period_days || '');
                        
                        // Show/hide period_days field based on use_periods
                        if (package.use_periods) {
                            $('#period_days_group').show();
                            $('#period_days').prop('required', true);
                        } else {
                            $('#period_days_group').hide();
                            $('#period_days').prop('required', false);
                        }

                        $('#packageModal').modal('show');
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

            // Delete package
            window.onDelete = function(id) {
                currentPackageId = id;
                $('#deleteConfirmationModal').modal('show');
            };

            // Confirm delete
            $('#confirmDelete').click(function() {
                if (!currentPackageId) return;

                $.ajax({
                    url: `/api/admin/packages/${currentPackageId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Authorization': 'Bearer ' + localStorage.getItem('admin_token')
                    },
                    success: function() {
                        $('#deleteConfirmationModal').modal('hide');

                        swal({
                            title: 'موفقیت',
                            text: 'تعرفه با موفقیت حذف شد',
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
