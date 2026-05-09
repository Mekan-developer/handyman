<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Сантехника' => [
                'Ремонт кранов',
                'Установка унитаза',
                'Прочистка труб',
                'Замена смесителя',
            ],
            'Электрика' => [
                'Замена розеток',
                'Установка люстры',
                'Электромонтаж',
                'Поиск короткого замыкания',
            ],
            'Бытовая техника' => [
                'Ремонт холодильника',
                'Ремонт стиральной машины',
                'Ремонт кондиционера',
            ],
            'Уборка' => [
                'Генеральная уборка',
                'Мойка окон',
                'Химчистка мебели',
            ],
            'Мебель' => [
                'Сборка мебели',
                'Перетяжка дивана',
            ],
        ];

        foreach ($tree as $parentName => $children) {
            $parent = Category::updateOrCreate(
                ['name' => $parentName, 'parent_id' => null],
                ['is_active' => true]
            );

            foreach ($children as $childName) {
                Category::updateOrCreate(
                    ['name' => $childName, 'parent_id' => $parent->id],
                    ['is_active' => true]
                );
            }
        }
    }
}
