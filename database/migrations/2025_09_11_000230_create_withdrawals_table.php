<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->enum('category_type', ['operational', 'capital', 'partner']);
            $table->unsignedBigInteger('category_id')->nullable(); // expense_category_id or partner_id
            $table->foreignId('user_id')->constrained();
            $table->decimal('amount', 10, 2);
            $table->date('withdrawn_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('withdrawals');
    }
};


