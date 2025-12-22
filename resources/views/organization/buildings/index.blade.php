@extends('organization.layout.master')

@section('title', 'مدیریت ساختمان‌ها/پروژه‌ها')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">مدیریت ساختمان‌ها/پروژه‌ها - <span id="org-name-buildings">...</span></h5>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        @include('organization.components.datatable', [
                            'title' => 'ساختمان‌ها/پروژه‌ها',
                            'apiUrl' => '/api/organization/buildings',
                            'createButton' => true,
                            'createButtonText' => 'افزودن ساختمان/پروژه جدید',
                            'columns' => [
                                [
                                    'field' => 'name',
                                    'label' => 'نام ساختمان/پروژه',
                                    'formatter' => 'function(value) { return value; }',
                                ],
                                [
                                    'field' => 'manager_name',
                                    'label' => 'مدیر/نماینده',
                                    'formatter' => 'function(value) { return value; }',
                                ],
                                [
                                    'field' => 'manager_phone',
                                    'label' => 'شماره تماس',
                                    'formatter' => 'function(value) { return value; }',
                                ],
                                [
                                    'field' => 'organization_user',
                                    'label' => 'ایجادکننده',
                                    'formatter' => 'function(value) { 
                                        if (!value || value === null || value === undefined) return "-";
                                        if (value.name) {
                                            return value.name;
                                        }
                                        if (value.first_name && value.last_name) {
                                            return value.first_name + " " + value.last_name;
                                        }
                                        return "-";
                                    }',
                                ],
                                [
                                    'field' => 'building_type',
                                    'label' => 'نوع ساختمان',
                                    'formatter' => 'function(value) { 
                                        const types = {
                                            "residential": "مسکونی",
                                            "office": "اداری", 
                                            "commercial": "تجاری"
                                        };
                                        return types[value] || value;
                                    }',
                                ],
                                [
                                    'field' => 'province',
                                    'label' => 'استان',
                                    'formatter' => 'function(value) { return value ? value.name : "-"; }',
                                ],
                                [
                                    'field' => 'city',
                                    'label' => 'شهر',
                                    'formatter' => 'function(value) { return value ? value.name : "-"; }',
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
                                
                                // Elevators list button (page)
                                html += \'<button type="button" class="btn btn-sm btn-primary elevators-list-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" data-slug="\' + (item.slug || item.id) + \'" title="لیست آسانسورها">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-list"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>\';
                                html += \'</button>\';
                                
                                // Public page button (opens QR code modal)
                                html += \'<button type="button" class="btn btn-sm btn-info public-page-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" data-slug="\' + (item.slug || item.id) + \'" title="آرشیو سرویس ها">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-external-link"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>\';
                                html += \'</button>\';
                                
                                // Dashboard button
                                html += \'<button type="button" class="btn btn-sm btn-primary dashboard-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" data-slug="\' + (item.slug || item.id) + \'" title="داشبورد ساختمان">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-bar-chart-2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>\';
                                html += \'</button>\';
                                
                                // Contracts button
                                html += \'<button type="button" class="btn btn-sm btn-info contracts-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" data-slug="\' + (item.slug || item.id) + \'" title="مدیریت قراردادها">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>\';
                                html += \'</button>\';
                                
                                // Financial Dashboard button
                                html += \'<button type="button" class="btn btn-sm btn-success financial-dashboard-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" data-slug="\' + (item.slug || item.id) + \'" title="داشبورد مالی">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>\';
                                html += \'</button>\';
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(".show-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShow(id);
                                });
                                
                                // Handle elevators list button click (page)
                                $(".elevators-list-btn").on("click", function() {
                                    const slug = $(this).data("slug") || $(this).data("id");
                                    window.location.href = `/buildings/${slug}/elevators`;
                                });
                                
                                // Handle public page button click (opens QR code modal)
                                $(".public-page-btn").on("click", function() {
                                    const slug = $(this).data("slug") || $(this).data("id");
                                    window.onShowQRCode(slug);
                                });
                                
                                // Handle dashboard button click
                                $(".dashboard-btn").on("click", function() {
                                    const slug = $(this).data("slug") || $(this).data("id");
                                    window.location.href = `/buildings/${slug}/dashboard`;
                                });
                                
                                // Handle contracts button click
                                $(".contracts-btn").on("click", function() {
                                    const slug = $(this).data("slug") || $(this).data("id");
                                    window.location.href = `/buildings/${slug}/contracts`;
                                });
                                
                                // Handle financial dashboard button click
                                $(".financial-dashboard-btn").on("click", function() {
                                    const slug = $(this).data("slug") || $(this).data("id");
                                    window.location.href = `/buildings/${slug}/financial-dashboard`;
                                });
                            ',
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="buildingModal" tabindex="-1" role="dialog" aria-labelledby="buildingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buildingModalLabel">افزودن ساختمان/پروژه جدید</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="buildingForm">
                <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">نام ساختمان/پروژه <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manager_name">نام و نام خانوادگی مدیر/نماینده <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="manager_name" name="manager_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manager_phone">شماره تماس مدیر/نماینده <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="manager_phone" name="manager_phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="building_type">نوع ساختمان <span class="text-danger">*</span></label>
                                <select class="form-control" id="building_type" name="building_type" required>
                                    <option value="">انتخاب کنید</option>
                                    <option value="residential">مسکونی</option>
                                    <option value="office">اداری</option>
                                    <option value="commercial">تجاری</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="province_id">استان <span class="text-danger">*</span></label>
                                <select class="form-control" id="province_id" name="province_id" required>
                                    <option value="">انتخاب کنید</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city_id">شهر <span class="text-danger">*</span></label>
                                <select class="form-control" id="city_id" name="city_id" required>
                                    <option value="">ابتدا استان را انتخاب کنید</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="elevators_count">مدیریت آسانسورها <span class="text-danger">*</span></label>
                                <div>
                                    <input type="hidden" id="elevators_count" name="elevators_count" value="0">
                                    <button type="button" class="btn btn-primary" id="manageElevatorsBtn">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-up"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
                                        افزودن آسانسورها (<span id="elevatorsCountDisplay">0</span>)
                                    </button>
                                </div>
                                <small class="form-text text-muted">برای افزودن و مدیریت آسانسورها کلیک کنید. حداقل یک آسانسور الزامی است.</small>
                            </div>
                        </div>
                    </div>
                    <hr id="contractFieldsSeparator">
                    <div id="contractFieldsSection">
                        <h6 class="mb-3">اطلاعات قرارداد</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contract_start_date">تاریخ شروع قرارداد <span class="text-danger">*</span></label>
                                    <input data-jdp-only-date="true" type="text" class="form-control" id="contract_start_date" name="contract_start_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contract_end_date">تاریخ پایان قرارداد <span class="text-danger">*</span></label>
                                    <input data-jdp-only-date="true" type="text" class="form-control" id="contract_end_date" name="contract_end_date" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contract_monthly_amount">مبلغ ماهیانه قرارداد <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="contract_monthly_amount" name="contract_monthly_amount" min="0" step="0.01" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contract_annual_amount">مبلغ سالیانه قرارداد</label>
                                    <input type="number" class="form-control" id="contract_annual_amount" name="contract_annual_amount" readonly disabled>
                                    <small class="form-text text-muted">محاسبه خودکار (مبلغ ماهیانه × 12)</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="payment_method">نحوه دریافت مبلغ قرارداد <span class="text-danger">*</span></label>
                                    <select class="form-control" id="payment_method" name="payment_method" required>
                                        <option value="">انتخاب کنید</option>
                                        <option value="1">ماهانه بعد از انجام سرویس</option>
                                        <option value="2">2ماه یکبار بعد از انجام سرویس</option>
                                        <option value="3">3 ماه یکبار بعد از انجام سرویس</option>
                                        <option value="4">3 ماه یکبار قبل از انجام سرویس</option>
                                        <option value="5">6ماه یکبار قبل از انجام سرویس</option>
                                        <option value="6">یکساله زمان عقد قرارداد</option>
                                        <option value="custom">وارد کردن</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div id="custom_payment_method_fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment_timing">زمان دریافت <span class="text-danger">*</span></label>
                                        <select class="form-control" id="payment_timing" name="payment_timing">
                                            <option value="">انتخاب کنید</option>
                                            <option value="after_service">بعد از انجام سرویس</option>
                                            <option value="before_service">قبل از انجام سرویس</option>
                                            <option value="at_contract_time">زمان عقد قرارداد</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="payment_frequency_value">تعداد سرویس در هر دوره <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="payment_frequency_value" name="payment_frequency_value" min="1" placeholder="مثال: 3 (برای 3 سرویس در هر دوره)">
                                        <small class="form-text text-muted">تعداد سرویس‌هایی که در یک دوره پرداخت قرار می‌گیرند</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="previous_debt">بدهی قبلی</label>
                                    <input type="number" class="form-control" id="previous_debt" name="previous_debt" min="0" step="0.01" placeholder="0.00" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">وضعیت <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="true">فعال</option>
                                    <option value="false">غیرفعال</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">آدرس متنی <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>موقعیت انتخابی</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="selected_latitude" name="selected_latitude" placeholder="عرض جغرافیایی" readonly>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="selected_longitude" name="selected_longitude" placeholder="طول جغرافیایی" readonly>
                            </div>
                        </div>
                        <small class="form-text text-muted">روی نقشه کلیک کنید تا موقعیت را انتخاب کنید</small>
                    </div>
                    <div class="form-group">
                        <label>انتخاب موقعیت روی نقشه</label>
                        <div id="locationMap" style="height: 300px; width: 100%; border: 1px solid #ddd; border-radius: 4px;"></div>
                        <small class="form-text text-muted">نقشه بر اساس شهر انتخابی بارگذاری می‌شود</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" id="saveBuilding">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">جزئیات ساختمان/پروژه</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>نام ساختمان/پروژه</th>
                            <td id="detailName"></td>
                        </tr>
                        <tr>
                            <th>مدیر/نماینده</th>
                            <td id="detailManagerName"></td>
                        </tr>
                        <tr>
                            <th>شماره تماس</th>
                            <td id="detailManagerPhone"></td>
                        </tr>
                        <tr>
                            <th>ایجادکننده</th>
                            <td id="detailCreator"></td>
                        </tr>
                        <tr>
                            <th>نوع ساختمان</th>
                            <td id="detailBuildingType"></td>
                        </tr>
                        <tr>
                            <th>استان</th>
                            <td id="detailProvince"></td>
                        </tr>
                        <tr>
                            <th>شهر</th>
                            <td id="detailCity"></td>
                        </tr>
                        <tr>
                            <th>آدرس</th>
                            <td id="detailAddress"></td>
                        </tr>
                        <tr>
                            <th>موقعیت انتخابی</th>
                            <td id="detailLocation"></td>
                        </tr>
                        <tr>
                            <th>تعداد آسانسورها</th>
                            <td id="detailElevatorsCount"></td>
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
                <div id="detailMapContainer" style="margin-top: 20px; display: none;">
                    <h6 class="mb-3">موقعیت روی نقشه</h6>
                    <div id="detailMap" style="height: 400px; width: 100%; border: 1px solid #ddd; border-radius: 4px;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>


