<?php

namespace App\Services;

class SmsPattern
{
    /**
     * All SMS patterns
     * Each pattern has a code, text with placeholders, and description
     */
    private static array $patterns = [
        'otp_verification' => [
            'code' => 'otp_verification',
            'text' => 'کد تایید شما: {otp_code}',
            'description' => 'کد تایید ورود',
            'required_fields' => ['otp_code'],
        ],
        'service_assigned' => [
            'code' => 'service_assigned',
            'text' => 'سرویس شما برای تاریخ {service_date} به تکنسین {technician_name} اختصاص داده شد. شماره تماس: {technician_phone}',
            'description' => 'اختصاص سرویس به تکنسین',
            'required_fields' => ['service_date', 'technician_name', 'technician_phone'],
        ],
        'service_completed' => [
            'code' => 'service_completed',
            'text' => 'سرویس شما برای تاریخ {service_date} تکمیل شد. برای مشاهده جزئیات به پنل مراجعه کنید.',
            'description' => 'تکمیل سرویس',
            'required_fields' => ['service_date'],
        ],
        'service_reminder' => [
            'code' => 'service_reminder',
            'text' => 'یادآوری: سرویس شما برای تاریخ {service_date} در ساختمان {building_name} تعیین شده است.',
            'description' => 'یادآوری سرویس',
            'required_fields' => ['service_date', 'building_name'],
        ],
        'package_expiring' => [
            'code' => 'package_expiring',
            'text' => 'هشدار: بسته شما در تاریخ {expire_date} منقضی می‌شود. لطفا نسبت به تمدید اقدام کنید.',
            'description' => 'هشدار انقضای بسته',
            'required_fields' => ['expire_date'],
        ],
        'package_expired' => [
            'code' => 'package_expired',
            'text' => 'بسته شما در تاریخ {expire_date} منقضی شده است. لطفا نسبت به تمدید اقدام کنید.',
            'description' => 'انقضای بسته',
            'required_fields' => ['expire_date'],
        ],
        'welcome' => [
            'code' => 'welcome',
            'text' => 'خوش آمدید {name}! به سیستم مدیریت سرویس خوش آمدید.',
            'description' => 'پیام خوش‌آمدگویی',
            'required_fields' => ['name'],
        ],
        'password_reset' => [
            'code' => 'password_reset',
            'text' => 'کد بازیابی رمز عبور شما: {reset_code}',
            'description' => 'بازیابی رمز عبور',
            'required_fields' => ['reset_code'],
        ],
        'notification' => [
            'code' => 'notification',
            'text' => '{message}',
            'description' => 'اعلان عمومی',
            'required_fields' => ['message'],
        ],
        'technician_welcome' => [
            'code' => '8lt442ze0rimu9k',
            'text' => 'تکنسین محترم به اپلیکیشن لیفتر خوش آمدید' . "\n\n" . 'کد ورود: {code}',
            'description' => 'خوش‌آمدگویی تکنسین با کد ورود',
            'required_fields' => ['code'],
        ],
        'organization_user_welcome' => [
            'code' => 'fpju29q6zt649o8',
            'text' => 'کاربر {user_name}' . "\n\n" . 'حساب کاربری شما در شرکت {organization_name} ایجاد گردید' . "\n\n" . 'رمز عبور: {password}' . "\n\n" . 'جهت ورود به آدرس زیر مراجعه نمایید' . "\n" . 'app.liftr.ir',
            'description' => 'خوش‌آمدگویی کاربر سازمان با اطلاعات ورود',
            'required_fields' => ['user_name', 'organization_name', 'password'],
        ],
        'technician_welcome_no_password' => [
            'code' => '1d5rhmbojz57qv6',
            'text' => 'تکنسین محترم آقای {technician_name}' . "\n\n" . 'حساب کاربری شما جهت ورود به اپلیکیشن لیفتر توسط شرکت {organization_name} ایجاد گردید.' . "\n\n" . 'جهت نصب اپلیکیشن به لینک زیر مراجعه نمایید.' . "\n" . 'liftr.ir',
            'description' => 'خوش‌آمدگویی تکنسین بدون رمز عبور',
            'required_fields' => ['technician_name', 'organization_name'],
        ],
        'technician_welcome_with_password' => [
            'code' => '7axbsjhz2d56edw',
            'text' => 'تکنسین محترم آقای {technician_name}' . "\n\n" . 'حساب کاربری شما جهت ورود به اپلیکیشن لیفتر توسط شرکت {organization_name} ایجاد گردید.' . "\n\n" . 'رمز عبور: {password}' . "\n\n" . 'جهت نصب اپلیکیشن به لینک زیر مراجعه نمایید.' . "\n" . 'liftr.ir',
            'description' => 'خوش‌آمدگویی تکنسین با رمز عبور',
            'required_fields' => ['technician_name', 'organization_name', 'password'],
        ],
        'technician_password_changed' => [
            'code' => 'zkugvxew60gopyn',
            'text' => 'تکنسین محترم آقای {technician_name}' . "\n\n" . 'رمز ورود شما جهت ورود به اپلیکیشن لیفتر توسط شرکت {organization_name} تغییر گردید' . "\n\n" . 'رمز عبور جدید: {password}',
            'description' => 'اعلام تغییر رمز عبور تکنسین',
            'required_fields' => ['technician_name', 'organization_name', 'password'],
        ],
        'building_manager_technician_assigned' => [
            'code' => 'nktfdutobk8zoe6',
            'text' => 'مدیر محترم ساختمان {building_name}' . "\n\n" . 'با سلام' . "\n\n" . 'به اطلاع می‌رساند نماینده شرکت در تاریخ {date_value} و بازه زمانی {time_periods_value} جهت انجام سرویس آسانسور به ساختمان مراجعه خواهد نمود.' . "\n\n" . 'آسانسور {organization_name}' . "\n\n" . 'ایرادات و اشکالات آسانسور را از طریق لینک زیر اعلام نمایید.' . "\n\n" . '{url_value}',
            'description' => 'اعلام اختصاص تکنسین به ساختمان',
            'required_fields' => ['building_name', 'date_value', 'time_periods_value', 'organization_name', 'url_value'],
        ],
    ];

    /**
     * Get pattern by code
     *
     * @param string $code
     * @return array|null
     */
    public static function getPattern(string $code): ?array
    {
        return self::$patterns[$code] ?? null;
    }

    /**
     * Get all patterns
     *
     * @return array
     */
    public static function getAllPatterns(): array
    {
        return self::$patterns;
    }

    /**
     * Check if pattern exists
     *
     * @param string $code
     * @return bool
     */
    public static function exists(string $code): bool
    {
        return isset(self::$patterns[$code]);
    }

    /**
     * Get pattern text
     *
     * @param string $code
     * @return string|null
     */
    public static function getPatternText(string $code): ?string
    {
        $pattern = self::getPattern($code);
        return $pattern['text'] ?? null;
    }

    /**
     * Get required fields for a pattern
     *
     * @param string $code
     * @return array
     */
    public static function getRequiredFields(string $code): array
    {
        $pattern = self::getPattern($code);
        return $pattern['required_fields'] ?? [];
    }
}

