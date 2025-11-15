<div class="widget-content widget-content-area br-6">
    <!-- Unified Header Section -->
    <div class="unified-header mb-3">
        @if (isset($title))
            <div class="section-header">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="title-wrapper">
                            <h4 class="section-title mb-0">{{ $title }}</h4>
                        </div>
                    </div>
                    @if (isset($createButton) && $createButton)
                        <div class="col-auto">
                            <button type="button" class="btn btn-primary btn-lg create-new-button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-plus me-2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                {{ $createButtonText ?? 'افزودن' }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="header-controls">
            <!-- Search Input with RTL Support -->
            <div class="search-container">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <button class="btn search-button" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-search">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </button>
                    </div>
                    <input type="text" class="form-control search-input" placeholder="جستجو...">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Filters Toggle (if there are any filters) -->
                @if ((isset($filters) && count($filters) > 0) || (!isset($hideDefaultFilters) || !$hideDefaultFilters))
                    <button class="btn btn-outline-secondary btn-sm filters-toggle" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-filter">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                             <span class="btn-text d-none d-sm-inline">فیلترها</span>
                    </button>
                @endif

                     <!-- Print Button -->
                     <button class="btn btn-outline-secondary btn-sm print-button" title="خروجی گزارش">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                             stroke-linejoin="round" class="feather feather-download">
                             <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                             <polyline points="7,10 12,15 17,10"></polyline>
                             <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                         <span class="btn-text d-none d-sm-inline">خروجی گزارش</span>
                </button>

                     <!-- Per-page selector -->
                     <div class="per-page-selector">
                         <select id="perPageSelect" class="per-page-dropdown" title="تعداد در صفحه">
                             <option value="10">10</option>
                             <option value="25">25</option>
                             <option value="50">50</option>
                             <option value="100">100</option>
                         </select>
                     </div>

                <!-- Refresh Button -->
                <button class="btn btn-outline-primary btn-sm refresh-button" title="بروزرسانی">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-refresh-cw">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                </button>
                </div>
            </div>
        </div>

        <!-- Collapsible Filters Section -->
        @if ((isset($filters) && count($filters) > 0) || (!isset($hideDefaultFilters) || !$hideDefaultFilters))
            <div class="advanced-filters collapse mt-3" id="advancedFilters">
                <div class="card card-body p-3">
                    <div class="row">
                        @php
                            $hasStatusFilter = false;
                            if (isset($filters)) {
                                foreach ($filters as $filter) {
                                    if ($filter['name'] === 'status') {
                                        $hasStatusFilter = true;
                                        break;
                                    }
                                }
                            }
                        @endphp

                        <!-- Default Status Filter (only if no custom status filter exists) -->
                        @if ((!isset($hideDefaultFilters) || !$hideDefaultFilters) && !$hasStatusFilter)
                            <div class="col-md-4 col-lg-3 mb-2">
                                <label class="form-label small text-muted">وضعیت</label>
                                <select class="form-control form-control-sm filter-control" data-filter-name="status">
                                    <option value="">همه وضعیت‌ها</option>
                                    <option value="active">فعال</option>
                                    <option value="inactive">غیرفعال</option>
                                </select>
                            </div>
                        @endif


                        <!-- Custom Filters -->
                        @if (isset($filters) && count($filters) > 0)
                            @foreach ($filters as $filter)
                                <div class="col-md-4 col-lg-3 mb-2">
                                    <label class="form-label small text-muted">{{ $filter['label'] ?? ucfirst(str_replace('_', ' ', $filter['name'])) }}</label>
                                    @if ($filter['type'] == 'select')
                                        <select class="form-control form-control-sm filter-control" data-filter-name="{{ $filter['name'] }}">
                                            <option value="">{{ $filter['placeholder'] ?? 'همه' }}</option>
                                            @foreach ($filter['options'] as $option)
                                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($filter['type'] == 'date')
                                        <input type="date" class="form-control form-control-sm filter-control"
                                            data-filter-name="{{ $filter['name'] }}"
                                            placeholder="{{ $filter['placeholder'] ?? '' }}">
                                    @elseif($filter['type'] == 'text')
                                        <input type="text" class="form-control form-control-sm filter-control"
                                            data-filter-name="{{ $filter['name'] }}"
                                            placeholder="{{ $filter['placeholder'] ?? '' }}">
                                    @elseif($filter['type'] == 'boolean')
                                        <select class="form-control form-control-sm filter-control" data-filter-name="{{ $filter['name'] }}">
                                            <option value="">{{ $filter['placeholder'] ?? 'همه' }}</option>
                                            <option value="1">{{ $filter['trueLabel'] ?? 'بله' }}</option>
                                            <option value="0">{{ $filter['falseLabel'] ?? 'خیر' }}</option>
                                        </select>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <!-- Date Range Filters -->
                    @if (!isset($hideDefaultFilters) || !$hideDefaultFilters)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="border-top pt-3">
                                    <h6 class="text-muted mb-3">فیلتر بر اساس تاریخ</h6>
                                    <div class="row">
                                        <div class="col-md-3 col-lg-2 mb-2">
                                            <label class="form-label small text-muted">از تاریخ</label>
                                            <input type="text" class="form-control form-control-sm filter-control" 
                                                data-filter-name="created_at_from" 
                                                id="created_at_from"
                                                data-jdp
                                                placeholder="انتخاب تاریخ">
                                        </div>
                                        <div class="col-md-3 col-lg-2 mb-2">
                                            <label class="form-label small text-muted">تا تاریخ</label>
                                            <input type="text" class="form-control form-control-sm filter-control" 
                                                data-filter-name="created_at_to" 
                                                id="created_at_to"
                                                data-jdp
                                                placeholder="انتخاب تاریخ">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-end mt-2">
                        <button class="btn btn-sm btn-outline-secondary clear-filters" type="button">
                            پاک کردن فیلترها
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="table-responsive mb-4 mt-4">
        <table id="datatable-{{ str_replace('.', '-', microtime(true)) }}" class="table table-hover style-3"
            style="width:100%">
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th class="sortable text-nowrap" data-field="{{ $column['field'] }}">
                            {{ $column['label'] }}
                            <span class="sort-icon"></span>
                        </th>
                    @endforeach
                    <th class="no-content text-center">عملیات</th>
                </tr>
            </thead>
            <tbody class="data-rows">
                <!-- Data will be loaded here -->
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-between mt-4">
        <div class="pagination-info"></div>
        <div class="paginating-container pagination-solid">
            <ul class="pagination pagination-controls">
                <!-- Pagination will be loaded here -->
            </ul>
        </div>
    </div>

    <!-- Field Selection Modal -->
    <div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printModalLabel">انتخاب فیلدها برای خروجی گزارش</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Record count indicator -->
                    <div class="alert alert-info" id="recordCountAlert" style="display: none;">
                        <i class="fas fa-info-circle"></i>
                        <span id="recordCountText"></span>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>فیلدهای موجود</h6>
                            <div id="availableFields" class="field-list">
                                <!-- Available fields will be loaded here -->
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>فیلدهای انتخاب شده</h6>
                            <div id="selectedFields" class="field-list">
                                <!-- Selected fields will be shown here -->
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllFields">انتخاب همه</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearAllFields">پاک کردن همه</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-success" id="printReport">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download me-2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7,10 12,15 17,10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        خروجی گزارش
                    </button>
                </div>
            </div>
        </div>
    </div>


