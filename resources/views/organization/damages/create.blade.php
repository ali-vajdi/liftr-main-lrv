@extends('organization.layout.master')

@section('title', 'فرم ثبت خرابی')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">فرم ثبت خرابی</h5>
                        <div>
                            <a href="{{ route('organization.damages.index') }}" class="btn btn-secondary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                                بازگشت به لیست
                            </a>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <form id="damageForm">
                        <div class="row mb-4 mt-2">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="building_id">ساختمان <span class="text-danger">*</span></label>
                                    <select class="form-control" id="building_id" name="building_id" required>
                                        <option value="">انتخاب کنید...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="technician_id">تکنسین</label>
                                    <select class="form-control" id="technician_id" name="technician_id">
                                        <option value="">انتخاب کنید...</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="report_date">تاریخ اعلام <span class="text-danger">*</span></label>
                                    <input data-jdp type="text" class="form-control" id="report_date" name="report_date" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="visit_date">تاریخ مراجعه</label>
                                    <input data-jdp type="text" class="form-control" id="visit_date" name="visit_date">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label for="description">توضیحات <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="5" required placeholder="توضیحات خرابی را وارد کنید..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0 mt-4 mx-0">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                ذخیره گزارش
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<style>
/* Fix horizontal spacing issues - apply consistent margins to all sections */
#damageForm {
    padding-left: 15px;
    padding-right: 15px;
}

#damageForm > * {
    margin-left: 0;
    margin-right: 0;
}

#damageForm .row {
    margin-left: 0;
    margin-right: 0;
}

#damageForm .row > [class*="col-"] {
    padding-left: 10px;
    padding-right: 10px;
}

#damageForm .form-group {
    margin-left: 0;
    margin-right: 0;
}
</style>
<script>
// Load buildings
function loadBuildings() {
    const token = localStorage.getItem('organization_token');
    if (!token) return;

    $.ajax({
        url: '/api/organization/damages/buildings',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                const select = $('#building_id');
                select.empty();
                select.append('<option value="">انتخاب کنید...</option>');
                (response.data || []).forEach(function(building) {
                    select.append(`<option value="${building.id}">${building.name}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading buildings:', xhr);
        }
    });
}

// Load technicians
function loadTechnicians() {
    const token = localStorage.getItem('organization_token');
    if (!token) return;

    $.ajax({
        url: '/api/organization/services/technicians',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                const select = $('#technician_id');
                select.empty();
                select.append('<option value="">انتخاب کنید...</option>');
                (response.data || []).forEach(function(tech) {
                    select.append(`<option value="${tech.id}">${tech.name} - ${tech.phone_number}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading technicians:', xhr);
        }
    });
}

// Initialize Jalali DatePicker
$(document).ready(function() {
    loadBuildings();
    loadTechnicians();
    
    jalaliDatepicker.startWatch({
        selector: '#report_date',
        date: true,
        time: true,
        hasSecond: false,
        showSelectTimeBtnAlways: false,
        format: 'YYYY/MM/DD HH:mm',
        separatorChars: {
            date: '/',
            between: ' ',
            time: ':'
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
    
    jalaliDatepicker.startWatch({
        selector: '#visit_date',
        date: true,
        time: true,
        hasSecond: false,
        showSelectTimeBtnAlways: false,
        format: 'YYYY/MM/DD HH:mm',
        separatorChars: {
            date: '/',
            between: ' ',
            time: ':'
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
});

// Handle form submission
$('#damageForm').on('submit', function(e) {
    e.preventDefault();
    
    const buildingId = $('#building_id').val();
    const technicianId = $('#technician_id').val();
    const reportDate = $('#report_date').val();
    const visitDate = $('#visit_date').val();
    const description = $('#description').val();
    
    if (!buildingId) {
        swal({
            title: 'خطا',
            text: 'لطفاً ساختمان را انتخاب کنید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    if (!description) {
        swal({
            title: 'خطا',
            text: 'لطفاً توضیحات را وارد کنید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    if (!reportDate) {
        swal({
            title: 'خطا',
            text: 'لطفاً تاریخ اعلام را وارد کنید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    const formData = {
        building_id: buildingId,
        technician_id: technicianId || null,
        report_date: reportDate,
        visit_date: visitDate || null,
        description: description
    };
    
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
    
    // Disable submit button
    const submitBtn = $('#damageForm button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true);
    submitBtn.html('<span class="spinner-border spinner-border-sm mr-2"></span>در حال ذخیره...');
    
    $.ajax({
        url: '/api/organization/damages',
        type: 'POST',
        data: formData,
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                swal({
                    title: 'موفقیت',
                    text: response.message || 'گزارش خرابی با موفقیت ثبت شد',
                    type: 'success',
                    padding: '2em',
                    timer: 2000
                }).then(function() {
                    window.location.href = '{{ route("organization.damages.index") }}';
                });
            } else {
                swal({
                    title: 'خطا',
                    text: response.message || 'خطا در ثبت گزارش خرابی',
                    type: 'error',
                    padding: '2em'
                });
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            let errorMessage = 'خطا در ثبت گزارش خرابی';
            if (response?.errors) {
                const errors = Object.values(response.errors).flat();
                errorMessage = errors.join('\n');
            } else if (response?.message) {
                errorMessage = response.message;
            }
            swal({
                title: 'خطا',
                text: errorMessage,
                type: 'error',
                padding: '2em'
            });
            submitBtn.prop('disabled', false);
            submitBtn.html(originalText);
        }
    });
});
</script>
@endsection

