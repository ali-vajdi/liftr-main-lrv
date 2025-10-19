<div class="row layout-spacing">
    <!-- Page Header -->
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget-header">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <h4 class="mb-0">آمار معاملات طلا</h4>
                    <p class="text-muted mb-0">نمایش کلیه خرید، فروش و مازاد معاملات طلا</p>
                </div>
            </div>
        </div>
    </div>

    <!-- خرید Section -->
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-card-four">
            <div class="widget-content">
                <div class="w-header">
                    <div class="w-info">
                        <h6 class="value">خرید طلا</h6>
                        <p class="text-muted">کل معاملات خرید</p>
                    </div>
                </div>

                <div class="w-content">
                    <div class="w-info">
                        <!-- ریال Section -->
                        <div class="stat-item">
                            <div class="stat-icon green-bg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value green-text">{{ $data['gold_transactions']['total_gold_sell_price'] }}</span>
                                <span class="stat-label">ریال</span>
                            </div>
                        </div>
                        
                        <!-- گرم Section -->
                        <div class="stat-item">
                            <div class="stat-icon green-bg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-zap">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                </svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value green-text">{{ number_format($data['gold_transactions']['total_gold_sell_amount'], 3) }}</span>
                                <span class="stat-label">گرم</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- فروش Section -->
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-card-four">
            <div class="widget-content">
                <div class="w-header">
                    <div class="w-info">
                        <h6 class="value">فروش طلا</h6>
                        <p class="text-muted">کل معاملات فروش</p>
                    </div>
                </div>

                <div class="w-content">
                    <div class="w-info">
                        <!-- ریال Section -->
                        <div class="stat-item">
                            <div class="stat-icon red-bg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value red-text">{{ $data['gold_transactions']['total_gold_buy_price'] }}</span>
                                <span class="stat-label">ریال</span>
                            </div>
                        </div>
                        
                        <!-- گرم Section -->
                        <div class="stat-item">
                            <div class="stat-icon red-bg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-zap">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                </svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value red-text">{{ number_format($data['gold_transactions']['total_gold_buy_amount'], 3) }}</span>
                                <span class="stat-label">گرم</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- مازاد خرید و فروش Section -->
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-card-four">
            <div class="widget-content">
                <div class="w-header">
                    <div class="w-info">
                        <h6 class="value">مازاد خرید و فروش</h6>
                        <p class="text-muted">تفاوت خرید و فروش</p>
                    </div>
                </div>

                <div class="w-content">
                    <div class="w-info">
                        <!-- ریال Section -->
                        <div class="stat-item">
                            <div class="stat-icon {{ $data['gold_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-bg' : 'red-bg' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                </svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value {{ $data['gold_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-text' : 'red-text' }}">{{ $data['gold_transactions']['mazaz_kharid_va_foroosh_price'] }}</span>
                                <span class="stat-label">ریال</span>
                            </div>
                        </div>
                        
                        <!-- گرم Section -->
                        <div class="stat-item">
                            <div class="stat-icon {{ $data['gold_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-bg' : 'red-bg' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-zap">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                </svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value {{ $data['gold_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-text' : 'red-text' }}">{{ number_format($data['gold_transactions']['mazaz_kharid_va_foroosh_amount'], 3) }}</span>
                                <span class="stat-label">گرم</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards Row -->
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="row">
            <!-- Total Buy Card -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="widget widget-card-one">
                    <div class="widget-content">
                        <div class="media">
                            <div class="w-img green-bg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                            </div>
                            <div class="media-body">
                                <h6>کل خرید</h6>
                                <div class="metric-item">
                                    <span class="metric-value green-text">{{ $data['gold_transactions']['total_gold_sell_price'] }}</span>
                                    <span class="metric-label">ریال</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-value green-text">{{ number_format($data['gold_transactions']['total_gold_sell_amount'], 3) }}</span>
                                    <span class="metric-label">گرم</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Sell Card -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="widget widget-card-one">
                    <div class="widget-content">
                        <div class="media">
                            <div class="w-img red-bg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-credit-card">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                            </div>
                            <div class="media-body">
                                <h6>کل فروش</h6>
                                <div class="metric-item">
                                    <span class="metric-value red-text">{{ $data['gold_transactions']['total_gold_buy_price'] }}</span>
                                    <span class="metric-label">ریال</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-value red-text">{{ number_format($data['gold_transactions']['total_gold_buy_amount'], 3) }}</span>
                                    <span class="metric-label">گرم</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Surplus Card -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="widget widget-card-one">
                    <div class="widget-content">
                        <div class="media">
                            <div class="w-img {{ $data['gold_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-bg' : 'red-bg' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                </svg>
                            </div>
                            <div class="media-body">
                                <h6>مازاد</h6>
                                <div class="metric-item">
                                    <span class="metric-value {{ $data['gold_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-text' : 'red-text' }}">{{ $data['gold_transactions']['mazaz_kharid_va_foroosh_price'] }}</span>
                                    <span class="metric-label">ریال</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-value {{ $data['gold_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-text' : 'red-text' }}">{{ number_format($data['gold_transactions']['mazaz_kharid_va_foroosh_amount'], 3) }}</span>
                                    <span class="metric-label">گرم</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="widget widget-card-one">
                    <div class="widget-content">
                        <div class="media">
                            <div class="w-img {{ $data['gold_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-bg' : 'red-bg' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
                            </div>
                            <div class="media-body">
                                <h6>وضعیت</h6>
                                <div class="metric-item">
                                    @if($data['gold_transactions']['mazaz_kharid_va_foroosh_amount'] > 0)
                                        <span class="badge badge-success">مازاد مثبت</span>
                                    @elseif($data['gold_transactions']['mazaz_kharid_va_foroosh_amount'] < 0)
                                        <span class="badge badge-danger">مازاد منفی</span>
                                    @else
                                        <span class="badge badge-secondary">متوازن</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gold Quotes Details Section -->
    @if(isset($data['gold_quotes_details']) && count($data['gold_quotes_details']) > 0)
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-card-four">
            <div class="widget-content">
                <div class="w-header">
                    <div class="w-info">
                        <h6 class="value">آمار معاملات طلا</h6>
                        <p class="text-muted mb-0">نمایش کلیه خرید، فروش و مازاد معاملات طلا</p>
                    </div>
                    <div class="w-action">
                        <button class="main-collapse-toggle" data-target="gold-quotes-details">
                            <span class="toggle-text">نمایش جزئیات مظنه‌ها</span>
                            <svg class="toggle-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="w-content">
                    <div class="main-collapsible-content collapsed" id="gold-quotes-details">
                        <div class="gold-quotes-grid">
                            @foreach($data['gold_quotes_details'] as $quote)
                            <div class="quote-card">
                                <div class="quote-header">
                                    <div class="quote-title">
                                        <h6>{{ $quote['name'] }}</h6>
                                        @if($quote['description'])
                                            <p class="quote-description">{{ $quote['description'] }}</p>
                                        @endif
                                    </div>
                                    <div class="quote-status">
                                        <span class="badge badge-{{ $quote['status'] === 'active' ? 'success' : ($quote['status'] === 'inactive' ? 'warning' : 'secondary') }}">
                                            {{ $quote['status'] === 'active' ? 'فعال' : ($quote['status'] === 'inactive' ? 'غیرفعال' : 'مخفی') }}
                                        </span>
                                    </div>
                                </div>

                                @if($quote['latest_price'])
                                <div class="quote-prices">
                                    <div class="price-item">
                                        <span class="price-label">قیمت خرید:</span>
                                        <span class="price-value {{ $quote['latest_price']['buy_status'] === 'enabled' ? 'green-text' : 'text-muted' }}">
                                            {{ number_format($quote['latest_price']['buy_price']) }} ریال
                                        </span>
                                        <span class="price-status {{ $quote['latest_price']['buy_status'] === 'enabled' ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $quote['latest_price']['buy_status'] === 'enabled' ? 'فعال' : 'غیرفعال' }}
                                        </span>
                                    </div>
                                    <div class="price-item">
                                        <span class="price-label">قیمت فروش:</span>
                                        <span class="price-value {{ $quote['latest_price']['sell_status'] === 'enabled' ? 'red-text' : 'text-muted' }}">
                                            {{ number_format($quote['latest_price']['sell_price']) }} ریال
                                        </span>
                                        <span class="price-status {{ $quote['latest_price']['sell_status'] === 'enabled' ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $quote['latest_price']['sell_status'] === 'enabled' ? 'فعال' : 'غیرفعال' }}
                                        </span>
                                    </div>
                                </div>
                                @endif

                                <!-- Collapsible Details Section -->
                                <div class="quote-details-collapsible">
                                    <button class="collapse-toggle" data-target="details-{{ $quote['id'] }}">
                                        <span class="toggle-text">نمایش جزئیات</span>
                                        <svg class="toggle-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </button>
                                    
                                    <div class="collapsible-content collapsed" id="details-{{ $quote['id'] }}">
                                        <!-- Statistics Section -->
                                        <div class="quote-statistics">
                                            <!-- خرید Row -->
                                            <div class="stat-row">
                                                <div class="stat-col">
                                                    <div class="stat-header green-text">خرید</div>
                                                    <div class="stat-value green-text">{{ $quote['statistics']['buy']['total_price'] }}</div>
                                                    <div class="stat-label">ریال</div>
                                                    <div class="stat-value green-text">{{ number_format((float)$quote['statistics']['buy']['total_amount'], 3) }}</div>
                                                    <div class="stat-label">گرم ({{ $quote['statistics']['buy']['transaction_count'] }} تراکنش)</div>
                                                </div>
                                            </div>
                                            
                                            <!-- فروش Row -->
                                            <div class="stat-row">
                                                <div class="stat-col">
                                                    <div class="stat-header red-text">فروش</div>
                                                    <div class="stat-value red-text">{{ $quote['statistics']['sell']['total_price'] }}</div>
                                                    <div class="stat-label">ریال</div>
                                                    <div class="stat-value red-text">{{ number_format((float)$quote['statistics']['sell']['total_amount'], 3) }}</div>
                                                    <div class="stat-label">گرم ({{ $quote['statistics']['sell']['transaction_count'] }} تراکنش)</div>
                                                </div>
                                            </div>
                                            
                                            <!-- مازاد Row -->
                                            <div class="stat-row">
                                                <div class="stat-col">
                                                    <div class="stat-header {{ $quote['statistics']['surplus']['amount'] >= 0 ? 'green-text' : 'red-text' }}">مازاد</div>
                                                    <div class="stat-value {{ $quote['statistics']['surplus']['amount'] >= 0 ? 'green-text' : 'red-text' }}">{{ $quote['statistics']['surplus']['price'] }}</div>
                                                    <div class="stat-label">ریال</div>
                                                    <div class="stat-value {{ $quote['statistics']['surplus']['amount'] >= 0 ? 'green-text' : 'red-text' }}">{{ number_format((float)$quote['statistics']['surplus']['amount'], 3) }}</div>
                                                    <div class="stat-label">گرم</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Recent Transactions -->
                                        <div class="quote-transactions">
                                            <div class="transactions-header">
                                                <h6>آخرین تراکنش‌ها</h6>
                                            </div>
                                            <div class="transactions-content">
                                                <div class="transaction-tabs">
                                                    <button class="tab-btn active" data-tab="buy-{{ $quote['id'] }}">خرید ({{ count($quote['recent_transactions']['buy']) }})</button>
                                                    <button class="tab-btn" data-tab="sell-{{ $quote['id'] }}">فروش ({{ count($quote['recent_transactions']['sell']) }})</button>
                                                </div>
                                                
                                                <div class="tab-content active" id="buy-{{ $quote['id'] }}">
                                                    @if(count($quote['recent_transactions']['buy']) > 0)
                                                        @foreach($quote['recent_transactions']['buy'] as $transaction)
                                                        <div class="transaction-item">
                                                            <div class="transaction-info">
                                                                <span class="user-name">{{ $transaction['user_name'] }}</span>
                                                                <span class="transaction-amount">{{ number_format((float)$transaction['amount'], 3) }} گرم</span>
                                                                <span class="transaction-price">{{ $transaction['total_price'] }} ریال</span>
                                                            </div>
                                                            <div class="transaction-meta">
                                                                <span class="transaction-status badge-{{ $transaction['status'] === 'accepted' ? 'success' : ($transaction['status'] === 'pending' ? 'warning' : 'danger') }}">
                                                                    {{ $transaction['status'] === 'accepted' ? 'تایید شده' : ($transaction['status'] === 'pending' ? 'در انتظار' : 'رد شده') }}
                                                                </span>
                                                                <span class="transaction-date">{{ $transaction['created_at'] }}</span>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @else
                                                        <div class="no-transactions">هیچ تراکنش خریدی یافت نشد</div>
                                                    @endif
                                                </div>
                                                
                                                <div class="tab-content" id="sell-{{ $quote['id'] }}">
                                                    @if(count($quote['recent_transactions']['sell']) > 0)
                                                        @foreach($quote['recent_transactions']['sell'] as $transaction)
                                                        <div class="transaction-item">
                                                            <div class="transaction-info">
                                                                <span class="user-name">{{ $transaction['user_name'] }}</span>
                                                                <span class="transaction-amount">{{ number_format((float)$transaction['amount'], 3) }} گرم</span>
                                                                <span class="transaction-price">{{ $transaction['total_price'] }} ریال</span>
                                                            </div>
                                                            <div class="transaction-meta">
                                                                <span class="transaction-status badge-{{ $transaction['status'] === 'accepted' ? 'success' : ($transaction['status'] === 'pending' ? 'warning' : 'danger') }}">
                                                                    {{ $transaction['status'] === 'accepted' ? 'تایید شده' : ($transaction['status'] === 'pending' ? 'در انتظار' : 'رد شده') }}
                                                                </span>
                                                                <span class="transaction-date">{{ $transaction['created_at'] }}</span>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @else
                                                        <div class="no-transactions">هیچ تراکنش فروشی یافت نشد</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Coin Transactions Section -->
<div class="row layout-spacing">
    <!-- Page Header -->
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget-header">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <h4 class="mb-0">آمار معاملات سکه</h4>
                    <p class="text-muted mb-0">نمایش کلیه خرید، فروش و مازاد معاملات سکه</p>
                </div>
            </div>
        </div>
    </div>

    <!-- خرید Section -->
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-card-four">
            <div class="widget-content">
                <div class="w-header">
                    <div class="w-info">
                        <h6 class="value">خرید سکه</h6>
                        <p class="text-muted">کل معاملات خرید</p>
                    </div>
                </div>

                <div class="w-content">
                    <div class="w-info">
                        <!-- ریال Section -->
                        <div class="stat-item">
                            <div class="stat-icon green-bg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value green-text">{{ $data['coin_transactions']['total_coin_sell_price'] }}</span>
                                <span class="stat-label">ریال</span>
                            </div>
                        </div>
                        
                        <!-- عدد Section -->
                        <div class="stat-item">
                            <div class="stat-icon green-bg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-zap">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                </svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value green-text">{{ number_format($data['coin_transactions']['total_coin_sell_amount']) }}</span>
                                <span class="stat-label">عدد</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- فروش Section -->
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-card-four">
            <div class="widget-content">
                <div class="w-header">
                    <div class="w-info">
                        <h6 class="value">فروش سکه</h6>
                        <p class="text-muted">کل معاملات فروش</p>
                    </div>
                </div>

                <div class="w-content">
                    <div class="w-info">
                        <!-- ریال Section -->
                        <div class="stat-item">
                            <div class="stat-icon red-bg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-dollar-sign">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value red-text">{{ $data['coin_transactions']['total_coin_buy_price'] }}</span>
                                <span class="stat-label">ریال</span>
                            </div>
                        </div>
                        
                        <!-- عدد Section -->
                        <div class="stat-item">
                            <div class="stat-icon red-bg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-zap">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                </svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value red-text">{{ number_format($data['coin_transactions']['total_coin_buy_amount']) }}</span>
                                <span class="stat-label">عدد</span>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
    </div>

    <!-- مازاد خرید و فروش Section -->
    <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-card-four">
            <div class="widget-content">
                <div class="w-header">
                    <div class="w-info">
                        <h6 class="value">مازاد خرید و فروش</h6>
                        <p class="text-muted">تفاوت خرید و فروش</p>
                    </div>
                </div>

                <div class="w-content">
                    <div class="w-info">
                        <!-- ریال Section -->
                        <div class="stat-item">
                            <div class="stat-icon {{ $data['coin_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-bg' : 'red-bg' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                </svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value {{ $data['coin_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-text' : 'red-text' }}">{{ $data['coin_transactions']['mazaz_kharid_va_foroosh_price'] }}</span>
                                <span class="stat-label">ریال</span>
                            </div>
                        </div>
                        
                        <!-- عدد Section -->
                        <div class="stat-item">
                            <div class="stat-icon {{ $data['coin_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-bg' : 'red-bg' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-zap">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                </svg>
                            </div>
                            <div class="stat-details">
                                <span class="stat-value {{ $data['coin_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-text' : 'red-text' }}">{{ number_format($data['coin_transactions']['mazaz_kharid_va_foroosh_amount']) }}</span>
                                <span class="stat-label">عدد</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards Row -->
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="row">
            <!-- Total Buy Card -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="widget widget-card-one">
                    <div class="widget-content">
                        <div class="media">
                            <div class="w-img green-bg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                            </div>
                            <div class="media-body">
                                <h6>کل خرید</h6>
                                <div class="metric-item">
                                    <span class="metric-value green-text">{{ $data['coin_transactions']['total_coin_sell_price'] }}</span>
                                    <span class="metric-label">ریال</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-value green-text">{{ number_format($data['coin_transactions']['total_coin_sell_amount']) }}</span>
                                    <span class="metric-label">عدد</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Sell Card -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="widget widget-card-one">
                    <div class="widget-content">
                        <div class="media">
                            <div class="w-img red-bg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-credit-card">
                                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                    <line x1="1" y1="10" x2="23" y2="10"></line>
                                </svg>
                            </div>
                            <div class="media-body">
                                <h6>کل فروش</h6>
                                <div class="metric-item">
                                    <span class="metric-value red-text">{{ $data['coin_transactions']['total_coin_buy_price'] }}</span>
                                    <span class="metric-label">ریال</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-value red-text">{{ number_format($data['coin_transactions']['total_coin_buy_amount']) }}</span>
                                    <span class="metric-label">عدد</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Surplus Card -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="widget widget-card-one">
                    <div class="widget-content">
                        <div class="media">
                            <div class="w-img {{ $data['coin_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-bg' : 'red-bg' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                </svg>
                            </div>
                            <div class="media-body">
                                <h6>مازاد</h6>
                                <div class="metric-item">
                                    <span class="metric-value {{ $data['coin_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-text' : 'red-text' }}">{{ $data['coin_transactions']['mazaz_kharid_va_foroosh_price'] }}</span>
                                    <span class="metric-label">ریال</span>
                                </div>
                                <div class="metric-item">
                                    <span class="metric-value {{ $data['coin_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-text' : 'red-text' }}">{{ number_format($data['coin_transactions']['mazaz_kharid_va_foroosh_amount']) }}</span>
                                    <span class="metric-label">عدد</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 col-12">
                <div class="widget widget-card-one">
                    <div class="widget-content">
                        <div class="media">
                            <div class="w-img {{ $data['coin_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'green-bg' : 'red-bg' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-activity">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
                            </div>
                            <div class="media-body">
                                <h6>وضعیت</h6>
                                <div class="metric-item">
                                    @if($data['coin_transactions']['mazaz_kharid_va_foroosh_amount'] > 0)
                                        <span class="badge badge-success">مازاد مثبت</span>
                                    @elseif($data['coin_transactions']['mazaz_kharid_va_foroosh_amount'] < 0)
                                        <span class="badge badge-danger">مازاد منفی</span>
                                    @else
                                        <span class="badge badge-secondary">متوازن</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Coins Details Section -->
    @if(isset($data['coins_details']) && count($data['coins_details']) > 0)
    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 layout-spacing">
        <div class="widget widget-card-four">
            <div class="widget-content">
                <div class="w-header">
                    <div class="w-info">
                        <h6 class="value">آمار معاملات سکه</h6>
                        <p class="text-muted mb-0">نمایش کلیه خرید، فروش و مازاد معاملات سکه</p>
                    </div>
                    <div class="w-action">
                        <button class="main-collapse-toggle" data-target="coins-details">
                            <span class="toggle-text">نمایش جزئیات سکه‌ها</span>
                            <svg class="toggle-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="w-content">
                    <div class="main-collapsible-content collapsed" id="coins-details">
                        <div class="gold-quotes-grid">
                            @foreach($data['coins_details'] as $coin)
                            <div class="quote-card">
                                <div class="quote-header">
                                    <div class="quote-title">
                                        <h6>{{ $coin['name'] }}</h6>
                                        @if($coin['description'])
                                            <p class="quote-description">{{ $coin['description'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                @if($coin['latest_price'])
                                <div class="quote-prices">
                                    <div class="price-item">
                                        <span class="price-label">قیمت خرید:</span>
                                        <span class="price-value {{ $coin['latest_price']['buy_status'] === 'enabled' ? 'green-text' : 'text-muted' }}">
                                            {{ number_format($coin['latest_price']['buy_price']) }} ریال
                                        </span>
                                        <span class="price-status {{ $coin['latest_price']['buy_status'] === 'enabled' ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $coin['latest_price']['buy_status'] === 'enabled' ? 'فعال' : 'غیرفعال' }}
                                        </span>
                                    </div>
                                    <div class="price-item">
                                        <span class="price-label">قیمت فروش:</span>
                                        <span class="price-value {{ $coin['latest_price']['sell_status'] === 'enabled' ? 'red-text' : 'text-muted' }}">
                                            {{ number_format($coin['latest_price']['sell_price']) }} ریال
                                        </span>
                                        <span class="price-status {{ $coin['latest_price']['sell_status'] === 'enabled' ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $coin['latest_price']['sell_status'] === 'enabled' ? 'فعال' : 'غیرفعال' }}
                                        </span>
                                    </div>
                                </div>
                                @endif

                                <!-- Collapsible Details Section -->
                                <div class="quote-details-collapsible">
                                    <button class="collapse-toggle" data-target="coin-details-{{ $coin['id'] }}">
                                        <span class="toggle-text">نمایش جزئیات</span>
                                        <svg class="toggle-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="6 9 12 15 18 9"></polyline>
                                        </svg>
                                    </button>
                                    
                                    <div class="collapsible-content collapsed" id="coin-details-{{ $coin['id'] }}">
                                        <!-- Statistics Section -->
                                        <div class="quote-statistics">
                                            <!-- خرید Row -->
                                            <div class="stat-row">
                                                <div class="stat-col">
                                                    <div class="stat-header green-text">خرید</div>
                                                    <div class="stat-value green-text">{{ $coin['statistics']['buy']['total_price'] }}</div>
                                                    <div class="stat-label">ریال</div>
                                                    <div class="stat-value green-text">{{ number_format((float)$coin['statistics']['buy']['total_amount']) }}</div>
                                                    <div class="stat-label">عدد ({{ $coin['statistics']['buy']['transaction_count'] }} تراکنش)</div>
                                                </div>
                                            </div>
                                            
                                            <!-- فروش Row -->
                                            <div class="stat-row">
                                                <div class="stat-col">
                                                    <div class="stat-header red-text">فروش</div>
                                                    <div class="stat-value red-text">{{ $coin['statistics']['sell']['total_price'] }}</div>
                                                    <div class="stat-label">ریال</div>
                                                    <div class="stat-value red-text">{{ number_format((float)$coin['statistics']['sell']['total_amount']) }}</div>
                                                    <div class="stat-label">عدد ({{ $coin['statistics']['sell']['transaction_count'] }} تراکنش)</div>
                                                </div>
                                            </div>
                                            
                                            <!-- مازاد Row -->
                                            <div class="stat-row">
                                                <div class="stat-col">
                                                    <div class="stat-header {{ $coin['statistics']['surplus']['amount'] >= 0 ? 'green-text' : 'red-text' }}">مازاد</div>
                                                    <div class="stat-value {{ $coin['statistics']['surplus']['amount'] >= 0 ? 'green-text' : 'red-text' }}">{{ $coin['statistics']['surplus']['price'] }}</div>
                                                    <div class="stat-label">ریال</div>
                                                    <div class="stat-value {{ $coin['statistics']['surplus']['amount'] >= 0 ? 'green-text' : 'red-text' }}">{{ number_format((float)$coin['statistics']['surplus']['amount']) }}</div>
                                                    <div class="stat-label">عدد</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Recent Transactions -->
                                        <div class="quote-transactions">
                                            <div class="transactions-header">
                                                <h6>آخرین تراکنش‌ها</h6>
                                            </div>
                                            <div class="transactions-content">
                                                <div class="transaction-tabs">
                                                    <button class="tab-btn active" data-tab="coin-buy-{{ $coin['id'] }}">خرید ({{ count($coin['recent_transactions']['buy']) }})</button>
                                                    <button class="tab-btn" data-tab="coin-sell-{{ $coin['id'] }}">فروش ({{ count($coin['recent_transactions']['sell']) }})</button>
                                                </div>
                                                
                                                <div class="tab-content active" id="coin-buy-{{ $coin['id'] }}">
                                                    @if(count($coin['recent_transactions']['buy']) > 0)
                                                        @foreach($coin['recent_transactions']['buy'] as $transaction)
                                                        <div class="transaction-item">
                                                            <div class="transaction-info">
                                                                <span class="user-name">{{ $transaction['user_name'] }}</span>
                                                                <span class="transaction-amount">{{ number_format((float)$transaction['amount']) }} عدد</span>
                                                                <span class="transaction-price">{{ $transaction['total_price'] }} ریال</span>
                                                            </div>
                                                            <div class="transaction-meta">
                                                                <span class="transaction-status badge-{{ $transaction['status'] === 'accepted' ? 'success' : ($transaction['status'] === 'pending' ? 'warning' : 'danger') }}">
                                                                    {{ $transaction['status'] === 'accepted' ? 'تایید شده' : ($transaction['status'] === 'pending' ? 'در انتظار' : 'رد شده') }}
                                                                </span>
                                                                <span class="transaction-date">{{ $transaction['created_at'] }}</span>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @else
                                                        <div class="no-transactions">هیچ تراکنش خریدی یافت نشد</div>
                                                    @endif
                                                </div>
                                                
                                                <div class="tab-content" id="coin-sell-{{ $coin['id'] }}">
                                                    @if(count($coin['recent_transactions']['sell']) > 0)
                                                        @foreach($coin['recent_transactions']['sell'] as $transaction)
                                                        <div class="transaction-item">
                                                            <div class="transaction-info">
                                                                <span class="user-name">{{ $transaction['user_name'] }}</span>
                                                                <span class="transaction-amount">{{ number_format((float)$transaction['amount']) }} عدد</span>
                                                                <span class="transaction-price">{{ $transaction['total_price'] }} ریال</span>
                                                            </div>
                                                            <div class="transaction-meta">
                                                                <span class="transaction-status badge-{{ $transaction['status'] === 'accepted' ? 'success' : ($transaction['status'] === 'pending' ? 'warning' : 'danger') }}">
                                                                    {{ $transaction['status'] === 'accepted' ? 'تایید شده' : ($transaction['status'] === 'pending' ? 'در انتظار' : 'رد شده') }}
                                                                </span>
                                                                <span class="transaction-date">{{ $transaction['created_at'] }}</span>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @else
                                                        <div class="no-transactions">هیچ تراکنش فروشی یافت نشد</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
            </div>
        </div>
    </div>
    </div>
    @endif
</div>

<style>
.widget-card-four {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 0 40px 0 rgba(94, 92, 154, 0.06);
    border: 1px solid #e0e6ed;
    transition: all 0.3s ease;
}

.widget-card-four:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px 0 rgba(94, 92, 154, 0.15);
}

.widget-card-four .widget-content {
    padding: 25px;
}

.widget-card-four .w-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.widget-card-four .w-header .w-info h6 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #3b3f5c;
}

.widget-card-four .w-header .w-info p {
    font-size: 13px;
    color: #888ea8;
    margin: 0;
}

.widget-card-four .w-header .w-action {
    display: flex;
    align-items: center;
}

.main-collapse-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 15px;
    background: #4361ee;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 13px;
    font-weight: 500;
    color: #fff;
    min-width: 180px;
}

.main-collapse-toggle:hover {
    background: #3a56d4;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.main-collapse-toggle:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
}

.main-collapse-toggle .toggle-text {
    margin-left: 8px;
}

.main-collapse-toggle .toggle-icon {
    transition: transform 0.3s ease;
    color: #fff;
}

.stat-item {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-right: 4px solid;
}

.stat-item:last-child {
    margin-bottom: 0;
}

.stat-item .green-bg {
    border-color: #00e396;
}

.stat-item .red-bg {
    border-color: #e74c3c;
}

.stat-icon {
    margin-left: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    color: #fff;
    flex-shrink: 0;
}

.green-bg {
    background: #00e396;
}

.red-bg {
    background: #e74c3c;
}

.stat-details {
    flex: 1;
}

.stat-value {
    display: block;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 5px;
    line-height: 1.2;
}

.green-text {
    color: #00e396;
}

.red-text {
    color: #e74c3c;
}

.stat-label {
    font-size: 13px;
    color: #888ea8;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 500;
}

.widget-card-one {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 0 40px 0 rgba(94, 92, 154, 0.06);
    border: 1px solid #e0e6ed;
    transition: all 0.3s ease;
}

.widget-card-one:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px 0 rgba(94, 92, 154, 0.15);
}

.widget-card-one .widget-content {
    padding: 20px;
}

.widget-card-one .media {
    display: flex;
    align-items: flex-start;
}

.widget-card-one .w-img {
    margin-left: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    color: #fff;
    flex-shrink: 0;
}

.widget-card-one .media-body {
    flex: 1;
}

.widget-card-one .media-body h6 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #3b3f5c;
}

