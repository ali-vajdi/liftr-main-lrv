<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\Moderator;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first moderator
        $moderator = Moderator::first();
        
        if (!$moderator) {
            $this->command->warn('No moderator found. Please run ModeratorSeeder first.');
            return;
        }

        $packages = [
            [
                'name' => 'پکیج یک روزه',
                'duration_days' => 1,
                'duration_label' => '1 روز',
                'price' => 10000,
                'is_public' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'پکیج یک هفته',
                'duration_days' => 7,
                'duration_label' => '1 هفته',
                'price' => 50000,
                'is_public' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'پکیج 15 روزه',
                'duration_days' => 15,
                'duration_label' => '15 روز',
                'price' => 80000,
                'is_public' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'پکیج یک ماهه',
                'duration_days' => 30,
                'duration_label' => '1 ماه',
                'price' => 150000,
                'is_public' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'پکیج سه ماهه',
                'duration_days' => 90,
                'duration_label' => '3 ماه',
                'price' => 400000,
                'is_public' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'پکیج شش ماهه',
                'duration_days' => 180,
                'duration_label' => '6 ماه',
                'price' => 700000,
                'is_public' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'پکیج یک ساله',
                'duration_days' => 365,
                'duration_label' => '1 سال',
                'price' => 1200000,
                'is_public' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'پکیج دو ساله',
                'duration_days' => 730,
                'duration_label' => '2 سال',
                'price' => 2000000,
                'is_public' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'پکیج آزمایشی',
                'duration_days' => 3,
                'duration_label' => '3 روز',
                'price' => 0,
                'is_public' => false,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'پکیج ویژه',
                'duration_days' => 60,
                'duration_label' => '2 ماه',
                'price' => 300000,
                'is_public' => false,
                'moderator_id' => $moderator->id,
            ],
        ];

        foreach ($packages as $packageData) {
            Package::create($packageData);
        }

        $this->command->info('Packages seeded successfully!');
    }
}