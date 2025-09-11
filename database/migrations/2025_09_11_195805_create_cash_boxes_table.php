<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'cash' أو 'bank'
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0); // الرصيد الحالي
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_boxes');
    }
};
