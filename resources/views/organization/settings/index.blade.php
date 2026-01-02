@extends('organization.layout.master')

@section('title', 'تنظیمات')

@section('content')
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                <div class="widget widget-chart-one">
                    <div class="widget-heading">
                        <h5 class="mb-0">تنظیمات قرارداد</h5>
                    </div>
                    <div class="widget-content">
                        <!-- Contract Settings Section -->
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fa fa-file-contract"></i> قالب شماره فاکتور
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Help Section -->
                                <div class="alert alert-info mb-4">
                                    <h6 class="alert-heading"><i class="fa fa-info-circle"></i> راهنمای استفاده:</h6>
                                    <ul class="mb-0" style="padding-right: 20px;">
                                        <li>برای ساخت قالب، از دکمه‌های زیر استفاده کنید و بخش‌های مورد نظر را اضافه کنید</li>
                                        <li>می‌توانید با کشیدن و رها کردن، ترتیب بخش‌ها را تغییر دهید</li>
                                        <li>برای حذف هر بخش، روی دکمه حذف کلیک کنید</li>
                                        <li>جداکننده بین بخش‌ها را می‌توانید انتخاب کنید ( / یا - )</li>
                                    </ul>
                                </div>

                                <!-- Preview Section -->
                                <div class="card bg-light mb-4">
                                    <div class="card-body text-center">
                                        <label class="text-muted mb-2 d-block">پیش‌نمایش قالب:</label>
                                        <div class="preview-container" style="min-height: 60px; display: flex; align-items: center; justify-content: center;">
                                            <div id="format-preview" class="h4 mb-0 text-primary font-weight-bold" style="font-family: 'Courier New', monospace; letter-spacing: 2px;">
                                                <span class="text-muted">هیچ قالبی تعریف نشده است</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Format Builder -->
                                <div class="mb-4">
                                    <label class="font-weight-bold mb-3 d-block">
                                        <i class="fa fa-puzzle-piece"></i> ساخت قالب:
                                    </label>
                                    
                                    <!-- Available Parts -->
                                    <div class="mb-4">
                                        <label class="text-muted mb-2 d-block">افزودن بخش:</label>
                                        <div class="row">
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <button type="button" class="btn btn-outline-primary btn-block add-part-btn" data-type="increment">
                                                    <i class="fa fa-sort-numeric-up"></i><br>
                                                    <small>عدد افزایشی</small>
                                                </button>
                                            </div>
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <button type="button" class="btn btn-outline-info btn-block add-part-btn" data-type="day">
                                                    <i class="fa fa-calendar-day"></i><br>
                                                    <small>روز</small>
                                                </button>
                                            </div>
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <button type="button" class="btn btn-outline-success btn-block add-part-btn" data-type="month">
                                                    <i class="fa fa-calendar-alt"></i><br>
                                                    <small>ماه</small>
                                                </button>
                                            </div>
                                            <div class="col-md-3 col-sm-6 mb-2">
                                                <button type="button" class="btn btn-outline-warning btn-block add-part-btn" data-type="text">
                                                    <i class="fa fa-font"></i><br>
                                                    <small>متن</small>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Format Parts Container -->
                                    <div class="mb-3">
                                        <label class="text-muted mb-2 d-block">ترتیب بخش‌ها:</label>
                                        <div id="format-parts-container" class="format-parts-container" style="min-height: 80px; padding: 15px; border: 2px dashed #dee2e6; border-radius: 8px; background-color: #f8f9fa;">
                                            <div class="empty-state text-center text-muted py-4" id="empty-state">
                                                <i class="fa fa-arrow-up fa-2x mb-2"></i>
                                                <p class="mb-0">برای شروع، یک بخش اضافه کنید</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Custom Text Input -->
                                    <div class="form-group" id="custom-text-group" style="display: none;">
                                        <label for="custom-text-input" class="font-weight-bold">
                                            <i class="fa fa-font"></i> متن سفارشی:
                                        </label>
                                        <input type="text" class="form-control form-control-lg" id="custom-text-input" placeholder="متن مورد نظر را وارد کنید (مثال: شرکت، پروژه و ...)">
                                        <small class="form-text text-muted">این متن در قالب شماره فاکتور نمایش داده می‌شود</small>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <button type="button" class="btn btn-secondary" id="reset-format-btn">
                                        <i class="fa fa-redo"></i> بازنشانی
                                    </button>
                                    <button type="button" class="btn btn-success btn-lg" id="save-contract-format-btn">
                                        <i class="fa fa-save"></i> ذخیره قالب
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .format-part-item {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 12px 15px;
            margin: 8px;
            cursor: move;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .format-part-item:hover {
            border-color: #007bff;
            box-shadow: 0 4px 8px rgba(0,123,255,0.2);
            transform: translateY(-2px);
        }
        .format-part-item.dragging {
            opacity: 0.5;
            border-color: #007bff;
        }
        .format-part-item .badge {
            font-size: 14px;
            padding: 8px 12px;
        }
        .format-parts-container {
            position: relative;
        }
        .separator-select {
            min-width: 70px;
            font-weight: bold;
            text-align: center;
        }
        .add-part-btn {
            height: 80px;
            transition: all 0.3s ease;
        }
        .add-part-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .part-icon {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .preview-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            padding: 20px;
            color: white;
            direction: ltr;
        }
        #format-preview {
            color: white !important;
        }
    </style>
