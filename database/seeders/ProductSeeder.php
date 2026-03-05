<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['cat' => 'boissons', 'name_fr' => 'Coca-Cola 1.5L', 'name_ar' => 'كوكا كولا 1.5 لتر', 'name_en' => 'Coca-Cola 1.5L', 'unit' => 'pack', 'price' => 42.00],
            ['cat' => 'boissons', 'name_fr' => 'Eau Sidi Ali 1.5L', 'name_ar' => 'ماء سيدي علي 1.5 لتر', 'name_en' => 'Sidi Ali Water 1.5L', 'unit' => 'pack', 'price' => 18.00],
            ['cat' => 'boissons', 'name_fr' => 'Jus Miami 1L', 'name_ar' => 'عصير ميامي 1 لتر', 'name_en' => 'Miami Juice 1L', 'unit' => 'box', 'price' => 36.00],
            ['cat' => 'produits-laitiers', 'name_fr' => 'Lait Centrale 1L', 'name_ar' => 'حليب سنطرال 1 لتر', 'name_en' => 'Centrale Milk 1L', 'unit' => 'pack', 'price' => 48.00],
            ['cat' => 'produits-laitiers', 'name_fr' => 'Yaourt Danone x12', 'name_ar' => 'زبادي دانون ×12', 'name_en' => 'Danone Yogurt x12', 'unit' => 'box', 'price' => 32.00],
            ['cat' => 'conserves', 'name_fr' => 'Tomate concentrée Le Jardin', 'name_ar' => 'معجون طماطم الحديقة', 'name_en' => 'Le Jardin Tomato Paste', 'unit' => 'box', 'price' => 55.00],
            ['cat' => 'conserves', 'name_fr' => 'Sardines Titus', 'name_ar' => 'سردين تيتوس', 'name_en' => 'Titus Sardines', 'unit' => 'box', 'price' => 120.00],
            ['cat' => 'cereales-et-pates', 'name_fr' => 'Couscous Dari 1kg', 'name_ar' => 'كسكس ضاري 1 كلغ', 'name_en' => 'Dari Couscous 1kg', 'unit' => 'bag', 'price' => 14.00],
            ['cat' => 'cereales-et-pates', 'name_fr' => 'Pâtes Tria 500g', 'name_ar' => 'معكرونة تريا 500غ', 'name_en' => 'Tria Pasta 500g', 'unit' => 'box', 'price' => 45.00],
            ['cat' => 'huiles-et-condiments', 'name_fr' => 'Huile Lesieur 5L', 'name_ar' => 'زيت لوزيور 5 لتر', 'name_en' => 'Lesieur Oil 5L', 'unit' => 'piece', 'price' => 85.00],
            ['cat' => 'huiles-et-condiments', 'name_fr' => 'Sucre 2kg', 'name_ar' => 'سكر 2 كلغ', 'name_en' => 'Sugar 2kg', 'unit' => 'bag', 'price' => 16.00],
            ['cat' => 'biscuits-et-confiserie', 'name_fr' => 'Biscuit Bimo', 'name_ar' => 'بسكويت بيمو', 'name_en' => 'Bimo Biscuit', 'unit' => 'box', 'price' => 38.00],
            ['cat' => 'nettoyage-et-hygiene', 'name_fr' => 'Javel Madar 1L', 'name_ar' => 'جافيل مادار 1 لتر', 'name_en' => 'Madar Bleach 1L', 'unit' => 'pack', 'price' => 24.00],
            ['cat' => 'nettoyage-et-hygiene', 'name_fr' => 'Savon Tide 3kg', 'name_ar' => 'صابون تايد 3 كلغ', 'name_en' => 'Tide Detergent 3kg', 'unit' => 'piece', 'price' => 65.00],
        ];

        foreach ($products as $p) {
            $category = Category::where('slug', $p['cat'])->first();
            if (!$category) continue;

            Product::firstOrCreate(
                ['name_fr' => $p['name_fr']],
                [
                    'category_id' => $category->id,
                    'name_ar' => $p['name_ar'],
                    'name_en' => $p['name_en'],
                    'slug' => Str::slug($p['name_en']) . '-' . Str::random(4),
                    'unit' => $p['unit'],
                    'reference_price' => $p['price'],
                    'is_active' => true,
                    'is_custom' => false,
                ]
            );
        }
    }
}