<!-- Elevators Modal (Bulk - kept for backward compatibility) -->
<div class="modal fade" id="elevatorsModal" tabindex="-1" role="dialog" aria-labelledby="elevatorsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="elevatorsModalLabel">مدیریت آسانسورها</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="elevatorsForm">
                <div class="modal-body">
                    <div id="elevatorsContainer">
                        <!-- Elevator forms will be dynamically generated here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" id="saveElevators">ذخیره آسانسورها</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- New Elevators Management Modal (Individual CRUD) -->
<div class="modal fade" id="elevatorsManagementModal" tabindex="-1" role="dialog" aria-labelledby="elevatorsManagementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="elevatorsManagementModalLabel">مدیریت آسانسورها</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <button type="button" class="btn btn-success" id="addElevatorBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        افزودن آسانسور جدید
                    </button>
                </div>
                <div id="elevatorsListContainer">
                    <!-- Elevators list will be dynamically generated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<!-- Elevator Form Modal (Add/Edit) -->
<div class="modal fade" id="elevatorFormModal" tabindex="-1" role="dialog" aria-labelledby="elevatorFormModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="elevatorFormModalLabel">افزودن آسانسور</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="elevatorForm">
                <div class="modal-body">
                    <input type="hidden" id="elevator_id" name="elevator_id">
                    <div class="form-group">
                        <label for="elevator_name">نام آسانسور <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="elevator_name" name="name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="elevator_stops_count">تعداد توقف <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="elevator_stops_count" name="stops_count" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="elevator_capacity">ظرفیت <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="elevator_capacity" name="capacity" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="elevator_status">وضعیت <span class="text-danger">*</span></label>
                        <select class="form-control" id="elevator_status" name="status" required>
                            <option value="true">فعال</option>
                            <option value="false">غیرفعال</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="elevator_description">توضیحات</label>
                        <textarea class="form-control" id="elevator_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary" id="saveElevatorBtn">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">تأیید حذف</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                آیا از حذف این ساختمان/پروژه اطمینان دارید؟
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">حذف</button>
            </div>
        </div>
    </div>
</div>

