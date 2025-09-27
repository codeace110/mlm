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
        // Add missing columns to admin_codes table
        Schema::table('admin_codes', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_codes', 'tracker')) {
                $table->string('tracker')->nullable()->after('code');
            }
            if (!Schema::hasColumn('admin_codes', 'generated_by')) {
                $table->string('generated_by', 20)->nullable()->after('tracker');
            }
            if (!Schema::hasColumn('admin_codes', 'batch_id')) {
                $table->string('batch_id')->nullable()->after('generated_by');
            }
            if (!Schema::hasColumn('admin_codes', 'batch_name')) {
                $table->string('batch_name')->nullable()->after('batch_id');
            }
            if (!Schema::hasColumn('admin_codes', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('batch_name');
            }
            if (!Schema::hasColumn('admin_codes', 'issued_to_user_id')) {
                $table->string('issued_to_user_id', 20)->nullable()->after('expires_at');
            }
            if (!Schema::hasColumn('admin_codes', 'issued_by_admin_id')) {
                $table->string('issued_by_admin_id', 20)->nullable()->after('issued_to_user_id');
            }
            if (!Schema::hasColumn('admin_codes', 'issued_at')) {
                $table->timestamp('issued_at')->nullable()->after('issued_by_admin_id');
            }
            if (!Schema::hasColumn('admin_codes', 'notes')) {
                $table->text('notes')->nullable()->after('issued_at');
            }
        });

        // Add missing columns to binary_trees table
        Schema::table('binary_trees', function (Blueprint $table) {
            if (!Schema::hasColumn('binary_trees', 'parent_id')) {
                $table->string('parent_id', 20)->nullable()->after('user_id');
            }
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
            if (!Schema::hasColumn('binary_trees', 'placement_side')) {
                $table->enum('placement_side', ['left', 'right'])->nullable()->after('spillover_pairs_paid');
            }
        });

        // Add missing columns to bonuses table
        Schema::table('bonuses', function (Blueprint $table) {
            if (!Schema::hasColumn('bonuses', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('bonuses', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('approved_at');
            }
        });

        // Add missing foreign keys
        Schema::table('binary_trees', function (Blueprint $table) {
            if (!Schema::hasColumn('binary_trees', 'parent_id')) {
                $table->foreign('parent_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        // Add missing indexes
        Schema::table('bonuses', function (Blueprint $table) {
            if (!$this->indexExists('bonuses', 'bonuses_reward_type_level_index_index')) {
                $table->index(['reward_type', 'level_index'], 'bonuses_reward_type_level_index_index');
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
        // Drop columns from admin_codes table
        Schema::table('admin_codes', function (Blueprint $table) {
            $table->dropColumn([
                'tracker', 'generated_by', 'batch_id', 'batch_name', 'expires_at',
                'issued_to_user_id', 'issued_by_admin_id', 'issued_at', 'notes'
            ]);
        });

        // Drop columns from binary_trees table
        Schema::table('binary_trees', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id', 'total_left_volume', 'total_right_volume', 'left_consumed',
                'right_consumed', 'level_index', 'reward_count', 'direct_pairs_paid',
                'spillover_pairs_paid', 'placement_side'
            ]);
        });

        // Drop columns from bonuses table
        Schema::table('bonuses', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'paid_at']);
        });

        // Drop indexes
        Schema::table('bonuses', function (Blueprint $table) {
            $table->dropIndexIfExists('bonuses_reward_type_level_index_index');
        });
    }

    /**
     * Check if an index exists
     *
     * @param string $table
     * @param string $index
     * @return bool
     */
    private function indexExists($table, $index)
    {
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);

        return isset($indexes[$index]);
    }
};