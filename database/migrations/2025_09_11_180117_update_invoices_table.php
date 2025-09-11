<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // السماح بأن تكون هذه الأعمدة NULL
            $table->bigInteger('subscriber_id')->unsigned()->nullable()->change();
            $table->bigInteger('distributor_id')->unsigned()->nullable()->change();
            $table->bigInteger('distributor_card_id')->unsigned()->nullable()->change();

            // تعديل ENUM status لإضافة 'completed' إذا بدك
            $table->enum('status', ['pending', 'paid', 'partially_paid', 'cancelled', 'completed'])
                ->default('pending')
                ->change();

            // تعديل ENUM payment_status إذا لزم الأمر
            $table->enum('payment_status', ['unpaid', 'paid', 'partial'])
                ->default('unpaid')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->bigInteger('subscriber_id')->unsigned()->nullable(false)->change();
            $table->bigInteger('distributor_id')->unsigned()->nullable(false)->change();
            $table->bigInteger('distributor_card_id')->unsigned()->nullable(false)->change();

            $table->enum('status', ['pending', 'paid', 'partially_paid', 'cancelled'])
                ->default('pending')
                ->change();

            $table->enum('payment_status', ['unpaid', 'paid', 'partial'])
                ->default('unpaid')
                ->change();
        });
    }
};
