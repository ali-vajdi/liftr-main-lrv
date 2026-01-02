@extends('organization.layout.master')

@section('title', 'صدور فاکتور')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">صدور فاکتور</h5>
                        <div>
                            <a href="{{ route('organization.financial.invoices.index') }}" class="btn btn-secondary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                                بازگشت به لیست فاکتورها
                            </a>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <form id="invoiceForm">
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
                                    <label for="invoice_date">تاریخ فاکتور</label>
                                    <input data-jdp-only-date="true" type="text" class="form-control" id="invoice_date" name="invoice_date">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 mx-0">

                        <div class="d-flex justify-content-between align-items-center mb-3 mx-0">
                            <h6 class="mb-0">فاکتور</h6>
                            <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                افزودن سطر
                            </button>
                        </div>

                        <div class="table-responsive mb-4 mx-0">
                            <table class="table table-bordered mb-0" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 40%;">شرح <span class="text-danger">*</span></th>
                                        <th style="width: 15%;">تعداد <span class="text-danger">*</span></th>
                                        <th style="width: 20%;">قیمت واحد <span class="text-danger">*</span></th>
                                        <th style="width: 20%;">جمع</th>
                                        <th style="width: 5%;">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Items will be added here dynamically -->
                                </tbody>
                            </table>
                        </div>

                        <hr class="my-4 mx-0">

                        <div class="row mb-3 mx-0">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="discount">تخفیف</label>
                                    @include('organization.components.price-input', [
                                        'id' => 'discount',
                                        'name' => 'discount',
                                        'placeholder' => 'مبلغ تخفیف را وارد کنید',
                                        'required' => false
                                    ])
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="tax_percentage">مالیات (درصد)</label>
                                    <input type="number" class="form-control" id="tax_percentage" name="tax_percentage" min="0" max="100" step="0.01" placeholder="درصد مالیات" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4 mx-0">
                            <div class="col-md-6 offset-md-6">
                                <table class="table mb-0">
                                    <tr>
                                        <td><strong>جمع کل:</strong></td>
                                        <td id="subtotalDisplay">0 ریال</td>
                                    </tr>
                                    <tr>
                                        <td><strong>تخفیف:</strong></td>
                                        <td id="discountDisplay">0 ریال</td>
                                    </tr>
                                    <tr>
                                        <td><strong>مالیات:</strong></td>
                                        <td id="taxDisplay">0 ریال (0%)</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td><strong>قیمت کل:</strong></td>
                                        <td id="totalDisplay"><strong>0 ریال</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="form-group mb-0 mt-4 mx-0">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                ذخیره فاکتور
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
.price-input-wrapper {
    position: relative;
}

.price-input-wrapper .input-group-text {
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    border-left: none;
    color: #495057;
    font-weight: 500;
}

.price-input-wrapper .price-display {
    border-right: none;
    direction: ltr;
    text-align: left;
}

.price-input-wrapper .price-display:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.price-input-wrapper .price-display:focus ~ .input-group-append .input-group-text {
    border-color: #80bdff;
}

/* Fix horizontal spacing issues - apply consistent margins to all sections */
#invoiceForm {
    padding-left: 15px;
    padding-right: 15px;
}

#invoiceForm > * {
    margin-left: 0;
    margin-right: 0;
}

#invoiceForm .row {
    margin-left: 0;
    margin-right: 0;
}

#invoiceForm .row > [class*="col-"] {
    padding-left: 10px;
    padding-right: 10px;
}

#invoiceForm .table-responsive {
    margin-left: 0;
    margin-right: 0;
}

#invoiceForm hr {
    margin-left: 0;
    margin-right: 0;
}

#invoiceForm .d-flex {
    margin-left: 0;
    margin-right: 0;
}

#invoiceForm .form-group {
    margin-left: 0;
    margin-right: 0;
}
</style>
<script>
let itemCounter = 0;

// Format currency
function formatCurrency(amount) {
    if (!amount) return '0 ریال';
    const formatted = new Intl.NumberFormat('fa-IR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
    return `${formatted} ریال`;
}

// Load buildings
function loadBuildings() {
    const token = localStorage.getItem('organization_token');
    if (!token) return;

    $.ajax({
        url: '/api/organization/invoices/buildings',
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

// Add item row
function addItemRow() {
    itemCounter++;
    const rowId = `item-${itemCounter}`;
    const unitPriceId = `unit_price_${itemCounter}`;
    
    const row = `
        <tr id="${rowId}">
            <td>
                <input type="text" class="form-control item-description" placeholder="شرح" required>
            </td>
            <td>
                <input type="number" class="form-control item-quantity" min="1" value="1" required>
            </td>
            <td>
                <div class="price-input-wrapper">
                    <div class="input-group">
                        <input type="text" class="price-display form-control" id="${unitPriceId}_display" placeholder="قیمت واحد" required autocomplete="off">
                        <div class="input-group-append">
                            <span class="input-group-text">ریال</span>
                        </div>
                    </div>
                    <input type="hidden" class="price-base" id="${unitPriceId}" name="unit_price_${itemCounter}" value="" required>
                </div>
            </td>
            <td>
                <span class="item-total">0 ریال</span>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-item-btn" data-row-id="${rowId}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </td>
        </tr>
    `;
    
    $('#itemsTableBody').append(row);
    
    // Initialize price input for the new row
    const displayInput = document.getElementById(`${unitPriceId}_display`);
    const baseInput = document.getElementById(unitPriceId);
    
    if (displayInput && baseInput) {
        // Add event listeners
        displayInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d.]/g, '');
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            baseInput.value = value;
            if (value) {
                const num = parseFloat(value);
                if (!isNaN(num)) {
                    displayInput.value = new Intl.NumberFormat('en-US').format(num);
                }
            }
            calculateItemTotal(rowId, unitPriceId);
            calculateTotals();
        });
        
        displayInput.addEventListener('blur', function() {
            const value = baseInput.value;
            if (value) {
                const num = parseFloat(value);
                if (!isNaN(num)) {
                    displayInput.value = new Intl.NumberFormat('en-US').format(num);
                }
            }
        });
        
        displayInput.addEventListener('focus', function() {
            const value = baseInput.value;
            if (value) {
                displayInput.value = value;
            }
        });
    }
    
    // Attach event handlers
    $(`#${rowId} .item-quantity`).on('input', function() {
        calculateItemTotal(rowId, unitPriceId);
        calculateTotals();
    });
    
    $(`#${rowId} .remove-item-btn`).on('click', function() {
        $(`#${rowId}`).remove();
        calculateTotals();
    });
}

// Calculate item total
function calculateItemTotal(rowId, unitPriceId) {
    const row = $(`#${rowId}`);
    const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
    const unitPrice = parseFloat(getPriceInputValue(unitPriceId)) || 0;
    const total = quantity * unitPrice;
    
    row.find('.item-total').text(formatCurrency(total));
}

// Calculate all totals
function calculateTotals() {
    let subtotal = 0;
    
    $('#itemsTableBody tr').each(function() {
        const quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
        // Find the price input base field (hidden input)
        const unitPriceBaseInput = $(this).find('.price-base');
        const unitPriceId = unitPriceBaseInput.attr('id');
        const unitPrice = parseFloat(getPriceInputValue(unitPriceId)) || 0;
        subtotal += quantity * unitPrice;
    });
    
    const discount = parseFloat(getPriceInputValue('discount')) || 0;
    const taxPercentage = parseFloat($('#tax_percentage').val()) || 0;
    
    const subtotalAfterDiscount = subtotal - discount;
    const taxAmount = (subtotalAfterDiscount * taxPercentage) / 100;
    const total = subtotalAfterDiscount + taxAmount;
    
    $('#subtotalDisplay').text(formatCurrency(subtotal));
    $('#discountDisplay').text(formatCurrency(discount));
    $('#taxDisplay').text(`${formatCurrency(taxAmount)} (${taxPercentage}%)`);
    $('#totalDisplay').html(`<strong>${formatCurrency(total)}</strong>`);
}

// Initialize Jalali DatePicker
jalaliDatepicker.startWatch({
    selector: '#invoice_date',
    date: true,
    time: false,
    hasSecond: false,
    showSelectTimeBtnAlways: false,
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
});

// Initialize price input for discount
$(document).ready(function() {
    // Discount and tax percentage change handlers
    $('#discount').on('input', function() {
        calculateTotals();
    });
    
    $('#tax_percentage').on('input', function() {
        calculateTotals();
    });
    
    // Add first item row
    addItemRow();
    
    // Load buildings
    loadBuildings();
});

// Handle add item button
$('#addItemBtn').on('click', function() {
    addItemRow();
});

// Handle form submission
$('#invoiceForm').on('submit', function(e) {
    e.preventDefault();
    
    const buildingId = $('#building_id').val();
    const invoiceDate = $('#invoice_date').val();
    const discount = parseFloat(getPriceInputValue('discount')) || 0;
    const taxPercentage = parseFloat($('#tax_percentage').val()) || 0;
    
    // Collect items
    const items = [];
    let hasError = false;
    
    $('#itemsTableBody tr').each(function() {
        const description = $(this).find('.item-description').val();
        const quantity = parseInt($(this).find('.item-quantity').val());
        const unitPriceBaseInput = $(this).find('.price-base');
        const unitPriceId = unitPriceBaseInput.attr('id');
        const unitPrice = parseFloat(getPriceInputValue(unitPriceId)) || 0;
        
        if (!description || !quantity || !unitPrice) {
            hasError = true;
            return false;
        }
        
        items.push({
            description: description,
            quantity: quantity,
            unit_price: unitPrice
        });
    });
    
    if (!buildingId) {
        swal({
            title: 'خطا',
            text: 'لطفاً ساختمان را انتخاب کنید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    if (items.length === 0) {
        swal({
            title: 'خطا',
            text: 'لطفاً حداقل یک سطر به فاکتور اضافه کنید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    if (hasError) {
        swal({
            title: 'خطا',
            text: 'لطفاً تمام فیلدهای سطر را پر کنید',
            type: 'error',
            padding: '2em'
        });
        return;
    }
    
    const formData = {
        building_id: buildingId,
        discount: discount,
        tax_percentage: taxPercentage,
        invoice_date: invoiceDate || null,
        items: items
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
    const submitBtn = $('#invoiceForm button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true);
    submitBtn.html('<span class="spinner-border spinner-border-sm mr-2"></span>در حال ذخیره...');
    
    $.ajax({
        url: '/api/organization/invoices',
        type: 'POST',
        data: formData,
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                swal({
                    title: 'موفقیت',
                    text: response.message || 'فاکتور با موفقیت ایجاد شد',
                    type: 'success',
                    padding: '2em',
                    timer: 2000
                }).then(function() {
                    window.location.href = '{{ route("organization.financial.invoices.index") }}';
                });
            } else {
                swal({
                    title: 'خطا',
                    text: response.message || 'خطا در ایجاد فاکتور',
                    type: 'error',
                    padding: '2em'
                });
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            let errorMessage = 'خطا در ایجاد فاکتور';
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