.metric-item {
    margin-bottom: 12px;
    display: flex;
    flex-direction: column;
}

.metric-item:last-child {
    margin-bottom: 0;
}

.metric-value {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 3px;
    line-height: 1.2;
}

.metric-label {
    font-size: 12px;
    color: #888ea8;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 500;
}

.badge {
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: inline-block;
}

.badge-success {
    background: rgba(0, 227, 150, 0.14);
    color: #00e396;
}

.badge-danger {
    background: rgba(231, 76, 60, 0.14);
    color: #e74c3c;
}

.badge-secondary {
    background: rgba(59, 63, 92, 0.14);
    color: #3b3f5c;
}

.badge-warning {
    background: rgba(243, 156, 18, 0.14);
    color: #f39c12;
}

/* Gold Quotes Details Styles */
.gold-quotes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
    align-items: start;
}

.widget-card-four .w-content {
    overflow: visible;
}

.widget-card-four .main-collapsible-content.expanded {
    overflow: visible;
    max-height: none;
}

.quote-card {
    background: #fff;
    border: 1px solid #e0e6ed;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    max-height: none;
    overflow: visible;
}

.quote-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.quote-card .collapsible-content.expanded {
    max-height: none;
    overflow: visible;
}

.quote-card .quote-transactions {
    border-top: 1px solid #f1f2f3;
    padding-top: 15px;
    overflow: visible;
}

