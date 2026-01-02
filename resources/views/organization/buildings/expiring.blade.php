@extends('organization.layout.master')

@section('title', 'قراردادهای رو به اتمام')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <h5 class="mb-0">قراردادهای رو به اتمام - <span id="org-name-expiring">...</span></h5>
                </div>
                <div class="widget-content">
                    <div class="alert alert-warning mb-4">
                        <strong>توجه:</strong> این صفحه ساختمان‌هایی را نمایش می‌دهد که تاریخ پایان قرارداد آن‌ها در بازه زمانی انتخابی است.
                    </div>
                    <!-- Custom Filters for Expiring Contracts -->
                    <div class="card mb-4" style="border: 1px solid #e0e6ed; border-radius: 8px;">
                        <div class="card-body">
                            <h6 class="card-title mb-3" style="font-weight: 600; color: #3b3f5c;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 8px;">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                                فیلترها
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label for="periodSelect" class="form-label" style="font-weight: 500; margin-bottom: 8px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 5px;">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        بازه زمانی (روز)
                                    </label>
                                    <select id="periodSelect" class="form-control" style="border-radius: 6px; padding: 10px 15px;">
                                        <option value="7">7 روز</option>
                                        <option value="15">15 روز</option>
                                        <option value="30" selected>30 روز</option>
                                        <option value="60">60 روز</option>
                                        <option value="90">90 روز</option>
                                        <option value="180">180 روز</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label for="statusSelect" class="form-label" style="font-weight: 500; margin-bottom: 8px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 5px;">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="2" x2="12" y2="6"></line>
                                            <line x1="12" y1="18" x2="12" y2="22"></line>
                                            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
                                            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
                                            <line x1="2" y1="12" x2="6" y2="12"></line>
                                            <line x1="18" y1="12" x2="22" y2="12"></line>
                                            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
                                            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
                                        </svg>
                                        وضعیت
                                    </label>
                                    <select id="statusSelect" class="form-control" style="border-radius: 6px; padding: 10px 15px;">
                                        <option value="all" selected>همه</option>
                                        <option value="true">فعال</option>
                                        <option value="false">غیرفعال</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary btn-block" id="applyFiltersBtn" style="border-radius: 6px; padding: 10px 15px; font-weight: 500;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; margin-left: 5px;">
                                            <polyline points="23 4 23 10 17 10"></polyline>
                                            <polyline points="1 20 1 14 7 14"></polyline>
                                            <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                        </svg>
                                        اعمال
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="widget-content widget-content-area br-6">
                        @include('organization.components.datatable', [
                            'title' => 'قراردادهای رو به اتمام',
                            'apiUrl' => '/api/organization/buildings?expiring=true&days=30&status=all',
                            'createButton' => false,
                            'hideDefaultFilters' => true,
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
                                    'field' => 'contract_end_date_jalali',
                                    'label' => 'تاریخ پایان قرارداد',
                                    'formatter' => 'function(value, row) { 
                                        if (!value || !row.contract || !row.contract.contract_end_date) return "-";
                                        if (row.days_remaining !== null && row.days_remaining !== undefined) {
                                            let badgeClass = "badge-success";
                                            if (row.days_remaining <= 7) {
                                                badgeClass = "badge-danger";
                                            } else if (row.days_remaining <= 15) {
                                                badgeClass = "badge-warning";
                                            }
                                            return value + " <span class=\"badge " + badgeClass + "\">" + row.days_remaining + " روز باقی مانده</span>";
                                        }
                                        return value;
                                    }',
                                ],
                                [
                                    'field' => 'status',
                                    'label' => 'وضعیت',
                                    'formatter' => 'function(value) {
                                        return value ? "<span class=\"badge badge-success\">فعال</span>" : "<span class=\"badge badge-danger\">غیرفعال</span>";
                                    }',
                                ],
                            ],
                            'primaryKey' => 'id',
                            'hideDefaultActions' => true,
                            'actions' => '
                                // Show button
                                html += \'<button type="button" class="btn btn-sm btn-info show-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="مشاهده">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>\';
                                html += \'</button>\';
                                
                                // Edit contract button
                                html += \'<button type="button" class="btn btn-sm btn-primary edit-contract-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" title="ویرایش قرارداد">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>\';
                                html += \'</button>\';
                                
                                // Toggle status button
                                const statusIcon = item.status ? "x-circle" : "check-circle";
                                const statusTitle = item.status ? "غیرفعال کردن" : "فعال کردن";
                                html += \'<button type="button" class="btn btn-sm btn-secondary toggle-status-btn mr-1 bs-tooltip" data-id="\' + item.id + \'" data-status="\' + (item.status ? "true" : "false") + \'" title="\' + statusTitle + \'">\';
                                html += \'<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-\' + statusIcon + \'"><circle cx="12" cy="12" r="10"></circle>\';
                                if (item.status) {
                                    html += \'<line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>\';
                                } else {
                                    html += \'<polyline points="9 11 12 14 22 4"></polyline>\';
                                }
                                html += \'</svg></button>\';
                            ',
                            'actionHandlers' => '
                                // Handle show button click
                                $(".show-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShow(id);
                                });
                                
                                // Handle location button click
                                $(".location-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onShowLocation(id);
                                });
                                
                                // Handle edit contract button click
                                $(".edit-contract-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    window.onEditContract(id);
                                });
                                
                                // Handle toggle status button click
                                $(".toggle-status-btn").on("click", function() {
                                    const id = $(this).data("id");
                                    const currentStatus = $(this).data("status") === "true";
                                    window.onToggleStatus(id, currentStatus);
                                });
                            ',
                        ])
                    </div>
                </div>
            </div>
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
                            <th>تاریخ شروع قرارداد</th>
                            <td id="detailServiceStartDate"></td>
                        </tr>
                        <tr>
                            <th>تاریخ پایان قرارداد</th>
                            <td id="detailServiceEndDate"></td>
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<!-- Location Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" role="dialog" aria-labelledby="locationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="locationModalLabel">موقعیت ساختمان/پروژه</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="map" style="height: 400px; width: 100%;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Contract Modal -->
<div class="modal fade" id="addContractModal" tabindex="-1" role="dialog" aria-labelledby="addContractModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addContractModalLabel">افزودن قرارداد جدید</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addContractForm">
                <div class="modal-body" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                    <input type="hidden" id="addContractBuildingId" name="building_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="addManagerName">نام مدیر ساختمان <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="addManagerName" name="manager_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="addManagerPhone">شماره تماس مدیر ساختمان <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="addManagerPhone" name="manager_phone" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="addContractStartDate">تاریخ شروع قرارداد <span class="text-danger">*</span></label>
                                <input data-jdp-only-date="true" type="text" class="form-control" id="addContractStartDate" name="contract_start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="addContractEndDate">تاریخ پایان قرارداد <span class="text-danger">*</span></label>
                                <input data-jdp-only-date="true" type="text" class="form-control" id="addContractEndDate" name="contract_end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="addContractMonthlyAmount">مبلغ ماهیانه قرارداد <span class="text-danger">*</span></label>
                                @include('organization.components.price-input', [
                                    'id' => 'addContractMonthlyAmount',
                                    'name' => 'monthly_amount',
                                    'placeholder' => 'مبلغ ماهیانه را وارد کنید',
                                    'required' => true
                                ])
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="addContractAnnualAmount">مبلغ سالیانه قرارداد</label>
                                @include('organization.components.price-input', [
                                    'id' => 'addContractAnnualAmount',
                                    'name' => 'annual_amount',
                                    'placeholder' => 'محاسبه خودکار',
                                    'disabled' => true
                                ])
                                <small class="form-text text-muted">محاسبه خودکار (مبلغ ماهیانه × 12)</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="addPaymentMethod">نحوه دریافت مبلغ قرارداد <span class="text-danger">*</span></label>
                                <select class="form-control" id="addPaymentMethod" name="payment_method" required>
                                    <option value="">انتخاب کنید</option>
                                    <option value="1">ماهانه بعد از انجام سرویس</option>
                                    <option value="2">2ماه یکبار بعد از انجام سرویس</option>
                                    <option value="3">3 ماه یکبار بعد از انجام سرویس</option>
                                    <option value="4">3 ماه یکبار قبل از انجام سرویس</option>
                                    <option value="5">6ماه یکبار قبل از انجام سرویس</option>
                                    <option value="6">یکساله زمان عقد قرارداد</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="addCustomPaymentMethodFields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="addPaymentTiming">زمان دریافت <span class="text-danger">*</span></label>
                                    <select class="form-control" id="addPaymentTiming" name="payment_timing">
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
                                    <label for="addPaymentFrequencyValue">تعداد سرویس در هر دوره <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="addPaymentFrequencyValue" name="payment_frequency_value" min="1" placeholder="مثال: 3 (برای 3 سرویس در هر دوره)">
                                    <small class="form-text text-muted">تعداد سرویس‌هایی که در یک دوره پرداخت قرار می‌گیرند</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="addPreviousDebt">بدهی قبلی</label>
                                @include('organization.components.price-input', [
                                    'id' => 'addPreviousDebt',
                                    'name' => 'previous_debt',
                                    'value' => '0',
                                    'placeholder' => 'مبلغ بدهی قبلی را وارد کنید'
                                ])
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" id="saveNewContract">ذخیره</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('page-scripts')
<script>
let map = null;

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
                    data.organization_user.name : 
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
                $('#detailServiceStartDate').text(data.contract_start_date_jalali || (data.contract && data.contract.contract_start_date_jalali) || '-');
                $('#detailServiceEndDate').text(data.contract_end_date_jalali || (data.contract && data.contract.contract_end_date_jalali) || '-');
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

                $('#detailsModal').modal('show');
            }
        },
        error: function(xhr) {
            console.error('Error loading building details:', xhr);
        }
    });
};

