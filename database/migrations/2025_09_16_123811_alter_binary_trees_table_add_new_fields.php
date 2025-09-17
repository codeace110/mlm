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
            if (!Schema::hasColumn('binary_trees', 'parent_id')) {
                $table->string('parent_id', 20)->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('binary_trees', 'left_spillover')) {
                $table->decimal('left_spillover', 15, 2)->default(0)->after('right_volume');
            }
            if (!Schema::hasColumn('binary_trees', 'right_spillover')) {
                $table->decimal('right_spillover', 15, 2)->default(0)->after('left_spillover');
            }
            if (!Schema::hasColumn('binary_trees', 'total_left_volume')) {
                $table->decimal('total_left_volume', 15, 2)->default(0)->after('carryover_right');
            }
            if (!Schema::hasColumn('binary_trees', 'total_right_volume')) {
                $table->decimal('total_right_volume', 15, 2)->default(0)->after('total_left_volume');
            }
            if (!Schema::hasColumn('binary_trees', 'left_consumed')) {
                $table->decimal('left_consumed', 15, 2)->default(0)->after('total_right_volume');
            }
            if (!Schema::hasColumn('binary_trees', 'right_consumed')) {
                $table->decimal('right_consumed', 15, 2)->default(0)->after('left_consumed');
            }
            if (!Schema::hasColumn('binary_trees', 'level_index')) {
                $table->integer('level_index')->default(1)->after('right_consumed');
            }
            if (!Schema::hasColumn('binary_trees', 'reward_count')) {
                $table->integer('reward_count')->default(0)->after('level_index');
            }
            if (!Schema::hasColumn('binary_trees', 'direct_pairs_paid')) {
                $table->integer('direct_pairs_paid')->default(0)->after('reward_count');
            }

            if (!Schema::hasColumn('binary_trees', 'parent_id')) {
                $table->foreign('parent_id')->references('user_id')->on('binary_trees')->nullOnDelete();
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
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id',
                'left_spillover',
                'right_spillover',
                'total_left_volume',
                'total_right_volume',
                'left_consumed',
                'right_consumed',
                'level_index',
                'reward_count',
                'direct_pairs_paid',
            ]);
        });
    }
};