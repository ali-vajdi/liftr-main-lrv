<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>فاکتور</title>
    <style>
        /** Set the margins of the page to show border **/
        @page {
            margin: 5mm;
        }

        html {
            direction: rtl;
        }

        body {
            font-family: 'vazir', 'Tahoma', Arial, sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 15px;
            line-height: 1.6;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /** Page container with border - exact A4 size **/
        .page-container {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 0;
            border: 2px solid #000;
            box-sizing: border-box;
            background: #fff;
            position: relative;
            overflow: hidden;
            page-break-inside: avoid;
        }

        /** Content area - accounts for padding **/
        .page-content {
            padding: 10mm;
            box-sizing: border-box;
            overflow: visible;
        }

        table {
            direction: rtl;
            text-align: right;
        }
        
        * {
            unicode-bidi: embed;
        }
        
        body, div, table, tr, td, th {
            unicode-bidi: embed;
        }

        .header {
            margin-bottom: 15px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
            direction: ltr;
        }

        .header td {
            padding: 8px;
            vertical-align: top;
            direction: rtl;
            font-size: 15px;
        }

        .header-left {
            text-align: left;
        }

        .header-center {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
        }

        .header-right {
            text-align: right;
        }

        .divider {
            border-top: 1px solid #000;
            margin: 10px 0;
        }

        .building-info {
            margin-bottom: 15px;
        }

        .building-info table {
            width: 100%;
            border-collapse: collapse;
            direction: ltr;
        }

        .building-info td {
            padding: 8px;
            vertical-align: top;
            direction: rtl;
            font-size: 15px;
        }

        .building-info-right {
            text-align: right;
        }

        .building-info-left {
            text-align: left;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            direction: rtl;
            table-layout: fixed;
        }

        .invoice-table th,
        .invoice-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
            direction: rtl;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .invoice-table th {
            background-color: #e0e0e0;
            font-weight: bold;
            overflow: visible;
            word-wrap: break-word;
            font-size: 15px;
        }

        .invoice-table td {
            font-size: 14px;
        }

        .invoice-table td.description-cell {
            text-align: right;
            word-break: break-word;
            hyphens: auto;
        }

        .invoice-table th:first-child,
        .invoice-table td.row-number-cell {
            width: 8%;
            min-width: 8%;
            text-align: center;
            padding: 10px 6px;
            overflow: visible;
            white-space: normal;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            direction: rtl;
        }

        .summary-table td {
            padding: 10px;
            border: 1px solid #000;
            direction: rtl;
            font-size: 15px;
        }

        .summary-table td:first-child {
            text-align: right;
            width: 70%;
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .summary-table td:last-child {
            text-align: center;
            width: 30%;
        }

        .total-row {
            font-weight: bold;
            background-color: #e0e0e0;
        }

        .final-amount-row {
            font-weight: bold;
            background-color: #e0e0e0;
        }

        .final-amount-row td {
            padding: 12px;
            font-size: 17px;
        }

        .rotated-rectangle {
            background-color: #e0e0e0;
            border: 1px solid #000;
            width: 50px;
            height: 50px;
            position: relative;
            display: inline-block;
            transform: rotate(45deg);
            -webkit-transform: rotate(45deg);
            -moz-transform: rotate(45deg);
            -ms-transform: rotate(45deg);
            -o-transform: rotate(45deg);
        }

        .rotated-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            -webkit-transform: translate(-50%, -50%) rotate(-45deg);
            -moz-transform: translate(-50%, -50%) rotate(-45deg);
            -ms-transform: translate(-50%, -50%) rotate(-45deg);
            -o-transform: translate(-50%, -50%) rotate(-45deg);
            font-weight: bold;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-content">
            <!-- Header -->
            <div class="header">
                <table>
                    <tr>
                        <td class="header-left" style="width: 33.33%;">
                            <div>
                                <strong>تاریخ فاکتور:</strong> {{ $invoiceDate }}
                            </div>
                        </td>
                        <td class="header-center" style="width: 33.34%;">
                            فاکتور
                        </td>
                        <td class="header-right" style="width: 33.33%;">
                            <div>
                                <strong>شماره فاکتور:</strong> {{ $invoice->invoice_number }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="divider"></div>

            <!-- Organization Info -->
            <div class="building-info">
                <table style="width: 100%;">
                    <tr>
                        <td style="padding-right: 10px;">
                            <table style="width: 100%;">
                                <tr>
                                    <td class="building-info-left" style="width: 50%; text-align: left;">
                                        <div>
                                            <strong>تلفن ثابت:</strong> {{ $organization->landline_phone ?? 'نامشخص' }}
                                        </div>
                                    </td>
                                    <td class="building-info-right" style="width: 50%; text-align: right;">
                                        <div>
                                            <strong>نام شرکت:</strong> {{ $organization && $organization->name ? 'آسانسور ' . $organization->name : 'نامشخص' }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="building-info-right" style="text-align: right;">
                                        <div>
                                            <strong>آدرس شرکت:</strong> {{ $organization->address ?? 'نامشخص' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 80px; vertical-align: top; padding: 0; text-align: center;">
                            <svg width="80" height="80" style="display: block; margin: 0;">
                                <g transform="translate(40, 40) rotate(90)">
                                    <rect x="-40" y="-15" width="80" height="30" fill="#e0e0e0" stroke="#000" stroke-width="1"/>
                                    <text x="0" y="0" text-anchor="middle" dominant-baseline="central" font-weight="bold" font-size="14">فروشنده</text>
                                </g>
                            </svg>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="divider"></div>

            <!-- Building Info -->
            <div class="building-info">
                <table style="width: 100%;">
                    <tr>
                        <td style="padding-right: 10px;">
                            <table style="width: 100%;">
                                <tr>
                                    <td class="building-info-left" style="width: 50%; text-align: left;">
                                        <div>
                                            <strong>شماره تماس:</strong> {{ $building->manager_phone ?? 'نامشخص' }}
                                        </div>
                                    </td>
                                    <td class="building-info-right" style="width: 50%; text-align: right;">
                                        <div>
                                            <strong>نام ساختمان:</strong> {{ $building->name ?? 'نامشخص' }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="building-info-right" style="text-align: right;">
                                        <div>
                                            <strong>نام مدیر ساختمان:</strong> {{ $building->manager_name ?? 'نامشخص' }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="building-info-right" style="text-align: right;">
                                        <div>
                                            <strong>آدرس:</strong> {{ $building->address ?? 'نامشخص' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td style="width: 80px; vertical-align: top; padding: 0; text-align: center;">
                            <svg width="80" height="80" style="display: block; margin: 0;">
                                <g transform="translate(40, 40) rotate(90)">
                                    <rect x="-40" y="-15" width="80" height="30" fill="#e0e0e0" stroke="#000" stroke-width="1"/>
                                    <text x="0" y="0" text-anchor="middle" dominant-baseline="central" font-weight="bold" font-size="14">خریدار</text>
                                </g>
                            </svg>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="divider"></div>

            <!-- Invoice Items Table -->
            <table class="invoice-table" dir="rtl">
                <thead>
                    <tr>
                        <th style="width: 8%;">ردیف</th>
                        <th style="width: 40%;">شرح</th>
                        <th style="width: 10%;">تعداد</th>
                        <th style="width: 14%;">قیمت واحد</th>
                        <th style="width: 14%;">قیمت کل</th>
                    </tr>
                </thead>
                <tbody>
                    @if($invoice->items->count() > 0)
                        @foreach($invoice->items as $index => $item)
                            <tr>
                                <td class="row-number-cell">{{ $index + 1 }}</td>
                                <td class="description-cell">{{ $item->description }}</td>
                                <td>{{ number_format($item->quantity, 0) }}</td>
                                <td>{{ number_format($item->unit_price, 0) }} ریال</td>
                                <td>{{ number_format($item->total, 0) }} ریال</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px;">
                                هیچ آیتمی یافت نشد
                            </td>
                        </tr>
                    @endif
                    
                    <!-- جمع فاکتور - Label spans first 4 columns, price column in last column -->
                    <tr class="total-row">
                        <td colspan="4" style="text-align: center; font-weight: bold;">جمع فاکتور</td>
                        <td style="text-align: center; font-weight: bold;">{{ number_format($invoice->subtotal, 0) }} ریال</td>
                    </tr>
                    
                    <!-- تخفیف - Label spans first 4 columns, price column in last column -->
                    @if($invoice->discount > 0)
                    <tr>
                        <td colspan="4" style="text-align: center;">تخفیف</td>
                        <td style="text-align: center;">{{ number_format($invoice->discount, 0) }} ریال</td>
                    </tr>
                    @endif
                    
                    <!-- مالیات - Label spans first 4 columns, price column in last column -->
                    @if($invoice->tax_percentage > 0)
                    <tr>
                        <td colspan="4" style="text-align: center;">مالیات ({{ number_format($invoice->tax_percentage, 2) }}%)</td>
                        <td style="text-align: center;">{{ number_format($invoice->tax_amount, 0) }} ریال</td>
                    </tr>
                    @endif
                    
                    <!-- مبلغ به حروف و مبلغ نهایی - One row split in half (2.5 columns each) -->
                    <tr class="final-amount-row">
                        <td colspan="2" style="text-align: center; border-right: 1px solid #000; vertical-align: top;">
                            <div style="font-weight: bold; margin-bottom: 5px;">مبلغ به حروف</div>
                            <div style="font-size: 14px; text-align: right; padding: 0 10px;">{{ $totalInWords }} ریال</div>
                        </td>
                        <td colspan="3" style="text-align: center; vertical-align: top;">
                            <div style="font-weight: bold; margin-bottom: 5px;">مبلغ نهایی</div>
                            <div style="font-size: 18px;">{{ number_format($invoice->total, 0) }} ریال</div>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Footer with Signatures -->
            <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #000;">
                <table style="width: 100%; border-collapse: collapse; direction: rtl;">
                    <tr>
                        <td style="width: 50%; text-align: center; padding: 20px; border-left: 1px solid #000;">
                            <div style="font-weight: bold; margin-bottom: 40px; font-size: 16px;">امضاء خریدار</div>
                            <div style="border-top: 1px solid #000; padding-top: 5px;">
                            </div>
                        </td>
                        <td style="width: 50%; text-align: center; padding: 20px;">
                            <div style="font-weight: bold; margin-bottom: 40px; font-size: 16px;">امضاء فروشنده</div>
                            <div style="border-top: 1px solid #000; padding-top: 5px;">
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

