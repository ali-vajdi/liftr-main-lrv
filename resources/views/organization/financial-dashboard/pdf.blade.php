<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>صورتحساب مالی ساختمان</title>
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
            font-size: 12px;
            line-height: 1.5;
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
            padding: 5px;
            vertical-align: top;
            direction: rtl;
        }

        .header-left {
            text-align: left;
        }

        .header-center {
            text-align: center;
            font-size: 18px;
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
            padding: 5px;
            vertical-align: top;
            direction: rtl;
        }

        .building-info-right {
            text-align: right;
        }

        .building-info-left {
            text-align: left;
        }

        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            direction: rtl;
            table-layout: fixed;
        }

        .financial-table th,
        .financial-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            vertical-align: middle;
            direction: rtl;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .financial-table th {
            background-color: #e0e0e0;
            font-weight: bold;
            overflow: visible;
            word-wrap: break-word;
        }

        .financial-table td {
            font-size: 11px;
        }

        .financial-table td.description-cell {
            text-align: right;
            word-break: break-word;
            hyphens: auto;
        }

        .financial-table th:first-child,
        .financial-table td.row-number-cell {
            width: 8%;
            min-width: 8%;
            text-align: center;
            padding: 8px 4px;
            overflow: visible;
            white-space: normal;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
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
            padding: 10px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    @php
        $totalRecords = $recordChunks->flatten()->count();
        $recordIndex = 0;
    @endphp
    
    @foreach($recordChunks as $pageIndex => $records)
    <div class="page-container" style="@if(!$loop->last) page-break-after: always; @else page-break-after: avoid !important; @endif">
        <div class="page-content">
            @if($pageIndex == 0)
            <!-- Header - Only on first page -->
            <div class="header">
                <table>
                    <tr>
                        <td class="header-left" style="width: 33.33%;">
                            <div>
                                <strong>تاریخ:</strong> {{ $currentDate }}
                            </div>
                        </td>
                        <td class="header-center" style="width: 33.34%;">
                            صورتحساب مالی ساختمان
                        </td>
                        <td class="header-right" style="width: 33.33%;">
                        </td>
                    </tr>
                </table>
            </div>

            <div class="divider"></div>

            <!-- Organization Info - Only on first page -->
            <div class="building-info">
                <table>
                    <tr>
                        <td class="building-info-left" style="width: 50%; text-align: left;">
                            <div>
                                <strong>تلفن ثابت:</strong> {{ $organization->landline_phone ?? 'نامشخص' }}
                            </div>
                        </td>
                        <td class="building-info-right" style="width: 50%; text-align: right;">
                            <div>
                                <strong>نام شرکت:</strong> {{ $organization->name ?? 'نامشخص' }}
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
            </div>

            <div class="divider"></div>

            <!-- Building Info - Only on first page -->
            <div class="building-info">
                <table>
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
            </div>

            <div class="divider"></div>
            @endif

            <!-- Financial Records Table -->
            <table class="financial-table" dir="rtl">
                @if($pageIndex == 0)
                <thead>
                    <tr>
                        <th style="width: 8%;">ردیف</th>
                        <th style="width: 14%;">تاریخ</th>
                        <th style="width: 28%;">شرح</th>
                        <th style="width: 15%;">بدهکاری</th>
                        <th style="width: 15%;">بستانکاری</th>
                        <th style="width: 20%;">مانده</th>
                    </tr>
                </thead>
                @endif
                <tbody>
                    @if($records->count() > 0)
                        @foreach($records as $record)
                            <tr>
                                <td class="row-number-cell">{{ ++$recordIndex }}</td>
                                <td>{{ $record['transaction_date_jalali'] ?? '-' }}</td>
                                <td class="description-cell">{{ $record['description'] ?? '-' }}</td>
                                <td>{{ $record['debit'] ? number_format($record['debit'], 0) . ' ریال' : '-' }}</td>
                                <td>{{ $record['credit'] ? number_format($record['credit'], 0) . ' ریال' : '-' }}</td>
                                <td>
                                    @if($record['balance'] > 0)
                                        <span dir="ltr" style="direction: ltr; display: inline-block;">+{{ number_format($record['balance'], 0) . ' ریال' }}</span>
                                    @elseif($record['balance'] < 0)
                                        <span dir="ltr" style="direction: ltr; display: inline-block;">-{{ number_format(abs($record['balance']), 0) . ' ریال' }}</span>
                                    @else
                                        {{ number_format(0, 0) . ' ریال' }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @elseif($pageIndex == 0)
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">
                                هیچ تراکنش مالی یافت نشد
                            </td>
                        </tr>
                    @endif

                    @if($loop->last)
                    <!-- Total Row - Only on last page -->
                    <tr class="total-row">
                        <td colspan="3" style="text-align: center;">
                            <strong>جمع</strong>
                        </td>
                        <td>
                            <strong>{{ number_format($totalDebits, 0) }} ریال</strong>
                        </td>
                        <td>
                            <strong>{{ number_format($totalCredits, 0) }} ریال</strong>
                        </td>
                        <td>
                            <strong>
                                @if($finalBalance > 0)
                                    <span dir="ltr" style="direction: ltr; display: inline-block;">+{{ number_format($finalBalance, 0) . ' ریال' }}</span>
                                @elseif($finalBalance < 0)
                                    <span dir="ltr" style="direction: ltr; display: inline-block;">-{{ number_format(abs($finalBalance), 0) . ' ریال' }}</span>
                                @else
                                    {{ number_format(0, 0) . ' ریال' }}
                                @endif
                            </strong>
                        </td>
                    </tr>

                    <!-- Final Amount Row - Only on last page -->
                    <tr class="final-amount-row">
                        <td colspan="4" style="text-align: right; padding: 8px;">
                            <div>
                                <strong>مبلغ به حروف:</strong> {{ $finalBalanceInWords ?? '' }} ریال
                            </div>
                        </td>
                        <td style="text-align: center; padding: 8px;">
                            <strong>مبلغ نهایی:</strong>
                        </td>
                        <td style="text-align: center; padding: 8px;">
                            <strong>
                                @if($finalBalance > 0)
                                    <span dir="ltr" style="direction: ltr; display: inline-block;">+{{ number_format($finalBalance, 0) . ' ریال' }}</span> (بستانکار)
                                @elseif($finalBalance < 0)
                                    <span dir="ltr" style="direction: ltr; display: inline-block;">-{{ number_format(abs($finalBalance), 0) . ' ریال' }}</span> (بدهکار)
                                @else
                                    {{ number_format(0, 0) . ' ریال' }}
                                @endif
                            </strong>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endforeach
</body>
</html>

