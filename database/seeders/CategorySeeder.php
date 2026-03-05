<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name_fr' => 'Boissons', 'name_ar' => 'مشروبات', 'name_en' => 'Beverages'],
            ['name_fr' => 'Produits laitiers', 'name_ar' => 'منتجات الحليب', 'name_en' => 'Dairy Products'],
            ['name_fr' => 'Conserves', 'name_ar' => 'معلبات', 'name_en' => 'Canned Goods'],
            ['name_fr' => 'Céréales et Pâtes', 'name_ar' => 'حبوب ومعكرونة', 'name_en' => 'Cereals & Pasta'],
            ['name_fr' => 'Huiles et Condiments', 'name_ar' => 'زيوت وتوابل', 'name_en' => 'Oils & Condiments'],
            ['name_fr' => 'Biscuits et Confiserie', 'name_ar' => 'بسكويت وحلويات', 'name_en' => 'Biscuits & Confectionery'],
            ['name_fr' => 'Nettoyage et Hygiène', 'name_ar' => 'تنظيف ونظافة', 'name_en' => 'Cleaning & Hygiene'],
            ['name_fr' => 'Fruits et Légumes', 'name_ar' => 'فواكه وخضروات', 'name_en' => 'Fruits & Vegetables'],
            ['name_fr' => 'Viandes et Charcuterie', 'name_ar' => 'لحوم ومقانق', 'name_en' => 'Meats & Deli'],
            ['name_fr' => 'Épicerie fine', 'name_ar' => 'بقالة فاخرة', 'name_en' => 'Gourmet Grocery'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => Str::slug($cat['name_fr'])],
                array_merge($cat, [
                    'slug' => Str::slug($cat['name_fr']),
                    'is_active' => true,
                ])
            );
        }
    }
}
