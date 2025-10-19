<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OrganizationUser;
use App\Models\Organization;
use App\Models\Moderator;

class OrganizationUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first moderator to assign users to
        $moderator = Moderator::first();
        
        if (!$moderator) {
            $this->command->warn('No moderator found. Please create a moderator first.');
            return;
        }

        // Get organizations
        $organizations = Organization::all();
        
        if ($organizations->isEmpty()) {
            $this->command->warn('No organizations found. Please create organizations first.');
            return;
        }

        $users = [
            [
                'name' => 'احمد محمدی',
                'phone_number' => '09123456789',
                'username' => 'ahmad_mohammadi',
                'password' => 'password123',
                'status' => true,
            ],
            [
                'name' => 'فاطمه احمدی',
                'phone_number' => '09987654321',
                'username' => 'fateme_ahmadi',
                'password' => 'password123',
                'status' => true,
            ],
            [
                'name' => 'علی رضایی',
                'phone_number' => '09111111111',
                'username' => null,
                'password' => null,
                'status' => false,
            ],
            [
                'name' => 'زهرا کریمی',
                'phone_number' => '09222222222',
                'username' => 'zahra_karimi',
                'password' => 'password123',
                'status' => true,
            ],
            [
                'name' => 'محمد حسینی',
                'phone_number' => '09333333333',
                'username' => null,
                'password' => null,
                'status' => true,
            ],
        ];

        foreach ($organizations as $organization) {
            // Add 2-3 users to each organization
            $randomUsers = collect($users)->random(rand(2, 3));
            
            foreach ($randomUsers as $userData) {
                $userData['organization_id'] = $organization->id;
                $userData['moderator_id'] = $moderator->id;
                
                OrganizationUser::create($userData);
            }
        }

        $this->command->info('Organization users seeded successfully!');
    }
}