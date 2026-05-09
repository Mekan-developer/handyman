<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'Ашхабад',
            'Туркменабат',
            'Дашогуз',
            'Мары',
            'Балканабат',
            'Туркменбаши',
        ];

        foreach ($cities as $name) {
            City::updateOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