// Show building location on map
window.onShowLocation = function(id) {
    $.ajax({
        url: `/api/organization/buildings/${id}`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                if (data.selected_latitude && data.selected_longitude) {
                    showMap(data.selected_latitude, data.selected_longitude, data.name);
                } else {
                    swal({
                        title: 'اطلاع',
                        text: 'موقعیت برای این ساختمان تعریف نشده است',
                        type: 'info',
                        padding: '2em'
                    });
                }
            }
        },
        error: function(xhr) {
            console.error('Error loading building location:', xhr);
        }
    });
};

// Show map
function showMap(lat, lng, title) {
    $('#locationModal').modal('show');
    
    // Initialize map after modal is shown
    $('#locationModal').on('shown.bs.modal', function() {
        if (map) {
            map.remove();
        }
        
        map = L.map('map').setView([lat, lng], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        L.marker([lat, lng]).addTo(map)
            .bindPopup(title)
            .openPopup();
    });
}

// Load organization name
getOrganizationData(function(org, error) {
    if (!error && org) {
        $('#org-name-expiring').text(org.name);
    }
});

// Edit contract function - check for active contract and pending services first
window.onEditContract = function(id) {
    // Check for active contract and pending services
    $.ajax({
        url: `/api/organization/buildings/${id}/contracts/check-pending`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                if (response.data.has_active_contract) {
                    // There's an active contract - ask what to do
                    let message = 'قرارداد فعالی برای این ساختمان وجود دارد.';
                    if (response.data.has_pending_services && response.data.pending_count > 0) {
                        message += `<br>این قرارداد دارای ${response.data.pending_count} سرویس در انتظار است.`;
                    }
                    message += '<br>آیا می‌خواهید قرارداد قبلی را تمام شده کنید؟';
                    
                    const options = {
                        title: 'هشدار',
                        html: message,
                        type: 'warning',
                        showCancelButton: true,
                        cancelButtonText: 'انصراف',
                        padding: '2em'
                    };
                    
                    if (response.data.has_pending_services && response.data.pending_count > 0) {
                        // Show options for canceling services
                        options.showDenyButton = true;
                        options.confirmButtonText = 'تمام کردن قرارداد و لغو سرویس‌ها';
                        options.denyButtonText = 'تمام کردن قرارداد بدون لغو سرویس‌ها';
                    } else {
                        options.confirmButtonText = 'بله، تمام کردن قرارداد';
                    }
                    
                    swal(options).then((result) => {
                        if (result.value) {
                            // Finish contract and cancel services
                            finishOldContractAndOpenModal(id, response.data.contract_id, true);
                        } else if (result.dismiss === swal.DismissReason.deny) {
                            // Finish contract without canceling services
                            finishOldContractAndOpenModal(id, response.data.contract_id, false);
                        }
                    });
                } else {
                    // No active contract, proceed directly
                    openAddContractModal(id);
                }
            }
        },
        error: function(xhr) {
            console.error('Error checking pending services:', xhr);
            // If endpoint doesn't exist or error, proceed anyway
            openAddContractModal(id);
        }
    });
};