.quote-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f1f2f3;
}

.quote-title h6 {
    font-size: 16px;
    font-weight: 600;
    color: #3b3f5c;
    margin: 0 0 5px 0;
}

.quote-description {
    font-size: 12px;
    color: #888ea8;
    margin: 0;
}

.quote-prices {
    margin-bottom: 20px;
}

.price-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 8px;
    padding: 8px 12px;
    background: #f8f9fa;
    border-radius: 6px;
}

.price-label {
    font-size: 12px;
    color: #888ea8;
    font-weight: 500;
}

.price-value {
    font-size: 14px;
    font-weight: 600;
}

.price-status {
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 12px;
}

.quote-statistics {
    margin-bottom: 20px;
}

.stat-row {
    display: flex;
    margin-bottom: 15px;
}

.stat-row:last-child {
    margin-bottom: 0;
}

.stat-col {
    flex: 1;
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-right: 3px solid;
}

.stat-col:nth-child(1) {
    border-color: #00e396;
}

.stat-col:nth-child(2) {
    border-color: #e74c3c;
}

.stat-col:nth-child(3) {
    border-color: #4361ee;
}

.stat-header {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}

.quote-transactions {
    border-top: 1px solid #f1f2f3;
    padding-top: 15px;
}

.transactions-header h6 {
    font-size: 14px;
    font-weight: 600;
    color: #3b3f5c;
    margin-bottom: 15px;
}

