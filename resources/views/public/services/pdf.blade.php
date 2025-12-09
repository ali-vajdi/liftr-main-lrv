<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>چک لیست سرویس و نگهداری</title>
    <style>
        /** Set the margins of the page to show border **/
        @page {
            margin: 5mm;
        }

        /** Define body styles **/
        body {
            font-family: 'vazir', 'Tahoma', Arial, sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 13px;
            line-height: 1.4;
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

        /** Content area - accounts for footer space **/
        .page-content {
            padding: 10mm 12mm;
            padding-bottom: 125px;
            box-sizing: border-box;
            overflow: visible;
        }

        /** Section containers **/
        .section {
            margin-bottom: 12px;
            border: 1px solid #000;
            border-radius: 2px;
            overflow: hidden;
        }

        .section-header {
            background: #f8f9fa;
            border-bottom: 1px solid #000;
            padding: 6px 10px;
            font-weight: bold;
            font-size: 14px;
        }

        .section-content {
            padding: 8px 10px;
        }

        /** Info boxes **/
        .info-box {
            border: 1px solid #ddd;
            padding: 6px 8px;
            margin-bottom: 6px;
            border-radius: 2px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 4px;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            min-width: 100px;
            padding-left: 8px;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            vertical-align: top;
        }

        /** Table styles **/
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        .data-table th {
            background: #f8f9fa;
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }

        .data-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: right;
            font-size: 11px;
        }

        .data-table tr:not(:last-child) td {
            border-bottom: 1px solid #ddd;
        }

        /** List styles **/
        .checklist-items {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .checklist-item {
            border: 1px solid #ddd;
            border-bottom: none;
            padding: 6px 8px;
            display: table;
            width: 100%;
        }

        .checklist-item:last-child {
            border-bottom: 1px solid #ddd;
        }

        .checklist-number {
            display: table-cell;
            width: 30px;
            font-weight: bold;
            vertical-align: top;
        }

        .checklist-text {
            display: table-cell;
            vertical-align: top;
        }

        /** Description items **/
        .description-item {
            border: 1px solid #ddd;
            border-bottom: none;
            padding: 6px 8px;
            margin-bottom: 0;
        }

        .description-item:last-child {
            border-bottom: 1px solid #ddd;
        }

        .description-title {
            font-weight: bold;
            margin-bottom: 3px;
        }

        .description-text {
            margin-right: 25px;
            line-height: 1.3;
        }

        /** Footer inside each page - fixed at bottom **/
        .page-footer {
            position: absolute;
            bottom: 0;
            left: 4mm;
            right: 4mm;
            width: calc(100% - 8mm);
            height: 120px;
            background: #fff;
            border: none;
            padding: 8px 8mm;
            box-sizing: border-box;
            page-break-inside: avoid;
            z-index: 10;
        }

        .signature-box {
            border: 1px solid #ddd;
            padding: 6px;
            height: 100%;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    @if($service->checklist && $service->checklist->elevatorChecklists)
    @foreach($service->checklist->elevatorChecklists as $elevatorIndex => $elevatorChecklist)
        @php
            $descriptions = $elevatorChecklist->descriptions ?? collect([]);
            $descriptionsPerPage = 10;
            $descriptionChunks = $descriptions->chunk($descriptionsPerPage);
            $totalPages = max(1, $descriptionChunks->count());
            if($descriptionChunks->count() == 0) {
                $descriptionChunks = collect([collect([])]);
            }
            $pageId = 'page_' . $elevatorIndex . '_' . uniqid();
        @endphp

        @foreach($descriptionChunks as $pageIndex => $descriptionChunk)
        @php
            $uniquePageId = $pageId . '_' . $pageIndex . '_' . rand(1000, 9999);
        @endphp
        <div id="{{ $uniquePageId }}" class="page-container" style="@if(!$loop->parent->last || !$loop->last) page-break-after: always; @else page-break-after: avoid !important; @endif">
            <div class="page-content">
                @if($pageIndex == 0)
                <!-- Header - Only on first page -->
                <div class="section" style="margin-bottom: 10px;">
                    <div class="section-header" style="text-align: center; font-size: 18px;">چک لیست سرویس و نگهداری</div>
                    <div class="section-content">
                        <table class="data-table">
                            <tr>
                                <td style="width: 33.33%; text-align: right; border: none; padding: 4px 8px;">
                                    <div class="info-row">
                                        <span class="info-label">نام شرکت:</span>
                                        <span class="info-value">{{ $service->building->organization->name ?? 'نامشخص' }}</span>
                                    </div>
                                </td>
                                <td style="width: 33.34%; text-align: center; border: none; padding: 4px 8px;">
                                    <div style="font-size: 18px; font-weight: bold;">چک لیست سرویس و نگهداری</div>
                                </td>
                                <td style="width: 33.33%; text-align: left; border: none; padding: 4px 8px;">
                                    <div class="info-row">
                                        <span class="info-label">تاریخ بازدید:</span>
                                        <span class="info-value">{{ $completedDate ?? 'نامشخص' }}</span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Building and Elevator Info - Only on first page -->
                <div class="section" style="margin-bottom: 10px;">
                    <div class="section-header">اطلاعات پروژه و آسانسور</div>
                    <div class="section-content">
                        <table class="data-table">
                            <tr>
                                <td style="width: 50%; text-align: right; border: none; padding: 4px 8px;">
                                    <div class="info-row">
                                        <span class="info-label">نام پروژه:</span>
                                        <span class="info-value">{{ $service->building->name ?? 'نامشخص' }}</span>
                                    </div>
                                </td>
                                <td style="width: 50%; text-align: left; border: none; padding: 4px 8px;">
                                    <div class="info-row">
                                        <span class="info-label">نام آسانسور:</span>
                                        <span class="info-value">{{ ($elevatorChecklist->elevator && $elevatorChecklist->elevator->name) ? $elevatorChecklist->elevator->name : 'نامشخص' }}</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 50%; text-align: right; border: none; padding: 4px 8px;">
                                    <div class="info-row">
                                        <span class="info-label">تعداد توقف:</span>
                                        <span class="info-value">{{ ($elevatorChecklist->elevator && isset($elevatorChecklist->elevator->stops_count)) ? $elevatorChecklist->elevator->stops_count : 'نامشخص' }}</span>
                                    </div>
                                    <div class="info-row" style="margin-top: 4px;">
                                        <span class="info-label">ظرفیت:</span>
                                        <span class="info-value">{{ ($elevatorChecklist->elevator && isset($elevatorChecklist->elevator->capacity)) ? $elevatorChecklist->elevator->capacity : 'نامشخص' }}</span>
                                    </div>
                                </td>
                                <td style="width: 50%; text-align: left; border: none; padding: 4px 8px;">
                                    <div class="info-row">
                                        <span class="info-label">سرویس ماه:</span>
                                        <span class="info-value">{{ ($service->service_month && isset($monthNames[$service->service_month])) ? $monthNames[$service->service_month] : 'نامشخص' }} {{ $service->service_year ?? '' }}</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: right; border: none; padding: 4px 8px;">
                                    <div class="info-row">
                                        <span class="info-label">آدرس:</span>
                                        <span class="info-value">{{ $service->building->address ?? '' }}</span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Unit Checklists - Only on first page -->
                <div class="section" style="margin-bottom: 10px;">
                    <div class="section-header">موارد زیر مورد بررسی قرار گرفت</div>
                    <div class="section-content">
                        <ul class="checklist-items">
                            @foreach($unitChecklists as $index => $unitChecklist)
                            <li class="checklist-item">
                                <span class="checklist-number">{{ $index + 1 }}.</span>
                                <span class="checklist-text">{{ $unitChecklist->title }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @else
                <!-- Continuation page - start from top -->
                <div style="padding: 0; margin: 0;"></div>
                @endif

                <!-- Descriptions -->
                <div class="section" style="margin-bottom: 10px;">
                    <div class="section-header">توضیحات</div>
                    <div class="section-content">
                        @if($descriptions->count() > 0)
                        <div style="page-break-inside: auto;">
                            @foreach($descriptionChunk as $chunkIndex => $description)
                            @php
                                $globalIndex = ($pageIndex * $descriptionsPerPage) + $chunkIndex;
                            @endphp
                            <div class="description-item" style="page-break-inside: avoid; @if($chunkIndex == 0 && $pageIndex > 0) margin-top: 0; padding-top: 0; @endif">
                                <div style="display: table; width: 100%;">
                                    <span style="display: table-cell; width: 25px; font-weight: bold; vertical-align: top; padding-left: 6px;">{{ $globalIndex + 1 }}.</span>
                                    <div style="display: table-cell; vertical-align: top;">
                                        @if($description->title)
                                        <div class="description-title">{{ $description->title }}</div>
                                        @endif
                                        @if($description->description)
                                        <div class="description-text">{{ $description->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @elseif($pageIndex == 0)
                        <div style="text-align: center; font-size: 11px; margin: 12px 0; font-style: italic; padding: 12px 0; border: 1px solid #ddd; border-radius: 2px;">هیچ توضیحی وجود ندارد</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Footer with Signatures - At bottom of each page -->
            <div class="page-footer">
                <table class="data-table" style="border: none;">
                    <tr style="border: none;">
                        <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0 8px;">
                            <div class="signature-box">
                                <div style="font-weight: bold; margin-bottom: 6px; font-size: 16px; text-align: center; border-bottom: 1px solid #ddd; padding-bottom: 4px;">امضا مدیر/نماینده ساختمان</div>
                                <div style="height: 75px; margin-bottom: 6px; padding: 4px 0; text-align: center; overflow: visible; border-bottom: 1px solid #ddd; vertical-align: middle;">
                                    @if($managerSig && !empty($managerSig->signature))
                                        <img src="{{ trim($managerSig->signature) }}" alt="امضای مدیر" style="max-width: 100%; height: 70px; width: auto; display: block; margin: 0 auto; object-fit: contain;">
                                    @else
                                        <span style="font-size: 9px; color: #999; line-height: 75px;">امضا نشده</span>
                                    @endif
                                </div>
                                <div style="font-size: 14px; text-align: center; padding-top: 4px;">
                                    {{ $managerSig->name ?? 'نامشخص' }}
                                </div>
                            </div>
                        </td>
                        <td style="width: 50%; text-align: center; vertical-align: top; border: none; padding: 0 8px;">
                            <div class="signature-box">
                                <div style="font-weight: bold; margin-bottom: 6px; font-size: 16px; text-align: center; border-bottom: 1px solid #ddd; padding-bottom: 4px;">امضا سرویس کار</div>
                                <div style="height: 75px; margin-bottom: 6px; padding: 4px 0; text-align: center; overflow: visible; border-bottom: 1px solid #ddd; vertical-align: middle;">
                                    @if($technicianSig && !empty($technicianSig->signature))
                                        <img src="{{ trim($technicianSig->signature) }}" alt="امضای تکنسین" style="max-width: 100%; height: 70px; width: auto; display: block; margin: 0 auto; object-fit: contain;">
                                    @else
                                        <span style="font-size: 9px; color: #999; line-height: 75px;">امضا نشده</span>
                                    @endif
                                </div>
                                <div style="font-size: 14px; text-align: center; padding-top: 4px;">
                                    {{ $technicianSig->name ?? 'نامشخص' }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endforeach
    @endforeach
    @endif
</body>
</html>
