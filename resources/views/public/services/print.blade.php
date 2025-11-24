<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>چک لیست سرویس و نگهداری</title>
    
    <!-- Bootstrap RTL CSS -->
    <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        @font-face {
            font-family: 'Vazir';
            src: url('{{ asset("assets/fonts/vazir/Vazir-Regular.woff2") }}') format('woff2'),
                 url('{{ asset("assets/fonts/vazir/Vazir-Regular.woff") }}') format('woff');
            font-weight: normal;
            font-style: normal;
        }
        
        @font-face {
            font-family: 'Vazir';
            src: url('{{ asset("assets/fonts/vazir/Vazir-Bold.woff2") }}') format('woff2'),
                 url('{{ asset("assets/fonts/vazir/Vazir-Bold.woff") }}') format('woff');
            font-weight: bold;
            font-style: normal;
        }

        body {
            font-family: 'Vazir', 'Tahoma', Arial, sans-serif;
            direction: rtl;
            text-align: right;
            font-size: 12px;
            line-height: 1.6;
            color: #000;
            background: #f5f5f5;
            padding: 20px;
        }

        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .print-controls .btn {
            margin-left: 10px;
        }

        .page {
            background: white;
            padding: 20mm;
            margin: 0 auto 20px auto;
            min-height: 297mm;
            width: 210mm;
            max-width: 100%;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }

        .page-header {
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .header-label {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 11px;
        }

        .header-value {
            font-size: 12px;
        }

        .separator {
            border-top: 1px solid #000;
            margin: 15px 0;
        }

        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 100px;
            margin-left: 10px;
        }

        .checklist-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 10px 0;
        }

        .checklist-items {
            list-style: none;
            padding: 0;
            margin: 0 0 15px 0;
        }

        .checklist-item {
            padding: 5px 0;
            border-bottom: 1px dotted #666;
            font-size: 11px;
        }

        .checklist-item:last-child {
            border-bottom: none;
        }

        .checklist-number {
            display: inline-block;
            width: 30px;
            font-weight: bold;
            margin-left: 10px;
        }

        .descriptions-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 10px 0;
        }

        .description-item {
            margin-bottom: 8px;
            padding: 6px 10px;
            border: 1px solid #ddd;
            background: #f9f9f9;
            border-radius: 4px;
        }

        .description-title {
            font-weight: bold;
            margin-bottom: 4px;
            color: #333;
            font-size: 11px;
        }

        .description-text {
            color: #666;
            line-height: 1.5;
            font-size: 10px;
        }

        .descriptions-container {
            margin-bottom: 60px;
            max-height: 400px;
            overflow: hidden;
        }

        .page-footer {
            position: absolute;
            bottom: 20mm;
            right: 20mm;
            left: 20mm;
            margin-top: 20px;
        }

        .signature-box {
            text-align: center;
        }

        .signature-label {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .signature-image {
            border: 1px solid #000;
            min-height: 70px;
            margin-bottom: 10px;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
        }

        .signature-image img {
            max-width: 100%;
            max-height: 60px;
            object-fit: contain;
        }

        .signature-name {
            font-size: 12px;
            margin-top: 5px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .page {
                padding: 15mm;
                width: 100%;
                margin: 0 auto 15px auto;
            }

            .print-controls {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 20px;
                text-align: center;
            }

            .print-controls .btn {
                display: block;
                width: 100%;
                margin: 5px 0;
            }

        .page-footer {
            position: relative;
            bottom: auto;
            margin-top: 30px;
        }

        .descriptions-container {
            margin-bottom: 60px;
        }
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .print-controls {
                display: none !important;
            }

            .page {
                margin: 0;
                padding: 20mm;
                box-shadow: none;
                page-break-after: always;
                width: 100%;
                height: 100%;
                max-width: 100%;
            }

            .page:last-child {
                page-break-after: auto;
            }

            .page-footer {
                position: absolute;
            }

            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> چاپ
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            <i class="fas fa-times"></i> بستن
        </button>
    </div>

    @foreach($service->checklist->elevatorChecklists as $elevatorChecklist)
    <div class="page">
        <!-- Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-4 text-right">
                    <div class="header-label">نام شرکت:</div>
                    <div class="header-value">{{ $service->building->organization->name ?? 'نامشخص' }}</div>
                </div>
                <div class="col-4 text-center">
                    <h4 style="font-weight: bold; margin: 0;">چک لیست سرویس و نگهداری</h4>
                </div>
                <div class="col-4 text-left">
                    <div class="header-label">تاریخ بازدید:</div>
                    <div class="header-value">{{ $completedDate ?? 'نامشخص' }}</div>
                </div>
            </div>
        </div>

        <div class="separator"></div>

        <!-- Building and Elevator Info -->
        <div class="row mb-3">
            <div class="col-md-6 col-12 text-right">
                <div class="mb-2">
                    <span class="info-label">نام پروژه:</span>
                    <span>{{ $service->building->name }}</span>
                </div>
            </div>
            <div class="col-md-6 col-12 text-left">
                <div class="mb-2">
                    <span class="info-label">نام آسانسور:</span>
                    <span>{{ $elevatorChecklist->elevator->name }}</span>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 col-12 text-right">
                <div class="mb-2">
                    <span class="info-label">تعداد توقف:</span>
                    <span>{{ $elevatorChecklist->elevator->stops_count }}</span>
                    <span class="info-label" style="margin-right: 15px;">ظرفیت:</span>
                    <span>{{ $elevatorChecklist->elevator->capacity }}</span>
                </div>
            </div>
            <div class="col-md-6 col-12 text-left">
                <div class="mb-2">
                    <span class="info-label">سرویس ماه:</span>
                    <span>{{ $monthNames[$service->service_month] }} {{ $service->service_year }}</span>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 text-right">
                <div class="mb-2">
                    {{ $service->building->address ?? '' }}
                </div>
            </div>
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
        @if($elevatorChecklist->descriptions->count() > 0)
        <div class="descriptions-title">توضیحات</div>
        <div class="descriptions-container">
            <div class="row">
                <div class="col-12">
                    @foreach($elevatorChecklist->descriptions as $description)
                    <div class="description-item">
                        @if($description->title)
                        <div class="description-title">{{ $description->title }}</div>
                        @endif
                        @if($description->description)
                        <div class="description-text">{{ $description->description }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @else
        <div style="margin-bottom: 60px;"></div>
        @endif

        <!-- Footer with Signatures -->
        <div class="page-footer">
            <div class="row">
                <div class="col-md-6 col-12">
                    <div class="signature-box">
                        <div class="signature-label">امضا مدیر/نماینده ساختمان</div>
                        <div class="signature-image">
                            @if($managerSig && !empty($managerSig->signature))
                                <img src="{{ trim($managerSig->signature) }}" alt="امضای مدیر">
                            @else
                                <span style="color: #999;">امضا نشده</span>
                            @endif
                        </div>
                        <div class="signature-name">
                            {{ $managerSig->name ?? 'نامشخص' }}
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="signature-box">
                        <div class="signature-label">امضا سرویس کار</div>
                        <div class="signature-image">
                            @if($technicianSig && !empty($technicianSig->signature))
                                <img src="{{ trim($technicianSig->signature) }}" alt="امضای تکنسین">
                            @else
                                <span style="color: #999;">امضا نشده</span>
                            @endif
                        </div>
                        <div class="signature-name">
                            {{ $technicianSig->name ?? 'نامشخص' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Bootstrap JS -->
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