<!-- Contract Management Modal -->
<div class="modal fade" id="contractModal" tabindex="-1" role="dialog" aria-labelledby="contractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contractModalLabel">مدیریت قرارداد</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="contractForm">
                <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contract_start_date">تاریخ شروع قرارداد <span class="text-danger">*</span></label>
                                <input data-jdp-only-date="true" type="text" class="form-control" id="contract_start_date" name="contract_start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contract_end_date">تاریخ پایان قرارداد <span class="text-danger">*</span></label>
                                <input data-jdp-only-date="true" type="text" class="form-control" id="contract_end_date" name="contract_end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contract_monthly_amount">مبلغ ماهیانه قرارداد <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="contract_monthly_amount" name="monthly_amount" min="0" step="0.01" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="contract_annual_amount">مبلغ سالیانه قرارداد</label>
                                <input type="number" class="form-control" id="contract_annual_amount" name="annual_amount" readonly disabled>
                                <small class="form-text text-muted">محاسبه خودکار (مبلغ ماهیانه × 12)</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="payment_method">نحوه دریافت مبلغ قرارداد <span class="text-danger">*</span></label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="">انتخاب کنید</option>
                                    <option value="1">ماهانه بعد از انجام سرویس</option>
                                    <option value="2">2ماه یکبار بعد از انجام سرویس</option>
                                    <option value="3">3 ماه یکبار بعد از انجام سرویس</option>
                                    <option value="4">3 ماه یکبار قبل از انجام سرویس</option>
                                    <option value="5">6ماه یکبار قبل از انجام سرویس</option>
                                    <option value="6">یکساله زمان عقد قرارداد</option>
                                    <option value="custom">وارد کردن</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="custom_payment_method_fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_timing">زمان دریافت <span class="text-danger">*</span></label>
                                    <select class="form-control" id="payment_timing" name="payment_timing">
                                        <option value="">انتخاب کنید</option>
                                        <option value="after_service">بعد از انجام سرویس</option>
                                        <option value="before_service">قبل از انجام سرویس</option>
                                        <option value="at_contract_time">زمان عقد قرارداد</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="payment_frequency_value">تعداد سرویس در هر دوره <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="payment_frequency_value" name="payment_frequency_value" min="1" placeholder="مثال: 3 (برای 3 سرویس در هر دوره)">
                                    <small class="form-text text-muted">تعداد سرویس‌هایی که در یک دوره پرداخت قرار می‌گیرند</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="previous_debt">بدهی قبلی</label>
                                <input type="number" class="form-control" id="previous_debt" name="previous_debt" min="0" step="0.01" placeholder="0.00" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" id="saveContract">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrcodeModal" tabindex="-1" role="dialog" aria-labelledby="qrcodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrcodeModalLabel">QR Code آرشیو سرویس ها</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div id="qrcode-container" style="display: flex; justify-content: center; align-items: center; padding: 20px; min-height: 300px;">
                    <canvas id="qrcode-canvas"></canvas>
                </div>
                <div class="mt-3">
                    <p class="mb-2"><strong>لینک آرشیو سرویس ها:</strong></p>
                    <div class="input-group">
                        <input type="text" class="form-control" id="public-link-input" readonly>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="copy-link-btn" title="کپی لینک">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                <button type="button" class="btn btn-info" id="open-public-page-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-external-link"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    باز کردن آرشیو سرویس ها
                </button>
                <button type="button" class="btn btn-warning" id="send-sms-btn" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                    ارسال آرشیو سرویس ها از طریق SMS
                </button>
                <button type="button" class="btn btn-primary" id="download-qrcode-btn">دانلود QR Code</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-scripts')
<style>
    /* Make building modal scrollable */
    #buildingModal .modal-dialog {
        max-height: 90vh;
        margin: 1.75rem auto;
    }
    
    #buildingModal .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }
    
    #buildingModal .modal-body {
        overflow-y: auto;
        max-height: calc(90vh - 200px);
        padding: 1rem;
    }
    
    #buildingModal .modal-header,
    #buildingModal .modal-footer {
        flex-shrink: 0;
    }
</style>
<script>
let currentBuildingId = null;
let map = null;
let locationMap = null;
let temporaryElevators = []; // Store elevators temporarily before building is saved

// Handle create button click
$('.create-new-button').click(function() {
    currentBuildingId = null;
    temporaryElevators = []; // Clear temporary elevators
    $('#buildingForm')[0].reset();
    $('#buildingModalLabel').text('افزودن ساختمان/پروژه جدید');
    updateElevatorsCount(0);
    $('#contractFieldsSeparator').show(); // Show contract separator for new building
    $('#contractFieldsSection').show(); // Show contract fields for new building
    $('#buildingModal').modal('show');
    
    // Clear the location map
    if (locationMap) {
        locationMap.remove();
        locationMap = null;
    }
});

