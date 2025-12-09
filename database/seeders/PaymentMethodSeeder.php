<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentMethod::firstOrCreate(
            ['code' => 'system'],
            [
                'name' => 'پرداخت سیستمی',
                'description' => 'پرداخت‌های ثبت شده توسط سیستم',
                'is_active' => true,
                'is_system' => true,
            ]
        );

        // Zarinpal Main Payment Gateway
        PaymentMethod::firstOrCreate(
            ['code' => 'zarinpal'],
            [
                'name' => 'زرین‌پال',
                'description' => 'درگاه پرداخت زرین‌پال',
                'is_active' => true,
                'is_system' => false,
                'config' => [
                    'merchant_id' => '12de1ed3-0c38-4d52-add9-7e631e430214',
                    'base_url' => 'https://payment.zarinpal.com',
                    'is_sandbox' => false,
                ],
            ]
        );

        // Zarinpal Sandbox Payment Gateway
        PaymentMethod::firstOrCreate(
            ['code' => 'zarinpal_sandbox'],
            [
                'name' => 'سندباکس زرین‌پال',
                'description' => 'درگاه پرداخت سندباکس زرین‌پال (تست)',
                'is_active' => true,
                'is_system' => false,
                'config' => [
                    'merchant_id' => '12de1ed3-0c38-4d52-add9-7e631e430214',
                    'base_url' => 'https://sandbox.zarinpal.com',
                    'is_sandbox' => true,
                ],
            ]
        );
    }
}
