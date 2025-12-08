<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>چک لیست سرویس و نگهداری</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'vazir', 'Tahoma', Arial, sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 13px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
            height: auto;
        }
        
        html {
            margin: 0;
            padding: 0;
            height: auto;
        }

        .page {
            width: 210mm;
            padding: 0 12mm 12mm 12mm;
            margin: 0;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            background: #fff;
        }

        .page:not(:last-child) {
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: avoid !important;
        }


        /* Page Content Wrapper */
        .page-content {
            min-height: calc(297mm - 24mm - 110px);
            padding-bottom: 0;
        }

        /* Header Section */
        .page-header {
            padding: 12mm 0 8px 0;
            margin-bottom: 12px;
            margin-top: 0;
        }

        .header-label {
            font-weight: bold;
            margin-bottom: 2px;
            font-size: 11px;
        }

        .header-value {
            font-size: 12px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .separator {
            border-top: 1px solid #000;
            margin: 10px 0;
        }

        /* Info Section */
        .info-section {
            padding: 8px 0;
            margin-bottom: 12px;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 90px;
            margin-left: 8px;
            font-size: 11px;
        }

        .info-value {
            font-size: 12px;
        }

        /* Checklist Section */
        .checklist-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 12px 0 10px 0;
            padding: 6px 0;
        }

        .checklist-items {
            list-style: none;
            padding: 0;
            margin: 0 0 12px 0;
        }

        .checklist-item {
            padding: 5px 0;
            border-bottom: 1px solid #000;
            font-size: 11px;
            line-height: 1.5;
        }

        .checklist-item:last-child {
            border-bottom: none;
        }

        .checklist-number {
            display: inline-block;
            width: 25px;
            font-weight: bold;
            margin-left: 8px;
        }

        /* Descriptions Section */
        .descriptions-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 12px 0 10px 0;
            padding: 6px 0;
        }

        .descriptions-container {
            margin-bottom: 20px;
            page-break-inside: auto;
        }

        .description-item {
            margin-bottom: 3px;
            padding: 3px 0;
            border-bottom: 1px solid #000;
            font-size: 10px;
            line-height: 1.3;
            page-break-inside: avoid;
        }

        /* Reset top margin/padding for items that break to new page */
        .description-item:first-child {
            margin-top: 0;
            padding-top: 0;
        }

        .description-number {
            display: inline-block;
            width: 22px;
            font-weight: bold;
            margin-left: 6px;
        }

        .description-title {
            font-weight: bold;
            margin-bottom: 1px;
            font-size: 11px;
        }

        .description-text {
            line-height: 1.3;
            font-size: 10px;
            word-wrap: break-word;
        }

        .no-description {
            text-align: center;
            font-size: 11px;
            margin: 12px 0 20px 0;
            font-style: italic;
            padding: 12px 0;
        }

        /* Footer Section */
        .page-footer {
            position: relative;
            margin-top: 20px;
            padding-top: 10px;
            width: 100%;
        }

        .footer-row {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .footer-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-box {
            text-align: center;
            padding: 5px 0;
            border: none;
            border-bottom: none;
        }

        .signature-label {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 20px;
            text-decoration: none;
            border-bottom: none;
        }

        .signature-image {
            height: 50px;
            margin-bottom: 5px;
            padding: 4px 0;
            text-align: center;
            overflow: hidden;
            border: none;
        }

        .signature-image img {
            max-width: 100%;
            max-height: 48px;
            display: block;
            margin: 0 auto;
        }

        .signature-name {
            font-size: 18px;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    @foreach($service->checklist->elevatorChecklists as $elevatorChecklist)
    <div class="page">
        <!-- Header -->
        <div class="page-header">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 33.33%; text-align: right; vertical-align: top;">
                        <div>
                            <span class="header-label">نام شرکت:</span>
                            <span class="header-value">{{ $service->building->organization->name ?? 'نامشخص' }}</span>
                        </div>
                    </td>
                    <td style="width: 33.34%; text-align: center; vertical-align: top;">
                        <div class="header-title">چک لیست سرویس و نگهداری</div>
                    </td>
                    <td style="width: 33.33%; text-align: left; vertical-align: top;">
                        <div>
                            <span class="header-label">تاریخ بازدید:</span>
                            <span class="header-value">{{ $completedDate ?? 'نامشخص' }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="separator"></div>

        <!-- Building and Elevator Info -->
        <div class="info-section">
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
                <tr>
                    <td style="width: 50%; text-align: right; vertical-align: top; padding-left: 8px;">
                        <div>
                            <span class="info-label">نام پروژه:</span>
                            <span class="info-value">{{ $service->building->name }}</span>
                        </div>
                    </td>
                    <td style="width: 50%; text-align: left; vertical-align: top; padding-right: 8px;">
                        <div>
                            <span class="info-label">نام آسانسور:</span>
                            <span class="info-value">{{ $elevatorChecklist->elevator->name }}</span>
                        </div>
                    </td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px;">
                <tr>
                    <td style="width: 50%; text-align: right; vertical-align: top; padding-left: 8px;">
                        <div>
                            <span class="info-label">تعداد توقف:</span>
                            <span class="info-value">{{ $elevatorChecklist->elevator->stops_count }}</span>
                            <span class="info-label" style="margin-right: 8px;">ظرفیت:</span>
                            <span class="info-value">{{ $elevatorChecklist->elevator->capacity }}</span>
                        </div>
                    </td>
                    <td style="width: 50%; text-align: left; vertical-align: top; padding-right: 8px;">
                        <div>
                            <span class="info-label">سرویس ماه:</span>
                            <span class="info-value">{{ $monthNames[$service->service_month] }} {{ $service->service_year }}</span>
                        </div>
                    </td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 100%; text-align: right; vertical-align: top; padding-left: 8px;">
                        <div>
                            <span class="info-label">آدرس:</span>
                            <span class="info-value">{{ $service->building->address ?? '' }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="separator"></div>

        <!-- Unit Checklists -->
        <div class="checklist-title">موارد زیر مورد بررسی قرار گرفت</div>
        <ul class="checklist-items">
            @foreach($unitChecklists as $index => $unitChecklist)
            <li class="checklist-item">
                <span class="checklist-number">{{ $index + 1 }}.</span>
                {{ $unitChecklist->title }}
            </li>
            @endforeach
        </ul>

        <!-- Descriptions -->
        <div class="descriptions-title">توضیحات</div>
        @if($elevatorChecklist->descriptions->count() > 0)
        <div class="descriptions-container">
            @foreach($elevatorChecklist->descriptions as $index => $description)
            <div class="description-item">
                <span class="description-number">{{ $index + 1 }}.</span>
                @if($description->title)
                <span class="description-title">{{ $description->title }}</span>
                @endif
                @if($description->description)
                <div class="description-text" style="margin-top: 1px; margin-right: 22px;">{{ $description->description }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="no-description">هیچ توضیحی وجود ندارد</div>
        @endif

        <!-- Footer with Signatures -->
        <div class="page-footer">
            <table style="width: 100%; border-collapse: collapse; border: none;">
                <tr>
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none;">
                        <div class="signature-box">
                            <div class="signature-label" style="text-decoration: none; border-bottom: none; border: none;">امضا مدیر/نماینده ساختمان</div>
                            <div class="signature-image">
                                @if($managerSig && !empty($managerSig->signature))
                                    <img src="{{ trim($managerSig->signature) }}" alt="امضای مدیر">
                                @else
                                    <span style="font-size: 9px;">امضا نشده</span>
                                @endif
                            </div>
                            <div class="signature-name">
                                {{ $managerSig->name ?? 'نامشخص' }}
                            </div>
                        </div>
                    </td>
                    <td style="width: 50%; text-align: center; vertical-align: top; border: none;">
                        <div class="signature-box">
                            <div class="signature-label" style="text-decoration: none; border-bottom: none; border: none;">امضا سرویس کار</div>
                            <div class="signature-image">
                                @if($technicianSig && !empty($technicianSig->signature))
                                    <img src="{{ trim($technicianSig->signature) }}" alt="امضای تکنسین">
                                @else
                                    <span style="font-size: 9px;">امضا نشده</span>
                                @endif
                            </div>
                            <div class="signature-name">
                                {{ $technicianSig->name ?? 'نامشخص' }}
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endforeach
</body>
</html>
