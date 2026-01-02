<?php

namespace Database\Seeders;

use App\Models\Moderator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ModeratorSeeder extends Seeder
{
    public function run(): void
    {
        Moderator::firstOrCreate(
            ['username' => 'ali13076'],
            [
                'full_name' => 'Admin User',
                'password' => Hash::make('ali130761171@'),
            ]
        );
    }
} 