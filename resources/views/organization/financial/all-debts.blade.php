@extends('organization.layout.master')

@section('title', 'کل بدهی‌ها')

@section('content')
<div class="layout-px-spacing">
    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
            <div class="widget widget-chart-one">
                <div class="widget-heading">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">کل بدهی‌ها</h5>
                    </div>
                </div>
                <div class="widget-content">
                    <div class="widget-content widget-content-area br-6">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="buildingsFinancialTable">
                                <thead>
                                    <tr>
                                        <th>نام ساختمان</th>
                                        <th>مدیر/نماینده ساختمان</th>
                                        <th>شماره تماس</th>
                                        <th>وضعیت</th>
                                        <th>بدهکاری</th>
                                        <th>بستانکاری</th>
                                        <th>مانده</th>
                                        <th>مشاهده</th>
                                    </tr>
                                </thead>
                                <tbody id="buildingsFinancialTableBody">
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">در حال بارگذاری...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script>
// Format currency
function formatCurrency(amount) {
    if (!amount) return '0 ریال';
    const formatted = new Intl.NumberFormat('fa-IR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(amount);
    return `${formatted} ریال`;
}

// Load buildings financial data
function loadBuildingsFinancialData() {
    const token = localStorage.getItem('organization_token');
    if (!token) {
        $('#buildingsFinancialTableBody').html('<tr><td colspan="8" class="text-center text-danger">لطفاً مجدداً وارد شوید</td></tr>');
        return;
    }

    $.ajax({
        url: '/api/organization/financial/all-buildings-summary',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            if (response.success) {
                renderBuildingsTable(response.data || []);
            } else {
                $('#buildingsFinancialTableBody').html('<tr><td colspan="8" class="text-center text-danger">خطا در بارگذاری داده‌ها</td></tr>');
            }
        },
        error: function(xhr) {
            console.error('Error loading buildings financial data:', xhr);
            $('#buildingsFinancialTableBody').html('<tr><td colspan="8" class="text-center text-danger">خطا در بارگذاری داده‌ها</td></tr>');
        }
    });
}

// Render buildings table
function renderBuildingsTable(buildings) {
    const tbody = $('#buildingsFinancialTableBody');
    tbody.empty();
    
    if (buildings.length === 0) {
        tbody.html('<tr><td colspan="8" class="text-center">هیچ ساختمانی یافت نشد</td></tr>');
        return;
    }
    
    buildings.forEach(function(building) {
        const balance = building.balance || 0;
        // Red for بدهکار (negative/debtor), black for بستانکار (positive/creditor)
        const balanceClass = balance < 0 ? 'text-danger' : '';
        const statusBadge = building.status === 'فعال' 
            ? '<span class="badge badge-success">فعال</span>' 
            : '<span class="badge badge-secondary">غیرفعال</span>';
        
        const row = `
            <tr>
                <td>${building.name || '-'}</td>
                <td>${building.manager_name || '-'}</td>
                <td>${building.manager_phone || '-'}</td>
                <td>${statusBadge}</td>
                <td class="text-danger">${formatCurrency(building.total_debits || 0)}</td>
                <td class="text-success">${formatCurrency(building.total_credits || 0)}</td>
                <td class="${balanceClass}">${formatCurrency(Math.abs(balance))}</td>
                <td>
                    <a href="/buildings/${building.slug}/financial-dashboard" class="btn btn-sm btn-info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        مشاهده
                    </a>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

// Load data on page load
$(document).ready(function() {
    loadBuildingsFinancialData();
});
</script>
@endsection

