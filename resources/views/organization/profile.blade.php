@extends('organization.layout.master')

@section('title', 'پروفایل شرکت')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">پروفایل شرکت - {{ $organization->name }}</h5>
                    </div>
                    <div class="widget-content">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">اطلاعات شرکت</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th width="200">نام شرکت</th>
                                                        <td>{{ $organization->name }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>آدرس</th>
                                                        <td>{{ $organization->address }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>وضعیت</th>
                                                        <td>
                                                            @if($organization->status)
                                                                <span class="badge badge-success">فعال</span>
                                                            @else
                                                                <span class="badge badge-danger">غیرفعال</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>تاریخ ایجاد</th>
                                                        <td>{{ \Morilog\Jalali\Jalalian::fromCarbon($organization->created_at)->format('Y/m/d H:i') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>آخرین بروزرسانی</th>
                                                        <td>{{ \Morilog\Jalali\Jalalian::fromCarbon($organization->updated_at)->format('Y/m/d H:i') }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">لوگو شرکت</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        @if($organization->logo)
                                            <img src="{{ asset( $organization->logo) }}" 
                                                 alt="لوگو {{ $organization->name }}" 
                                                 class="img-fluid" 
                                                 style="max-width: 200px; max-height: 200px;">
                                        @else
                                            <div class="text-muted">
                                                <i class="fa fa-image fa-3x"></i>
                                                <p class="mt-2">هیچ لوگویی آپلود نشده است</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Package Statistics -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">آمار پکیج‌ها</h5>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $stats = $organization->package_statistics;
                                            $activePackages = $organization->activePackages();
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
                                                    <h6 class="text-muted">کل روزهای باقی‌مانده</h6>
                                                    <h4 class="text-warning">{{ $organization->total_remaining_days }}</h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h6 class="text-muted">کل مبلغ پرداخت شده</h6>
                                                    <h4 class="text-primary">{{ number_format($stats['total_amount_paid']) }} تومان</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
