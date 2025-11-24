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

        <!-- Signatures (Only for completed services) -->
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
    @endif

    <!-- User Note Section -->
    <div class="building-info">
        <h3>یادداشت شما</h3>
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
                            <label for="user_note">یادداشت شما:</label>
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
                                ذخیره
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

        .visit-info-text {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 0.5rem;
            color: #1e40af;
            line-height: 1.6;
        }

        .visit-info-text i {
            color: #3b82f6;
            margin-top: 0.25rem;
            font-size: 1.125rem;
        }

        .visit-info-text strong {
            font-weight: 600;
        }

        .user-note-display,
        .user-note-empty {
            margin-bottom: 1rem;
        }

        .user-note-content {
            padding: 1rem;
            background: #f9fafb;
            border: 1px solid #e5e5e5;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .user-note-content p {
            margin: 0;
            color: #1a1a1a;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .user-note-empty {
            text-align: center;
            padding: 2rem;
            background: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 0.5rem;
        }

        .user-note-empty p {
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .btn-edit-note,
        .btn-add-note {
            padding: 0.5rem 1rem;
            background: var(--primary);
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

        .btn-edit-note:hover,
        .btn-add-note:hover {
            background: var(--primary-dark);
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
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.9375rem;
            font-family: inherit;
            resize: vertical;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 119, 182, 0.1);
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
            saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ذخیره...';
            
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
                    messageDiv.textContent = data.message || 'یادداشت با موفقیت ذخیره شد.';
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
                    messageDiv.textContent = data.message || 'خطا در ذخیره یادداشت. لطفا دوباره تلاش کنید.';
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

