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
            if (!Schema::hasColumn('binary_trees', 'left_spillover')) {
                $table->decimal('left_spillover', 15, 2)->default(0)->after('right_consumed');
            }
            if (!Schema::hasColumn('binary_trees', 'right_spillover')) {
                $table->decimal('right_spillover', 15, 2)->default(0)->after('left_spillover');
            }
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