$(document).ready(function() {
    // Load provinces on page load
    loadProvinces();
        
    // Initialize JalaliDatePicker for contract_start_date (in building form)
    jalaliDatepicker.startWatch({
        selector: '#contract_start_date',
        date: true,
        time: false,
        hasSecond: false,
        format: 'YYYY/MM/DD',
        showSelectTimeBtnAlways:false,
        separatorChars: {
            date: '/',
            between: ' ',
        },
        persianDigits: false,
        autoShow: true,
        autoHide: true,
        hideAfterChange: true,
        showTodayBtn: true,
        showEmptyBtn: true,
        showCloseBtn: true,
        useDropDownYears: true,
        container: 'body',
        zIndex: 10000,
    });

    // Initialize JalaliDatePicker for contract_end_date (in building form)
    jalaliDatepicker.startWatch({
        selector: '#contract_end_date',
        date: true,
        time: false,
        hasSecond: false,
        showSelectTimeBtnAlways:false,
        format: 'YYYY/MM/DD',
        separatorChars: {
            date: '/',
            between: ' ',
        },
        persianDigits: false,
        autoShow: true,
        autoHide: true,
        hideAfterChange: true,
        showTodayBtn: true,
        showEmptyBtn: true,
        showCloseBtn: true,
        useDropDownYears: true,
        container: 'body',
        zIndex: 10000,
        maxDate: "attr"
        // No maxDate restriction - allows any future date
    });
    
    // Calculate annual amount from monthly amount (in building form)
    $('#contract_monthly_amount').on('input', function() {
        const monthlyAmount = parseFloat($(this).val()) || 0;
        const annualAmount = monthlyAmount * 12;
        $('#contract_annual_amount').val(annualAmount.toFixed(2));
    });
    
    // Payment method mappings
    const paymentMethodMappings = {
        '1': { payment_timing: 'after_service', payment_frequency_value: 1 },
        '2': { payment_timing: 'after_service', payment_frequency_value: 2 },
        '3': { payment_timing: 'after_service', payment_frequency_value: 3 },
        '4': { payment_timing: 'before_service', payment_frequency_value: 3 },
        '5': { payment_timing: 'before_service', payment_frequency_value: 6 },
        '6': { payment_timing: 'before_service', payment_frequency_value: 12 }
    };
    
    // Handle payment method change (in building form)
    $('#payment_method').on('change', function() {
        const value = $(this).val();
        if (value === 'custom') {
            $('#custom_payment_method_fields').show();
            $('#payment_timing').prop('required', true);
            $('#payment_frequency_value').prop('required', true);
            // Clear fields for custom input
            $('#payment_timing').val('');
            $('#payment_frequency_value').val('');
        } else if (value && paymentMethodMappings[value]) {
            // Auto-fill fields for predefined options
            const mapping = paymentMethodMappings[value];
            $('#payment_timing').val(mapping.payment_timing);
            $('#payment_frequency_value').val(mapping.payment_frequency_value);
            // Hide custom fields but keep values filled
            $('#custom_payment_method_fields').hide();
            $('#payment_timing').prop('required', false);
            $('#payment_frequency_value').prop('required', false);
        } else {
            // No selection or invalid option
            $('#custom_payment_method_fields').hide();
            $('#payment_timing').prop('required', false);
            $('#payment_frequency_value').prop('required', false);
            $('#payment_timing').val('');
            $('#payment_frequency_value').val('');
        }
    });

    // Handle province change
    $('#province_id').on('change', function() {
        const provinceId = $(this).val();
        if (provinceId) {
            loadCities(provinceId);
        } else {
            $('#city_id').html('<option value="">ابتدا استان را انتخاب کنید</option>');
        }
    });
    
    // Handle city change
    $('#city_id').on('change', function() {
        const cityId = $(this).val();
        if (cityId) {
            loadCityLocation(cityId);
            initializeLocationMap();
        }
    });
    
    // Handle edit button click (called by datatable component)
    window.onEdit = function(id) {
        currentBuildingId = id;
        
        $.ajax({
            url: `/api/organization/buildings/${id}`,
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#name').val(data.name);
                    $('#manager_name').val(data.manager_name);
                    $('#manager_phone').val(data.manager_phone);
                    $('#building_type').val(data.building_type);
                    $('#province_id').val(data.province_id);
                    $('#address').val(data.address);
                    $('#status').val(data.status ? 'true' : 'false');
                    $('#selected_latitude').val(data.selected_latitude);
                    $('#selected_longitude').val(data.selected_longitude);
                    $('#elevators_count').val(data.elevators_count || 0);
                    updateElevatorsCount(data.elevators_count || 0);
                    
                    // Hide contract fields for edit mode
                    $('#contractFieldsSeparator').hide();
                    $('#contractFieldsSection').hide();
                    $('#contractFieldsSection').find('input, select').prop('required', false);
                    
                    // Load elevators into temporary storage for editing
                    if (data.elevators && data.elevators.length > 0) {
                        temporaryElevators = data.elevators.map(e => ({
                            id: e.id,
                            name: e.name,
                            stops_count: e.stops_count,
                            capacity: e.capacity,
                            status: e.status,
                            description: e.description || null
                        }));
                    } else {
                        temporaryElevators = [];
                    }
                    
                // Load cities for selected province
                if (data.province_id) {
                    loadCities(data.province_id);
                    setTimeout(() => {
                        $('#city_id').val(data.city_id);
                        // Initialize map after city is loaded
                        setTimeout(() => {
                            initializeLocationMap();
                        }, 100);
                    }, 500);
                }
                    
                    $('#buildingModalLabel').text('ویرایش ساختمان/پروژه');
                    $('#buildingModal').modal('show');
                }
            },
            error: function(xhr) {
                console.error('Error loading building for edit:', xhr);
            }
        });
    };

    // Handle delete button click (called by datatable component)
    window.onDelete = function(id) {
        currentBuildingId = id;
        $('#deleteModal').modal('show');
    };

    // Handle save button click
    $('#saveBuilding').on('click', function() {
        const formData = new FormData($('#buildingForm')[0]);
        const data = Object.fromEntries(formData.entries());
        
        // Check if building is being created (not edited)
        const isNewBuilding = !currentBuildingId;
        
        // Validate that at least one elevator is added before creating a new building
        if (isNewBuilding && temporaryElevators.length === 0) {
            swal({
                title: 'خطا',
                text: 'لطفاً حداقل یک آسانسور اضافه کنید. مدیریت آسانسورها الزامی است.',
                type: 'error',
                padding: '2em'
            });
            return;
        }
        
        // Add elevators data if available (from temporary elevators or existing)
        if (temporaryElevators.length > 0) {
            data.elevators = temporaryElevators.map(e => ({
                id: e.id || null,
                name: e.name,
                stops_count: e.stops_count,
                capacity: e.capacity,
                status: e.status ? 'true' : 'false',
                description: e.description || null
            }));
        }
        
        // For new buildings, add contract data
        if (!currentBuildingId) {
            data.contract_monthly_amount = $('#contract_monthly_amount').val();
            // Contract fields are already in the form data
        }
        
        const url = currentBuildingId 
            ? `/api/organization/buildings/${currentBuildingId}`
            : '/api/organization/buildings';
        
        const method = currentBuildingId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            type: method,
            data: data,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
            },
            success: function(response) {
                if (response.success) {
                    const isNewBuilding = !currentBuildingId;
                    const buildingId = response.data.id;
                    currentBuildingId = buildingId; // Update current building ID
                    
                    // Update elevators count if available
                    if (response.data.elevators_count !== undefined) {
                        updateElevatorsCount(response.data.elevators_count);
                    }
                    
                    // Clear temporary elevators after successful save
                    temporaryElevators = [];
                    
                    $('#buildingModal').modal('hide');
                    
                    swal({
                        title: 'موفقیت',
                        text: response.message,
                        type: 'success',
                        padding: '2em'
                    });
                    
                    // Reload datatable
                    if (typeof window.datatableApi !== 'undefined' && window.datatableApi.refresh) {
                        window.datatableApi.refresh();
                    }
                    
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = 'خطاهای اعتبارسنجی:\n';
                    for (const field in errors) {
                        errorMessage += errors[field][0] + '\n';
                    }
                    swal({
                        title: 'خطا',
                        text: errorMessage,
                        type: 'error',
                        padding: '2em'
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
});

// Load provinces
function loadProvinces() {
    $.ajax({
        url: '/api/organization/provinces',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const options = '<option value="">انتخاب کنید</option>';
                response.data.forEach(function(province) {
                    $('#province_id').append(`<option value="${province.id}">${province.name}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading provinces:', xhr);
        }
    });
}

// Load cities by province
function loadCities(provinceId) {
    $.ajax({
        url: '/api/organization/cities-by-province',
        type: 'GET',
        data: { province_id: provinceId },
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                $('#city_id').html('<option value="">انتخاب کنید</option>');
                response.data.forEach(function(city) {
                    $('#city_id').append(`<option value="${city.id}" data-lat="${city.latitude}" data-lng="${city.longitude}">${city.name}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading cities:', xhr);
        }
    });
}

// Load city location
function loadCityLocation(cityId) {
    const selectedOption = $('#city_id option:selected');
    const lat = selectedOption.data('lat');
    const lng = selectedOption.data('lng');
    
    if (lat && lng) {
        $('#selected_latitude').val(lat);
        $('#selected_longitude').val(lng);
    }
}

// Initialize location map for selection
function initializeLocationMap() {
    const lat = parseFloat($('#selected_latitude').val());
    const lng = parseFloat($('#selected_longitude').val());
    
    if (isNaN(lat) || isNaN(lng)) {
        return;
    }
    
    // Remove existing map if it exists
    if (locationMap) {
        locationMap.remove();
    }
    
    // Initialize map
    locationMap = L.map('locationMap').setView([lat, lng], 13);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(locationMap);
    
    // Add marker for current location
    const marker = L.marker([lat, lng]).addTo(locationMap);
    
    // Add click handler to map
    locationMap.on('click', function(e) {
        const newLat = e.latlng.lat;
        const newLng = e.latlng.lng;
        
        // Update coordinate inputs
        $('#selected_latitude').val(newLat.toFixed(8));
        $('#selected_longitude').val(newLng.toFixed(8));
        
        // Update marker position
        marker.setLatLng([newLat, newLng]);
        
        // Update marker popup
        marker.bindPopup(`موقعیت انتخابی: ${newLat.toFixed(6)}, ${newLng.toFixed(6)}`).openPopup();
    });
    
    // Add marker popup for current location
    marker.bindPopup(`موقعیت شهر: ${lat.toFixed(6)}, ${lng.toFixed(6)}`).openPopup();
}

// Show building details
window.onShow = function(id) {
    $.ajax({
        url: `/api/organization/buildings/${id}`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                $('#detailName').text(data.name);
                $('#detailManagerName').text(data.manager_name);
                $('#detailManagerPhone').text(data.manager_phone);
                $('#detailCreator').text(data.organization_user ? 
                    (data.organization_user.name || (data.organization_user.first_name && data.organization_user.last_name ? data.organization_user.first_name + ' ' + data.organization_user.last_name : 'نامشخص')) : 
                    'نامشخص'
                );
                const buildingTypes = {
                    'residential': 'مسکونی',
                    'office': 'اداری',
                    'commercial': 'تجاری'
                };
                $('#detailBuildingType').text(buildingTypes[data.building_type] || data.building_type);
                $('#detailProvince').text(data.province ? data.province.name : '-');
                $('#detailCity').text(data.city ? data.city.name : '-');
                $('#detailAddress').text(data.address);
                $('#detailLocation').text(
                    data.selected_latitude && data.selected_longitude 
                        ? `${data.selected_latitude}, ${data.selected_longitude}`
                        : 'تعریف نشده'
                );
                $('#detailElevatorsCount').text(data.elevators_count || 0);
                $('#detailStatus').html(data.status ? 
                    '<span class="badge badge-success">فعال</span>' : 
                    '<span class="badge badge-danger">غیرفعال</span>'
                );
                $('#detailCreatedAt').text(new Date(data.created_at).toLocaleDateString('fa-IR'));

                // Show/hide map based on location availability
                if (data.selected_latitude && data.selected_longitude) {
                    $('#detailMapContainer').show();
                } else {
                    $('#detailMapContainer').hide();
                }

                $('#detailsModal').modal('show');
                
                // Initialize map after modal is shown (only if location exists)
                if (data.selected_latitude && data.selected_longitude) {
                    $('#detailsModal').one('shown.bs.modal', function() {
                        initializeDetailMap(data.selected_latitude, data.selected_longitude, data.name);
                    });
                }
                
                // Clean up map when modal is hidden
                $('#detailsModal').one('hidden.bs.modal', function() {
                    if (map) {
                        map.remove();
                        map = null;
                    }
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading building details:', xhr);
        }
    });
};

// Show building elevators
window.onShowElevators = function(id) {
    currentBuildingId = id;
    
    // Load building data to get elevators_count
    $.ajax({
        url: `/api/organization/buildings/${id}`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const building = response.data;
                const elevatorsCount = building.elevators_count || 0;
                
                // Load existing elevators
                $.ajax({
                    url: `/api/organization/buildings/${id}/elevators?all=true`,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(elevatorsResponse) {
                        if (elevatorsResponse.success) {
                            const existingElevators = elevatorsResponse.data || [];
                            renderElevatorsForm(elevatorsCount, existingElevators);
                            $('#elevatorsModal').modal('show');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading elevators:', xhr);
                        renderElevatorsForm(elevatorsCount, []);
                        $('#elevatorsModal').modal('show');
                    }
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading building:', xhr);
            swal({
                title: 'خطا',
                text: 'خطا در بارگذاری اطلاعات ساختمان',
                type: 'error',
                padding: '2em'
            });
        }
    });
};

// Render elevators form
function renderElevatorsForm(count, existingElevators) {
    const container = $('#elevatorsContainer');
    container.empty();
    
    if (count === 0) {
        container.html('<div class="alert alert-info">تعداد آسانسورها برای این ساختمان تعریف نشده است. لطفاً ابتدا تعداد آسانسورها را در فرم ویرایش ساختمان مشخص کنید.</div>');
        return;
    }
    
    for (let i = 0; i < count; i++) {
        const elevator = existingElevators[i] || null;
        const elevatorHtml = `
            <div class="card mb-3 elevator-form-item" data-index="${i}">
                <div class="card-header">
                    <h6 class="mb-0">آسانسور ${i + 1}</h6>
                </div>
                <div class="card-body">
                    <input type="hidden" class="elevator-id" value="${elevator ? elevator.id : ''}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>نام آسانسور <span class="text-danger">*</span></label>
                                <input type="text" class="form-control elevator-name" value="${elevator ? elevator.name : ''}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>تعداد توقف <span class="text-danger">*</span></label>
                                <input type="number" class="form-control elevator-stops-count" value="${elevator ? elevator.stops_count : ''}" min="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ظرفیت <span class="text-danger">*</span></label>
                                <input type="number" class="form-control elevator-capacity" value="${elevator ? elevator.capacity : ''}" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>وضعیت <span class="text-danger">*</span></label>
                                <select class="form-control elevator-status" required>
                                    <option value="true" ${elevator && elevator.status ? 'selected' : ''}>فعال</option>
                                    <option value="false" ${elevator && !elevator.status ? 'selected' : ''}>غیرفعال</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>توضیحات</label>
                                <textarea class="form-control elevator-description" rows="3">${elevator ? (elevator.description || '') : ''}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.append(elevatorHtml);
    }
}

// Handle save elevators (Bulk - kept for backward compatibility)
$('#saveElevators').on('click', function() {
    if (!currentBuildingId) {
        swal({
            title: 'خطا',
            text: 'ساختمان نامعتبر است',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    const elevators = [];
    let hasError = false;
    
    $('.elevator-form-item').each(function() {
        if (hasError) return false;
        
        const id = $(this).find('.elevator-id').val();
        const name = $(this).find('.elevator-name').val();
        const stopsCount = $(this).find('.elevator-stops-count').val();
        const capacity = $(this).find('.elevator-capacity').val();
        const status = $(this).find('.elevator-status').val();
        const description = $(this).find('.elevator-description').val();
        
        if (!name || !stopsCount || !capacity) {
            swal({
                title: 'خطا',
                text: 'لطفاً تمام فیلدهای آسانسورها را پر کنید',
                type: 'error',
                padding: '2em'
            });
            hasError = true;
            return false;
        }
        
        elevators.push({
            id: id || null,
            name: name,
            stops_count: parseInt(stopsCount),
            capacity: parseInt(capacity),
            status: status === 'true',
            description: description || null
        });
    });
    
    if (hasError) {
        return;
    }
    
    // Save elevators
    $.ajax({
        url: `/api/organization/buildings/${currentBuildingId}/elevators/bulk`,
        type: 'POST',
        data: JSON.stringify({ elevators: elevators }),
        contentType: 'application/json',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                $('#elevatorsModal').modal('hide');
                swal({
                    title: 'موفقیت',
                    text: response.message,
                    type: 'success',
                    padding: '2em'
                });
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'خطاهای اعتبارسنجی:\n';
                for (const field in errors) {
                    errorMessage += errors[field][0] + '\n';
                }
                swal({
                    title: 'خطا',
                    text: errorMessage,
                    type: 'error',
                    padding: '2em'
                });
            } else {
                swal({
                    title: 'خطا',
                    text: 'خطا در ذخیره اطلاعات آسانسورها',
                    type: 'error',
                    padding: '2em'
                });
            }
        }
    });
});

// ========== New Elevators Management Functions ==========

// Handle manage elevators button click (from building form)
$('#manageElevatorsBtn').on('click', function() {
    openElevatorsManagementModal();
});

// Open elevators management modal
function openElevatorsManagementModal() {
    if (currentBuildingId) {
        // Building is saved, load from API
        loadElevatorsList();
    } else {
        // Building not saved yet, use temporary elevators
        renderElevatorsList(temporaryElevators);
        updateElevatorsCount(temporaryElevators.length);
    }
    $('#elevatorsManagementModal').modal('show');
}

// Load elevators list
function loadElevatorsList() {
    if (!currentBuildingId) {
        // Building not saved, use temporary elevators
        renderElevatorsList(temporaryElevators);
        updateElevatorsCount(temporaryElevators.length);
        return;
    }
    
    $.ajax({
        url: `/api/organization/buildings/${currentBuildingId}/elevators?all=true`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const elevators = response.data || [];
                renderElevatorsList(elevators);
                updateElevatorsCount(elevators.length);
                // Also update temporary elevators for consistency
                temporaryElevators = elevators.map(e => ({
                    id: e.id,
                    name: e.name,
                    stops_count: e.stops_count,
                    capacity: e.capacity,
                    status: e.status,
                    description: e.description
                }));
            }
        },
        error: function(xhr) {
            console.error('Error loading elevators:', xhr);
            renderElevatorsList([]);
            updateElevatorsCount(0);
        }
    });
}

// Render elevators list
function renderElevatorsList(elevators) {
    const container = $('#elevatorsListContainer');
    container.empty();
    
    if (elevators.length === 0) {
        container.html('<div class="alert alert-info">هیچ آسانسوری تعریف نشده است. برای افزودن آسانسور جدید روی دکمه "افزودن آسانسور جدید" کلیک کنید.</div>');
        return;
    }
    
    elevators.forEach(function(elevator, index) {
        const elevatorId = elevator.id || elevator.temp_id;
        const elevatorHtml = `
            <div class="card mb-3 elevator-item" data-id="${elevatorId}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="mb-1">${elevator.name}</h6>
                            <p class="mb-1 text-muted">
                                <small>تعداد توقف: ${elevator.stops_count} | ظرفیت: ${elevator.capacity}</small>
                            </p>
                            <p class="mb-0">
                                <span class="badge ${elevator.status ? 'badge-success' : 'badge-danger'}">
                                    ${elevator.status ? 'فعال' : 'غیرفعال'}
                                </span>
                            </p>
                            ${elevator.description ? `<p class="mt-2 mb-0"><small>${elevator.description}</small></p>` : ''}
                        </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class="btn btn-sm btn-primary edit-elevator-btn mr-2" data-id="${elevatorId}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                ویرایش
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-elevator-btn" data-id="${elevatorId}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                حذف
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.append(elevatorHtml);
    });
    
    // Attach event handlers
    $('.edit-elevator-btn').on('click', function() {
        const elevatorId = $(this).data('id');
        editElevator(elevatorId);
    });
    
    $('.delete-elevator-btn').on('click', function() {
        const elevatorId = $(this).data('id');
        deleteElevator(elevatorId);
    });
}

// Update elevators count display
function updateElevatorsCount(count) {
    $('#elevatorsCountDisplay').text(count);
    $('#elevators_count').val(count);
}

// Handle add elevator button
$('#addElevatorBtn').on('click', function() {
    openElevatorFormModal();
});

// Open elevator form modal (for add/edit)
function openElevatorFormModal(elevatorId = null, elevatorData = null) {
    $('#elevatorForm')[0].reset();
    $('#elevator_id').val(elevatorId || '');
    
    if (elevatorId) {
        $('#elevatorFormModalLabel').text('ویرایش آسانسور');
        
        if (!currentBuildingId && elevatorData) {
            // Building not saved, use provided data
            $('#elevator_name').val(elevatorData.name);
            $('#elevator_stops_count').val(elevatorData.stops_count);
            $('#elevator_capacity').val(elevatorData.capacity);
            $('#elevator_status').val(elevatorData.status ? 'true' : 'false');
            $('#elevator_description').val(elevatorData.description || '');
        } else if (currentBuildingId) {
            // Load elevator data from API
            $.ajax({
                url: `/api/organization/buildings/${currentBuildingId}/elevators/${elevatorId}`,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                },
                success: function(response) {
                    if (response.success) {
                        const elevator = response.data;
                        $('#elevator_name').val(elevator.name);
                        $('#elevator_stops_count').val(elevator.stops_count);
                        $('#elevator_capacity').val(elevator.capacity);
                        $('#elevator_status').val(elevator.status ? 'true' : 'false');
                        $('#elevator_description').val(elevator.description || '');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading elevator:', xhr);
                    swal({
                        title: 'خطا',
                        text: 'خطا در بارگذاری اطلاعات آسانسور',
                        type: 'error',
                        padding: '2em'
                    });
                }
            });
        }
    } else {
        $('#elevatorFormModalLabel').text('افزودن آسانسور');
    }
    
    $('#elevatorFormModal').modal('show');
}

// Edit elevator
function editElevator(elevatorId) {
    if (!currentBuildingId) {
        // Building not saved, find in temporary elevators
        const elevator = temporaryElevators.find(e => e.temp_id === elevatorId || e.id === elevatorId);
        if (elevator) {
            openElevatorFormModal(elevatorId, elevator);
        }
    } else {
        openElevatorFormModal(elevatorId);
    }
}

// Delete elevator
function deleteElevator(elevatorId) {
    swal({
        title: 'تأیید حذف',
        text: 'آیا از حذف این آسانسور اطمینان دارید؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله، حذف کن',
        cancelButtonText: 'انصراف',
        padding: '2em'
    }).then((result) => {
        if (result.value) {
            if (!currentBuildingId) {
                // Building not saved, remove from temporary elevators
                temporaryElevators = temporaryElevators.filter(e => e.temp_id !== elevatorId && e.id !== elevatorId);
                loadElevatorsList();
                swal({
                    title: 'موفقیت',
                    text: 'آسانسور حذف شد',
                    type: 'success',
                    padding: '2em',
                    timer: 2000
                });
            } else {
                $.ajax({
                    url: `/api/organization/buildings/${currentBuildingId}/elevators/${elevatorId}`,
                    type: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                    },
                    success: function(response) {
                        if (response.success) {
                            swal({
                                title: 'موفقیت',
                                text: response.message,
                                type: 'success',
                                padding: '2em',
                                timer: 2000
                            });
                            loadElevatorsList();
                        }
                    },
                    error: function(xhr) {
                        swal({
                            title: 'خطا',
                            text: 'خطا در حذف آسانسور',
                            type: 'error',
                            padding: '2em'
                        });
                    }
                });
            }
        }
    });
}

// Handle elevator form submit
$('#elevatorForm').on('submit', function(e) {
    e.preventDefault();
    
    const elevatorId = $('#elevator_id').val();
    const formData = {
        name: $('#elevator_name').val(),
        stops_count: parseInt($('#elevator_stops_count').val()),
        capacity: parseInt($('#elevator_capacity').val()),
        status: $('#elevator_status').val() === 'true',
        description: $('#elevator_description').val() || null
    };
    
    if (!currentBuildingId) {
        // Building not saved yet, store in temporary elevators
        if (elevatorId) {
            // Update existing temporary elevator
            const index = temporaryElevators.findIndex(e => e.temp_id === elevatorId || e.id === elevatorId);
            if (index !== -1) {
                temporaryElevators[index] = { ...temporaryElevators[index], ...formData };
            }
        } else {
            // Add new temporary elevator
            const tempId = 'temp_' + Date.now();
            temporaryElevators.push({ ...formData, temp_id: tempId });
        }
        
        $('#elevatorFormModal').modal('hide');
        swal({
            title: 'موفقیت',
            text: 'آسانسور ذخیره شد. پس از ذخیره ساختمان، آسانسورها نیز ذخیره خواهند شد.',
            type: 'success',
            padding: '2em',
            timer: 2000
        });
        loadElevatorsList();
        return;
    }
    
    const url = elevatorId 
        ? `/api/organization/buildings/${currentBuildingId}/elevators/${elevatorId}`
        : `/api/organization/buildings/${currentBuildingId}/elevators`;
    const method = elevatorId ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        type: method,
        data: formData,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                $('#elevatorFormModal').modal('hide');
                swal({
                    title: 'موفقیت',
                    text: response.message,
                    type: 'success',
                    padding: '2em',
                    timer: 2000
                });
                loadElevatorsList();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'خطاهای اعتبارسنجی:\n';
                for (const field in errors) {
                    errorMessage += errors[field][0] + '\n';
                }
                swal({
                    title: 'خطا',
                    text: errorMessage,
                    type: 'error',
                    padding: '2em'
                });
            } else {
                swal({
                    title: 'خطا',
                    text: 'خطا در ذخیره اطلاعات آسانسور',
                    type: 'error',
                    padding: '2em'
                });
            }
        }
    });
});

// Initialize map in details modal
function initializeDetailMap(lat, lng, title) {
    // Remove existing map if it exists
    if (map) {
        map.remove();
    }
    
    // Initialize map
    map = L.map('detailMap').setView([lat, lng], 13);
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add marker
    L.marker([lat, lng]).addTo(map)
        .bindPopup(title)
        .openPopup();
}


// Handle delete confirmation
$('#confirmDelete').on('click', function() {
    if (currentBuildingId) {
        $.ajax({
            url: `/api/organization/buildings/${currentBuildingId}`,
            type: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteModal').modal('hide');
                    // Reload datatable
                    if (typeof window.datatableApi !== 'undefined' && window.datatableApi.refresh) {
                        window.datatableApi.refresh();
                    }
                    // Show success message
                    swal({
                        title: 'موفقیت',
                        text: response.message,
                        type: 'success',
                        padding: '2em'
                    });
                }
            },
            error: function(xhr) {
                swal({
                    title: 'خطا',
                    text: 'خطا در حذف اطلاعات',
                    type: 'error',
                    padding: '2em'
                });
            }
        });
    }
});

