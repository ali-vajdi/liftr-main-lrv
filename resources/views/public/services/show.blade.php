@extends('public.layout.master')

@section('title', 'جزئیات سرویس - ' . $service->service_date_text)

@section('page-title', 'جزئیات سرویس')

@section('content')
    <!-- Service Information -->
    <div class="building-info">
        <h3>اطلاعات سرویس</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">دوره سرویس</span>
                <span class="info-value">{{ $monthNames[$service->service_month] }} {{ $service->service_year }}</span>
            </div>
            @if($service->technician)
            <div class="info-item">
                <span class="info-label">تکنسین</span>
                <span class="info-value">{{ $service->technician->full_name }}</span>
            </div>
            @endif
            @if($service->assigned_at)
            <div class="info-item">
                <span class="info-label">تاریخ اختصاص</span>
                <span class="info-value">
                    @php
                        try {
                            if ($service->assigned_at instanceof \Carbon\Carbon) {
                                $jalaliDate = \Morilog\Jalali\Jalalian::fromCarbon($service->assigned_at);
                            } else {
                                $jalaliDate = \Morilog\Jalali\Jalalian::fromDateTime($service->assigned_at);
                            }
                            echo $jalaliDate->format('Y/m/d H:i');
                        } catch (\Exception $e) {
                            echo $service->assigned_at instanceof \Carbon\Carbon 
                                ? $service->assigned_at->format('Y/m/d H:i')
                                : date('Y/m/d H:i', strtotime($service->assigned_at));
                        }
                    @endphp
                </span>
            </div>
            @endif
            @if($service->completed_at)
            <div class="info-item">
                <span class="info-label">تاریخ تکمیل</span>
                <span class="info-value">
                    @php
                        try {
                            if ($service->completed_at instanceof \Carbon\Carbon) {
                                $jalaliDate = \Morilog\Jalali\Jalalian::fromCarbon($service->completed_at);
                            } else {
                                $jalaliDate = \Morilog\Jalali\Jalalian::fromDateTime($service->completed_at);
                            }
                            echo $jalaliDate->format('Y/m/d H:i');
                        } catch (\Exception $e) {
                            echo $service->completed_at instanceof \Carbon\Carbon 
                                ? $service->completed_at->format('Y/m/d H:i')
                                : date('Y/m/d H:i', strtotime($service->completed_at));
                        }
                    @endphp
                </span>
            </div>
            @endif
            @if($service->checklist && $service->checklist->submitted_at)
            <div class="info-item">
                <span class="info-label">تاریخ ثبت چک‌لیست</span>
                <span class="info-value">
                    @php
                        try {
                            if ($service->checklist->submitted_at instanceof \Carbon\Carbon) {
                                $jalaliDate = \Morilog\Jalali\Jalalian::fromCarbon($service->checklist->submitted_at);
                            } else {
                                $jalaliDate = \Morilog\Jalali\Jalalian::fromDateTime($service->checklist->submitted_at);
                            }
                            echo $jalaliDate->format('Y/m/d H:i');
                        } catch (\Exception $e) {
                            echo $service->checklist->submitted_at instanceof \Carbon\Carbon 
                                ? $service->checklist->submitted_at->format('Y/m/d H:i')
                                : date('Y/m/d H:i', strtotime($service->checklist->submitted_at));
                        }
                    @endphp
                </span>
            </div>
            @endif
            @if($service->notes)
            <div class="info-item" style="grid-column: 1 / -1;">
                <span class="info-label">یادداشت</span>
                <span class="info-value">{{ $service->notes }}</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Building Information -->
    <div class="building-info">
        <h3>اطلاعات ساختمان</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">نام ساختمان</span>
                <span class="info-value">{{ $service->building->name }}</span>
            </div>
            @if($service->building->manager_name)
            <div class="info-item">
                <span class="info-label">نام مدیر</span>
                <span class="info-value">{{ $service->building->manager_name }}</span>
            </div>
            @endif
            @if($service->building->manager_phone)
            <div class="info-item">
                <span class="info-label">شماره تماس</span>
                <span class="info-value">{{ $service->building->manager_phone }}</span>
            </div>
            @endif
            @if($service->building->address)
            <div class="info-item" style="grid-column: 1 / -1;">
                <span class="info-label">آدرس</span>
                <span class="info-value">{{ $service->building->address }}</span>
            </div>
            @endif
            @if($service->building->province || $service->building->city)
            <div class="info-item">
                <span class="info-label">موقعیت</span>
                <span class="info-value">
                    @if($service->building->province){{ $service->building->province->name }}@endif
                    @if($service->building->city && $service->building->province) - @endif
                    @if($service->building->city){{ $service->building->city->name }}@endif
                </span>
            </div>
            @endif
        </div>
    </div>

    <!-- Elevators Information -->
    @if($service->checklist && $service->checklist->elevatorChecklists->count() > 0)
        <div class="building-info">
            <h3>آسانسورهای سرویس شده</h3>
            <div class="elevators-list">
                @foreach($service->checklist->elevatorChecklists as $elevatorChecklist)
                    <div class="elevator-item">
                        <div class="elevator-header">
                            <div class="elevator-name">
                                <i class="fas fa-arrow-up"></i>
                                {{ $elevatorChecklist->elevator->name }}
                            </div>
                        </div>
                        <div class="elevator-details">
                            <div class="elevator-specs">
                                <div class="spec-item">
                                    <span class="spec-label">تعداد توقف:</span>
                                    <span class="spec-value">{{ $elevatorChecklist->elevator->stops_count }}</span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">ظرفیت:</span>
                                    <span class="spec-value">{{ $elevatorChecklist->elevator->capacity }} نفر</span>
                                </div>
                            </div>
                            @if($elevatorChecklist->descriptions->count() > 0)
                            <div class="elevator-descriptions">
                                <div class="descriptions-title">توضیحات سرویس:</div>
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
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif($service->building->elevators->count() > 0)
        <div class="building-info">
            <h3>آسانسورهای ساختمان</h3>
            <div class="elevators-list">
                @foreach($service->building->elevators as $elevator)
                    <div class="elevator-item">
                        <div class="elevator-header">
                            <div class="elevator-name">
                                <i class="fas fa-arrow-up"></i>
                                {{ $elevator->name }}
                            </div>
                        </div>
                        <div class="elevator-details">
                            <div class="elevator-specs">
                                <div class="spec-item">
                                    <span class="spec-label">تعداد توقف:</span>
                                    <span class="spec-value">{{ $elevator->stops_count }}</span>
                                </div>
                                <div class="spec-item">
                                    <span class="spec-label">ظرفیت:</span>
                                    <span class="spec-value">{{ $elevator->capacity }} نفر</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Signatures -->
    @if($service->checklist)
        @php
            $checklist = $service->checklist;
            // Try to get signatures from the collection first
            $allSignatures = $checklist->signatures;
            $technicianSig = $allSignatures->where('type', 'technician')->first();
            $managerSig = $allSignatures->where('type', 'manager')->first();
            
            // Fallback to direct relationships
            if (!$technicianSig) {
                $technicianSig = $checklist->technicianSignature;
            }
            if (!$managerSig) {
                $managerSig = $checklist->managerSignature;
            }
        @endphp
        
        @if(($technicianSig && !empty($technicianSig->signature)) || ($managerSig && !empty($managerSig->signature)))
        <div class="building-info">
            <h3>امضاها</h3>
            <div class="signatures-grid">
                @if($technicianSig && !empty($technicianSig->signature))
                <div class="signature-item">
                    <div class="signature-label">امضای تکنسین</div>
                    <div class="signature-name">{{ $technicianSig->name ?? 'نامشخص' }}</div>
                    <div class="signature-image">
                        <img src="{{ trim($technicianSig->signature) }}" alt="امضای تکنسین">
                    </div>
                </div>
                @endif
                @if($managerSig && !empty($managerSig->signature))
                <div class="signature-item">
                    <div class="signature-label">امضای مدیر</div>
                    <div class="signature-name">{{ $managerSig->name ?? 'نامشخص' }}</div>
                    <div class="signature-image">
                        <img src="{{ trim($managerSig->signature) }}" alt="امضای مدیر">
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif
    @endif

    <!-- Back Button -->
    <div style="margin-top: 2rem; text-align: center;">
        <a href="{{ route('public.buildings.services', $service->building_id) }}" class="btn-detail">
            <i class="fas fa-arrow-right"></i>
            بازگشت به لیست سرویس‌ها
        </a>
    </div>
