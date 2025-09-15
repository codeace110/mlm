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
            $table->integer('total_left_volume')->default(0)->after('right_volume');
            $table->integer('total_right_volume')->default(0)->after('total_left_volume');
            $table->integer('left_consumed')->default(0)->after('total_right_volume');
            $table->integer('right_consumed')->default(0)->after('left_consumed');
            $table->integer('level_index')->default(1)->after('right_consumed');
            $table->integer('reward_count')->default(0)->after('level_index');
            $table->integer('direct_pairs_paid')->default(0)->after('reward_count');
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
            $table->dropColumn([
                'total_left_volume',
                'total_right_volume',
                'left_consumed',
                'right_consumed',
                'level_index',
                'reward_count',
                'direct_pairs_paid'
            ]);
        });
    }
};
