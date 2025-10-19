<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Moderator;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first moderator to assign organizations to
        $moderator = Moderator::first();
        
        if (!$moderator) {
            $this->command->warn('No moderator found. Please create a moderator first.');
            return;
        }

        $organizations = [
            [
                'name' => 'شرکت فناوری اطلاعات پارس',
                'address' => 'تهران، خیابان ولیعصر، پلاک 123',
                'logo' => null, // No logo initially
                'status' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'مجموعه تجاری آریا',
                'address' => 'اصفهان، خیابان چهارباغ، پلاک 456',
                'logo' => null, // No logo initially
                'status' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'شرکت تولیدی کیمیا',
                'address' => 'شیراز، خیابان زند، پلاک 789',
                'logo' => null, // No logo initially
                'status' => false,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'موسسه آموزشی دانش',
                'address' => 'مشهد، خیابان امام رضا، پلاک 321',
                'logo' => null, // No logo initially
                'status' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'شرکت خدمات مشاوره',
                'address' => 'تبریز، خیابان آزادی، پلاک 654',
                'logo' => null, // No logo initially
                'status' => true,
                'moderator_id' => $moderator->id,
            ],
        ];

        foreach ($organizations as $organization) {
            Organization::create($organization);
        }

        $this->command->info('Organizations seeded successfully!');
    }
}