</div>

<style>
    /* Unified Header Styling */
    .unified-header {
        background: #f8f9fa;
        border-radius: 0.5rem;
        border: 1px solid #e9ecef;
        margin: -1rem -1rem 1rem -1rem;
        overflow: hidden;
    }

    .section-header {
        padding: 1rem 1rem 0.75rem 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .title-wrapper {
        text-align: right;
        position: relative;
        padding: 0.5rem 0;
    }

    .title-wrapper::before {
        content: '';
        position: absolute;
        right: 0;
        top: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-radius: 2px;
        opacity: 0.8;
    }

    .section-title {
        font-size: 1.4rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
        line-height: 1.3;
        position: relative;
        padding-right: 1rem;
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .section-subtitle {
        font-size: 0.85rem;
        color: #6c757d;
        margin: 0;
        font-weight: 400;
        opacity: 0.8;
    }

    .create-new-button {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        font-weight: 500;
        font-size: 0.875rem;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.2);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        position: relative;
        overflow: hidden;
    }

    .create-new-button::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .create-new-button:hover {
        background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }

    .create-new-button:hover::before {
        left: 100%;
    }

    .create-new-button:active {
        transform: translateY(0);
        box-shadow: 0 1px 3px rgba(0, 123, 255, 0.2);
    }

    .create-new-button svg {
        width: 16px;
        height: 16px;
        transition: transform 0.2s ease;
    }

    .create-new-button:hover svg {
        transform: rotate(90deg);
    }

    /* RTL Support and Minimal Design */
    .datatable-header {
        direction: rtl;
        text-align: right;
    }

    .header-controls {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.5rem;
        padding: 1rem 1rem 1.25rem 1rem;
        background: transparent;
    }

    .search-container {
        flex: 1;
        max-width: 350px;
        min-width: 250px;
    }

    .search-container .input-group {
        direction: ltr;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-radius: 0.375rem;
        overflow: hidden;
        border: 1px solid #dee2e6;
        background: white;
        height: 40px;
        display: flex;
        align-items: stretch;
    }

    .search-container .input-group .form-control {
        text-align: right;
        border: none;
        background: transparent;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        height: 100%;
        flex: 1;
        min-width: 0;
    }

    .search-container .input-group .form-control:focus {
        box-shadow: none;
        background: #f8f9fa;
        border-color: #80bdff;
    }

    .search-container .input-group-prepend {
        margin-right: 0;
        display: flex;
    }

    .search-container .input-group-prepend .btn {
        border: none;
        background: #f8f9fa;
        color: #6c757d;
        padding: 0.5rem 0.75rem;
        transition: all 0.2s ease;
        border-radius: 0;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .search-container .input-group-prepend .btn:hover {
        background: #e9ecef;
        color: #495057;
        transform: none;
    }

    .search-container .input-group-prepend .btn:focus {
        box-shadow: none;
        background: #e9ecef;
    }

    .search-container .input-group-prepend .btn svg {
        width: 16px;
        height: 16px;
        color: inherit;
        fill: none;
        stroke: currentColor;
    }

    .search-container .input-group-prepend .btn:hover svg {
        color: #495057;
    }

    .action-buttons {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .action-buttons .btn {
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .action-buttons .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    .btn-text {
        margin-right: 0.25rem;
    }

    .filters-toggle {
        position: relative;
        transition: all 0.3s ease;
    }

    .filters-toggle:hover {
        transform: translateY(-1px);
    }

    .filters-toggle .badge {
        font-size: 0.7rem;
        margin-right: 0.25rem;
    }

    .advanced-filters {
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .advanced-filters .card {
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
    }

    .advanced-filters .form-label {
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .clear-filters {
        font-size: 0.8rem;
    }

    /* Action buttons styling */
    .action-buttons .btn {
        padding: 0.375rem 0.75rem;
        margin: 0 0.125rem;
        border-radius: 0.375rem;
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .action-buttons .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    .action-buttons .btn svg {
        width: 14px;
        height: 14px;
        transition: transform 0.2s ease;
        color: inherit;
        fill: none;
        stroke: currentColor;
    }

    .action-buttons .btn:hover svg {
        transform: scale(1.05);
        color: inherit;
    }

    /* Table action buttons styling */
    .table-action-buttons .btn {
        padding: 0.375rem 0.5rem;
        margin: 0 0.125rem;
        border-radius: 0.375rem;
        font-size: 0.8rem;
        min-width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        font-weight: 500;
    }

    .table-action-buttons .btn svg {
        width: 14px;
        height: 14px;
        transition: transform 0.2s ease;
    }

    .table-action-buttons .btn:hover svg {
        transform: scale(1.1);
    }

    .table-action-buttons .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-color: #007bff;
        color: white;
    }

    .table-action-buttons .btn-primary:hover {
        background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
        border-color: #0056b3;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,123,255,0.3);
    }

    .table-action-buttons .btn-primary:active {
        transform: translateY(0);
        box-shadow: 0 1px 3px rgba(0,123,255,0.2);
    }

    .table-action-buttons .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border-color: #dc3545;
        color: white;
    }

    .table-action-buttons .btn-danger:hover {
        background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
        border-color: #c82333;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(220,53,69,0.3);
    }

    .table-action-buttons .btn-danger:active {
        transform: translateY(0);
        box-shadow: 0 1px 3px rgba(220,53,69,0.2);
    }

    .table-action-buttons .btn:focus {
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .unified-header {
            margin: -1rem -1rem 1rem -1rem;
        }

        .section-header {
            padding: 0.75rem 0.75rem 0.75rem 0.75rem;
        }

        .header-controls {
            padding: 0.75rem 0.75rem 1rem 0.75rem;
            gap: 1rem;
        }

        .section-title {
            font-size: 1.25rem;
        }

        .section-subtitle {
            font-size: 0.8rem;
        }

        .create-new-button {
            width: 100%;
            justify-content: center;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
        }

        .header-controls {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }

        .search-container {
            max-width: none;
            width: 100%;
        }

        .action-buttons {
            justify-content: center;
            flex-wrap: wrap;
        }

        .advanced-filters .row .col-md-4 {
            margin-bottom: 0.5rem;
        }
    }

    @media (max-width: 576px) {
        .header-controls {
            padding: 0.5rem;
        }

        .action-buttons {
            gap: 0.25rem;
        }

        .action-buttons .btn {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .btn-text {
            display: none;
        }
    }

    /* RTL specific adjustments */
    .datatable-header .ml-1 {
        margin-left: 0 !important;
        margin-right: 0.25rem !important;
    }

    .datatable-header .text-right {
        text-align: left !important;
    }

    /* Smooth transitions */
    .collapse {
        transition: all 0.3s ease;
    }

    .btn {
        transition: all 0.2s ease;
    }

    .form-control {
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    /* Active state for filters toggle */
    .filters-toggle.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    .filters-toggle.active .badge {
        background-color: rgba(255,255,255,0.2);
        color: white;
    }

    /* Improved spacing and alignment */
    .datatable-header .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    /* Better visual hierarchy */
    .advanced-filters .card-body {
        background-color: #f8f9fa;
    }

    /* Loading state improvements */
    .data-rows tr td {
        vertical-align: middle;
    }

    /* Enhanced search input */
    .search-input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }

    /* Enhanced Table Styling for Light Mode */
    .table-hover tbody tr:hover {
        background-color: #f8f9fa !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }

    .table tbody tr {
        background-color: #ffffff;
        border-color: #e9ecef;
        transition: all 0.2s ease;
    }

    .table tbody tr td {
        color: #495057;
        border-color: #e9ecef;
        padding: 0.875rem 0.75rem;
        vertical-align: middle;
    }

    .table thead tr th {
        background-color: #f8f9fa;
        color: #495057;
        border-color: #dee2e6;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem 0.75rem;
    }

    .table {
        border-color: #dee2e6;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .table-responsive {
        background-color: #ffffff;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    /* Enhanced Pagination */
    .pagination .page-link {
        color: #007bff;
        border-color: #dee2e6;
        padding: 0.5rem 0.75rem;
        margin: 0 0.125rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }

    .pagination .page-link:hover {
        color: #0056b3;
        background-color: #e9ecef;
        border-color: #adb5bd;
        transform: translateY(-1px);
    }

    .pagination .page-item.active .page-link {
        background-color: #007bff;
        border-color: #007bff;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(0,123,255,0.3);
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #ffffff;
        border-color: #dee2e6;
    }

    /* Dark Mode Support */
    [data-theme="dark"] .unified-header {
        background: var(--bg-white);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .section-header {
        border-bottom-color: var(--border-color);
    }

    [data-theme="dark"] .section-title {
        color: var(--text-primary);
        background: linear-gradient(135deg, var(--text-primary) 0%, var(--text-light) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    [data-theme="dark"] .title-wrapper::before {
        background: linear-gradient(135deg, #1b55e2 0%, #0d47a1 100%);
    }

    [data-theme="dark"] .search-container .input-group {
        background: var(--bg-white);
        border-color: var(--border-color);
        box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    [data-theme="dark"] .search-container .input-group .form-control {
        background: transparent;
        color: var(--text-primary);
    }

    [data-theme="dark"] .search-container .input-group .form-control:focus {
        background: var(--bg-hover-light);
        color: var(--text-primary);
    }

    [data-theme="dark"] .search-container .input-group .form-control::placeholder {
        color: var(--text-secondary);
    }

    [data-theme="dark"] .search-container .input-group-prepend .btn {
        background: var(--bg-hover-light);
        color: var(--text-secondary);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .search-container .input-group-prepend .btn:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
    }

    [data-theme="dark"] .action-buttons .btn {
        background: var(--bg-white);
        border-color: var(--border-color);
        color: var(--text-secondary);
    }

    [data-theme="dark"] .action-buttons .btn:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
        border-color: var(--bg-hover);
    }

    [data-theme="dark"] .action-buttons .btn-outline-primary {
        color: #1b55e2;
        border-color: #1b55e2;
    }

    [data-theme="dark"] .action-buttons .btn-outline-primary:hover {
        background: #1b55e2;
        color: white;
    }

    [data-theme="dark"] .action-buttons .btn-outline-secondary {
        color: var(--text-secondary);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .action-buttons .btn-outline-secondary:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
        border-color: var(--bg-hover);
    }

    /* Dark Mode for Advanced Filters */
    [data-theme="dark"] .advanced-filters .card {
        background: var(--bg-white);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .advanced-filters .card-body {
        background: var(--bg-white);
    }

    [data-theme="dark"] .advanced-filters .form-label {
        color: var(--text-secondary);
    }

    [data-theme="dark"] .advanced-filters .form-control {
        background: var(--bg-white);
        border-color: var(--border-color);
        color: var(--text-primary);
    }

    [data-theme="dark"] .advanced-filters .form-control:focus {
        background: var(--bg-white);
        border-color: #1b55e2;
        color: var(--text-primary);
        box-shadow: 0 0 0 0.2rem rgba(27, 85, 226, 0.25);
    }

    [data-theme="dark"] .advanced-filters .form-control::placeholder {
        color: var(--text-secondary);
    }

    [data-theme="dark"] .advanced-filters .clear-filters {
        background: var(--bg-white);
        border-color: var(--border-color);
        color: var(--text-secondary);
    }

    [data-theme="dark"] .advanced-filters .clear-filters:hover {
        background: var(--bg-hover);
        color: var(--text-primary);
        border-color: var(--bg-hover);
    }

    /* Dark Mode for Table Content */
    [data-theme="dark"] .table-hover tbody tr:hover {
        background-color: var(--bg-hover-light) !important;
    }

    [data-theme="dark"] .table tbody tr {
        background-color: var(--bg-white);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .table tbody tr td {
        color: var(--text-primary);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .table thead tr th {
        background-color: var(--bg-hover);
        color: var(--text-primary);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .table {
        border-color: var(--border-color);
    }

    [data-theme="dark"] .table-responsive {
        background-color: var(--bg-white);
        border-radius: 0.5rem;
    }

    /* Dark Mode Pagination */
    [data-theme="dark"] .paginating-container {
        background: transparent;
    }

    [data-theme="dark"] .pagination-solid {
        background: transparent !important;
    }

    [data-theme="dark"] .pagination .page-link {
        color: var(--text-primary);
        border-color: var(--border-color);
        background-color: var(--bg-white);
    }

    [data-theme="dark"] .pagination .page-link:hover {
        color: var(--text-primary);
        background-color: var(--bg-hover);
        border-color: var(--bg-hover);
    }

    [data-theme="dark"] .pagination .page-item.active .page-link {
        background-color: #1b55e2;
        border-color: #1b55e2;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(27, 85, 226, 0.3);
    }

    [data-theme="dark"] .pagination .page-item.disabled .page-link {
        color: var(--text-secondary);
        background-color: var(--bg-white);
        border-color: var(--border-color);
    }

    /* Fix for pagination-solid specific styling */
    [data-theme="dark"] .pagination-solid .pagination .page-link {
        background-color: var(--bg-white) !important;
        border-color: var(--border-color) !important;
        color: var(--text-primary) !important;
    }

    [data-theme="dark"] .pagination-solid .pagination .page-link:hover {
        background-color: var(--bg-hover) !important;
        border-color: var(--bg-hover) !important;
        color: var(--text-primary) !important;
    }

    [data-theme="dark"] .pagination-solid .pagination .page-item.active .page-link {
        background-color: #1b55e2 !important;
        border-color: #1b55e2 !important;
        color: #ffffff !important;
    }

    [data-theme="dark"] .pagination-solid .pagination .page-item.disabled .page-link {
        background-color: var(--bg-white) !important;
        border-color: var(--border-color) !important;
        color: var(--text-secondary) !important;
    }

    /* Date Picker Styling */
    #created_at_from, #created_at_to {
        cursor: pointer;
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
    }

    #created_at_from:focus, #created_at_to:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        outline: none;
    }

    #created_at_from:hover, #created_at_to:hover {
        border-color: #adb5bd;
    }

    /* Dark Mode for Date Picker */
    [data-theme="dark"] #created_at_from, 
    [data-theme="dark"] #created_at_to {
        background-color: var(--bg-white);
        border-color: var(--border-color);
        color: var(--text-primary);
    }

    [data-theme="dark"] #created_at_from:focus, 
    [data-theme="dark"] #created_at_to:focus {
        border-color: #1b55e2;
        box-shadow: 0 0 0 0.2rem rgba(27, 85, 226, 0.25);
    }

    [data-theme="dark"] #created_at_from:hover, 
    [data-theme="dark"] #created_at_to:hover {
        border-color: var(--bg-hover);
    }

    /* Date Range Section Styling */
    .advanced-filters .border-top {
        border-color: #e9ecef !important;
    }

    .advanced-filters h6 {
        font-size: 0.9rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    /* Dark Mode for Date Range Section */
    [data-theme="dark"] .advanced-filters .border-top {
        border-color: var(--border-color) !important;
    }

    [data-theme="dark"] .advanced-filters h6 {
        color: var(--text-secondary);
    }

    /* Export Modal Styling */
    .field-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.5rem;
        background: #f8f9fa;
    }

    .field-item {
        display: flex;
        align-items: center;
        padding: 0.5rem;
        margin: 0.25rem 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .field-item:hover {
        background: #e9ecef;
        border-color: #007bff;
    }

    .field-item.selected {
        background: #d4edda;
        border-color: #28a745;
    }

    .field-item .field-label {
        flex: 1;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .field-item .field-type {
        font-size: 0.75rem;
        color: #6c757d;
        background: #e9ecef;
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        margin-right: 0.5rem;
    }

    .field-item .field-actions {
        display: flex;
        gap: 0.25rem;
    }

    .field-item .btn-sm {
        padding: 0.125rem 0.375rem;
        font-size: 0.75rem;
    }

    .export-button {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
    }

    .export-button:hover {
        background: linear-gradient(135deg, #218838 0%, #1ea080 100%);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
    }

    /* Dark Mode for Export Modal */
    [data-theme="dark"] .field-list {
        background: var(--bg-hover);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .field-item {
        background: var(--bg-white);
        border-color: var(--border-color);
        color: var(--text-primary);
    }

    [data-theme="dark"] .field-item:hover {
        background: var(--bg-hover);
        border-color: #1b55e2;
    }

    [data-theme="dark"] .field-item.selected {
        background: #1e3a2e;
        border-color: #28a745;
    }

    [data-theme="dark"] .field-item .field-type {
        background: var(--bg-hover);
        color: var(--text-secondary);
    }

    /* Dark Mode for Print Modal */
    [data-theme="dark"] #printModal .modal-content {
        background: var(--bg-white);
        border-color: var(--border-color);
    }

    [data-theme="dark"] #printModal .modal-header {
        background: var(--bg-hover);
        border-bottom-color: var(--border-color);
    }

    [data-theme="dark"] #printModal .modal-title {
        color: var(--text-primary);
    }

    [data-theme="dark"] #printModal .modal-body {
        background: var(--bg-white);
        color: var(--text-primary);
    }

    [data-theme="dark"] #printModal .modal-footer {
        background: var(--bg-hover);
        border-top-color: var(--border-color);
    }

    [data-theme="dark"] #printModal .btn-secondary {
        background: var(--bg-hover);
        border-color: var(--border-color);
        color: var(--text-secondary);
    }

    [data-theme="dark"] #printModal .btn-secondary:hover {
        background: var(--bg-hover-light);
        color: var(--text-primary);
        border-color: var(--bg-hover-light);
    }

    [data-theme="dark"] #printModal .btn-success {
        background: #28a745;
        border-color: #28a745;
        color: white;
    }

    [data-theme="dark"] #printModal .btn-success:hover {
        background: #218838;
        border-color: #1e7e34;
    }

    /* Per-page selector styling - Button-like appearance */
    .per-page-selector {
        position: relative;
        display: inline-block;
    }

    .per-page-dropdown {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 32px;
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.5;
        color: #6c757d;
        background-color: transparent;
        border: 1px solid #6c757d;
        border-radius: 0.375rem;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236c757d' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        background-size: 12px 8px;
        appearance: none;
        transition: all 0.15s ease-in-out;
        cursor: pointer;
        text-align: center;
        min-width: 60px;
    }

    .per-page-dropdown:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    }

    .per-page-dropdown:focus {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    }

    .per-page-dropdown option {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        text-align: center;
        background-color: #fff;
        color: #495057;
    }

    /* Mobile responsiveness */
    @media (max-width: 576px) {
        .action-buttons .d-flex {
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .per-page-dropdown {
            width: 50px;
            height: 30px;
            font-size: 0.8rem;
            padding: 0.25rem 0.375rem;
            background-position: right 0.375rem center;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
    }

    @media (max-width: 480px) {
        .action-buttons .d-flex {
            justify-content: center;
        }
        
        .per-page-dropdown {
            width: 45px;
            height: 28px;
            font-size: 0.75rem;
            padding: 0.2rem 0.3rem;
            background-position: right 0.3rem center;
        }
    }

    /* Dark theme support */
    [data-theme="dark"] .per-page-dropdown {
        background-color: transparent;
        border-color: #6c757d;
        color: #6c757d;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%236c757d' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    }

    [data-theme="dark"] .per-page-dropdown:hover {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    }

    [data-theme="dark"] .per-page-dropdown:focus {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
        box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    }

    [data-theme="dark"] .per-page-dropdown option {
        background-color: #2d3748;
        color: #e2e8f0;
    }

    /* Export Modal Dark Theme Support */
    [data-theme="dark"] #exportModal .modal-content {
        background-color: #2d3748;
        border-color: #4a5568;
    }

    [data-theme="dark"] #exportModal .modal-header {
        background-color: #2d3748;
        border-bottom-color: #4a5568;
    }

    [data-theme="dark"] #exportModal .modal-title {
        color: #e2e8f0;
    }

    [data-theme="dark"] #exportModal .close {
        color: #a0aec0;
    }

    [data-theme="dark"] #exportModal .close:hover {
        color: #e2e8f0;
    }

    [data-theme="dark"] #exportModal .modal-body {
        background-color: #2d3748;
        color: #e2e8f0;
    }

    [data-theme="dark"] #exportModal .modal-footer {
        background-color: #2d3748;
        border-top-color: #4a5568;
    }

    [data-theme="dark"] #exportModal h6 {
        color: #e2e8f0;
    }

    [data-theme="dark"] #exportModal .alert-info {
        background-color: #1a365d;
        border-color: #2c5282;
        color: #90cdf4;
    }

    [data-theme="dark"] #exportModal .alert-warning {
        background-color: #744210;
        border-color: #975a16;
        color: #fbd38d;
    }

    [data-theme="dark"] #exportModal .field-list {
        background-color: #4a5568;
        border-color: #718096;
    }

    [data-theme="dark"] #exportModal .field-item {
        background-color: #4a5568;
        border-color: #718096;
        color: #e2e8f0;
    }

    [data-theme="dark"] #exportModal .field-item:hover {
        background-color: #2d3748;
        border-color: #a0aec0;
    }

    [data-theme="dark"] #exportModal .field-item.selected {
        background-color: #2b6cb0;
        border-color: #3182ce;
        color: #fff;
    }

    [data-theme="dark"] #exportModal .field-item .field-type {
        background-color: #2d3748;
        color: #a0aec0;
    }

    [data-theme="dark"] #exportModal .btn-outline-primary {
        color: #63b3ed;
        border-color: #63b3ed;
    }

    [data-theme="dark"] #exportModal .btn-outline-primary:hover {
        background-color: #63b3ed;
        border-color: #63b3ed;
        color: #2d3748;
    }

    [data-theme="dark"] #exportModal .btn-outline-secondary {
        color: #a0aec0;
        border-color: #a0aec0;
    }

    [data-theme="dark"] #exportModal .btn-outline-secondary:hover {
        background-color: #a0aec0;
        border-color: #a0aec0;
        color: #2d3748;
    }

    [data-theme="dark"] #exportModal .btn-secondary {
        background-color: #4a5568;
        border-color: #4a5568;
        color: #e2e8f0;
    }

    [data-theme="dark"] #exportModal .btn-secondary:hover {
        background-color: #718096;
        border-color: #718096;
    }

    [data-theme="dark"] #exportModal .btn-success {
        background-color: #38a169;
        border-color: #38a169;
        color: #fff;
    }

    [data-theme="dark"] #exportModal .btn-success:hover {
        background-color: #2f855a;
        border-color: #2f855a;
    }

    /* Simple Print Styles */
    @media print {
        body * {
            visibility: hidden;
        }
        
        .print-content, .print-content * {
            visibility: visible;
        }
        
        .print-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        
        @page {
            margin: 1cm;
            size: A4;
        }
    }