@endsection

@section('page-styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .elevators-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .elevator-item {
            border: 1px solid #e5e5e5;
            padding: 1.25rem;
        }

        .elevator-item:hover {
            border-color: var(--primary);
        }

        .elevator-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e5e5;
        }

        .elevator-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .elevator-name i {
            color: var(--primary);
        }

        .elevator-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .elevator-specs {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .spec-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .spec-label {
            font-size: 0.875rem;
            color: #666;
            font-weight: 500;
        }

        .spec-value {
            font-size: 1rem;
            color: #1a1a1a;
            font-weight: 400;
        }

        .elevator-descriptions {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e5e5;
        }

        .descriptions-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 0.75rem;
        }

        .description-item {
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: #f9fafb;
            border: 1px solid #e5e5e5;
        }

        .description-item:last-child {
            margin-bottom: 0;
        }

        .description-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .description-text {
            font-size: 0.9375rem;
            color: #1a1a1a;
            line-height: 1.6;
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .signature-item {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            padding: 1.25rem;
            border: 1px solid #e5e5e5;
            background: #f9fafb;
        }

        .signature-label {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--primary);
        }

        .signature-name {
            font-size: 0.875rem;
            color: #666;
            font-weight: 500;
        }

        .signature-image {
            margin-top: 0.5rem;
            padding: 1rem;
            background: white;
            border: 1px solid #e5e5e5;
            text-align: center;
        }

        .signature-image img {
            max-width: 100%;
            height: auto;
            max-height: 150px;
            display: block;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .elevator-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .elevator-specs {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

