<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\City;
use App\Models\Province;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get provinces for mapping
        $provinces = Province::all()->keyBy('name');
        
        $cities = [
            // Province 1: آذربایجان شرقی
            ['province' => 'آذربایجان شرقی', 'name' => ' آذرشهر', 'name_en' => 'Azarshahr', 'latitude' => '37.75888900', 'longitude' => '45.97833300'],
            ['province' => 'آذربایجان شرقی', 'name' => ' اسکو', 'name_en' => 'Osku', 'latitude' => '37.91583300', 'longitude' => '46.12361100'],
            ['province' => 'آذربایجان شرقی', 'name' => ' اهر', 'name_en' => 'Ahar', 'latitude' => '38.48943050', 'longitude' => '47.06835750'],
            ['province' => 'آذربایجان شرقی', 'name' => ' بستان آباد', 'name_en' => 'Bostanabad', 'latitude' => '37.85000000', 'longitude' => '46.83333300'],
            ['province' => 'آذربایجان شرقی', 'name' => ' بناب', 'name_en' => 'Bonab', 'latitude' => '37.34027800', 'longitude' => '46.05611100'],
            ['province' => 'آذربایجان شرقی', 'name' => ' تبریز', 'name_en' => 'Tabriz', 'latitude' => '38.06666700', 'longitude' => '46.30000000'],
            ['province' => 'آذربایجان شرقی', 'name' => ' جلفا', 'name_en' => 'Jolfa', 'latitude' => '38.94027800', 'longitude' => '45.63083300'],
            ['province' => 'آذربایجان شرقی', 'name' => ' چار اویماق', 'name_en' => 'Charuymaq', 'latitude' => '37.12990520', 'longitude' => '47.02456860'],
            ['province' => 'آذربایجان شرقی', 'name' => ' سراب', 'name_en' => 'Sarab', 'latitude' => '37.94083300', 'longitude' => '47.53666700'],
            ['province' => 'آذربایجان شرقی', 'name' => ' شبستر', 'name_en' => 'Shabestar', 'latitude' => '38.18027800', 'longitude' => '45.70277800'],
            ['province' => 'آذربایجان شرقی', 'name' => ' عجبشیر', 'name_en' => 'Ajab Shir', 'latitude' => '37.47750000', 'longitude' => '45.89416700'],
            ['province' => 'آذربایجان شرقی', 'name' => ' کلیبر', 'name_en' => 'Kaleybar', 'latitude' => '38.86944400', 'longitude' => '47.03555600'],
            ['province' => 'آذربایجان شرقی', 'name' => ' مراغه', 'name_en' => 'Maragheh', 'latitude' => '37.38916700', 'longitude' => '46.23750000'],
            ['province' => 'آذربایجان شرقی', 'name' => ' مرند', 'name_en' => 'Marand', 'latitude' => '38.42511700', 'longitude' => '45.76963600'],
            ['province' => 'آذربایجان شرقی', 'name' => ' ملکان', 'name_en' => 'Malekan', 'latitude' => '37.14562500', 'longitude' => '46.16852420'],
            ['province' => 'آذربایجان شرقی', 'name' => ' میانه', 'name_en' => 'Mianeh', 'latitude' => '37.42111100', 'longitude' => '47.71500000'],
            ['province' => 'آذربایجان شرقی', 'name' => ' ورزقان', 'name_en' => 'Varzaqan', 'latitude' => '38.50972200', 'longitude' => '46.65444400'],
            ['province' => 'آذربایجان شرقی', 'name' => ' هریس', 'name_en' => 'Heris', 'latitude' => '29.77518250', 'longitude' => '-95.31025050'],
            ['province' => 'آذربایجان شرقی', 'name' => 'هشترود', 'name_en' => 'Hashtrud', 'latitude' => '37.47777800', 'longitude' => '47.05083300'],
            
            // Province 2: آذربایجان غربی
            ['province' => 'آذربایجان غربی', 'name' => ' ارومیه', 'name_en' => 'Urmia', 'latitude' => '37.55527800', 'longitude' => '45.07250000'],
            ['province' => 'آذربایجان غربی', 'name' => ' اشنویه', 'name_en' => 'Oshnavieh', 'latitude' => '37.03972200', 'longitude' => '45.09833300'],
            ['province' => 'آذربایجان غربی', 'name' => ' بوکان', 'name_en' => 'Bukan', 'latitude' => '36.52111100', 'longitude' => '46.20888900'],
            ['province' => 'آذربایجان غربی', 'name' => ' پیرانشهر', 'name_en' => 'Piranshahr', 'latitude' => '36.69444400', 'longitude' => '45.14166700'],
            ['province' => 'آذربایجان غربی', 'name' => ' تکاب', 'name_en' => 'Takab', 'latitude' => '36.40083300', 'longitude' => '47.11333300'],
            ['province' => 'آذربایجان غربی', 'name' => ' چالدران', 'name_en' => 'Chaldoran', 'latitude' => '39.06498370', 'longitude' => '44.38446790'],
            ['province' => 'آذربایجان غربی', 'name' => ' خوی', 'name_en' => 'Khoy', 'latitude' => '38.55027800', 'longitude' => '44.95222200'],
            ['province' => 'آذربایجان غربی', 'name' => ' سردشت', 'name_en' => 'Sardasht', 'latitude' => '36.15527800', 'longitude' => '45.47888900'],
            ['province' => 'آذربایجان غربی', 'name' => ' سلماس', 'name_en' => 'Salmas', 'latitude' => '38.19722200', 'longitude' => '44.76527800'],
            ['province' => 'آذربایجان غربی', 'name' => ' شاهین دژ', 'name_en' => 'Shahin Dezh', 'latitude' => '36.67916700', 'longitude' => '46.56694400'],
            ['province' => 'آذربایجان غربی', 'name' => ' ماکو', 'name_en' => 'Maku', 'latitude' => '39.29527800', 'longitude' => '44.51666700'],
            ['province' => 'آذربایجان غربی', 'name' => ' مهاباد', 'name_en' => 'Mahabad', 'latitude' => '36.76305600', 'longitude' => '45.72222200'],
            ['province' => 'آذربایجان غربی', 'name' => ' میاندوآب', 'name_en' => 'Miandoab', 'latitude' => '36.96944400', 'longitude' => '46.10277800'],
            ['province' => 'آذربایجان غربی', 'name' => ' نقده', 'name_en' => 'Naqadeh', 'latitude' => '36.95527800', 'longitude' => '45.38805600'],
            
            // Province 3: اردبیل
            ['province' => 'اردبیل', 'name' => ' اردبیل', 'name_en' => 'Ardabil', 'latitude' => '38.48532760', 'longitude' => '47.89112090'],
            ['province' => 'اردبیل', 'name' => ' بیله سوار', 'name_en' => 'Bileh Savar', 'latitude' => '39.35677750', 'longitude' => '47.94907650'],
            ['province' => 'اردبیل', 'name' => ' پارس آباد', 'name_en' => 'Parsabad', 'latitude' => '39.64833300', 'longitude' => '47.91750000'],
            ['province' => 'اردبیل', 'name' => ' خلخال', 'name_en' => 'Khalkhal', 'latitude' => '37.61888900', 'longitude' => '48.52583300'],
            ['province' => 'اردبیل', 'name' => ' کوثر', 'name_en' => 'Kowsar', 'latitude' => '31.86768660', 'longitude' => '54.33798020'],
            ['province' => 'اردبیل', 'name' => ' گرمی', 'name_en' => 'Germi', 'latitude' => '39.03722670', 'longitude' => '47.92770210'],
            ['province' => 'اردبیل', 'name' => ' مشگین', 'name_en' => 'Meshginshahr', 'latitude' => '38.39888900', 'longitude' => '47.68194400'],
            ['province' => 'اردبیل', 'name' => ' نمین', 'name_en' => 'Namin', 'latitude' => '38.42694400', 'longitude' => '48.48388900'],
            ['province' => 'اردبیل', 'name' => ' نیر', 'name_en' => 'Nir', 'latitude' => '38.03472200', 'longitude' => '47.99861100'],
            
            // Province 8: تهران (most important cities)
            ['province' => 'تهران', 'name' => ' اسلام شهر', 'name_en' => 'Eslamshahr', 'latitude' => '35.54458050', 'longitude' => '51.23024570'],
            ['province' => 'تهران', 'name' => ' پاکدشت', 'name_en' => 'Pakdasht', 'latitude' => '35.46689130', 'longitude' => '51.68606250'],
            ['province' => 'تهران', 'name' => ' تهران', 'name_en' => 'Tehran', 'latitude' => '35.69611100', 'longitude' => '51.42305600'],
            ['province' => 'تهران', 'name' => ' دماوند', 'name_en' => 'Damavand', 'latitude' => '35.94674940', 'longitude' => '52.12754810'],
            ['province' => 'تهران', 'name' => ' رباط کریم', 'name_en' => 'Robat Karim', 'latitude' => '35.48472200', 'longitude' => '51.08277800'],
            ['province' => 'تهران', 'name' => ' ری', 'name_en' => 'Rey', 'latitude' => '35.57733200', 'longitude' => '51.46276200'],
            ['province' => 'تهران', 'name' => ' شمیرانات', 'name_en' => 'Shemiranat', 'latitude' => '35.95480210', 'longitude' => '51.59916430'],
            ['province' => 'تهران', 'name' => ' شهریار', 'name_en' => 'Shahriar', 'latitude' => '35.65972200', 'longitude' => '51.05916700'],
            ['province' => 'تهران', 'name' => ' فیروزکوه', 'name_en' => 'Firuzkuh', 'latitude' => '35.43867100', 'longitude' => '60.80938700'],
            ['province' => 'تهران', 'name' => ' ورامین', 'name_en' => 'Varamin', 'latitude' => '35.32524070', 'longitude' => '51.64719870'],
        ];

        foreach ($cities as $cityData) {
            $province = $provinces->get($cityData['province']);
            if ($province) {
                City::firstOrCreate(
                    [
                        'province_id' => $province->id,
                        'name' => $cityData['name']
                    ],
                    [
                        'province_id' => $province->id,
                        'name' => $cityData['name'],
                        'name_en' => $cityData['name_en'],
                        'latitude' => $cityData['latitude'],
                        'longitude' => $cityData['longitude'],
                    ]
                );
            }
        }
    }
}
