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
        // Add indexes to admin_codes table
        Schema::table('admin_codes', function (Blueprint $table) {
            // Add indexes for better performance (only if columns exist and indexes don't exist)
            if (Schema::hasColumn('admin_codes', 'issued_at') && !$this->indexExists('admin_codes', 'admin_codes_status_issued_at_index')) {
                $table->index(['status', 'issued_at'], 'admin_codes_status_issued_at_index');
            }
            if (Schema::hasColumn('admin_codes', 'assigned_to') && !$this->indexExists('admin_codes', 'admin_codes_assigned_to_status_index')) {
                $table->index(['assigned_to', 'status'], 'admin_codes_assigned_to_status_index');
            }
            if (Schema::hasColumn('admin_codes', 'batch_id') && !$this->indexExists('admin_codes', 'admin_codes_batch_id_index')) {
                $table->index('batch_id', 'admin_codes_batch_id_index');
            }
        });

        // Add indexes to binary_trees table
        Schema::table('binary_trees', function (Blueprint $table) {
            if (!$this->indexExists('binary_trees', 'binary_trees_user_id_parent_id_index')) {
                $table->index(['user_id', 'parent_id'], 'binary_trees_user_id_parent_id_index');
            }
            if (!$this->indexExists('binary_trees', 'binary_trees_level_reward_index')) {
                $table->index(['level_index', 'reward_count'], 'binary_trees_level_reward_index');
            }
            if (!$this->indexExists('binary_trees', 'binary_trees_volume_index')) {
                $table->index(['total_left_volume', 'total_right_volume'], 'binary_trees_volume_index');
            }
            if (!$this->indexExists('binary_trees', 'binary_trees_parent_id_index')) {
                $table->index('parent_id', 'binary_trees_parent_id_index');
            }
        });

        // Add indexes to bonuses table
        Schema::table('bonuses', function (Blueprint $table) {
            if (!$this->indexExists('bonuses', 'bonuses_user_id_status_index')) {
                $table->index(['user_id', 'status'], 'bonuses_user_id_status_index');
            }
            if (!$this->indexExists('bonuses', 'bonuses_reward_type_level_index')) {
                $table->index(['reward_type', 'level_index'], 'bonuses_reward_type_level_index');
            }
            if (!$this->indexExists('bonuses', 'bonuses_status_created_index')) {
                $table->index(['status', 'created_at'], 'bonuses_status_created_index');
            }
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('registration_code');
            $table->index(['sponsor_id', 'status']);
            $table->index(['is_admin', 'status']);
        });

        // Add indexes to other important tables
        Schema::table('referral_codes', function (Blueprint $table) {
            $table->index(['assigned_to', 'status']);
            $table->index(['generated_by', 'status']);
            $table->index('code');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Check if an index exists
     */
    private function indexExists($table, $index)
    {
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);

        return isset($indexes[$index]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop indexes from admin_codes table
        Schema::table('admin_codes', function (Blueprint $table) {
            $table->dropIndex(['status', 'issued_at']);
            $table->dropIndex(['issued_to_user_id', 'status']);
            $table->dropIndex(['tracker']);
            $table->dropIndex(['batch_id']);
        });

        // Drop indexes from binary_trees table
        Schema::table('binary_trees', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'parent_id']);
            $table->dropIndex(['level_index', 'reward_count']);
            $table->dropIndex(['total_left_volume', 'total_right_volume']);
            $table->dropIndex(['parent_id']);
        });

        // Drop indexes from bonuses table
        Schema::table('bonuses', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['reward_type', 'level_index']);
            $table->dropIndex(['status', 'created_at']);
        });

        // Drop indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['registration_code']);
            $table->dropIndex(['sponsor_id', 'status']);
            $table->dropIndex(['is_admin', 'status']);
        });

        // Drop indexes from other tables
        Schema::table('referral_codes', function (Blueprint $table) {
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropIndex(['generated_by', 'status']);
            $table->dropIndex(['code']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'read_at']);
            $table->dropIndex(['type', 'created_at']);
        });
    }
};