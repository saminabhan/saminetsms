<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('distributor_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_received')->default(0); // الكمية المستلمة
            $table->integer('quantity_sold')->default(0); // الكمية المباعة
            $table->integer('quantity_available')->default(0); // الكمية المتاحة
            $table->decimal('card_price', 10, 2); // سعر الكارت الواحد
            $table->decimal('total_amount', 10, 2)->default(0); // إجمالي المبلغ
            $table->decimal('paid_amount', 10, 2)->default(0); // المبلغ المدفوع
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->date('received_at'); // تاريخ الاستلام
            $table->foreignId('user_id')->constrained(); // المستخدم الذي أضاف الكروت
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('distributor_cards');
    }
};