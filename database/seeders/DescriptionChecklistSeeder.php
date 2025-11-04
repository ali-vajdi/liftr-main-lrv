<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DescriptionChecklist;
use App\Models\Moderator;

class DescriptionChecklistSeeder extends Seeder
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
            ['title' => 'ups کار نمیکند', 'order' => 1],
            ['title' => 'میکرو سوییچ گاورنر - پاراشوت کار نمیکند', 'order' => 2],
            ['title' => 'روغن موتور باید تعوض گردد', 'order' => 3],
            ['title' => 'حد بالا پایین در مدار نیست', 'order' => 4],
            ['title' => 'استپ ته چاله کار نمیکند', 'order' => 5],
            ['title' => 'فلکه موتور – هرز گرد خوردگی دارد', 'order' => 6],
            ['title' => 'روغندان ها باید تعویض گردد', 'order' => 7],
            ['title' => 'سیم بکسل ها خوردگی دارند', 'order' => 8],
            ['title' => 'روغن نمره 10 خریداری شود', 'order' => 9],
            ['title' => 'کفشک ها باید تعویض گردد', 'order' => 10],
            ['title' => 'لنت کفشک ها باید تعویض گردد', 'order' => 11],
            ['title' => 'درب کابین در مدار نمیباشد', 'order' => 12],
            ['title' => 'دیکتاتور درب طبقه باید تعویض گردد', 'order' => 13],
            ['title' => 'شیشه-دستگره درب شکسته است', 'order' => 14],
            ['title' => 'هالوژن ها – لامپ داخل کابیت باید تعویض گردد', 'order' => 15],
            ['title' => 'فن کابین کار نمیکند', 'order' => 16],
            ['title' => 'آلارم کابین کار نمیکند', 'order' => 17],
            ['title' => 'استپ روی کابین کار نمیکند', 'order' => 18],
            ['title' => 'کابین – کادر وزنه با فر ندارد', 'order' => 19],
            ['title' => 'پایه با فرها مستحکم نیستند', 'order' => 20],
            ['title' => 'جعبه ریویزیون به درستی کار نمیکند', 'order' => 21],
            ['title' => 'موتور خانه نیاز به نظافت دارد', 'order' => 22],
            ['title' => 'ته چاله باید نظافت گردد', 'order' => 23],
            ['title' => 'روی کابین نیاز به نظافت دارد', 'order' => 24],
            ['title' => 'سیم بکسل ها نیاز به روغن کاری دارد', 'order' => 25],
        ];

        foreach ($checklists as $checklistData) {
            DescriptionChecklist::updateOrCreate(
                [
                    'title' => $checklistData['title'],
                ],
                [
                    'order' => $checklistData['order'],
                    'moderator_id' => $moderator->id,
                ]
            );
        }

        $this->command->info('Description checklists seeded successfully!');
    }
}