// Show QR Code
window.onShowQRCode = function(slug) {
    const publicUrl = `${window.location.origin}/buildings/${slug}/services`;
    
    // Set the link input
    $('#public-link-input').val(publicUrl);
    
    // Clear previous QR code
    const canvas = document.getElementById('qrcode-canvas');
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Generate QR code
    if (typeof QRCode !== 'undefined') {
        QRCode.toCanvas(canvas, publicUrl, {
            width: 300,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        }, function (error) {
            if (error) {
                console.error('Error generating QR code:', error);
                // Fallback: use img tag with API
                const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(publicUrl)}`;
                $('#qrcode-container').html(`<img src="${qrApiUrl}" alt="QR Code" class="img-fluid">`);
            }
        });
    } else {
        // Fallback: use img tag with API
        const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=${encodeURIComponent(publicUrl)}`;
        $('#qrcode-container').html(`<img src="${qrApiUrl}" alt="QR Code" class="img-fluid">`);
    }
    
    // Show modal
    $('#qrcodeModal').modal('show');
};

// Copy link to clipboard
$('#copy-link-btn').on('click', function() {
    const input = document.getElementById('public-link-input');
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        document.execCommand('copy');
        swal({
            title: 'موفقیت',
            text: 'لینک با موفقیت کپی شد',
            type: 'success',
            padding: '2em',
            timer: 2000
        });
    } catch (err) {
        swal({
            title: 'خطا',
            text: 'خطا در کپی کردن لینک',
            type: 'error',
            padding: '2em'
        });
    }
});

// Open public page
$('#open-public-page-btn').on('click', function() {
    const publicUrl = $('#public-link-input').val();
    if (publicUrl) {
        window.open(publicUrl, '_blank');
    }
});

// Download QR Code
$('#download-qrcode-btn').on('click', function() {
    const canvas = document.getElementById('qrcode-canvas');
    if (canvas && canvas.width > 0) {
        const url = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = 'building-qrcode.png';
        link.href = url;
        link.click();
    } else {
        // Fallback for API-generated QR codes
        const img = document.querySelector('#qrcode-container img');
        if (img) {
            const link = document.createElement('a');
            link.download = 'building-qrcode.png';
            link.href = img.src;
            link.click();
        } else {
            swal({
                title: 'خطا',
                text: 'QR Code در دسترس نیست',
                type: 'error',
                padding: '2em'
            });
        }
    }
});

// Load organization name
getOrganizationData(function(org, error) {
    if (!error && org) {
        $('#org-name-buildings').text(org.name);
    }
});

// ========== Contract Management Functions ==========

// Handle contract management button click
$('#manageContractBtn').on('click', function() {
    if (!currentBuildingId) {
        swal({
            title: 'خطا',
            text: 'لطفاً ابتدا ساختمان را ذخیره کنید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    loadContractData();
    $('#contractModal').modal('show');
});

// Load contract data
function loadContractData() {
    if (!currentBuildingId) return;
    
    $.ajax({
        url: `/api/organization/buildings/${currentBuildingId}/contract`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success && response.data) {
                const contract = response.data;
                $('#contract_start_date').val(contract.contract_start_date_jalali || '');
                $('#contract_end_date').val(contract.contract_end_date_jalali || '');
                $('#contract_monthly_amount').val(contract.monthly_amount || '');
                $('#contract_annual_amount').val(contract.annual_amount || '');
                $('#payment_method').val(contract.payment_method || '');
                $('#previous_debt').val(contract.previous_debt || 0);
                
                // Handle payment method - auto-fill fields based on selection
                if (contract.payment_method === 'custom') {
                    $('#custom_payment_method_fields').show();
                    $('#payment_timing').val(contract.payment_timing || '');
                    $('#payment_frequency_value').val(contract.payment_frequency_value || '');
                    $('#payment_timing').prop('required', true);
                    $('#payment_frequency_value').prop('required', true);
                } else if (contract.payment_method && paymentMethodMappingsContract[contract.payment_method]) {
                    // Auto-fill from mapping for predefined options
                    const mapping = paymentMethodMappingsContract[contract.payment_method];
                    $('#payment_timing').val(mapping.payment_timing);
                    $('#payment_frequency_value').val(mapping.payment_frequency_value);
                    $('#custom_payment_method_fields').hide();
                    $('#payment_timing').prop('required', false);
                    $('#payment_frequency_value').prop('required', false);
                } else {
                    $('#custom_payment_method_fields').hide();
                    $('#payment_timing').prop('required', false);
                    $('#payment_frequency_value').prop('required', false);
                }
            } else {
                // No contract exists, reset form
                $('#contractForm')[0].reset();
                $('#custom_payment_method_fields').hide();
            }
        },
        error: function(xhr) {
            if (xhr.status === 404) {
                // No contract exists, reset form
                $('#contractForm')[0].reset();
                $('#custom_payment_method_fields').hide();
            } else {
                console.error('Error loading contract:', xhr);
                swal({
                    title: 'خطا',
                    text: 'خطا در بارگذاری اطلاعات قرارداد',
                    type: 'error',
                    padding: '2em'
                });
            }
        }
    });
}

// Handle payment method change
// Payment method mappings (for contract edit modal)
const paymentMethodMappingsContract = {
    '1': { payment_timing: 'after_service', payment_frequency_value: 1 },
    '2': { payment_timing: 'after_service', payment_frequency_value: 2 },
    '3': { payment_timing: 'after_service', payment_frequency_value: 3 },
    '4': { payment_timing: 'before_service', payment_frequency_value: 3 },
    '5': { payment_timing: 'before_service', payment_frequency_value: 6 },
    '6': { payment_timing: 'before_service', payment_frequency_value: 12 }
};

$('#payment_method').on('change', function() {
    const value = $(this).val();
    if (value === 'custom') {
        $('#custom_payment_method_fields').show();
        $('#payment_timing').prop('required', true);
        $('#payment_frequency_value').prop('required', true);
        // Clear fields for custom input
        $('#payment_timing').val('');
        $('#payment_frequency_value').val('');
    } else if (value && paymentMethodMappingsContract[value]) {
        // Auto-fill fields for predefined options
        const mapping = paymentMethodMappingsContract[value];
        $('#payment_timing').val(mapping.payment_timing);
        $('#payment_frequency_value').val(mapping.payment_frequency_value);
        // Hide custom fields but keep values filled
        $('#custom_payment_method_fields').hide();
        $('#payment_timing').prop('required', false);
        $('#payment_frequency_value').prop('required', false);
    } else {
        // No selection or invalid option
        $('#custom_payment_method_fields').hide();
        $('#payment_timing').prop('required', false);
        $('#payment_frequency_value').prop('required', false);
        $('#payment_timing').val('');
        $('#payment_frequency_value').val('');
    }
});

// Calculate annual amount from monthly amount
$('#contract_monthly_amount').on('input', function() {
    const monthlyAmount = parseFloat($(this).val()) || 0;
    const annualAmount = monthlyAmount * 12;
    $('#contract_annual_amount').val(annualAmount.toFixed(2));
});

// Handle save contract
$('#saveContract').on('click', function() {
    if (!currentBuildingId) {
        swal({
            title: 'خطا',
            text: 'ساختمان نامعتبر است',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    const formData = {
        contract_start_date: $('#contract_start_date').val(),
        contract_end_date: $('#contract_end_date').val(),
        monthly_amount: $('#contract_monthly_amount').val(),
        payment_method: $('#payment_method').val(),
        previous_debt: $('#previous_debt').val() || 0
    };
    
    // Add custom payment method fields if selected
    if (formData.payment_method === 'custom') {
        formData.payment_timing = $('#payment_timing').val();
        formData.payment_frequency_value = $('#payment_frequency_value').val();
        
        // Validate custom fields
        if (!formData.payment_timing || !formData.payment_frequency_value) {
            swal({
                title: 'خطا',
                text: 'لطفاً تمام فیلدهای روش پرداخت سفارشی را پر کنید',
                type: 'error',
                padding: '2em'
            });
            return;
        }
    }
    
    // Validate required fields
    if (!formData.contract_start_date || !formData.contract_end_date || !formData.monthly_amount || !formData.payment_method) {
        swal({
            title: 'خطا',
            text: 'لطفاً تمام فیلدهای الزامی را پر کنید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    $.ajax({
        url: `/api/organization/buildings/${currentBuildingId}/contract`,
        type: 'POST',
        data: formData,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                $('#contractModal').modal('hide');
                swal({
                    title: 'موفقیت',
                    text: response.message,
                    type: 'success',
                    padding: '2em'
                });
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'خطاهای اعتبارسنجی:\n';
                for (const field in errors) {
                    errorMessage += errors[field][0] + '\n';
                }
                swal({
                    title: 'خطا',
                    text: errorMessage,
                    type: 'error',
                    padding: '2em'
                });
            } else {
                swal({
                    title: 'خطا',
                    text: 'خطا در ذخیره قرارداد',
                    type: 'error',
                    padding: '2em'
                });
            }
        }
    });
});
</script>

<!-- Leaflet CSS and JS for map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
@endsection

