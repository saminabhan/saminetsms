<?php
// database/migrations/xxxx_xx_xx_create_subscriber_balances_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscriber_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
            $table->decimal('balance', 10, 2)->default(0); // الرصيد (موجب = دائن، سالب = مدين)
            $table->decimal('total_invoices', 10, 2)->default(0); // إجمالي الفواتير
            $table->decimal('total_payments', 10, 2)->default(0); // إجمالي المدفوعات
            $table->timestamp('last_updated')->useCurrent();
            $table->timestamps();
            
            $table->unique('subscriber_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscriber_balances');
    }
};