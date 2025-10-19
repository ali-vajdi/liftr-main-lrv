<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Province;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            ['name' => 'آذربایجان شرقی', 'name_en' => 'East Azerbaijan', 'latitude' => '37.90357330', 'longitude' => '46.26821090'],
            ['name' => 'آذربایجان غربی', 'name_en' => 'West Azerbaijan', 'latitude' => '37.45500620', 'longitude' => '45.00000000'],
            ['name' => 'اردبیل', 'name_en' => 'Ardabil', 'latitude' => '38.48532760', 'longitude' => '47.89112090'],
            ['name' => 'اصفهان', 'name_en' => 'Isfahan', 'latitude' => '32.65462750', 'longitude' => '51.66798260'],
            ['name' => 'البرز', 'name_en' => 'Alborz', 'latitude' => '35.99604670', 'longitude' => '50.92892460'],
            ['name' => 'ایلام', 'name_en' => 'Ilam', 'latitude' => '33.29576180', 'longitude' => '46.67053400'],
            ['name' => 'بوشهر', 'name_en' => 'Bushehr', 'latitude' => '28.92338370', 'longitude' => '50.82031400'],
            ['name' => 'تهران', 'name_en' => 'Tehran', 'latitude' => '35.69611100', 'longitude' => '51.42305600'],
            ['name' => 'چهارمحال و بختیاری', 'name_en' => 'Chaharmahal and Bakhtiari', 'latitude' => '31.96143480', 'longitude' => '50.84563230'],
            ['name' => 'خراسان جنوبی', 'name_en' => 'South Khorasan', 'latitude' => '32.51756430', 'longitude' => '59.10417580'],
            ['name' => 'خراسان رضوی', 'name_en' => 'Razavi Khorasan', 'latitude' => '35.10202530', 'longitude' => '59.10417580'],
            ['name' => 'خراسان شمالی', 'name_en' => 'North Khorasan', 'latitude' => '37.47103530', 'longitude' => '57.10131880'],
            ['name' => 'خوزستان', 'name_en' => 'Khuzestan', 'latitude' => '31.43601490', 'longitude' => '49.04131200'],
            ['name' => 'زنجان', 'name_en' => 'Zanjan', 'latitude' => '36.50181850', 'longitude' => '48.39881860'],
            ['name' => 'سمنان', 'name_en' => 'Semnan', 'latitude' => '35.22555850', 'longitude' => '54.43421380'],
            ['name' => 'سیستان و بلوچستان', 'name_en' => 'Sistan and Baluchestan', 'latitude' => '27.52999060', 'longitude' => '60.58206760'],
            ['name' => 'فارس', 'name_en' => 'Fars', 'latitude' => '29.10438130', 'longitude' => '53.04589300'],
            ['name' => 'قزوین', 'name_en' => 'Qazvin', 'latitude' => '36.08813170', 'longitude' => '49.85472660'],
            ['name' => 'قم', 'name_en' => 'Qom', 'latitude' => '34.63994430', 'longitude' => '50.87594190'],
            ['name' => 'كردستان', 'name_en' => 'Kurdistan', 'latitude' => '35.95535790', 'longitude' => '47.13621250'],
            ['name' => 'كرمان', 'name_en' => 'Kerman', 'latitude' => '30.28393790', 'longitude' => '57.08336280'],
            ['name' => 'كرمانشاه', 'name_en' => 'Kermanshah', 'latitude' => '34.31416700', 'longitude' => '47.06500000'],
            ['name' => 'کهگیلویه و بویراحمد', 'name_en' => 'Kohgiluyeh and Boyer-Ahmad', 'latitude' => '30.65094790', 'longitude' => '51.60525000'],
            ['name' => 'گلستان', 'name_en' => 'Golestan', 'latitude' => '37.28981230', 'longitude' => '55.13758340'],
            ['name' => 'گیلان', 'name_en' => 'Gilan', 'latitude' => '37.11716170', 'longitude' => '49.52799960'],
            ['name' => 'لرستان', 'name_en' => 'Lorestan', 'latitude' => '33.58183940', 'longitude' => '48.39881860'],
            ['name' => 'مازندران', 'name_en' => 'Mazandaran', 'latitude' => '36.22623930', 'longitude' => '52.53186040'],
            ['name' => 'مركزی', 'name_en' => 'Markazi', 'latitude' => '33.50932940', 'longitude' => '-92.39611900'],
            ['name' => 'هرمزگان', 'name_en' => 'Hormozgan', 'latitude' => '27.13872300', 'longitude' => '55.13758340'],
            ['name' => 'همدان', 'name_en' => 'Hamadan', 'latitude' => '34.76079990', 'longitude' => '48.39881860'],
            ['name' => 'یزد', 'name_en' => 'Yazd', 'latitude' => '32.10063870', 'longitude' => '54.43421380'],
        ];

        foreach ($provinces as $province) {
            Province::firstOrCreate(
                ['name' => $province['name']],
                $province
            );
        }
    }
}