</style>

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof jQuery === 'undefined') {
                console.error('jQuery is not loaded. Please include jQuery in your layout.');
                return;
            }



            jQuery(function($) {
                const apiUrl = '{{ $apiUrl }}';
                let currentPage = 1;
                let perPage = 10;
                let searchTerm = '';
                let sortField = '{{ isset($columns[0]) ? $columns[0]['field'] : 'id' }}';
                let sortDirection = 'asc';
                let filters = {};

                // Initialize filters
                $('.filter-control').each(function() {
                    const filterName = $(this).data('filter-name');
                    filters[filterName] = '';
                });

                // Initialize per-page selector
                $('#perPageSelect').val(perPage);
                $('#perPageSelect').on('change', function() {
                    perPage = parseInt($(this).val());
                    currentPage = 1; // Reset to first page when changing per-page
                    loadData();
                });

                // Initialize Jalali Date Picker
                if (typeof jalaliDatepicker !== 'undefined' && jalaliDatepicker) {
                    jalaliDatepicker.startWatch({
                        selector: '#created_at_from, #created_at_to',
                        date: true,
                        time: true,
                        hasSecond: true,
                        format: 'YYYY/MM/DD HH:mm:ss',
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
                        zIndex: 10000, // Higher z-index to appear above modals
                        minDate: {year: 1400, month: 1, day: 1}, // Allow dates from 1400/01/01
                        maxDate: 'today'  // Allow up to today
                    });
                }

                // Initial data load
                loadData();

                // Search functionality
                $('.search-button').click(function() {
                    searchTerm = $('.search-input').val();
                    currentPage = 1;
                    loadData();
                });

                // Enter key in search input
                $('.search-input').keypress(function(e) {
                    if (e.which === 13) {
                        searchTerm = $(this).val();
                        currentPage = 1;
                        loadData();
                    }
                });

                // Dynamic filters
                $('.filter-control').change(function() {
                    const filterName = $(this).data('filter-name');
                    filters[filterName] = $(this).val();
                    currentPage = 1;
                    loadData();
                });

                // Date picker change handler
                $('#created_at_from, #created_at_to').on('change', function() {
                    const filterName = $(this).data('filter-name');
                    filters[filterName] = $(this).val();
                    currentPage = 1;
                    loadData();
                });

                // Refresh button
                $('.refresh-button').click(function() {
                    loadData();
                });

                // Print button
                $('.print-button').click(function() {
                    openPrintModal();
                });

                // Collapsible filters toggle
                $('.filters-toggle').click(function() {
                    $('#advancedFilters').collapse('toggle');
                    $(this).toggleClass('active');
                });

                // Clear all filters
                $('.clear-filters').click(function() {
                    $('.filter-control').each(function() {
                        $(this).val('');
                        const filterName = $(this).data('filter-name');
                        filters[filterName] = '';
                    });
                    
                    // Clear date pickers
                    $('#created_at_from, #created_at_to').val('');
                    
                    currentPage = 1;
                    loadData();
                });

                // Sorting
                $('.sortable').click(function() {
                    const field = $(this).data('field');

                    if (sortField === field) {
                        sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        sortField = field;
                        sortDirection = 'asc';
                    }

                    // Update sort icons
                    $('.sortable .sort-icon').html('');
                    const icon = sortDirection === 'asc' ? '↑' : '↓';
                    $(this).find('.sort-icon').html(icon);

                    loadData();
                });


                // Load data from API
                function loadData() {
                    return new Promise((resolve, reject) => {
                    $('.data-rows').html(
                        '<tr><td colspan="{{ count($columns) + 1 }}" class="text-center">در حال بارگذاری...</td></tr>'
                        );

                    // Prepare request data
                    let requestData = {
                        page: currentPage,
                        per_page: perPage,
                        search: searchTerm,
                        sort_field: sortField,
                        sort_direction: sortDirection
                    };

                    // Add filters to request data
                    for (const [key, value] of Object.entries(filters)) {
                        if (value !== '') {
                            requestData[key] = value;
                        }
                    }

                    $.ajax({
                        url: apiUrl,
                        type: 'GET',
                        data: requestData,
                        headers: {
                            'Authorization': 'Bearer ' + localStorage.getItem('organization_token')
                        },
                        success: function(response) {
                            // Check if the response has meta or pagination property
                            const paginationData = response.meta || response.pagination;
                            renderData(response.data, paginationData);
                                resolve(response);
                        },
                        error: function(xhr) {
                            if (xhr.status === 401) {
                                // Unauthorized, redirect to login
                                swal({
                                    title: 'خطای دسترسی',
                                    text: 'لطفا مجددا وارد سیستم شوید',
                                    type: 'error',
                                    padding: '2em'
                                }).then(function() {
                                    window.location.href = "{{ route('organization.login') }}";
                                });
                                    reject(xhr);
                            } else {
                                $('.data-rows').html(
                                    '<tr><td colspan="{{ count($columns) + 1 }}" class="text-center text-danger">خطا در بارگذاری اطلاعات</td></tr>'
                                    );

                                swal({
                                    title: 'خطا',
                                    text: 'خطا در بارگذاری اطلاعات',
                                    type: 'error',
                                    padding: '2em'
                                });
                                    reject(xhr);
                            }
                        }
                        });
                    });
                }

                // Render data to table
                function renderData(data, pagination) {
                    if (!data || data.length === 0) {
                        $('.data-rows').html(
                            '<tr><td colspan="{{ count($columns) + 1 }}" class="text-center">هیچ داده‌ای یافت نشد</td></tr>'
                            );
                        $('.pagination-info').html('نمایش 0 تا 0 از 0 مورد');
                        $('.pagination-controls').html('');
                        return;
                    }

                    let html = '';

                    data.forEach(function(item) {
                        @if (isset($rowClass))
                            const rowClass = {!! $rowClass !!}(item);
                            html += '<tr class="' + rowClass + '">';
                        @else
                            html += '<tr>';
                        @endif

                        @foreach ($columns as $column)
                            html += '<td>';
                            @if (isset($column['formatter']))
                                html += {!! $column['formatter'] !!}(item['{{ $column['field'] }}'], item);
                            @else
                                html += item['{{ $column['field'] }}'] !== null ? item[
                                    '{{ $column['field'] }}'] : '';
                            @endif
                            html += '</td>';
                        @endforeach

                        // Actions column with improved styling
                        html += '<td class="text-center">';
                        html += '<div class="table-action-buttons d-flex justify-content-center">';

                        @if (!isset($hideDefaultActions) || !$hideDefaultActions)
                            // Edit button with SVG icon
                            html +=
                                '<button type="button" class="btn btn-sm btn-primary edit-btn bs-tooltip" data-placement="top" data-id="' +
                                item.id + '" title="ویرایش">';
                            html +=
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>';
                            html += '</button>';

                            // Delete button with SVG icon
                            html +=
                                '<button type="button" class="btn btn-sm btn-danger delete-btn bs-tooltip" data-placement="top" data-id="' +
                                item.id + '" title="حذف">';
                            html +=
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>';
                            html += '</button>';
                        @endif

                        @if (isset($actions))
                            // Custom action buttons
                            {!! $actions !!}
                        @endif

                        html += '</div>';
                        html += '</td>';

                        html += '</tr>';
                    });

                    $('.data-rows').html(html);

                    // Attach event handlers to the newly created buttons
                    $('.edit-btn').on('click', function() {
                        const id = $(this).data('id');
                        if (typeof window.onEdit === 'function') {
                            window.onEdit(id);
                        }
                    });

                    $('.delete-btn').on('click', function() {
                        const id = $(this).data('id');
                        if (typeof window.onDelete === 'function') {
                            window.onDelete(id);
                        }
                    });

                    // Initialize tooltips for dynamically added elements
                    $('.bs-tooltip').tooltip();

                    // Handle custom action buttons if needed
                    @if (isset($actionHandlers))
                        {!! $actionHandlers !!}
                    @endif

                    // Update pagination info
                    if (pagination) {
                        const from = pagination.from || 0;
                        const to = pagination.to || 0;
                        const total = pagination.total || 0;

                        // Store pagination data globally for export validation
                        window.currentPagination = pagination;

                        $('.pagination-info').html('نمایش ' + from + ' تا ' + to + ' از ' + total +
                        ' مورد');

                        // Update pagination controls
                        renderPagination(pagination);
                    } else {
                        window.currentPagination = null;
                        $('.pagination-info').html('');
                        $('.pagination-controls').html('');
                    }
                }

                $('[data-toggle="tooltip"]').tooltip()

                // Render pagination controls
                function renderPagination(pagination) {
                    if (!pagination || !pagination.total || pagination.total === 0) {
                        $('.pagination-controls').html('');
                        return;
                    }

                    // Ensure we have all required pagination properties
                    const paginationCurrentPage = pagination.current_page || 1;
                    const lastPage = pagination.last_page || 1;

                    let paginationHtml = '';

                    // Previous button
                    paginationHtml += `
                        <li class="prev ${paginationCurrentPage <= 1 ? 'disabled' : ''}">
                            <a href="javascript:void(0);" data-page="${paginationCurrentPage - 1}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </a>
                        </li>
                    `;

                    // Page numbers
                    const totalPages = lastPage;

                    let startPage = Math.max(1, paginationCurrentPage - 2);
                    let endPage = Math.min(totalPages, startPage + 4);

                    if (endPage - startPage < 4) {
                        startPage = Math.max(1, endPage - 4);
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        paginationHtml += `
                            <li ${i === paginationCurrentPage ? 'class="active"' : ''}>
                                <a href="javascript:void(0);" data-page="${i}">${i}</a>
                            </li>
                        `;
                    }

                    // Next button
                    paginationHtml += `
                        <li class="next ${paginationCurrentPage >= lastPage ? 'disabled' : ''}">
                            <a href="javascript:void(0);" data-page="${paginationCurrentPage + 1}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-left"><polyline points="15 18 9 12 15 6"></polyline></svg>
                            </a>
                        </li>
                    `;

                    $('.pagination-controls').html(paginationHtml);

                    // Attach click event to pagination links
                    $('.pagination-controls a').click(function(e) {
                        e.preventDefault();
                        const pageNum = $(this).data('page');

                        if (pageNum >= 1 && pageNum <= lastPage && !$(this).parent().hasClass(
                                'disabled')) {
                            currentPage = pageNum;
                            loadData();
                        }
                    });
                }

                // Export functionality
                let availableFields = [];
                let selectedFields = [];

                function openPrintModal() {
                    // Use predefined column definitions from datatable
                    loadAvailableFields();
                    
                    // Show record count information
                    updateRecordCountDisplay();
                    
                    $('#printModal').modal('show');
                }

                function updateRecordCountDisplay() {
                    const recordCountAlert = $('#recordCountAlert');
                    const recordCountText = $('#recordCountText');
                    
                    if (window.currentPagination && window.currentPagination.total) {
                        const total = window.currentPagination.total;
                        const currentPerPage = parseInt($('#perPageSelect').val());
                        
                        if (total > 100) {
                            recordCountAlert.removeClass('alert-info').addClass('alert-warning');
                            recordCountText.html(`تعداد کل رکوردها: <strong>${total}</strong> - حداکثر 100 رکورد قابل خروجی گیری است. لطفا از فیلترها یا تنظیم تعداد در صفحه استفاده کنید.`);
                            recordCountAlert.show();
                        } else if (total > currentPerPage) {
                            recordCountAlert.removeClass('alert-warning').addClass('alert-info');
                            recordCountText.html(`تعداد کل رکوردها: <strong>${total}</strong> - برای خروجی همه رکوردها، تعداد در صفحه را به <strong>100</strong> تغییر دهید.`);
                            recordCountAlert.show();
                        } else {
                            recordCountAlert.removeClass('alert-warning').addClass('alert-info');
                            recordCountText.html(`تعداد کل رکوردها: <strong>${total}</strong> - قابل خروجی گیری`);
                            recordCountAlert.show();
                        }
                    } else {
                        recordCountAlert.hide();
                    }
                }

                function loadAvailableFields() {
                    // Use the predefined column definitions from the datatable
                    const predefinedColumns = @json($columns);
                    availableFields = predefinedColumns.map(function(column) {
                        return {
                            field: column.field,
                            label: column.label,
                            type: getFieldTypeFromColumn(column)
                        };
                    });
                                selectedFields = [];
                                renderFieldLists();
                }

                function getFieldTypeFromColumn(column) {
                    // Determine field type based on column definition or field name
                    if (column.type) {
                        return column.type;
                    }
                    
                    const fieldName = column.field.toLowerCase();
                    if (fieldName.includes('id') || fieldName.includes('count') || fieldName.includes('amount') || fieldName.includes('weight') || fieldName.includes('price')) {
                        return 'number';
                    }
                    if (fieldName.includes('date') || fieldName.includes('created_at') || fieldName.includes('updated_at')) {
                        return 'date';
                    }
                    if (fieldName.includes('is_') || fieldName.includes('active') || fieldName.includes('status')) {
                        return 'boolean';
                    }
                    return 'text';
                }

                function renderFieldLists() {
                    // Render available fields
                    let availableHtml = '';
                    availableFields.forEach(function(field) {
                        if (!selectedFields.includes(field.field)) {
                            availableHtml += `
                                <div class="field-item" data-field="${field.field}">
                                    <div class="field-label">${field.label}</div>
                                    <div class="field-type">${field.type}</div>
                                    <div class="field-actions">
                                        <button class="btn btn-sm btn-primary add-field" data-field="${field.field}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            `;
                        }
                    });
                    $('#availableFields').html(availableHtml);

                    // Render selected fields
                    let selectedHtml = '';
                    selectedFields.forEach(function(fieldName) {
                        const field = availableFields.find(f => f.field === fieldName);
                        if (field) {
                            selectedHtml += `
                                <div class="field-item selected" data-field="${field.field}">
                                    <div class="field-label">${field.label}</div>
                                    <div class="field-type">${field.type}</div>
                                    <div class="field-actions">
                                        <button class="btn btn-sm btn-danger remove-field" data-field="${field.field}">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            `;
                        }
                    });
                    $('#selectedFields').html(selectedHtml);

                    // Attach event handlers
                    $('.add-field').click(function() {
                        const fieldName = $(this).data('field');
                        addFieldToSelected(fieldName);
                    });

                    $('.remove-field').click(function() {
                        const fieldName = $(this).data('field');
                        removeFieldFromSelected(fieldName);
                    });
                }

                function addFieldToSelected(fieldName) {
                    if (!selectedFields.includes(fieldName)) {
                        selectedFields.push(fieldName);
                        renderFieldLists();
                    }
                }

                function removeFieldFromSelected(fieldName) {
                    selectedFields = selectedFields.filter(f => f !== fieldName);
                    renderFieldLists();
                }

                // Modal event handlers
                $('#selectAllFields').click(function() {
                    selectedFields = availableFields.map(f => f.field);
                    renderFieldLists();
                });

                $('#clearAllFields').click(function() {
                    selectedFields = [];
                    renderFieldLists();
                });

                $('#printReport').click(function() {
                    if (selectedFields.length === 0) {
                        swal({
                            title: 'خطا',
                            text: 'لطفا حداقل یک فیلد انتخاب کنید',
                            type: 'warning',
                            padding: '2em'
                        });
                        return;
                    }

                    printReport();
                });

                function printReport() {
                    // Close the modal first
                    $('#printModal').modal('hide');
                    
                    // Get current table data from the visible table (already formatted)
                    const tableData = [];
                    const tableRows = $('.data-rows tr');
                    
                    tableRows.each(function() {
                        const row = [];
                        const $row = $(this);
                        
                        // Get all cells in this row
                        $row.find('td').each(function(index) {
                            const $cell = $(this);
                            const column = @json($columns)[index];
                            
                            // Check if this column is selected for printing
                            if (column && selectedFields.includes(column.field)) {
                                // Get the text content, handling HTML content properly
                                let cellText = $cell.html();
                                
                                // Clean up HTML tags but preserve line breaks
                                cellText = cellText.replace(/<br\s*\/?>/gi, '\n');
                                cellText = cellText.replace(/<[^>]*>/g, '');
                                cellText = cellText.trim();
                                
                                row.push(cellText || '-');
                            }
                        });
                        
                        if (row.length > 0) {
                            tableData.push(row);
                        }
                    });

                    // Get selected field labels in the same order as data
                    const fieldLabels = [];
                    @json($columns).forEach(function(column) {
                        if (selectedFields.includes(column.field)) {
                            fieldLabels.push(column.label);
                        }
                    });

                    // Prepare data for the print template
                    const printData = {
                        title: '{{ $title ?? "گزارش" }}',
                        date: new Date().toLocaleDateString('fa-IR'),
                        records: tableData.length,
                        fieldLabels: fieldLabels,
                        tableData: tableData
                    };

                    // Create a form to submit data to the print template
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("admin.print.template") }}';
                    form.target = '_blank';
                    form.style.display = 'none';

                    // Add CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    // Add print data
                    const dataInput = document.createElement('input');
                    dataInput.type = 'hidden';
                    dataInput.name = 'print_data';
                    dataInput.value = JSON.stringify(printData);
                    form.appendChild(dataInput);

                    // Submit form
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                }

                // Expose functions to parent component
                window.datatableApi = {
                    refresh: loadData,
                    setPage: function(page) {
                        currentPage = page;
                        loadData();
                    },
                    setPerPage: function(pp) {
                        perPage = pp;
                        currentPage = 1;
                        loadData();
                    },
                    setSearch: function(term) {
                        searchTerm = term;
                        currentPage = 1;
                        loadData();
                    },
                    setFilter: function(name, value) {
                        filters[name] = value;
                        currentPage = 1;
                        loadData();
                    },
                    clearFilters: function() {
                        $('.filter-control').each(function() {
                            $(this).val('');
                            const filterName = $(this).data('filter-name');
                            filters[filterName] = '';
                        });
                        currentPage = 1;
                        loadData();
                    },
                    setSorting: function(field, direction) {
                        sortField = field;
                        sortDirection = direction;
                        loadData();
                    },
                    getFilters: function() {
                        return {
                            ...filters
                        };
                    },
                    printReport: openPrintModal
                };


            });
        });
    </script>
@endpush
