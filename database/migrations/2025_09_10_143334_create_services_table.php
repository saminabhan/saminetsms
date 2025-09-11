<?php
// database/migrations/xxxx_xx_xx_create_services_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // مثل: 8-hours cards
            $table->string('name_ar'); // بطاقة 8 ساعات
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // السعر بالشيكل
            $table->string('speed')->nullable(); // السرعة مثل 2M, 16M
            $table->integer('duration_hours')->nullable(); // المدة بالساعات
            $table->integer('duration_days')->nullable(); // المدة بالأيام
            $table->string('data_limit')->nullable(); // حد البيانات مثل 200G
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
};