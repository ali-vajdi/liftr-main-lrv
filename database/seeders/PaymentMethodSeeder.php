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
    }
}
