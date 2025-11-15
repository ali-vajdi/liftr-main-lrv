@extends('organization.layout.master')

@section('title', 'داشبورد شرکت')

@section('content')
<div class="row layout-top-spacing">
    <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
        <div class="widget widget-chart-one">
            <div class="widget-heading">
                <h5 class="mb-0">خوش آمدید، {{ $organization->name }}</h5>
            </div>
            <div class="widget-content">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <h5 class="card-title text-primary">پکیج‌های فعال</h5>
                                <h2 class="text-primary">{{ $organization->activePackages()->count() }}</h2>
                                <p class="card-text">تعداد پکیج‌های فعال شما</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h5 class="card-title text-success">روزهای باقی‌مانده</h5>
                                <h2 class="text-success">{{ $organization->total_remaining_days }}</h2>
                                <p class="card-text">کل روزهای باقی‌مانده</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <h5 class="card-title text-info">وضعیت شرکت</h5>
                                <h2><span class="badge {{ $organization->organization_status_badge_class }}">{{ $organization->organization_status_text }}</span></h2>
                                <p class="card-text">وضعیت فعلی پکیج‌ها</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($organization->activePackages()->count() > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">پکیج‌های فعال شما</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>نام پکیج</th>
                                                <th>مدت زمان</th>
                                                <th>قیمت</th>
                                                <th>روزهای باقی‌مانده</th>
                                                <th>تاریخ انقضا</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($organization->activePackages() as $package)
                                            <tr>
                                                <td>{{ $package->package_name }}</td>
                                                <td>{{ $package->package_duration_label }}</td>
                                                <td>{{ $package->formatted_price }}</td>
                                                <td>
                                                    <span class="badge badge-warning">{{ $package->remaining_days }} روز</span>
                                                </td>
                                                <td>{{ \Morilog\Jalali\Jalalian::fromCarbon($package->expires_at)->format('Y/m/d') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <h5>هیچ پکیج فعالی ندارید</h5>
                            <p>برای مشاهده پکیج‌های خود، به بخش "پکیج‌های من" مراجعه کنید.</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
