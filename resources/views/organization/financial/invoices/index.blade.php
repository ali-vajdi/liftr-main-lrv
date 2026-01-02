@extends('organization.layout.master')

@section('title', 'فاکتور ها')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">فاکتور ها</h5>
                        <div>
                            <a href="{{ route('organization.financial.invoices.create') }}" class="btn btn-primary btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                صدور فاکتور
                            </a>
                        </div>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="searchInput" placeholder="جستجو بر اساس شماره فاکتور یا نام ساختمان...">
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="buildingFilter">
                                    <option value="">همه ساختمان‌ها</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="widget-content widget-content-area br-6">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="invoicesTable">
                                <thead>
                                    <tr>
                                        <th>شماره فاکتور</th>
                                        <th>نام ساختمان</th>
                                        <th>تاریخ فاکتور</th>
                                        <th>جمع کل</th>
                                        <th>تخفیف</th>
                                        <th>مالیات</th>
                                        <th>قیمت کل</th>
                                        <th>تعداد آیتم</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody id="invoicesTableBody">
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">در حال بارگذاری...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="paginationContainer" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script>
let currentPage = 1;
let buildingsList = [];

// Format currency
function formatCurrency(amount) {
    if (!amount) return '0 ریال';
    const formatted = new Intl.NumberFormat('fa-IR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
    return `${formatted} ریال`;
}

// Load buildings for filter
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
                buildingsList = response.data || [];
                const select = $('#buildingFilter');
                select.empty();
                select.append('<option value="">همه ساختمان‌ها</option>');
                buildingsList.forEach(function(building) {
                    select.append(`<option value="${building.id}">${building.name}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading buildings:', xhr);
        }
    });
}

// Load invoices
function loadInvoices(page = 1) {
    const token = localStorage.getItem('organization_token');
    if (!token) {
        $('#invoicesTableBody').html('<tr><td colspan="9" class="text-center text-danger">لطفاً مجدداً وارد شوید</td></tr>');
        return;
    }

    const search = $('#searchInput').val();
    const buildingId = $('#buildingFilter').val();

    let url = `/api/organization/invoices?page=${page}`;
    if (search) {
        url += `&search=${encodeURIComponent(search)}`;
    }
    if (buildingId) {
        url += `&building_id=${buildingId}`;
    }

    $.ajax({
        url: url,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                renderInvoicesTable(response.data || []);
                renderPagination(response.pagination);
                currentPage = page;
            } else {
                $('#invoicesTableBody').html('<tr><td colspan="9" class="text-center text-danger">خطا در بارگذاری داده‌ها</td></tr>');
            }
        },
        error: function(xhr) {
            console.error('Error loading invoices:', xhr);
            $('#invoicesTableBody').html('<tr><td colspan="9" class="text-center text-danger">خطا در بارگذاری داده‌ها</td></tr>');
        }
    });
}

// Render invoices table
function renderInvoicesTable(invoices) {
    const tbody = $('#invoicesTableBody');
    tbody.empty();
    
    if (invoices.length === 0) {
        tbody.html('<tr><td colspan="9" class="text-center">هیچ فاکتوری یافت نشد</td></tr>');
        return;
    }
    
    invoices.forEach(function(invoice) {
        const row = `
            <tr>
                <td>${invoice.invoice_number || '-'}</td>
                <td>${invoice.building_name || '-'}</td>
                <td>${invoice.invoice_date_jalali || '-'}</td>
                <td>${formatCurrency(invoice.subtotal)}</td>
                <td>${formatCurrency(invoice.discount)}</td>
                <td>${formatCurrency(invoice.tax_amount)} (${invoice.tax_percentage}%)</td>
                <td><strong>${formatCurrency(invoice.total)}</strong></td>
                <td>${invoice.items_count || 0}</td>
                <td>
                    <button class="btn btn-sm btn-info view-invoice-btn mr-1" data-invoice-id="${invoice.id}" title="مشاهده">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                    <button class="btn btn-sm btn-danger export-pdf-btn" data-invoice-id="${invoice.id}" data-invoice-number="${invoice.invoice_number || ''}" data-building-name="${invoice.building_name || ''}" title="دریافت PDF">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    // Attach event handlers for view buttons
    $('.view-invoice-btn').on('click', function() {
        const invoiceId = $(this).data('invoice-id');
        viewInvoice(invoiceId);
    });
    
    // Attach event handlers for export PDF buttons
    $('.export-pdf-btn').on('click', function() {
        const invoiceId = $(this).data('invoice-id');
        const invoiceNumber = $(this).data('invoice-number') || '';
        const buildingName = $(this).data('building-name') || '';
        exportInvoicePdf(invoiceId, invoiceNumber, buildingName);
    });
}

// Render pagination
function renderPagination(pagination) {
    const container = $('#paginationContainer');
    container.empty();
    
    if (!pagination || pagination.last_page <= 1) {
        return;
    }
    
    let html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous button
    if (pagination.current_page > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">قبلی</a></li>`;
    }
    
    // Page numbers
    for (let i = 1; i <= pagination.last_page; i++) {
        if (i === pagination.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
    }
    
    // Next button
    if (pagination.current_page < pagination.last_page) {
        html += `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page + 1}">بعدی</a></li>`;
    }
    
    html += '</ul></nav>';
    container.html(html);
    
    // Attach pagination click handlers
    container.find('.page-link[data-page]').on('click', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        loadInvoices(page);
    });
}

// View invoice details
function viewInvoice(invoiceId) {
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

    $.ajax({
        url: `/api/organization/invoices/${invoiceId}`,
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                showInvoiceModal(response.data);
            } else {
                swal({
                    title: 'خطا',
                    text: 'خطا در بارگذاری فاکتور',
                    type: 'error',
                    padding: '2em'
                });
            }
        },
        error: function(xhr) {
            swal({
                title: 'خطا',
                text: 'خطا در بارگذاری فاکتور',
                type: 'error',
                padding: '2em'
            });
        }
    });
}

// Show invoice modal
function showInvoiceModal(invoice) {
    let itemsHtml = '';
    invoice.items.forEach(function(item) {
        itemsHtml += `
            <tr>
                <td>${item.description}</td>
                <td>${item.quantity}</td>
                <td>${formatCurrency(item.unit_price)}</td>
                <td>${formatCurrency(item.total)}</td>
            </tr>
        `;
    });

    const modalHtml = `
        <div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">فاکتور شماره: ${invoice.invoice_number}</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>نام ساختمان:</strong> ${invoice.building.name}
                            </div>
                            <div class="col-md-6">
                                <strong>تاریخ فاکتور:</strong> ${invoice.invoice_date_jalali}
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>شرح</th>
                                        <th>تعداد</th>
                                        <th>قیمت واحد</th>
                                        <th>جمع</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsHtml}
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6 offset-md-6">
                                <table class="table">
                                    <tr>
                                        <td><strong>جمع کل:</strong></td>
                                        <td>${formatCurrency(invoice.subtotal)}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>تخفیف:</strong></td>
                                        <td>${formatCurrency(invoice.discount)}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>مالیات (${invoice.tax_percentage}%):</strong></td>
                                        <td>${formatCurrency(invoice.tax_amount)}</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td><strong>قیمت کل:</strong></td>
                                        <td><strong>${formatCurrency(invoice.total)}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger export-pdf-modal-btn" data-invoice-id="${invoice.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            دریافت PDF
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#invoiceModal').remove();
    
    // Append and show modal
    $('body').append(modalHtml);
    $('#invoiceModal').modal('show');
    
    // Remove modal from DOM when closed
    $('#invoiceModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
    
    // Attach export PDF button handler in modal
    $(document).off('click', '.export-pdf-modal-btn').on('click', '.export-pdf-modal-btn', function() {
        const invoiceId = $(this).data('invoice-id');
        const invoiceNumber = invoice.invoice_number || '';
        const buildingName = invoice.building ? invoice.building.name : '';
        $('#invoiceModal').modal('hide');
        exportInvoicePdf(invoiceId, invoiceNumber, buildingName);
    });
}

// Search and filter handlers
$('#searchInput').on('keyup', function() {
    clearTimeout(window.searchTimeout);
    window.searchTimeout = setTimeout(function() {
        loadInvoices(1);
    }, 500);
});

$('#buildingFilter').on('change', function() {
    loadInvoices(1);
});

// Export invoice PDF
function exportInvoicePdf(invoiceId, invoiceNumber, buildingName) {
    const token = localStorage.getItem('organization_token');
    if (!token) {
        swal({
            title: 'خطا',
            text: 'لطفاً ابتدا وارد سیستم شوید',
            type: 'error',
            padding: '2em'
        });
        return;
    }

    const url = `/api/organization/invoices/${invoiceId}/export-pdf`;
    
    // Use fetch to download PDF with authentication
    fetch(url, {
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/pdf'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('خطا در دریافت فایل PDF');
        }
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        // Format filename exactly like backend: فاکتور_{invoice_number}_{building_name}.pdf
        const filename = `فاکتور_${invoiceNumber}_${buildingName}.pdf`;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    })
    .catch(error => {
        console.error('Error:', error);
        swal({
            title: 'خطا',
            text: 'خطا در دریافت فایل PDF. لطفاً دوباره تلاش کنید.',
            type: 'error',
            padding: '2em'
        });
    });
}

// Load data on page load
$(document).ready(function() {
    loadBuildings();
    loadInvoices(1);
});
</script>
@endsection

