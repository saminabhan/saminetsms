<?php
// database/seeders/ServiceSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceCategory;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        // إنشاء فئات الخدمات
        $hotspotCategory = ServiceCategory::create([
            'name' => 'hotspot_cards',
            'name_ar' => 'بطاقات Hotspot',
            'description' => 'بطاقات انترنت مؤقتة',
            'is_active' => true
        ]);

        $monthlyCategory = ServiceCategory::create([
            'name' => 'monthly_cards',
            'name_ar' => 'بطاقات شهرية',
            'description' => 'بطاقات اشتراك شهري',
            'is_active' => true
        ]);

        $homeCategory = ServiceCategory::create([
            'name' => 'home_subscriptions',
            'name_ar' => 'اشتراكات منزلية',
            'description' => 'اشتراكات انترنت منزلي',
            'is_active' => true
        ]);

        // بطاقات Hotspot
        Service::create([
            'service_category_id' => $hotspotCategory->id,
            'name' => '8-hours card',
            'name_ar' => 'بطاقة 8 ساعات',
            'price' => 15.00,
            'duration_hours' => 8,
            'is_active' => true
        ]);

        Service::create([
            'service_category_id' => $hotspotCategory->id,
            'name' => '4-hours card',
            'name_ar' => 'بطاقة 4 ساعات',
            'price' => 10.00,
            'duration_hours' => 4,
            'is_active' => true
        ]);

        // بطاقات شهرية
        Service::create([
            'service_category_id' => $monthlyCategory->id,
            'name' => '2M-30Days-45NIS',
            'name_ar' => 'بطاقة شهرية 2 ميجا - 45 شيكل',
            'price' => 45.00,
            'speed' => '2M',
            'duration_days' => 30,
            'is_active' => true
        ]);

        Service::create([
            'service_category_id' => $monthlyCategory->id,
            'name' => '2M-30Days-60NIS',
            'name_ar' => 'بطاقة شهرية 2 ميجا - 60 شيكل',
            'price' => 60.00,
            'speed' => '2M',
            'duration_days' => 30,
            'is_active' => true
        ]);

        // اشتراكات منزلية
        Service::create([
            'service_category_id' => $homeCategory->id,
            'name' => '16M-30Days-200G',
            'name_ar' => 'اشتراك منزلي 16 ميجا - 200 جيجا',
            'price' => 150.00,
            'speed' => '16M',
            'duration_days' => 30,
            'data_limit' => '200G',
            'is_active' => true
        ]);

        Service::create([
            'service_category_id' => $homeCategory->id,
            'name' => '16M-30Days-400G',
            'name_ar' => 'اشتراك منزلي 16 ميجا - 400 جيجا',
            'price' => 250.00,
            'speed' => '16M',
            'duration_days' => 30,
            'data_limit' => '400G',
            'is_active' => true
        ]);

        Service::create([
            'service_category_id' => $homeCategory->id,
            'name' => '16M-30Days-600G',
            'name_ar' => 'اشتراك منزلي 16 ميجا - 600 جيجا',
            'price' => 350.00,
            'speed' => '16M',
            'duration_days' => 30,
            'data_limit' => '600G',
            'is_active' => true
        ]);

        Service::create([
            'service_category_id' => $homeCategory->id,
            'name' => '6M-30Days',
            'name_ar' => 'اشتراك منزلي 6 ميجا',
            'price' => 100.00,
            'speed' => '6M',
            'duration_days' => 30,
            'is_active' => true
        ]);
    }
}