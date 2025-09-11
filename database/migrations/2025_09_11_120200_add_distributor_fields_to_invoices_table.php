<?php
// database/migrations/add_distributor_fields_to_invoices_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('client_type', ['subscriber', 'distributor'])->default('subscriber')->after('id');
            $table->foreignId('distributor_id')->nullable()->constrained()->after('subscriber_id');
            $table->foreignId('distributor_card_id')->nullable()->constrained()->after('distributor_id');
            
            // تعديل subscriber_id ليصبح nullable
            $table->foreignId('subscriber_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['client_type', 'distributor_id', 'distributor_card_id']);
            $table->foreignId('subscriber_id')->nullable(false)->change();
        });
    }
};