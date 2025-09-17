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
            $table->decimal('left_spillover', 15, 2)->default(0)->after('right_volume');
            $table->decimal('right_spillover', 15, 2)->default(0)->after('left_spillover');
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
            $table->dropColumn(['left_spillover', 'right_spillover']);
        });
    }
};
