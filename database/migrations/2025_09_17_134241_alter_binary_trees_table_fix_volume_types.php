<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('binary_trees', function (Blueprint $table) {
            $table->decimal('total_left_volume', 15, 2)->change();
            $table->decimal('total_right_volume', 15, 2)->change();
            $table->decimal('left_consumed', 15, 2)->change();
            $table->decimal('right_consumed', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('binary_trees', function (Blueprint $table) {
            $table->integer('total_left_volume')->change();
            $table->integer('total_right_volume')->change();
            $table->integer('left_consumed')->change();
            $table->integer('right_consumed')->change();
        });
    }
};
