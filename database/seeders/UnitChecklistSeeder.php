<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UnitChecklist;
use App\Models\Moderator;

class UnitChecklistSeeder extends Seeder
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

        $checklists = [
            ['title' => 'عملکرد تابلوی فرمان(سری فرمان،مدارفرمان تست (UPS', 'order' => 1],
            ['title' => 'عملکرد فک ترمز، فن موتور لنت ترمز روغن موتور', 'order' => 2],
            ['title' => 'عملکرد سیستم ترمز اضطراری (گاونر بالا پایین – پاراشوت)', 'order' => 3],
            ['title' => 'برسی سوییچ ها و سری استپ در چاله', 'order' => 4],
            ['title' => 'برسی کشش سیم بکسل ها سر بکسل ها', 'order' => 5],
            ['title' => 'برسی خوردگی یا سرخوردگی فلکه هت', 'order' => 6],
            ['title' => 'چک کردن روغن و عملکرد روغن دامنها', 'order' => 7],
            ['title' => 'برسی شرایط کفشک ها و لنت کفشک ها', 'order' => 8],
            ['title' => 'عملکرد درب کابین', 'order' => 9],
            ['title' => 'عملکرد درب طبقات(قفل دو شاخ – دیکتاتورها – شیشه و دستگیره)', 'order' => 10],
            ['title' => 'برسی روشنایی کابین و شستی های کابین و طبقه', 'order' => 11],
            ['title' => 'برسی متعلقات کابین ( فن،الرم)', 'order' => 12],
            ['title' => 'برسی عملکرد جعبه ریویزیون', 'order' => 13],
            ['title' => 'برسی لول طبقات', 'order' => 14],
            ['title' => 'برسی استحکام با ضه ها', 'order' => 15],
            ['title' => 'برسی نظافت کلی روی کابین د اخل موتور خانه و ته چاله اسانسور', 'order' => 16],
        ];

        foreach ($checklists as $checklistData) {
            UnitChecklist::updateOrCreate(
                [
                    'title' => $checklistData['title'],
                ],
                [
                    'order' => $checklistData['order'],
                    'moderator_id' => $moderator->id,
                ]
            );
        }

        $this->command->info('Unit checklists seeded successfully!');
    }
}
