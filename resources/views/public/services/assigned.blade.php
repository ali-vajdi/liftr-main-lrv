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
            <div class="info-item">
                <span class="info-label">وضعیت</span>
                <span class="info-value">
                    @if($service->status === 'assigned')
                        <span class="badge badge-info">{{ $service->status_text }}</span>
                    @elseif($service->status === 'completed')
                        <span class="badge badge-success">{{ $service->status_text }}</span>
                    @else
                        <span class="badge badge-secondary">{{ $service->status_text }}</span>
                    @endif
                </span>
            </div>
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
            @if($service->status === 'completed' && $service->completed_at)
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
            @if($service->status === 'completed' && $service->checklist && $service->checklist->submitted_at)
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

        <!-- Print PDF Button (Only for completed services) -->
        @if($service->checklist && $service->checklist->elevatorChecklists->count() > 0)
        <div class="building-info" style="text-align: center;">
            <a href="{{ route('public.services.print', ['building' => $building->slug, 'service' => $service->slug]) }}" target="_blank" class="btn-print-pdf">
                <i class="fas fa-print"></i>
                چاپ چک لیست
            </a>
        </div>
        @endif
    @endif

    <!-- User Note Section -->
    <div class="building-info">
        <h3>ایرادات، اشکالات و پیشنهادات شما</h3>
        <div id="user-note-section">
            @if($service->status === 'assigned')
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
            @else
                {{-- Read-only note display for completed services --}}
                @if($service->user_note)
                <div class="user-note-display">
                    <div class="user-note-content">
                        <p>{{ $service->user_note }}</p>
                    </div>
                </div>
                @else
                <div class="user-note-empty">
                    <p>یادداشتی ثبت نشده است.</p>
                </div>
                @endif
            @endif
        </div>
    </div>
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
@endsection

