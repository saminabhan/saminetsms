<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('service_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->boolean('allow_quantity')->default(false)->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('allow_quantity');
        });
    }
};


