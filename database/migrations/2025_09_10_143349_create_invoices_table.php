<?php
// database/migrations/xxxx_xx_xx_create_invoices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // رقم الفاتورة
            $table->foreignId('subscriber_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); // المستخدم الذي أنشأ الفاتورة
            
            // معلومات الفاتورة
            $table->decimal('original_price', 10, 2); // السعر الأصلي
            $table->decimal('discount_amount', 10, 2)->default(0); // مبلغ الخصم
            $table->decimal('final_amount', 10, 2); // المبلغ النهائي
            $table->decimal('paid_amount', 10, 2)->default(0); // المبلغ المدفوع
            
            // تواريخ الخدمة
            $table->date('service_start_date'); // تاريخ بداية الخدمة
            $table->date('service_end_date'); // تاريخ نهاية الخدمة
            
            // حالة الفاتورة
            $table->enum('status', ['pending', 'paid', 'partially_paid', 'cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'partial'])->default('unpaid');
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};