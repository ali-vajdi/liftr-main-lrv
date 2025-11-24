<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>چک لیست سرویس و نگهداری</title>
    <style>
        body {
            font-family: 'vazir', 'Tahoma', Arial, sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm 20mm;
            position: relative;
            page-break-after: always;
            page-break-inside: avoid;
            overflow: hidden;
        }

        .page:last-child {
            page-break-after: auto;
        }

        /* Header Section */
        .page-header {
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header-label {
            font-weight: bold;
            margin-bottom: 1px;
            font-size: 9px;
        }

        .header-value {
            font-size: 10px;
        }

        .header-title {
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }

        .separator {
            border-top: 1px solid #000;
            margin: 6px 0;
        }

        /* Info Section */
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 80px;
            margin-left: 5px;
            font-size: 9px;
        }

        .info-value {
            font-size: 10px;
        }

        /* Checklist Section */
        .checklist-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 8px 0 5px 0;
        }

        .checklist-items {
            list-style: none;
            padding: 0;
            margin: 0 0 6px 0;
        }

        .checklist-item {
            padding: 3px 0;
            border-bottom: 1px dotted #666;
            font-size: 9px;
            line-height: 1.3;
        }

        .checklist-item:last-child {
            border-bottom: none;
        }

        .checklist-number {
            display: inline-block;
            width: 20px;
            font-weight: bold;
            margin-left: 5px;
        }

        /* Descriptions Section */
        .descriptions-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 8px 0 5px 0;
        }

        .descriptions-container {
            margin-bottom: 35px;
            max-height: 180px;
            overflow: hidden;
        }

        .description-item {
            margin-bottom: 4px;
            padding: 3px 5px;
            border: 1px solid #ddd;
            background: #f9f9f9;
            font-size: 8px;
            line-height: 1.3;
        }

        .description-number {
            display: inline-block;
            width: 18px;
            font-weight: bold;
            margin-left: 4px;
        }

        .description-title {
            font-weight: bold;
            margin-bottom: 2px;
            color: #333;
            font-size: 9px;
        }

        .description-text {
            color: #666;
            line-height: 1.3;
            font-size: 8px;
        }

        .no-description {
            text-align: center;
            color: #999;
            font-size: 9px;
            margin: 10px 0 35px 0;
            font-style: italic;
        }

        /* Footer Section */
        .page-footer {
            position: absolute;
            bottom: 15mm;
            right: 20mm;
            left: 20mm;
            height: 70px;
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
        }

        .signature-box {
            text-align: center;
        }

        .signature-label {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 10px;
        }

        .signature-image {
            height: 40px;
            margin-bottom: 3px;
            padding: 2px;
            text-align: center;
            background: white;
            overflow: hidden;
        }

        .signature-image img {
            max-width: 100%;
            max-height: 38px;
            display: block;
            margin: 0 auto;
        }

        .signature-name {
            font-size: 9px;
            margin-top: 1px;
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
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
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

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
            <tr>
                <td style="width: 50%; text-align: right; vertical-align: top; padding-left: 8px;">
                    <div>
                        <span class="info-label">تعداد توقف:</span>
                        <span class="info-value">{{ $elevatorChecklist->elevator->stops_count }}</span>
                        <span class="info-label" style="margin-right: 6px;">ظرفیت:</span>
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

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
            <tr>
                <td style="width: 50%; text-align: right; vertical-align: top; padding-left: 8px;">
                    <div>
                        <span class="info-value">آدرس: {{ $service->building->address ?? '' }}</span>
                    </div>
                </td>
                <td style="width: 50%;"></td>
            </tr>
        </table>

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
                <div class="description-text" style="margin-top: 2px; margin-right: 22px;">{{ $description->description }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <div class="no-description">هیچ توضیحی وجود ندارد</div>
        @endif

        <!-- Footer with Signatures -->
        <div class="page-footer">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; text-align: center; vertical-align: top;">
                        <div class="signature-box">
                            <div class="signature-label">امضا مدیر/نماینده ساختمان</div>
                            <div class="signature-image">
                                @if($managerSig && !empty($managerSig->signature))
                                    <img src="{{ trim($managerSig->signature) }}" alt="امضای مدیر">
                                @else
                                    <span style="color: #999; font-size: 7px;">امضا نشده</span>
                                @endif
                            </div>
                            <div class="signature-name">
                                {{ $managerSig->name ?? 'نامشخص' }}
                            </div>
                        </div>
                    </td>
                    <td style="width: 50%; text-align: center; vertical-align: top;">
                        <div class="signature-box">
                            <div class="signature-label">امضا سرویس کار</div>
                            <div class="signature-image">
                                @if($technicianSig && !empty($technicianSig->signature))
                                    <img src="{{ trim($technicianSig->signature) }}" alt="امضای تکنسین">
                                @else
                                    <span style="color: #999; font-size: 7px;">امضا نشده</span>
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