// Finish old contract and open modal
function finishOldContractAndOpenModal(buildingId, contractId, cancelServices) {
    // This will be handled when creating the new contract
    // Store the decision in a global variable
    window.pendingContractAction = {
        cancelPendingServices: cancelServices
    };
    openAddContractModal(buildingId);
}

// Cancel pending services
function cancelPendingServices(buildingId, contractId, callback) {
    $.ajax({
        url: `/api/organization/buildings/${buildingId}/contracts/${contractId}/cancel-pending-services`,
        type: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                swal({
                    title: 'موفقیت',
                    text: response.message || 'سرویس‌های در انتظار لغو شدند',
                    type: 'success',
                    padding: '2em',
                    timer: 2000
                });
                if (callback) callback();
            }
        },
        error: function(xhr) {
            swal({
                title: 'خطا',
                text: 'خطا در لغو سرویس‌های در انتظار',
                type: 'error',
                padding: '2em'
            });
        }
    });
}

// Open add contract modal
function openAddContractModal(buildingId) {
    $('#addContractBuildingId').val(buildingId);
    const form = $('#addContractForm');
    if (form.length > 0 && form[0]) {
        form[0].reset();
    }
    $('#addCustomPaymentMethodFields').hide();
    
    // Load building data to populate manager fields
    $.ajax({
        url: `/api/organization/buildings/${buildingId}`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                const building = response.data;
                $('#addManagerName').val(building.manager_name || '');
                $('#addManagerPhone').val(building.manager_phone || '');
            }
        },
        error: function(xhr) {
            console.error('Error loading building data:', xhr);
        }
    });
    
    $('#addContractModal').modal('show');
    
    // Initialize date pickers
    jalaliDatepicker.startWatch({
        selector: '#addContractStartDate',
        date: true,
        time: false,
        format: 'YYYY/MM/DD',
        separatorChars: { date: '/' },
        persianDigits: false,
        autoShow: true,
        autoHide: true,
        container: 'body',
        zIndex: 10000,
    });

    jalaliDatepicker.startWatch({
        selector: '#addContractEndDate',
        date: true,
        time: false,
        format: 'YYYY/MM/DD',
        separatorChars: { date: '/' },
        persianDigits: false,
        autoShow: true,
        autoHide: true,
        container: 'body',
        zIndex: 10000,
        maxDate: "attr"
        // No maxDate restriction - allows any future date
    });
}

