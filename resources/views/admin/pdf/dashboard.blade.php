<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>گزارش داشبورد مدیریت</title>
    <style>
        @font-face {
            font-family: 'Vazir';
            src: url('{{ public_path("assets/fonts/vazir/Vazir-Regular.woff2") }}') format('woff2'),
                 url('{{ public_path("assets/fonts/vazir/Vazir-Regular.woff") }}') format('woff');
            font-weight: normal;
            font-style: normal;
        }
        
        @font-face {
            font-family: 'Vazir';
            src: url('{{ public_path("assets/fonts/vazir/Vazir-Bold.woff2") }}') format('woff2'),
                 url('{{ public_path("assets/fonts/vazir/Vazir-Bold.woff") }}') format('woff');
            font-weight: bold;
            font-style: normal;
        }
        
        body {
            font-family: 'Vazir', 'Tahoma', Arial, sans-serif;
            direction: rtl;
            margin: 0;
            padding: 15px;
            color: #333;
            font-size: 11px;
            line-height: 1.3;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 12px;
        }
        
        .header h1 {
            color: var(--primary-color);
            font-size: 18px;
            margin: 0 0 6px 0;
            font-weight: bold;
        }
        
        .header .subtitle {
            font-size: 10px;
            color: #666;
            margin: 2px 0;
        }
        
        .section {
            margin-bottom: 15px;
        }
        
        .section-title {
            background: var(--primary-color);
            color: white;
            padding: 6px 10px;
            margin: 0 0 10px 0;
            font-size: 12px;
            font-weight: bold;
        }
        
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        
        .stats-table th {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
        }
        
        .stats-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
            font-size: 10px;
        }
        
        .stats-table .gold-col {
            background: #fff3cd;
        }
        
        .stats-table .coin-col {
            background: #d1ecf1;
        }
        
        .stats-table .surplus-col {
            background: #d4edda;
        }
        
        .positive {
            color: #28a745;
            font-weight: bold;
        }
        
        .negative {
            color: #dc3545;
            font-weight: bold;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .details-table th {
            background: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
            padding: 4px;
            text-align: center;
            font-size: 9px;
        }
        
        .details-table td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: center;
            font-size: 9px;
        }
        
        .details-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-hidden {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .quote-card {
            margin-bottom: 12px;
            border: 1px solid #ddd;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .quote-header {
            background: #f8f9fa;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        
        .quote-title {
            margin: 0 0 4px 0;
            color: var(--primary-color);
            font-size: 11px;
            font-weight: bold;
        }
        
        .quote-description {
            margin: 0 0 4px 0;
            font-size: 9px;
            color: #666;
        }
        
        .quote-content {
            padding: 8px;
            background: white;
        }
        
        .price-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        
        .price-table td {
            border: 1px solid #eee;
            padding: 4px;
            text-align: center;
            font-size: 9px;
        }
        
        .stats-content {
            padding: 8px;
            background: #f8f9fa;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid var(--primary-color);
            padding-top: 8px;
        }
        
        /* Remove page breaks to prevent empty pages */
        .no-page-break {
            page-break-inside: avoid;
        }
        
        .section-break {
            page-break-before: avoid;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>گزارش داشبورد مدیریت</h1>
        <div class="subtitle">تاریخ تولید: {{ $data['generated_at'] }}</div>
        <div class="subtitle">گزارش جامع معاملات طلا و سکه</div>
    </div>

    <!-- Gold Transactions Overview -->
    <div class="section no-page-break">
        <h2 class="section-title">آمار کلی معاملات طلا</h2>
        
        <table class="stats-table">
            <thead>
                <tr>
                    <th style="width: 33%;">خرید طلا</th>
                    <th style="width: 33%;">فروش طلا</th>
                    <th style="width: 34%;">مازاد خرید و فروش</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="gold-col">
                        <div style="margin-bottom: 4px;">
                            <strong>{{ $data['dashboard_data']['gold_transactions']['total_gold_sell_price'] }}</strong><br>
                            <small>ریال</small>
                        </div>
                        <div>
                            <strong>{{ number_format($data['dashboard_data']['gold_transactions']['total_gold_sell_amount'], 3) }}</strong><br>
                            <small>گرم</small>
                        </div>
                    </td>
                    <td class="gold-col">
                        <div style="margin-bottom: 4px;">
                            <strong>{{ $data['dashboard_data']['gold_transactions']['total_gold_buy_price'] }}</strong><br>
                            <small>ریال</small>
                        </div>
                        <div>
                            <strong>{{ number_format($data['dashboard_data']['gold_transactions']['total_gold_buy_amount'], 3) }}</strong><br>
                            <small>گرم</small>
                        </div>
                    </td>
                    <td class="surplus-col">
                        <div style="margin-bottom: 4px;">
                            <strong class="{{ $data['dashboard_data']['gold_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'positive' : 'negative' }}">
                                {{ $data['dashboard_data']['gold_transactions']['mazaz_kharid_va_foroosh_price'] }}
                            </strong><br>
                            <small>ریال</small>
                        </div>
                        <div>
                            <strong class="{{ $data['dashboard_data']['gold_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'positive' : 'negative' }}">
                                {{ number_format($data['dashboard_data']['gold_transactions']['mazaz_kharid_va_foroosh_amount'], 3) }}
                            </strong><br>
                            <small>گرم</small>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Coin Transactions Overview -->
    <div class="section no-page-break">
        <h2 class="section-title">آمار کلی معاملات سکه</h2>
        
        <table class="stats-table">
            <thead>
                <tr>
                    <th style="width: 33%;">خرید سکه</th>
                    <th style="width: 33%;">فروش سکه</th>
                    <th style="width: 34%;">مازاد خرید و فروش</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="coin-col">
                        <div style="margin-bottom: 4px;">
                            <strong>{{ $data['dashboard_data']['coin_transactions']['total_coin_sell_price'] }}</strong><br>
                            <small>ریال</small>
                        </div>
                        <div>
                            <strong>{{ number_format($data['dashboard_data']['coin_transactions']['total_coin_sell_amount']) }}</strong><br>
                            <small>عدد</small>
                        </div>
                    </td>
                    <td class="coin-col">
                        <div style="margin-bottom: 4px;">
                            <strong>{{ $data['dashboard_data']['coin_transactions']['total_coin_buy_price'] }}</strong><br>
                            <small>ریال</small>
                        </div>
                        <div>
                            <strong>{{ number_format($data['dashboard_data']['coin_transactions']['total_coin_buy_amount']) }}</strong><br>
                            <small>عدد</small>
                        </div>
                    </td>
                    <td class="surplus-col">
                        <div style="margin-bottom: 4px;">
                            <strong class="{{ $data['dashboard_data']['coin_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'positive' : 'negative' }}">
                                {{ $data['dashboard_data']['coin_transactions']['mazaz_kharid_va_foroosh_price'] }}
                            </strong><br>
                            <small>ریال</small>
                        </div>
                        <div>
                            <strong class="{{ $data['dashboard_data']['coin_transactions']['mazaz_kharid_va_foroosh_amount'] >= 0 ? 'positive' : 'negative' }}">
                                {{ number_format($data['dashboard_data']['coin_transactions']['mazaz_kharid_va_foroosh_amount']) }}
                            </strong><br>
                            <small>عدد</small>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Gold Quotes Details -->
    @if(isset($data['dashboard_data']['gold_quotes_details']) && count($data['dashboard_data']['gold_quotes_details']) > 0)
    <div class="section section-break">
        <h2 class="section-title">جزئیات معاملات مظنه‌های طلا</h2>
        
        @foreach($data['dashboard_data']['gold_quotes_details'] as $quote)
        <div class="quote-card no-page-break">
            <div class="quote-header">
                <h4 class="quote-title">{{ $quote['name'] }}</h4>
                @if($quote['description'])
                    <p class="quote-description">{{ $quote['description'] }}</p>
                @endif
                <span class="status-badge status-{{ $quote['status'] === 'active' ? 'active' : ($quote['status'] === 'inactive' ? 'inactive' : 'hidden') }}">
                    {{ $quote['status'] === 'active' ? 'فعال' : ($quote['status'] === 'inactive' ? 'غیرفعال' : 'مخفی') }}
                </span>
            </div>
            
            @if($quote['latest_price'])
            <div class="quote-content">
                <table class="price-table">
                    <tr>
                        <td style="width: 50%;">
                            <strong style="color: #28a745;">قیمت خرید:</strong><br>
                            {{ number_format($quote['latest_price']['buy_price']) }} ریال<br>
                            <span class="status-badge status-{{ $quote['latest_price']['buy_status'] === 'enabled' ? 'active' : 'inactive' }}">
                                {{ $quote['latest_price']['buy_status'] === 'enabled' ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </td>
                        <td style="width: 50%;">
                            <strong style="color: #dc3545;">قیمت فروش:</strong><br>
                            {{ number_format($quote['latest_price']['sell_price']) }} ریال<br>
                            <span class="status-badge status-{{ $quote['latest_price']['sell_status'] === 'enabled' ? 'active' : 'inactive' }}">
                                {{ $quote['latest_price']['sell_status'] === 'enabled' ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            @endif
            
            <div class="stats-content">
                <table class="details-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">خرید</th>
                            <th style="width: 25%;">فروش</th>
                            <th style="width: 25%;">مازاد</th>
                            <th style="width: 25%;">تعداد تراکنش</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong style="color: #28a745;">{{ $quote['statistics']['buy']['total_price'] }}</strong><br>
                                <small>ریال</small><br>
                                <strong style="color: #28a745;">{{ number_format((float)$quote['statistics']['buy']['total_amount'], 3) }}</strong><br>
                                <small>گرم</small>
                            </td>
                            <td>
                                <strong style="color: #dc3545;">{{ $quote['statistics']['sell']['total_price'] }}</strong><br>
                                <small>ریال</small><br>
                                <strong style="color: #dc3545;">{{ number_format((float)$quote['statistics']['sell']['total_amount'], 3) }}</strong><br>
                                <small>گرم</small>
                            </td>
                            <td>
                                <strong class="{{ $quote['statistics']['surplus']['amount'] >= 0 ? 'positive' : 'negative' }}">
                                    {{ $quote['statistics']['surplus']['price'] }}
                                </strong><br>
                                <small>ریال</small><br>
                                <strong class="{{ $quote['statistics']['surplus']['amount'] >= 0 ? 'positive' : 'negative' }}">
                                    {{ number_format((float)$quote['statistics']['surplus']['amount'], 3) }}
                                </strong><br>
                                <small>گرم</small>
                            </td>
                            <td>
                                <strong>{{ $quote['statistics']['buy']['transaction_count'] + $quote['statistics']['sell']['transaction_count'] }}</strong><br>
                                <small>تراکنش</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Coins Details -->
    @if(isset($data['dashboard_data']['coins_details']) && count($data['dashboard_data']['coins_details']) > 0)
    <div class="section section-break">
        <h2 class="section-title">جزئیات معاملات سکه‌ها</h2>
        
        @foreach($data['dashboard_data']['coins_details'] as $coin)
        <div class="quote-card no-page-break">
            <div class="quote-header">
                <h4 class="quote-title">{{ $coin['name'] }}</h4>
                @if($coin['description'])
                    <p class="quote-description">{{ $coin['description'] }}</p>
                @endif
                <span class="status-badge status-{{ $coin['status'] === 'active' ? 'active' : ($coin['status'] === 'inactive' ? 'inactive' : 'hidden') }}">
                    {{ $coin['status'] === 'active' ? 'فعال' : ($coin['status'] === 'inactive' ? 'غیرفعال' : 'مخفی') }}
                </span>
            </div>
            
            @if($coin['latest_price'])
            <div class="quote-content">
                <table class="price-table">
                    <tr>
                        <td style="width: 50%;">
                            <strong style="color: #28a745;">قیمت خرید:</strong><br>
                            {{ number_format($coin['latest_price']['buy_price']) }} ریال<br>
                            <span class="status-badge status-{{ $coin['latest_price']['buy_status'] === 'enabled' ? 'active' : 'inactive' }}">
                                {{ $coin['latest_price']['buy_status'] === 'enabled' ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </td>
                        <td style="width: 50%;">
                            <strong style="color: #dc3545;">قیمت فروش:</strong><br>
                            {{ number_format($coin['latest_price']['sell_price']) }} ریال<br>
                            <span class="status-badge status-{{ $coin['latest_price']['sell_status'] === 'enabled' ? 'active' : 'inactive' }}">
                                {{ $coin['latest_price']['sell_status'] === 'enabled' ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            @endif
            
            <div class="stats-content">
                <table class="details-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">خرید</th>
                            <th style="width: 25%;">فروش</th>
                            <th style="width: 25%;">مازاد</th>
                            <th style="width: 25%;">تعداد تراکنش</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong style="color: #28a745;">{{ $coin['statistics']['buy']['total_price'] }}</strong><br>
                                <small>ریال</small><br>
                                <strong style="color: #28a745;">{{ number_format((float)$coin['statistics']['buy']['total_amount']) }}</strong><br>
                                <small>عدد</small>
                            </td>
                            <td>
                                <strong style="color: #dc3545;">{{ $coin['statistics']['sell']['total_price'] }}</strong><br>
                                <small>ریال</small><br>
                                <strong style="color: #dc3545;">{{ number_format((float)$coin['statistics']['sell']['total_amount']) }}</strong><br>
                                <small>عدد</small>
                            </td>
                            <td>
                                <strong class="{{ $coin['statistics']['surplus']['amount'] >= 0 ? 'positive' : 'negative' }}">
                                    {{ $coin['statistics']['surplus']['price'] }}
                                </strong><br>
                                <small>ریال</small><br>
                                <strong class="{{ $coin['statistics']['surplus']['amount'] >= 0 ? 'positive' : 'negative' }}">
                                    {{ number_format((float)$coin['statistics']['surplus']['amount']) }}
                                </strong><br>
                                <small>عدد</small>
                            </td>
                            <td>
                                <strong>{{ $coin['statistics']['buy']['transaction_count'] + $coin['statistics']['sell']['transaction_count'] }}</strong><br>
                                <small>تراکنش</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <p>این گزارش در تاریخ {{ $data['generated_at'] }} تولید شده است.</p>
        <p>پنل مدیریتی لیفتر - تمامی حقوق محفوظ است.</p>
        <p>گزارش جامع مدیریت سیستم معاملات طلا و سکه</p>
    </div>
</body>
</html>