.transaction-tabs {
    display: flex;
    margin-bottom: 15px;
    border-bottom: 1px solid #e0e6ed;
}

.tab-btn {
    flex: 1;
    padding: 10px;
    background: none;
    border: none;
    font-size: 12px;
    font-weight: 500;
    color: #888ea8;
    cursor: pointer;
    transition: all 0.3s ease;
    border-bottom: 2px solid transparent;
}

.tab-btn.active {
    color: #3b3f5c;
    border-bottom-color: #4361ee;
}

.tab-content {
    display: none;
    max-height: 300px;
    overflow-y: auto;
    padding: 10px;
    border: 1px solid #e0e6ed;
    border-radius: 6px;
    background: #f8f9fa;
}

.tab-content.active {
    display: block;
}

.transaction-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f1f2f3;
}

.transaction-item:last-child {
    border-bottom: none;
}

.transaction-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.user-name {
    font-size: 12px;
    font-weight: 600;
    color: #3b3f5c;
}

.transaction-amount {
    font-size: 11px;
    color: #888ea8;
}

.transaction-price {
    font-size: 11px;
    color: #888ea8;
}

.transaction-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 2px;
}

.transaction-status {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    text-transform: uppercase;
}

.transaction-date {
    font-size: 10px;
    color: #888ea8;
}