// Toggle status function
window.onToggleStatus = function(id, currentStatus) {
    const newStatus = !currentStatus;
    
    swal({
        title: 'تأیید تغییر وضعیت',
        text: 'آیا از ' + (newStatus ? 'فعال' : 'غیرفعال') + ' کردن این ساختمان اطمینان دارید؟',
        type: 'warning',
        showCancelButton: true,
        confirmButtonText: 'بله',
        cancelButtonText: 'انصراف',
        padding: '2em'
    }).then((result) => {
        if (result.value) {
            // Get current building data first
            $.ajax({
                url: `/api/organization/buildings/${id}`,
                type: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                },
                success: function(response) {
                    if (response.success) {
                        const building = response.data;
                        // Prepare update data with all required fields
                        const updateData = {
                            name: building.name,
                            manager_name: building.manager_name,
                            manager_phone: building.manager_phone,
                            building_type: building.building_type,
                            province_id: building.province_id,
                            city_id: building.city_id,
                            address: building.address,
                            service_start_date: building.contract_start_date_jalali || (building.contract && building.contract.contract_start_date_jalali) || '',
                            service_end_date: building.contract_end_date_jalali || (building.contract && building.contract.contract_end_date_jalali) || '',
                            status: newStatus ? 'true' : 'false',
                            elevators_count: building.elevators_count || 0,
                            monthly_amount: building.monthly_amount || '',
                            selected_latitude: building.selected_latitude,
                            selected_longitude: building.selected_longitude,
                            _method: 'PUT'
                        };
                        
                        $.ajax({
                            url: `/api/organization/buildings/${id}`,
                            type: 'PUT',
                            data: updateData,
                            headers: {
                                'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                            },
                            success: function(updateResponse) {
                                if (updateResponse.success) {
                                    swal({
                                        title: 'موفقیت',
                                        text: 'وضعیت ساختمان با موفقیت تغییر کرد',
                                        type: 'success',
                                        padding: '2em',
                                        timer: 2000
                                    });
                                    // Reload datatable
                                    $('.refresh-button').click();
                                }
                            },
                            error: function(xhr) {
                                if (xhr.status === 422) {
                                    const errors = xhr.responseJSON.errors;
                                    let errorMessage = 'لطفا اطلاعات درخواستی را به صورت کامل وارد نمایید:\n';
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
                                        text: 'خطا در تغییر وضعیت',
                                        type: 'error',
                                        padding: '2em'
                                    });
                                }
                            }
                        });
                    }
                },
                error: function(xhr) {
                    swal({
                        title: 'خطا',
                        text: 'خطا در بارگذاری اطلاعات ساختمان',
                        type: 'error',
                        padding: '2em'
                    });
                }
            });
        }
    });
};

