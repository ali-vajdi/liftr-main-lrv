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
                'logo' => 'https://via.placeholder.com/100x100/007bff/ffffff?text=PIT',
                'status' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'مجموعه تجاری آریا',
                'address' => 'اصفهان، خیابان چهارباغ، پلاک 456',
                'logo' => 'https://via.placeholder.com/100x100/28a745/ffffff?text=آریا',
                'status' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'شرکت تولیدی کیمیا',
                'address' => 'شیراز، خیابان زند، پلاک 789',
                'logo' => 'https://via.placeholder.com/100x100/dc3545/ffffff?text=کیمیا',
                'status' => false,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'موسسه آموزشی دانش',
                'address' => 'مشهد، خیابان امام رضا، پلاک 321',
                'logo' => 'https://via.placeholder.com/100x100/ffc107/000000?text=دانش',
                'status' => true,
                'moderator_id' => $moderator->id,
            ],
            [
                'name' => 'شرکت خدمات مشاوره',
                'address' => 'تبریز، خیابان آزادی، پلاک 654',
                'logo' => 'https://via.placeholder.com/100x100/6f42c1/ffffff?text=مشاوره',
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