.badge-success {
    background: rgba(0, 227, 150, 0.14);
    color: #00e396;
}

.badge-warning {
    background: rgba(243, 156, 18, 0.14);
    color: #f39c12;
}

.badge-danger {
    background: rgba(231, 76, 60, 0.14);
    color: #e74c3c;
}

.no-transactions {
    text-align: center;
    padding: 20px;
    color: #888ea8;
    font-size: 12px;
}

/* Collapsible Styles */
.quote-details-collapsible {
    margin-top: 15px;
}

.collapse-toggle {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 15px;
    background: #f8f9fa;
    border: 1px solid #e0e6ed;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 500;
    color: #3b3f5c;
}

.collapse-toggle:hover {
    background: #e9ecef;
    border-color: #4361ee;
}

.collapse-toggle:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.2);
}

.toggle-text {
    margin-left: 8px;
}

.toggle-icon {
    transition: transform 0.3s ease;
    color: #4361ee;
}

.collapsible-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    border-top: 0px solid #e0e6ed;
}

.collapsible-content.collapsed {
    max-height: 0;
    border-top-width: 0;
}

.collapsible-content.expanded {
    max-height: 3000px;
    border-top-width: 1px;
    padding-top: 15px;
    margin-top: 10px;
    overflow-y: auto;
}

