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
        // Fix bonuses table - add missing indexes and constraints
        Schema::table('bonuses', function (Blueprint $table) {
            if (!$this->indexExists('bonuses', 'bonuses_user_status_index')) {
                $table->index(['user_id', 'status'], 'bonuses_user_status_index');
            }
            if (!$this->indexExists('bonuses', 'bonuses_reward_type_index')) {
                $table->index('reward_type', 'bonuses_reward_type_index');
            }
            if (!$this->indexExists('bonuses', 'bonuses_created_at_index')) {
                $table->index('created_at', 'bonuses_created_at_index');
            }
        });

        // Fix binary_trees table - add missing indexes
        Schema::table('binary_trees', function (Blueprint $table) {
            if (!$this->indexExists('binary_trees', 'binary_trees_parent_id_index')) {
                $table->index('parent_id', 'binary_trees_parent_id_index');
            }
            if (!$this->indexExists('binary_trees', 'binary_trees_level_reward_index')) {
                $table->index(['level_index', 'reward_count'], 'binary_trees_level_reward_index');
            }
            if (!$this->indexExists('binary_trees', 'binary_trees_volume_index')) {
                $table->index(['total_left_volume', 'total_right_volume'], 'binary_trees_volume_index');
            }
            if (!$this->indexExists('binary_trees', 'binary_trees_pairs_paid_index')) {
                $table->index(['direct_pairs_paid', 'spillover_pairs_paid'], 'binary_trees_pairs_paid_index');
            }
        });

        // Fix admin_codes table - add missing indexes
        Schema::table('admin_codes', function (Blueprint $table) {
            if (Schema::hasColumn('admin_codes', 'assigned_to') && !$this->indexExists('admin_codes', 'admin_codes_assigned_to_status_index')) {
                $table->index(['assigned_to', 'status'], 'admin_codes_assigned_to_status_index');
            }
            if (Schema::hasColumn('admin_codes', 'used_by') && !$this->indexExists('admin_codes', 'admin_codes_used_by_index')) {
                $table->index('used_by', 'admin_codes_used_by_index');
            }
            if (Schema::hasColumn('admin_codes', 'used_at') && !$this->indexExists('admin_codes', 'admin_codes_used_at_index')) {
                $table->index('used_at', 'admin_codes_used_at_index');
            }
        });

        // Fix users table - add missing indexes for MLM functionality
        Schema::table('users', function (Blueprint $table) {
            if (!$this->indexExists('users', 'users_sponsor_placement_index')) {
                $table->index(['sponsor_id', 'placement_side'], 'users_sponsor_placement_index');
            }
            if (!$this->indexExists('users', 'users_referral_code_index')) {
                $table->index('referral_code', 'users_referral_code_index');
            }
            if (!$this->indexExists('users', 'users_registration_code_index')) {
                $table->index('registration_code', 'users_registration_code_index');
            }
        });

        // Add check constraints for data integrity
        DB::statement('ALTER TABLE bonuses ADD CONSTRAINT check_bonus_amount CHECK (amount >= 0)');
        DB::statement('ALTER TABLE bonuses ADD CONSTRAINT check_reward_type CHECK (reward_type IN ("direct", "level", "spillover"))');
        DB::statement('ALTER TABLE bonuses ADD CONSTRAINT check_bonus_status CHECK (status IN ("pending", "approved", "paid", "cancelled"))');

        DB::statement('ALTER TABLE binary_trees ADD CONSTRAINT check_volume_positive CHECK (total_left_volume >= 0 AND total_right_volume >= 0)');
        DB::statement('ALTER TABLE binary_trees ADD CONSTRAINT check_consumed_positive CHECK (left_consumed >= 0 AND right_consumed >= 0)');
        DB::statement('ALTER TABLE binary_trees ADD CONSTRAINT check_pairs_paid_positive CHECK (direct_pairs_paid >= 0 AND spillover_pairs_paid >= 0)');

        DB::statement('ALTER TABLE admin_codes ADD CONSTRAINT check_admin_code_status CHECK (status IN ("issued", "unused", "used", "expired"))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop indexes
        Schema::table('bonuses', function (Blueprint $table) {
            $table->dropIndexIfExists('bonuses_user_status_index');
            $table->dropIndexIfExists('bonuses_reward_type_index');
            $table->dropIndexIfExists('bonuses_created_at_index');
        });

        Schema::table('binary_trees', function (Blueprint $table) {
            $table->dropIndexIfExists('binary_trees_parent_id_index');
            $table->dropIndexIfExists('binary_trees_level_reward_index');
            $table->dropIndexIfExists('binary_trees_volume_index');
            $table->dropIndexIfExists('binary_trees_pairs_paid_index');
        });

        Schema::table('admin_codes', function (Blueprint $table) {
            $table->dropIndexIfExists('admin_codes_assigned_to_status_index');
            $table->dropIndexIfExists('admin_codes_used_by_index');
            $table->dropIndexIfExists('admin_codes_used_at_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndexIfExists('users_sponsor_placement_index');
            $table->dropIndexIfExists('users_referral_code_index');
            $table->dropIndexIfExists('users_registration_code_index');
        });

        // Drop check constraints
        DB::statement('ALTER TABLE bonuses DROP CONSTRAINT IF EXISTS check_bonus_amount');
        DB::statement('ALTER TABLE bonuses DROP CONSTRAINT IF EXISTS check_reward_type');
        DB::statement('ALTER TABLE bonuses DROP CONSTRAINT IF EXISTS check_bonus_status');

        DB::statement('ALTER TABLE binary_trees DROP CONSTRAINT IF EXISTS check_volume_positive');
        DB::statement('ALTER TABLE binary_trees DROP CONSTRAINT IF EXISTS check_consumed_positive');
        DB::statement('ALTER TABLE binary_trees DROP CONSTRAINT IF EXISTS check_pairs_paid_positive');

        DB::statement('ALTER TABLE admin_codes DROP CONSTRAINT IF EXISTS check_admin_code_status');
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