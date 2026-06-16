<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\ItemCategory;
use App\Models\Warehouse;

class ErpBasicsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create default units
        $units = [
            ['name' => 'قطعة', 'short_name' => 'حبة', 'is_active' => true],
            ['name' => 'كرتون', 'short_name' => 'كرتون', 'is_active' => true],
            ['name' => 'كيلوجرام', 'short_name' => 'كجم', 'is_active' => true],
            ['name' => 'متر', 'short_name' => 'م', 'is_active' => true],
            ['name' => 'ساعة', 'short_name' => 'ساعة', 'is_active' => true],
        ];
        
        foreach ($units as $unit) {
            Unit::firstOrCreate(['name' => $unit['name']], $unit);
        }

        // 2. Create default categories
        $categories = [
            ['name' => 'عام', 'description' => 'التصنيف العام للأصناف'],
            ['name' => 'منتجات تامة الصنع', 'description' => 'المنتجات الجاهزة للبيع'],
            ['name' => 'مواد خام', 'description' => 'مواد تستخدم في التصنيع'],
            ['name' => 'خدمات', 'description' => 'أصناف غير ملموسة كالتركيب والصيانة'],
        ];

        foreach ($categories as $category) {
            ItemCategory::firstOrCreate(['name' => $category['name']], $category);
        }

        // 3. Create default warehouse
        Warehouse::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'المستودع الرئيسي',
                'location' => 'المركز الرئيسي',
                'is_active' => true
            ]
        );
    }
}