// Payment method mappings
const paymentMethodMappings = {
    '1': { payment_timing: 'after_service', payment_frequency_value: 1 },
    '2': { payment_timing: 'after_service', payment_frequency_value: 2 },
    '3': { payment_timing: 'after_service', payment_frequency_value: 3 },
    '4': { payment_timing: 'before_service', payment_frequency_value: 3 },
    '5': { payment_timing: 'before_service', payment_frequency_value: 6 },
    '6': { payment_timing: 'before_service', payment_frequency_value: 12 }
};

// Handle payment method change
$('#addPaymentMethod').on('change', function() {
    const value = $(this).val();
    if (value === 'custom') {
        $('#addCustomPaymentMethodFields').show();
        $('#addPaymentTiming').prop('required', true);
        $('#addPaymentFrequencyValue').prop('required', true);
        $('#addPaymentTiming').val('');
        $('#addPaymentFrequencyValue').val('');
    } else if (value && paymentMethodMappings[value]) {
        const mapping = paymentMethodMappings[value];
        $('#addPaymentTiming').val(mapping.payment_timing);
        $('#addPaymentFrequencyValue').val(mapping.payment_frequency_value);
        $('#addCustomPaymentMethodFields').hide();
        $('#addPaymentTiming').prop('required', false);
        $('#addPaymentFrequencyValue').prop('required', false);
    } else {
        $('#addCustomPaymentMethodFields').hide();
        $('#addPaymentTiming').prop('required', false);
        $('#addPaymentFrequencyValue').prop('required', false);
        $('#addPaymentTiming').val('');
        $('#addPaymentFrequencyValue').val('');
    }
});

// Calculate annual amount
$(document).on('input', '#addContractMonthlyAmount_display', function() {
    const monthlyAmount = parseFloat(getPriceInputValue('addContractMonthlyAmount')) || 0;
    const annualAmount = monthlyAmount * 12;
    setPriceInputValue('addContractAnnualAmount', annualAmount.toFixed(2));
});