@endsection

@section('page-scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    var token = localStorage.getItem('organization_token');
    var formatParts = [];
    var formatSeparators = [];
    var customText = '';
    var sortableInstance = null;
    
    // Part configurations
    var partConfigs = {
        'increment': {
            label: 'عدد افزایشی',
            icon: 'fa-sort-numeric-up',
            badgeClass: 'badge-primary',
            description: 'شماره ترتیبی قرارداد (شروع از 1)'
        },
        'day': {
            label: 'روز',
            icon: 'fa-calendar-day',
            badgeClass: 'badge-info',
            description: 'روز جاری ماه (مثال: 22)'
        },
        'month': {
            label: 'ماه',
            icon: 'fa-calendar-alt',
            badgeClass: 'badge-success',
            description: 'نام ماه جاری (مثال: آبان)'
        },
        'text': {
            label: 'متن',
            icon: 'fa-font',
            badgeClass: 'badge-warning',
            description: 'متن سفارشی شما'
        }
    };
    
    // Load current organization settings
    function loadSettings() {
        if (!token) return;
        
        $.ajax({
            url: '/api/organization/organization',
            type: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token
            },
            success: function(response) {
                if (response.data) {
                    var org = response.data;
                    if (org.contract_number_format && org.contract_number_format.parts) {
                        formatParts = org.contract_number_format.parts;
                        formatSeparators = org.contract_number_format.separators || [];
                        customText = org.contract_number_format.custom_text || '';
                        
                        // Render existing format
                        renderFormat();
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading settings:', xhr);
            }
        });
    }
    
    // Render format parts
    function renderFormat() {
        var container = $('#format-parts-container');
        var emptyState = $('#empty-state');
        
        // Hide empty state if there are parts
        if (formatParts.length > 0) {
            emptyState.hide();
        } else {
            emptyState.show();
        }
        
        container.empty();
        
        formatParts.forEach(function(part, index) {
            var config = partConfigs[part];
            if (!config) return;
            
            var partHtml = '<div class="format-part-item d-flex align-items-center" data-index="' + index + '" data-type="' + part + '">';
            partHtml += '<div class="d-flex align-items-center flex-grow-1">';
            partHtml += '<i class="fa ' + config.icon + ' mr-2" style="font-size: 20px;"></i>';
            partHtml += '<span class="badge ' + config.badgeClass + ' p-2 mr-2" style="font-size: 14px;">' + config.label + '</span>';
            partHtml += '</div>';
            
            if (index < formatParts.length - 1) {
                var separator = formatSeparators[index] || '/';
                partHtml += '<select class="form-control form-control-sm separator-select mx-2" data-index="' + index + '" title="جداکننده">';
                partHtml += '<option value="/"' + (separator === '/' ? ' selected' : '') + '>/</option>';
                partHtml += '<option value="-"' + (separator === '-' ? ' selected' : '') + '>-</option>';
                partHtml += '</select>';
            }
            
            partHtml += '<button type="button" class="btn btn-sm btn-danger remove-part-btn" data-index="' + index + '" title="حذف">';
            partHtml += '<i class="fa fa-times"></i>';
            partHtml += '</button>';
            partHtml += '</div>';
            
            container.append(partHtml);
        });
        
        // Initialize sortable
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
        
        if (formatParts.length > 0 && typeof Sortable !== 'undefined') {
            sortableInstance = new Sortable(container[0], {
                animation: 150,
                handle: '.format-part-item',
                ghostClass: 'dragging',
                onEnd: function(evt) {
                    // Update arrays based on new order
                    var oldIndex = evt.oldIndex;
                    var newIndex = evt.newIndex;
                    
                    // Move part
                    var part = formatParts.splice(oldIndex, 1)[0];
                    formatParts.splice(newIndex, 0, part);
                    
                    // Move separator
                    if (formatSeparators.length > 0) {
                        if (oldIndex < formatSeparators.length) {
                            var sep = formatSeparators.splice(oldIndex, 1)[0];
                            if (newIndex <= formatSeparators.length) {
                                formatSeparators.splice(newIndex, 0, sep);
                            } else {
                                formatSeparators.push(sep);
                            }
                        }
                    }
                    
                    // Re-render to update indices
                    renderFormat();
                }
            });
        }
        
        // Show custom text input if text part exists
        if (formatParts.includes('text')) {
            $('#custom-text-group').slideDown();
            $('#custom-text-input').val(customText);
        } else {
            $('#custom-text-group').slideUp();
        }
        
        updatePreview();
    }
    
    // Update preview
    function updatePreview() {
        var preview = '';
        var jalaliDate = new Date();
        var day = jalaliDate.getDate();
        var monthNames = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
        var month = monthNames[jalaliDate.getMonth()];
        
        if (formatParts.length === 0) {
            $('#format-preview').html('<span class="text-muted">هیچ قالبی تعریف نشده است</span>');
            return;
        }
        
        formatParts.forEach(function(part, index) {
            if (index > 0 && formatSeparators[index - 1]) {
                preview += '<span class="separator-preview">' + formatSeparators[index - 1] + '</span>';
            }
            
            switch(part) {
                case 'increment':
                    preview += '<span class="part-preview">1</span>';
                    break;
                case 'day':
                    preview += '<span class="part-preview">' + day + '</span>';
                    break;
                case 'month':
                    preview += '<span class="part-preview">' + month + '</span>';
                    break;
                case 'text':
                    preview += '<span class="part-preview">' + (customText || 'متن') + '</span>';
                    break;
            }
        });
        
        $('#format-preview').html(preview);
    }
    
    // Add format part
    function addFormatPart(type) {
        formatParts.push(type);
        if (formatParts.length > 1) {
            formatSeparators.push('/');
        }
        renderFormat();
        
        // Show feedback
        var config = partConfigs[type];
        if (config) {
            // Scroll to the new part
            setTimeout(function() {
                $('.format-part-item[data-type="' + type + '"]').last().css({
                    'animation': 'pulse 0.5s'
                });
            }, 100);
        }
    }
    
    // Remove format part
    function removeFormatPart(index) {
        swal({
            title: 'آیا مطمئن هستید؟',
            text: 'این بخش از قالب حذف خواهد شد',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'بله، حذف کن',
            cancelButtonText: 'انصراف',
            confirmButtonColor: '#d33',
            padding: '2em'
        }).then(function(result) {
            if (result.value) {
                formatParts.splice(index, 1);
                if (index > 0 && index <= formatSeparators.length) {
                    formatSeparators.splice(index - 1, 1);
                } else if (formatSeparators.length > 0) {
                    formatSeparators.shift();
                }
                renderFormat();
            }
        });
    }
    
    // Reset format
    function resetFormat() {
        swal({
            title: 'آیا مطمئن هستید؟',
            text: 'تمام قالب فعلی پاک خواهد شد',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'بله، پاک کن',
            cancelButtonText: 'انصراف',
            confirmButtonColor: '#d33',
            padding: '2em'
        }).then(function(result) {
            if (result.value) {
                formatParts = [];
                formatSeparators = [];
                customText = '';
                $('#custom-text-input').val('');
                renderFormat();
            }
        });
    }
    
    // Event handlers
    $(document).on('click', '.add-part-btn', function() {
        var type = $(this).data('type');
        addFormatPart(type);
    });
    
    $(document).on('click', '.remove-part-btn', function(e) {
        e.stopPropagation();
        var index = $(this).data('index');
        removeFormatPart(index);
    });
    
    $(document).on('change', '.separator-select', function() {
        var index = $(this).data('index');
        formatSeparators[index] = $(this).val();
        updatePreview();
    });
    
    $('#custom-text-input').on('input', function() {
        customText = $(this).val();
        updatePreview();
    });
    
    $('#reset-format-btn').on('click', function() {
        resetFormat();
    });
    
    // Save contract format
    $('#save-contract-format-btn').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.html();
        
        if (formatParts.length === 0) {
            swal({
                title: 'خطا',
                text: 'لطفاً حداقل یک بخش به قالب اضافه کنید',
                type: 'error',
                padding: '2em'
            });
            return;
        }
        
        if (formatParts.includes('text') && !customText) {
            swal({
                title: 'خطا',
                text: 'لطفاً متن سفارشی را وارد کنید',
                type: 'error',
                padding: '2em'
            });
            return;
        }
        
        // Disable button
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> در حال ذخیره...');
        
        var formatData = {
            contract_number_format: {
                parts: formatParts,
                separators: formatSeparators,
                custom_text: customText
            }
        };
        
        $.ajax({
            url: '/api/organization/settings/contract',
            type: 'POST',
            data: JSON.stringify(formatData),
            headers: {
                'Authorization': 'Bearer ' + token,
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            success: function(response) {
                $btn.prop('disabled', false).html(originalText);
                swal({
                    title: 'موفقیت',
                    text: response.message || 'قالب شماره فاکتور با موفقیت ذخیره شد',
                    type: 'success',
                    padding: '2em',
                    timer: 2000
                });
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalText);
                var errorMessage = 'خطا در ذخیره قالب';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                swal({
                    title: 'خطا',
                    text: errorMessage,
                    type: 'error',
                    padding: '2em'
                });
            }
        });
    });
    
    // Add CSS for preview
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .part-preview {
                background: rgba(255,255,255,0.2);
                padding: 5px 10px;
                border-radius: 4px;
                margin: 0 2px;
                display: inline-block;
            }
            .separator-preview {
                margin: 0 5px;
                font-weight: bold;
            }
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
        `)
        .appendTo('head');
    
    // Load settings on page load
    loadSettings();
});
</script>
@endsection

