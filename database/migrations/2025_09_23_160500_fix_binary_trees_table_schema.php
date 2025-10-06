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
            // Remove fields not in specification
            if (Schema::hasColumn('binary_trees', 'left_child_id')) {
                $table->dropForeign(['left_child_id']);
            }
            if (Schema::hasColumn('binary_trees', 'right_child_id')) {
                $table->dropForeign(['right_child_id']);
            }

            $columnsToDrop = [];
            if (Schema::hasColumn('binary_trees', 'left_child_id')) {
                $columnsToDrop[] = 'left_child_id';
            }
            if (Schema::hasColumn('binary_trees', 'right_child_id')) {
                $columnsToDrop[] = 'right_child_id';
            }
            if (Schema::hasColumn('binary_trees', 'left_volume')) {
                $columnsToDrop[] = 'left_volume';
            }
            if (Schema::hasColumn('binary_trees', 'right_volume')) {
                $columnsToDrop[] = 'right_volume';
            }
            if (Schema::hasColumn('binary_trees', 'left_spillover')) {
                $columnsToDrop[] = 'left_spillover';
            }
            if (Schema::hasColumn('binary_trees', 'right_spillover')) {
                $columnsToDrop[] = 'right_spillover';
            }
            if (Schema::hasColumn('binary_trees', 'spillover_pairs_paid')) {
                $columnsToDrop[] = 'spillover_pairs_paid';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            // Ensure required fields exist with correct types
            if (!Schema::hasColumn('binary_trees', 'total_left_volume')) {
                $table->integer('total_left_volume')->default(0)->after('parent_id');
            }
            if (!Schema::hasColumn('binary_trees', 'total_right_volume')) {
                $table->integer('total_right_volume')->default(0)->after('total_left_volume');
            }
            if (!Schema::hasColumn('binary_trees', 'left_consumed')) {
                $table->integer('left_consumed')->default(0)->after('total_right_volume');
            }
            if (!Schema::hasColumn('binary_trees', 'right_consumed')) {
                $table->integer('right_consumed')->default(0)->after('left_consumed');
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
            if (!Schema::hasColumn('binary_trees', 'spillover_pairs_paid')) {
                $table->integer('spillover_pairs_paid')->default(0)->after('direct_pairs_paid');
            }
            if (!Schema::hasColumn('binary_trees', 'left_spillover')) {
                $table->integer('left_spillover')->default(0)->after('spillover_pairs_paid');
            }
            if (!Schema::hasColumn('binary_trees', 'right_spillover')) {
                $table->integer('right_spillover')->default(0)->after('left_spillover');
            }

            // Add indexes for better performance
            $table->index(['user_id', 'parent_id']);
            $table->index(['level_index', 'reward_count']);
            $table->index(['total_left_volume', 'total_right_volume']);
            $table->index('parent_id');
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
            // Drop indexes
            $table->dropIndex(['user_id', 'parent_id']);
            $table->dropIndex(['level_index', 'reward_count']);
            $table->dropIndex(['total_left_volume', 'total_right_volume']);

            // Add back removed fields
            $table->string('left_child_id', 20)->nullable()->after('parent_id');
            $table->string('right_child_id', 20)->nullable()->after('left_child_id');
            $table->decimal('left_volume', 15, 2)->default(0)->after('right_child_id');
            $table->decimal('right_volume', 15, 2)->default(0)->after('left_volume');
            $table->decimal('left_spillover', 15, 2)->default(0)->after('right_volume');
            $table->decimal('right_spillover', 15, 2)->default(0)->after('left_spillover');
            $table->integer('spillover_pairs_paid')->default(0)->after('direct_pairs_paid');

            // Add back foreign keys
            $table->foreign('left_child_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('right_child_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};