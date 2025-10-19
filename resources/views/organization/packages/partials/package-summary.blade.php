@php
    $activePackages = $organization->activePackages();
    $totalRemainingDays = $organization->total_remaining_days;
    $totalAmountPaid = $activePackages->sum('package_price');
    $averageDaysPerPackage = $activePackages->count() > 0 ? round($totalRemainingDays / $activePackages->count(), 1) : 0;
    $longestPackage = $activePackages->sortByDesc('package_duration_days')->first();
    $shortestPackage = $activePackages->sortBy('package_duration_days')->first();
@endphp

@if($activePackages->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-check-circle"></i> پکیج‌های فعال شما ({{ $activePackages->count() }} پکیج)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="text-center">
                                <h6 class="text-muted">کل روزهای باقی‌مانده</h6>
                                <h4 class="text-warning">{{ $totalRemainingDays }} روز</h4>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h6 class="text-muted">میانگین روزها</h6>
                                <h4 class="text-info">{{ $averageDaysPerPackage }} روز</h4>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h6 class="text-muted">کل مبلغ پرداخت شده</h6>
                                <h4 class="text-success">{{ number_format($totalAmountPaid) }} تومان</h4>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h6 class="text-muted">طولانی‌ترین پکیج</h6>
                                <h4 class="text-primary">{{ $longestPackage ? $longestPackage->package_duration_days . ' روز' : '-' }}</h4>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h6 class="text-muted">کوتاه‌ترین پکیج</h6>
                                <h4 class="text-secondary">{{ $shortestPackage ? $shortestPackage->package_duration_days . ' روز' : '-' }}</h4>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h6 class="text-muted">آخرین انقضا</h6>
                                <h4 class="text-danger">{{ \Morilog\Jalali\Jalalian::fromCarbon($activePackages->max('expires_at'))->format('Y/m/d') }}</h4>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Active Packages List -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>پکیج‌های فعال شما:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>نام پکیج</th>
                                            <th class="text-center">مدت زمان</th>
                                            <th class="text-center">قیمت</th>
                                            <th class="text-center">روزهای باقی‌مانده</th>
                                            <th class="text-center">تاریخ انقضا</th>
                                            <th class="text-center">تاریخ شروع</th>
                                            <th class="text-center">وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activePackages as $index => $package)
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge badge-light">{{ $index + 1 }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $package->package_name }}</strong>
                                                    @if($package->has_package_changed)
                                                        <small class="text-warning d-block">
                                                            <i class="fa fa-exclamation-triangle"></i> تغییر کرده
                                                        </small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-info">{{ $package->package_duration_days }} روز</span>
                                                </td>
                                                <td class="text-center">
                                                    <strong class="text-success">{{ $package->formatted_price }}</strong>
                                                </td>
                                                <td class="text-center">
                                                    @if($package->remaining_days > 0)
                                                        <span class="badge badge-warning">
                                                            <i class="fa fa-clock"></i> {{ $package->remaining_days }} روز
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger">
                                                            <i class="fa fa-times"></i> منقضی شده
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <small class="text-muted">
                                                        {{ \Morilog\Jalali\Jalalian::fromCarbon($package->expires_at)->format('Y/m/d') }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <small class="text-muted">
                                                        {{ \Morilog\Jalali\Jalalian::fromCarbon($package->started_at)->format('Y/m/d') }}
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    @if($package->is_active)
                                                        <span class="badge badge-success">
                                                            <i class="fa fa-check"></i> فعال
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger">
                                                            <i class="fa fa-times"></i> غیرفعال
                                                        </span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fa fa-exclamation-triangle"></i> بدون پکیج فعال
                    </h5>
                </div>
                <div class="card-body text-center">
                    <h4 class="text-warning">شما در حال حاضر پکیج فعالی ندارید</h4>
                    <p class="text-muted">برای اطلاع از پکیج‌های خود، با مدیر سیستم تماس بگیرید</p>
                </div>
            </div>
        </div>
    </div>
@endif

@if($organization->packages()->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-chart-bar"></i> آمار کلی پکیج‌های شما
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $stats = $organization->package_statistics;
                        $activeRate = $stats['total'] > 0 ? round(($stats['active'] / $stats['total']) * 100, 1) : 0;
                        $avgAmount = $stats['total'] > 0 ? round($stats['total_amount_paid'] / $stats['total']) : 0;
                    @endphp
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">کل پکیج‌ها</h6>
                                <h4 class="text-info">{{ $stats['total'] }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">پکیج‌های فعال</h6>
                                <h4 class="text-success">{{ $stats['active'] }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">پکیج‌های منقضی</h6>
                                <h4 class="text-danger">{{ $stats['expired'] }}</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h6 class="text-muted">نرخ فعال بودن</h6>
                                <h4 class="text-primary">{{ $activeRate }}%</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="text-center">
                                <h6 class="text-muted">کل مبلغ پرداخت شده</h6>
                                <h4 class="text-primary">{{ number_format($stats['total_amount_paid']) }} تومان</h4>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h6 class="text-muted">میانگین مبلغ هر پکیج</h6>
                                <h4 class="text-secondary">{{ number_format($avgAmount) }} تومان</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
