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
                <div style="margin-bottom: 6px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 33.33%; text-align: right; vertical-align: top;">
                                <div>
                                    <span style="font-weight: bold; margin-bottom: 2px; font-size: 11px; display: block;">نام شرکت:</span>
                                    <span style="font-size: 12px;">{{ $service->building->organization->name ?? 'نامشخص' }}</span>
                                </div>
                            </td>
                            <td style="width: 33.34%; text-align: center; vertical-align: top;">
                                <div style="font-size: 18px; font-weight: bold; margin: 0;">چک لیست سرویس و نگهداری</div>
                            </td>
                            <td style="width: 33.33%; text-align: left; vertical-align: top;">
                                <div>
                                    <span style="font-weight: bold; margin-bottom: 2px; font-size: 11px; display: block;">تاریخ بازدید:</span>
                                    <span style="font-size: 12px;">{{ $completedDate ?? 'نامشخص' }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div style="border-top: 1px solid #000; margin: 4px 0;"></div>

                <!-- Building and Elevator Info - Only on first page -->
                <div style="padding: 4px 0; margin-bottom: 6px;">
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
                        <tr>
                            <td style="width: 50%; text-align: right; vertical-align: top; padding-left: 8px;">
                                <div>
                                    <span style="font-weight: bold; display: inline-block; min-width: 90px; margin-left: 8px; font-size: 11px;">نام پروژه:</span>
                                    <span style="font-size: 12px;">{{ $service->building->name ?? 'نامشخص' }}</span>
                                </div>
                            </td>
                            <td style="width: 50%; text-align: left; vertical-align: top; padding-right: 8px;">
                                <div>
                                    <span style="font-weight: bold; display: inline-block; min-width: 90px; margin-left: 8px; font-size: 11px;">نام آسانسور:</span>
                                    <span style="font-size: 12px;">{{ ($elevatorChecklist->elevator && $elevatorChecklist->elevator->name) ? $elevatorChecklist->elevator->name : 'نامشخص' }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
                        <tr>
                            <td style="width: 50%; text-align: right; vertical-align: top; padding-left: 8px;">
                                <div>
                                    <span style="font-weight: bold; display: inline-block; min-width: 90px; margin-left: 8px; font-size: 11px;">تعداد توقف:</span>
                                    <span style="font-size: 12px;">{{ ($elevatorChecklist->elevator && isset($elevatorChecklist->elevator->stops_count)) ? $elevatorChecklist->elevator->stops_count : 'نامشخص' }}</span>
                                    <span style="font-weight: bold; display: inline-block; min-width: 90px; margin-left: 8px; margin-right: 8px; font-size: 11px;">ظرفیت:</span>
                                    <span style="font-size: 12px;">{{ ($elevatorChecklist->elevator && isset($elevatorChecklist->elevator->capacity)) ? $elevatorChecklist->elevator->capacity : 'نامشخص' }}</span>
                                </div>
                            </td>
                            <td style="width: 50%; text-align: left; vertical-align: top; padding-right: 8px;">
                                <div>
                                    <span style="font-weight: bold; display: inline-block; min-width: 90px; margin-left: 8px; font-size: 11px;">سرویس ماه:</span>
                                    <span style="font-size: 12px;">{{ ($service->service_month && isset($monthNames[$service->service_month])) ? $monthNames[$service->service_month] : 'نامشخص' }} {{ $service->service_year ?? '' }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 100%; text-align: right; vertical-align: top; padding-left: 8px;">
                                <div>
                                    <span style="font-weight: bold; display: inline-block; min-width: 90px; margin-left: 8px; font-size: 11px;">آدرس:</span>
                                    <span style="font-size: 12px;">{{ $service->building->address ?? '' }}</span>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <div style="border-top: 1px solid #000; margin: 4px 0;"></div>

                <!-- Unit Checklists - Only on first page -->
                <div style="text-align: center; font-size: 16px; font-weight: bold; margin: 6px 0 4px 0; padding: 3px 0;">موارد زیر مورد بررسی قرار گرفت</div>
                <ul style="list-style: none; padding: 0; margin: 0 0 6px 0;">
                    @foreach($unitChecklists as $index => $unitChecklist)
                    <li style="padding: 3px 0; border-bottom: 1px solid #000; font-size: 11px; line-height: 1.3; @if($loop->last) border-bottom: none; @endif">
                        <span style="display: inline-block; width: 25px; font-weight: bold; margin-left: 8px;">{{ $index + 1 }}.</span>
                        {{ $unitChecklist->title }}
                    </li>
                    @endforeach
                </ul>
                @else
                <!-- Continuation page - start from top -->
                <div style="padding: 0; margin: 0;"></div>
                @endif

                <!-- Descriptions -->
                <div style="text-align: center; font-size: 16px; font-weight: bold; margin: @if($pageIndex == 0) 6px 0 4px 0; @else 0 0 4px 0; @endif padding: 3px 0;">توضیحات</div>
                @if($descriptions->count() > 0)
                <div style="margin-bottom: 5px; page-break-inside: auto;">
                    @foreach($descriptionChunk as $chunkIndex => $description)
                    @php
                        $globalIndex = ($pageIndex * $descriptionsPerPage) + $chunkIndex;
                    @endphp
                    <div style="margin-bottom: 1px; padding: 2px 0; border-bottom: 1px solid #000; font-size: 10px; line-height: 1.2; page-break-inside: avoid; @if($chunkIndex == 0 && $pageIndex > 0) margin-top: 0; padding-top: 0; @endif">
                        <span style="display: inline-block; width: 22px; font-weight: bold; margin-left: 6px;">{{ $globalIndex + 1 }}.</span>
                        @if($description->title)
                        <span style="font-weight: bold; margin-bottom: 1px; font-size: 11px;">{{ $description->title }}</span>
                        @endif
                        @if($description->description)
                        <div style="line-height: 1.2; font-size: 10px; word-wrap: break-word; margin-top: 1px; margin-right: 22px;">{{ $description->description }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @elseif($pageIndex == 0)
                <div style="text-align: center; font-size: 11px; margin: 12px 0 15px 0; font-style: italic; padding: 12px 0;">هیچ توضیحی وجود ندارد</div>
                @endif
            </div>

            <!-- Footer with Signatures - At bottom of each page -->
            <div class="page-footer">
                <table style="width: 100%; border-collapse: collapse; border: 0; border-style: none; border-width: 0;">
                    <tr style="width: 100%; border: 0; border-style: none;">
                        <td style="width: 50%; text-align: center; vertical-align: top; border: 0; border-style: none; border-width: 0; padding: 0 10px;">
                            <div style="text-align: center; padding: 3px 0; border: 0; border-style: none;">
                                <div style="font-weight: bold; margin-bottom: 4px; font-size: 16px; text-decoration: none; border: 0;">امضا مدیر/نماینده ساختمان</div>
                                <div style="height: 85px; margin-bottom: 4px; padding: 2px 0; text-align: center; overflow: visible; border: 0; border-style: none;">
                                    @if($managerSig && !empty($managerSig->signature))
                                        <img src="{{ trim($managerSig->signature) }}" alt="امضای مدیر" style="max-width: 100%; height: 80px; width: auto; display: block; margin: 0 auto; border: 0; object-fit: contain;">
                                    @else
                                        <span style="font-size: 9px;">امضا نشده</span>
                                    @endif
                                </div>
                                <div style="font-size: 14px; margin-top: 2px;">
                                    {{ $managerSig->name ?? 'نامشخص' }}
                                </div>
                            </div>
                        </td>
                        <td style="width: 50%; text-align: center; vertical-align: top; border: 0; border-style: none; border-width: 0; padding: 0 10px;">
                            <div style="text-align: center; padding: 3px 0; border: 0; border-style: none;">
                                <div style="font-weight: bold; margin-bottom: 4px; font-size: 16px; text-decoration: none; border: 0;">امضا سرویس کار</div>
                                <div style="height: 85px; margin-bottom: 4px; padding: 2px 0; text-align: center; overflow: visible; border: 0; border-style: none;">
                                    @if($technicianSig && !empty($technicianSig->signature))
                                        <img src="{{ trim($technicianSig->signature) }}" alt="امضای تکنسین" style="max-width: 100%; height: 80px; width: auto; display: block; margin: 0 auto; border: 0; object-fit: contain;">
                                    @else
                                        <span style="font-size: 9px;">امضا نشده</span>
                                    @endif
                                </div>
                                <div style="font-size: 14px; margin-top: 2px;">
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
