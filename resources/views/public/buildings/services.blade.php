@extends('public.layout.master')

@section('title', 'سرویس‌های تکمیل شده - ' . $building->name)

@section('page-title', 'سرویس‌های تکمیل شده ساختمان')

@section('content')
    <!-- Building Information -->
    <div class="building-info">
        <h3>اطلاعات ساختمان</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">نام ساختمان</span>
                <span class="info-value">{{ $building->name }}</span>
            </div>
            @if($building->manager_name)
            <div class="info-item">
                <span class="info-label">نام مدیر</span>
                <span class="info-value">{{ $building->manager_name }}</span>
            </div>
            @endif
            @if($building->manager_phone)
            <div class="info-item">
                <span class="info-label">شماره تماس</span>
                <span class="info-value">{{ $building->manager_phone }}</span>
            </div>
            @endif
            @if($building->building_type)
            <div class="info-item">
                <span class="info-label">نوع ساختمان</span>
                <span class="info-value">{{ $building->building_type_text }}</span>
            </div>
            @endif
            @if($building->address)
            <div class="info-item" style="grid-column: 1 / -1;">
                <span class="info-label">آدرس</span>
                <span class="info-value">{{ $building->address }}</span>
            </div>
            @endif
            @if($building->province || $building->city)
            <div class="info-item">
                <span class="info-label">موقعیت</span>
                <span class="info-value">
                    @if($building->province){{ $building->province->name }}@endif
                    @if($building->city && $building->province) - @endif
                    @if($building->city){{ $building->city->name }}@endif
                </span>
            </div>
            @endif
        </div>
    </div>

    <!-- Services List -->
    @if(count($groupedServices) > 0)
        <div class="services-section">
            <h2 class="section-title">سرویس‌های تکمیل شده</h2>
            
            @foreach($groupedServices as $year => $months)
                @foreach($months as $month => $services)
                    <div class="service-group">
                        <div class="service-group-header">
                            <span class="service-group-title">{{ $monthNames[$month] }} {{ $year }}</span>
                            <span class="service-count">{{ count($services) }} سرویس</span>
                        </div>
                        <div class="service-list">
                            @foreach($services as $service)
                                <div class="service-item">
                                    <div class="service-content">
                                        <div class="service-title">
                                            سرویس {{ $monthNames[$service->service_month] }} {{ $service->service_year }}
                                        </div>
                                        <div class="service-meta">
                                            @if($service->technician)
                                            <div class="service-meta-item">
                                                <i class="fas fa-user"></i>
                                                <span>{{ $service->technician->full_name ?? 'نامشخص' }}</span>
                                            </div>
                                            @endif
                                            @if($service->completed_at)
                                            <div class="service-meta-item">
                                                <i class="fas fa-calendar"></i>
                                                <span>
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
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('public.services.assigned.show', $service->slug) }}" class="btn-detail">
                                            <i class="fas fa-eye"></i>
                                            جزئیات
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <h4>سرویس تکمیل شده‌ای یافت نشد</h4>
            <p>در حال حاضر هیچ سرویس تکمیل شده‌ای برای این ساختمان ثبت نشده است.</p>
        </div>
    @endif
@endsection

@section('page-styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection

