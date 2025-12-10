@extends('public.layout.master')

@section('title', 'جزئیات سرویس - ' . $service->service_date_text)

@section('page-title', 'جزئیات سرویس')

@section('content')
    <!-- Visit Information (Only for assigned services) -->
    @if($service->status === 'assigned' && ($service->visit_date || $service->visit_time_range))
    <div class="building-info">
        <h3>اطلاعات بازدید</h3>
        <div class="info-grid">
            @if($service->visit_date)
            <div class="info-item">
                <span class="info-label">تاریخ بازدید</span>
                <span class="info-value">
                    @php
                        try {
                            if ($service->visit_date instanceof \Carbon\Carbon) {
                                $jalaliDate = \Morilog\Jalali\Jalalian::fromCarbon($service->visit_date);
                            } else {
                                $jalaliDate = \Morilog\Jalali\Jalalian::fromDateTime($service->visit_date);
                            }
                            echo $jalaliDate->format('Y/m/d');
                        } catch (\Exception $e) {
                            echo $service->visit_date instanceof \Carbon\Carbon 
                                ? $service->visit_date->format('Y/m/d')
                                : date('Y/m/d', strtotime($service->visit_date));
                        }
                    @endphp
                </span>
            </div>
            @endif
            @if($service->visit_time_range)
            <div class="info-item">
                <span class="info-label">بازه زمانی بازدید</span>
                <span class="info-value">{{ $service->visit_time_range }}</span>
            </div>
            @endif
            @if($service->visit_date && $service->visit_time_range)
            <div class="info-item" style="grid-column: 1 / -1;">
                <div class="visit-info-text">
                    <i class="fas fa-info-circle"></i>
                    <span>تکنسین در تاریخ <strong>{{ \Morilog\Jalali\Jalalian::fromCarbon($service->visit_date)->format('Y/m/d') }}</strong> در بازه زمانی <strong>{{ $service->visit_time_range }}</strong> برای انجام سرویس مراجعه خواهد کرد.</span>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Service Information -->
    <div class="building-info">
        <h3>اطلاعات سرویس</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">دوره سرویس</span>
                <span class="info-value">{{ $monthNames[$service->service_month] }} {{ $service->service_year }}</span>
            </div>
            @if($service->status === 'completed')
            <div class="info-item">
                <span class="info-label">وضعیت</span>
                <span class="info-value">
                    <span class="badge badge-success">سرویس انجام شد</span>
                </span>
            </div>
            @endif
            @if($service->status === 'completed' && $service->checklist && $service->checklist->submitted_at)
            <div class="info-item">
                <span class="info-label">تاریخ و زمان ثبت چک‌ لیست</span>
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

    <!-- Print PDF Button -->
    @if($service->checklist && $service->checklist->elevatorChecklists->count() > 0)
        <div class="building-info" style="text-align: center;">
            <button type="button" onclick="openPdfVerificationModal()" class="btn-print-pdf">
                <i class="fas fa-print"></i>
                دریافت PDF برگه سرویس
            </button>
        </div>
    @endif

    <!-- Technician Information -->
    @if($service->technician)
    <div class="building-info">
        <h3>اطلاعات تکنسین</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">نام و نام خانوادگی</span>
                <span class="info-value">{{ $service->technician->full_name }}</span>
            </div>
            @if($service->technician->phone_number)
            <div class="info-item">
                <span class="info-label">شماره تماس</span>
                <span class="info-value">
                    <a href="tel:{{ $service->technician->phone_number }}" style="color: var(--primary); text-decoration: none;">
                        {{ $service->technician->phone_number }}
                    </a>
                </span>
            </div>
            @endif
        </div>
    </div>
    @endif

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

    <!-- Elevators Information (Only for completed services) -->
    @if($service->status === 'completed')
        @if($service->checklist && $service->checklist->elevatorChecklists->count() > 0)
            <div class="building-info">
                <h3>آسانسورهای سرویس شده</h3>
                <div class="elevators-list">
                    @foreach($service->checklist->elevatorChecklists as $elevatorChecklist)
                        <div class="elevator-item">
                            <div class="elevator-header">
                                <div class="elevator-name">
                                    <i class="fas fa-arrow-up"></i>
                                    نام آسانسور: {{ $elevatorChecklist->elevator->name }}
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
    @endif

    <!-- PDF Verification Modal -->
    <div id="pdfVerificationModal" class="pdf-modal" style="display: none;">
        <div class="pdf-modal-overlay" onclick="closePdfVerificationModal()"></div>
        <div class="pdf-modal-content">
            <div class="pdf-modal-header">
                <h3>تایید هویت برای دریافت PDF</h3>
                <button type="button" class="pdf-modal-close" onclick="closePdfVerificationModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="pdf-modal-body">
                <div id="pdfModalMessage" class="pdf-modal-message" style="display: none;"></div>
                
                <div id="pdfModalStep1">
                    <p style="margin-bottom: 1rem; color: var(--gray-700);">
                        برای دریافت فایل PDF چک لیست، کد تایید به شماره مدیر ساختمان ارسال خواهد شد.
                    </p>
                    @if($service->building->manager_phone)
                    <div style="background: var(--gray-50); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; text-align: center;">
                        <div style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.5rem;">شماره تماس مدیر ساختمان:</div>
                        <div style="font-size: 1.125rem; font-weight: 600; color: var(--primary); direction: ltr; display: inline-block;">
                            {{ $service->building->manager_phone }}
                        </div>
                    </div>
                    @endif
                    <button type="button" onclick="sendPdfVerificationCode()" class="btn-send-code" id="btnSendCode">
                        <i class="fas fa-paper-plane"></i>
                        ارسال کد تایید
                    </button>
                </div>

                <div id="pdfModalStep2" style="display: none;">
                    <p style="margin-bottom: 1rem; color: var(--gray-700);">
                        کد تایید به شماره مدیر ساختمان ارسال شد. لطفا کد 6 رقمی را وارد کنید:
                    </p>
                    <div class="form-group">
                        <input type="text" 
                               id="pdfVerificationCode" 
                               class="form-control" 
                               placeholder="کد 6 رقمی"
                               maxlength="6"
                               pattern="[0-9]{6}"
                               style="text-align: center; font-size: 1.5rem; letter-spacing: 0.5rem; font-weight: 600;">
                    </div>
                    <div class="form-actions" style="justify-content: center; margin-top: 1.5rem;">
                        <button type="button" onclick="verifyPdfCode()" class="btn-verify-code" id="btnVerifyCode">
                            <i class="fas fa-check"></i>
                            تایید و دانلود
                        </button>
                        <button type="button" onclick="resendPdfCode()" class="btn-resend-code" id="btnResendCode">
                            <i class="fas fa-redo"></i>
                            ارسال مجدد
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($service->status === 'assigned')
    <!-- User Note Section for assigned services -->
    <div class="building-info">
        <h3>ایرادات، اشکالات و پیشنهادات شما</h3>
        <p>لطفا در صورت وجود هرگونه ایراد یا مشکل در عملکرد آسانسور ها موارد را ثبت و اعلام فرمایید تا اقدامات لازم در حین سرویس انجام شود</p>
        <div id="user-note-section">
                {{-- Editable note section for assigned services --}}
                @if($service->user_note)
                <div id="user-note-display" class="user-note-display">
                    <div class="user-note-content">
                        <p>{{ $service->user_note }}</p>
                    </div>
                    <button type="button" class="btn-edit-note" onclick="editUserNote()">
                        <i class="fas fa-edit"></i>
                        ویرایش یادداشت
                    </button>
                </div>
                @else
                <div id="user-note-empty" class="user-note-empty">
                    <p>شما هنوز یادداشتی ثبت نکرده‌اید.</p>
                    <button type="button" class="btn-add-note" onclick="editUserNote()">
                        <i class="fas fa-plus"></i>
                        افزودن یادداشت
                    </button>
                </div>
                @endif

                <div id="user-note-form" class="user-note-form" style="display: none;">
                    <form id="note-form" onsubmit="saveUserNote(event)">
                        <div class="form-group">
                            <label for="user_note">ایرادات، اشکالات و پیشنهادات شما:</label>
                            <textarea 
                                id="user_note" 
                                name="user_note" 
                                class="form-control" 
                                rows="4" 
                                placeholder="یادداشت خود را اینجا وارد کنید..."
                            >{{ $service->user_note ?? '' }}</textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-save-note">
                                <i class="fas fa-save"></i>
                                ثبت
                            </button>
                            <button type="button" class="btn-cancel-note" onclick="cancelEditNote()">
                                <i class="fas fa-times"></i>
                                انصراف
                            </button>
                        </div>
                    </form>
                </div>

                <div id="note-message" class="note-message" style="display: none;"></div>
        </div>
    </div>
    @endif

