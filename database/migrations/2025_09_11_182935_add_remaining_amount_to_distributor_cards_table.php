<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('distributor_cards', function (Blueprint $table) {
        $table->decimal('remaining_amount', 12, 2)->default(0);
    });
}

public function down()
{
    Schema::table('distributor_cards', function (Blueprint $table) {
        $table->dropColumn('remaining_amount');
    });
}

};