.collapsible-content.expanded .toggle-icon {
    transform: rotate(180deg);
}

.main-collapsible-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    border-top: 0px solid #e0e6ed;
}

.main-collapsible-content.collapsed {
    max-height: 0;
    border-top-width: 0;
}

.main-collapsible-content.expanded {
    max-height: 5000px;
    border-top-width: 1px;
    padding-top: 15px;
    margin-top: 10px;
    overflow-y: auto;
}

.main-collapsible-content.expanded .toggle-icon {
    transform: rotate(180deg);
}

@media (max-width: 768px) {
    .widget-card-four .widget-content {
        padding: 20px;
    }
    
    .stat-value {
        font-size: 18px;
    }
    
    .widget-card-one .widget-content {
        padding: 15px;
    }
    
    .stat-item {
        padding: 12px;
        margin-bottom: 15px;
    }
    
    .stat-icon {
        width: 45px;
        height: 45px;
    }
    
    .gold-quotes-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .quote-card {
        padding: 15px;
    }
    
    .stat-row {
        flex-direction: column;
        gap: 10px;
    }
    
    .transaction-tabs {
        flex-direction: column;
    }
    
    .tab-btn {
        text-align: center;
        border-bottom: 1px solid #e0e6ed;
    }
}
</style>

<script>
$(document).ready(function() {
    // Tab functionality for transaction tabs
    $('.tab-btn').on('click', function() {
        const tabId = $(this).data('tab');
        const quoteCard = $(this).closest('.quote-card');
        
        // Remove active class from all tabs and contents in this quote card
        quoteCard.find('.tab-btn').removeClass('active');
        quoteCard.find('.tab-content').removeClass('active');
        
        // Add active class to clicked tab and corresponding content
        $(this).addClass('active');
        $('#' + tabId).addClass('active');
    });

    // Collapsible functionality
    $('.collapse-toggle').on('click', function() {
        const targetId = $(this).data('target');
        const content = $('#' + targetId);
        const toggleText = $(this).find('.toggle-text');
        const toggleIcon = $(this).find('.toggle-icon');
        
        if (content.hasClass('collapsed')) {
            // Expand
            content.removeClass('collapsed').addClass('expanded');
            toggleText.text('مخفی کردن جزئیات');
            toggleIcon.css('transform', 'rotate(180deg)');
            
            // Ensure proper scrolling after expansion
            setTimeout(function() {
                const quoteCard = content.closest('.quote-card');
                if (quoteCard.length) {
                    quoteCard[0].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            }, 350); // Wait for transition to complete
        } else {
            // Collapse
            content.removeClass('expanded').addClass('collapsed');
            toggleText.text('نمایش جزئیات');
            toggleIcon.css('transform', 'rotate(0deg)');
        }
    });

    // Main collapsible toggle for Gold Quotes Details
    $('.main-collapse-toggle').on('click', function() {
        const targetId = $(this).data('target');
        const mainContent = $('#' + targetId);
        const toggleText = $(this).find('.toggle-text');
        const toggleIcon = $(this).find('.toggle-icon');

        if (mainContent.hasClass('collapsed')) {
            // Expand
            mainContent.removeClass('collapsed').addClass('expanded');
            
            // Update text based on target
            if (targetId === 'gold-quotes-details') {
                toggleText.text('مخفی کردن جزئیات مظنه‌ها');
            } else if (targetId === 'coins-details') {
                toggleText.text('مخفی کردن جزئیات سکه‌ها');
            }
            
            toggleIcon.css('transform', 'rotate(180deg)');
        } else {
            // Collapse
            mainContent.removeClass('expanded').addClass('collapsed');
            
            // Update text based on target
            if (targetId === 'gold-quotes-details') {
                toggleText.text('نمایش جزئیات مظنه‌ها');
            } else if (targetId === 'coins-details') {
                toggleText.text('نمایش جزئیات سکه‌ها');
            }
            
            toggleIcon.css('transform', 'rotate(0deg)');
        }
    });
});
</script>