@endsection

@section('page-styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            border-radius: 0.25rem;
        }

        .badge-success {
            background: #d1fae5;
            color: #059669;
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            border-radius: 0.25rem;
        }

        .badge-secondary {
            background: #e5e7eb;
            color: #4b5563;
            padding: 0.375rem 0.75rem;
            font-size: 0.8125rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            border-radius: 0.25rem;
        }

        .elevators-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .elevator-item {
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            background: white;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .elevator-item:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .elevator-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-100);
        }

        .elevator-name {
            font-size: 1.1875rem;
            font-weight: 700;
            color: var(--gray-900);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .elevator-name i {
            color: var(--primary);
            font-size: 1.125rem;
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
            gap: 0.5rem;
            padding: 0.875rem;
            background: var(--gray-50);
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
        }

        .spec-item:hover {
            background: var(--gray-100);
        }

        .spec-label {
            font-size: 0.8125rem;
            color: var(--gray-600);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .spec-value {
            font-size: 1.0625rem;
            color: var(--gray-900);
            font-weight: 600;
        }

        .elevator-descriptions {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--gray-100);
        }

        .descriptions-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
            display: inline-block;
        }

        .description-item {
            margin-bottom: 1rem;
            padding: 1.25rem;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            transition: all 0.2s ease;
        }

        .description-item:hover {
            background: white;
            box-shadow: var(--shadow-sm);
        }

        .description-item:last-child {
            margin-bottom: 0;
        }

        .description-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }

        .description-text {
            font-size: 0.9375rem;
            color: var(--gray-800);
            line-height: 1.7;
        }


        .visit-info-text {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.25rem;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 2px solid #93c5fd;
            border-radius: var(--radius-lg);
            color: #1e40af;
            line-height: 1.7;
            box-shadow: var(--shadow-sm);
        }

        .visit-info-text i {
            color: #3b82f6;
            margin-top: 0.25rem;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .visit-info-text strong {
            font-weight: 700;
            color: #1e3a8a;
        }

        .user-note-display,
        .user-note-empty {
            margin-bottom: 1rem;
        }

        .user-note-content {
            padding: 1.25rem;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            box-shadow: var(--shadow-sm);
        }

        .user-note-content p {
            margin: 0;
            color: var(--gray-900);
            line-height: 1.7;
            white-space: pre-wrap;
        }

        .user-note-empty {
            text-align: center;
            padding: 2.5rem;
            background: var(--gray-50);
            border: 2px dashed var(--gray-300);
            border-radius: var(--radius-lg);
        }

        .user-note-empty p {
            color: var(--gray-600);
            margin-bottom: 1rem;
            font-size: 0.9375rem;
        }

        .btn-edit-note,
        .btn-add-note {
            padding: 0.625rem 1.25rem;
            background: var(--primary-gradient);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }

        .btn-edit-note:hover,
        .btn-add-note:hover {
            background: var(--primary-dark);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .user-note-form {
            margin-top: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #1a1a1a;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius-md);
            font-size: 0.9375rem;
            transition: all 0.2s ease;
            font-family: inherit;
            resize: vertical;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 119, 182, 0.1);
            background: white;
        }

        .form-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-start;
        }

        .btn-save-note {
            padding: 0.5rem 1rem;
            background: var(--success);
            color: white;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }

        .btn-save-note:hover {
            background: var(--success-dark);
        }

        .btn-cancel-note {
            padding: 0.5rem 1rem;
            background: #6b7280;
            color: white;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }

        .btn-cancel-note:hover {
            background: #4b5563;
        }

        .btn-print-pdf {
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: white;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
            box-shadow: var(--shadow-md);
            border: none;
            cursor: pointer;
        }

        .btn-print-pdf:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
            color: white;
            text-decoration: none;
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .btn-print-pdf:active {
            transform: translateY(0);
        }

        .btn-print-pdf i {
            font-size: 1.125rem;
        }

        .note-message {
            margin-top: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .note-message.success {
            background: #d1fae5;
            color: #059669;
            border: 1px solid #10b981;
        }

        .note-message.error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #ef4444;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }

            .btn-save-note,
            .btn-cancel-note {
                width: 100%;
                justify-content: center;
            }

            .elevator-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .elevator-specs {
                grid-template-columns: 1fr;
            }
        }

        /* PDF Verification Modal Styles */
        .pdf-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pdf-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
        }

        .pdf-modal-content {
            position: relative;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            z-index: 10001;
        }

        .pdf-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem;
            border-bottom: 2px solid var(--gray-200);
        }

        .pdf-modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--gray-900);
        }

        .pdf-modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray-500);
            cursor: pointer;
            padding: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .pdf-modal-close:hover {
            color: var(--gray-900);
        }

        .pdf-modal-body {
            padding: 1.5rem;
        }

        .pdf-modal-message {
            padding: 1rem;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            font-size: 0.9375rem;
        }

        .pdf-modal-message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .pdf-modal-message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .btn-send-code,
        .btn-verify-code,
        .btn-resend-code {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-send-code {
            background: var(--primary);
            color: white;
            width: 100%;
            justify-content: center;
        }

        .btn-send-code:hover {
            background: var(--primary-dark);
            box-shadow: var(--shadow-md);
        }

        .btn-send-code:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-verify-code {
            background: var(--success);
            color: white;
        }

        .btn-verify-code:hover {
            background: var(--success-dark);
            box-shadow: var(--shadow-md);
        }

        .btn-verify-code:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-resend-code {
            background: var(--gray-400);
            color: white;
        }

        .btn-resend-code:hover {
            background: var(--gray-500);
        }

        .btn-resend-code:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
@endsection

@section('page-scripts')
    @if($service->status === 'assigned')
    <script>
        function editUserNote() {
            document.getElementById('user-note-display')?.style.setProperty('display', 'none');
            document.getElementById('user-note-empty')?.style.setProperty('display', 'none');
            document.getElementById('user-note-form').style.display = 'block';
            document.getElementById('user_note').focus();
        }

        function cancelEditNote() {
            document.getElementById('user-note-form').style.display = 'none';
            document.getElementById('note-message').style.display = 'none';
            
            const hasNote = @json($service->user_note ? true : false);
            if (hasNote) {
                document.getElementById('user-note-display').style.display = 'block';
            } else {
                document.getElementById('user-note-empty').style.display = 'block';
            }
        }

        function saveUserNote(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const note = formData.get('user_note');
            const messageDiv = document.getElementById('note-message');
            
            // Show loading state
            const saveButton = form.querySelector('.btn-save-note');
            const originalText = saveButton.innerHTML;
            saveButton.disabled = true;
            saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ثبت...';
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Make API request
            fetch('/api/public/services/{{ $service->slug }}/user-note', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    user_note: note
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    messageDiv.className = 'note-message success';
                    messageDiv.textContent = data.message || 'یادداشت با موفقیت ثبت شد.';
                    messageDiv.style.display = 'block';
                    
                    // Update display
                    setTimeout(() => {
                        if (note && note.trim()) {
                            // Show note display
                            const noteDisplay = document.getElementById('user-note-display');
                            if (!noteDisplay) {
                                // Create display if it doesn't exist
                                const section = document.getElementById('user-note-section');
                                const emptyDiv = document.getElementById('user-note-empty');
                                const newDisplay = document.createElement('div');
                                newDisplay.id = 'user-note-display';
                                newDisplay.className = 'user-note-display';
                                newDisplay.innerHTML = `
                                    <div class="user-note-content">
                                        <p>${note}</p>
                                    </div>
                                    <button type="button" class="btn-edit-note" onclick="editUserNote()">
                                        <i class="fas fa-edit"></i>
                                        ویرایش یادداشت
                                    </button>
                                `;
                                section.insertBefore(newDisplay, emptyDiv);
                                emptyDiv.remove();
                            } else {
                                noteDisplay.querySelector('.user-note-content p').textContent = note;
                                noteDisplay.style.display = 'block';
                            }
                        } else {
                            // Show empty state
                            const noteDisplay = document.getElementById('user-note-display');
                            if (noteDisplay) {
                                noteDisplay.remove();
                            }
                            const emptyDiv = document.getElementById('user-note-empty');
                            if (!emptyDiv) {
                                const section = document.getElementById('user-note-section');
                                const newEmpty = document.createElement('div');
                                newEmpty.id = 'user-note-empty';
                                newEmpty.className = 'user-note-empty';
                                newEmpty.innerHTML = `
                                    <p>شما هنوز یادداشتی ثبت نکرده‌اید.</p>
                                    <button type="button" class="btn-add-note" onclick="editUserNote()">
                                        <i class="fas fa-plus"></i>
                                        افزودن یادداشت
                                    </button>
                                `;
                                section.appendChild(newEmpty);
                            } else {
                                emptyDiv.style.display = 'block';
                            }
                        }
                        
                        document.getElementById('user-note-form').style.display = 'none';
                        messageDiv.style.display = 'none';
                    }, 1500);
                } else {
                    // Show error message
                    messageDiv.className = 'note-message error';
                    messageDiv.textContent = data.message || 'خطا در ثبت یادداشت. لطفا دوباره تلاش کنید.';
                    messageDiv.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                messageDiv.className = 'note-message error';
                messageDiv.textContent = 'خطا در ارتباط با سرور. لطفا دوباره تلاش کنید.';
                messageDiv.style.display = 'block';
            })
            .finally(() => {
                saveButton.disabled = false;
                saveButton.innerHTML = originalText;
            });
        }
    </script>
    @endif

    @if($service->status === 'completed' && $service->checklist && $service->checklist->elevatorChecklists->count() > 0)
    <script>
        function openPdfVerificationModal() {
            document.getElementById('pdfVerificationModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            // Reset modal state
            document.getElementById('pdfModalStep1').style.display = 'block';
            document.getElementById('pdfModalStep2').style.display = 'none';
            document.getElementById('pdfVerificationCode').value = '';
            document.getElementById('pdfModalMessage').style.display = 'none';
            
            // Reset all buttons
            const btnSendCode = document.getElementById('btnSendCode');
            const btnVerifyCode = document.getElementById('btnVerifyCode');
            const btnResendCode = document.getElementById('btnResendCode');
            
            // Reset send code button
            btnSendCode.disabled = false;
            btnSendCode.innerHTML = '<i class="fas fa-paper-plane"></i> ارسال کد تایید';
            
            // Reset verify code button
            btnVerifyCode.disabled = false;
            btnVerifyCode.innerHTML = '<i class="fas fa-check"></i> تایید و دانلود';
            
            // Reset resend code button
            btnResendCode.disabled = false;
            btnResendCode.innerHTML = '<i class="fas fa-redo"></i> ارسال مجدد';
        }

        function closePdfVerificationModal() {
            document.getElementById('pdfVerificationModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        function showPdfModalMessage(message, type) {
            const messageDiv = document.getElementById('pdfModalMessage');
            messageDiv.textContent = message;
            messageDiv.className = 'pdf-modal-message ' + type;
            messageDiv.style.display = 'block';
        }

        function hidePdfModalMessage() {
            document.getElementById('pdfModalMessage').style.display = 'none';
        }

        function sendPdfVerificationCode() {
            const btn = document.getElementById('btnSendCode');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ارسال...';
            hidePdfModalMessage();

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/api/public/services/{{ $service->slug }}/pdf/send-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showPdfModalMessage(data.message || 'کد تایید با موفقیت ارسال شد.', 'success');
                    document.getElementById('pdfModalStep1').style.display = 'none';
                    document.getElementById('pdfModalStep2').style.display = 'block';
                    document.getElementById('pdfVerificationCode').focus();
                } else {
                    showPdfModalMessage(data.message || 'خطا در ارسال کد تایید. لطفا دوباره تلاش کنید.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showPdfModalMessage('خطا در ارتباط با سرور. لطفا دوباره تلاش کنید.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }

        function verifyPdfCode() {
            const code = document.getElementById('pdfVerificationCode').value.trim();
            
            if (!code || code.length !== 6) {
                showPdfModalMessage('لطفا کد 6 رقمی را وارد کنید.', 'error');
                return;
            }

            const btn = document.getElementById('btnVerifyCode');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال تایید...';
            hidePdfModalMessage();

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('/api/public/services/{{ $service->slug }}/pdf/verify-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    code: code
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.token) {
                    // Download PDF with token
                    const downloadUrl = '{{ route("public.services.print", ["building" => $building->slug, "service" => $service->slug]) }}?token=' + data.data.token;
                    window.open(downloadUrl, '_blank');
                    closePdfVerificationModal();
                } else {
                    showPdfModalMessage(data.message || 'کد تایید نامعتبر است.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showPdfModalMessage('خطا در ارتباط با سرور. لطفا دوباره تلاش کنید.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }

        function resendPdfCode() {
            const btn = document.getElementById('btnResendCode');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ارسال...';
            hidePdfModalMessage();

            sendPdfVerificationCode();
            
            // Re-enable resend button after a delay
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }, 2000);
        }

        // Allow Enter key to submit code
        document.addEventListener('DOMContentLoaded', function() {
            const codeInput = document.getElementById('pdfVerificationCode');
            if (codeInput) {
                codeInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        verifyPdfCode();
                    }
                });
                
                // Only allow numbers
                codeInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
            }
        });
    </script>
    @endif
@endsection