// Handle save new contract
$('#saveNewContract').on('click', function() {
    const buildingId = $('#addContractBuildingId').val();
    const formData = {
        manager_name: $('#addManagerName').val(),
        manager_phone: $('#addManagerPhone').val(),
        contract_start_date: $('#addContractStartDate').val(),
        contract_end_date: $('#addContractEndDate').val(),
        monthly_amount: getPriceInputValue('addContractMonthlyAmount') || 0,
        payment_method: $('#addPaymentMethod').val(),
        previous_debt: getPriceInputValue('addPreviousDebt') || 0
    };
    
    // Add finish old contract and cancel services flags if set
    if (window.pendingContractAction) {
        formData.finish_old_contract = true;
        formData.cancel_pending_services = window.pendingContractAction.cancelPendingServices;
        // Clear the action after using it
        delete window.pendingContractAction;
    }
    
    // Always include payment fields
    if (formData.payment_method === 'custom') {
        formData.payment_timing = $('#addPaymentTiming').val();
        formData.payment_frequency_value = $('#addPaymentFrequencyValue').val();
        
        if (!formData.payment_timing || !formData.payment_frequency_value) {
            swal({
                title: 'خطا',
                text: 'لطفاً تمام فیلدهای روش پرداخت سفارشی را پر کنید',
                type: 'error',
                padding: '2em'
            });
            return;
        }
    } else if (formData.payment_method && paymentMethodMappings[formData.payment_method]) {
        const mapping = paymentMethodMappings[formData.payment_method];
        formData.payment_timing = mapping.payment_timing;
        formData.payment_frequency_value = mapping.payment_frequency_value;
    }
    
    if (!formData.manager_name || !formData.manager_phone || !formData.contract_start_date || !formData.contract_end_date || !formData.monthly_amount || parseFloat(formData.monthly_amount) <= 0 || !formData.payment_method) {
        swal({
            title: 'خطا',
            text: 'لطفاً تمام فیلدهای الزامی را پر کنید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    $.ajax({
        url: `/api/organization/buildings/${buildingId}/contracts`,
        type: 'POST',
        data: formData,
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
        },
        success: function(response) {
            if (response.success) {
                $('#addContractModal').modal('hide');
                swal({
                    title: 'موفقیت',
                    text: response.message || 'قرارداد با موفقیت ایجاد شد',
                    type: 'success',
                    padding: '2em',
                    timer: 2000
                });
                // Reload datatable
                $('.refresh-button').click();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = 'لطفا اطلاعات درخواستی را به صورت کامل وارد نمایید:\n';
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
                    text: xhr.responseJSON?.message || 'خطا در ایجاد قرارداد',
                    type: 'error',
                    padding: '2em'
                });
            }
        }
    });
});

// Handle filter changes and update datatable
$(document).ready(function() {
    // Store custom parameters globally for datatable to use
    window.expiringFilters = {
        days: 30,
        status: 'all'
    };
    
    // Intercept AJAX calls to add custom parameters (only for buildings API with expiring)
    const originalAjax = $.ajax;
    const expiringAjaxWrapper = function(options) {
        // Only intercept if it's a buildings API call with expiring parameter
        if (options && typeof options === 'object' && options.url) {
            const url = typeof options.url === 'string' ? options.url : '';
            if (url.includes('/api/organization/buildings') && (url.includes('expiring=true') || url.includes('expired=true'))) {
                if (!options.data) options.data = {};
                // Always add expiring and days for expiring page
                if (url.includes('expiring=true')) {
                    options.data.expiring = 'true';
                    options.data.days = window.expiringFilters.days || 30;
                    // Add status only if not 'all'
                    if (window.expiringFilters.status && window.expiringFilters.status !== 'all') {
                        options.data.status = window.expiringFilters.status;
                    }
                }
            }
        }
        return originalAjax.apply(this, arguments);
    };
    
    // Replace $.ajax only for this page
    $.ajax = expiringAjaxWrapper;
    
    // Apply filters button
    $('#applyFiltersBtn').on('click', function() {
        const days = $('#periodSelect').val();
        const status = $('#statusSelect').val();
        
        // Update global filters
        window.expiringFilters.days = days;
        window.expiringFilters.status = status;
        
        // Trigger datatable reload by clicking refresh button
        $('.refresh-button').click();
    });
});
</script>

<!-- Leaflet CSS and JS for map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
@endsection

