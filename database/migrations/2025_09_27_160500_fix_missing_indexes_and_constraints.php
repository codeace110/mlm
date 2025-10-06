<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add missing indexes to admin_codes table
        Schema::table('admin_codes', function (Blueprint $table) {
            if (!$this->indexExists('admin_codes', 'admin_codes_status_index')) {
                $table->index('status', 'admin_codes_status_index');
            }
            if (!$this->indexExists('admin_codes', 'admin_codes_tracker_index')) {
                $table->index('tracker', 'admin_codes_tracker_index');
            }
            if (!$this->indexExists('admin_codes', 'admin_codes_batch_id_index')) {
                $table->index('batch_id', 'admin_codes_batch_id_index');
            }
            if (!$this->indexExists('admin_codes', 'admin_codes_issued_to_user_id_index')) {
                $table->index('issued_to_user_id', 'admin_codes_issued_to_user_id_index');
            }
            if (!$this->indexExists('admin_codes', 'admin_codes_used_by_user_id_index')) {
                $table->index('used_by_user_id', 'admin_codes_used_by_user_id_index');
            }
        });

        // Add missing indexes to bonuses table
        Schema::table('bonuses', function (Blueprint $table) {
            if (!$this->indexExists('bonuses', 'bonuses_status_created_at_index')) {
                $table->index(['status', 'created_at'], 'bonuses_status_created_at_index');
            }
            if (!$this->indexExists('bonuses', 'bonuses_user_id_status_index')) {
                $table->index(['user_id', 'status'], 'bonuses_user_id_status_index');
            }
        });

        // Add missing indexes to binary_trees table
        Schema::table('binary_trees', function (Blueprint $table) {
            if (!$this->indexExists('binary_trees', 'binary_trees_parent_id_index')) {
                $table->index('parent_id', 'binary_trees_parent_id_index');
            }
        });

        // Fix foreign key constraints
        Schema::table('binary_trees', function (Blueprint $table) {
            // Drop existing foreign key if it exists and recreate with proper cascade
            $foreignKeys = $this->getForeignKeys('binary_trees');
            if (in_array('binary_trees_user_id_foreign', $foreignKeys)) {
                $table->dropForeign(['user_id']);
            }
            if (!$this->foreignKeyExists('binary_trees', 'binary_trees_user_id_foreign')) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            }
        });

        // Add check constraints (only if they don't exist)
        $this->addConstraintIfNotExists('bonuses', 'check_bonus_status', 'ALTER TABLE bonuses ADD CONSTRAINT check_bonus_status CHECK (status IN ("pending", "approved", "paid", "cancelled"))');
        $this->addConstraintIfNotExists('bonuses', 'check_reward_type', 'ALTER TABLE bonuses ADD CONSTRAINT check_reward_type CHECK (reward_type IN ("direct", "level", "spillover"))');
        $this->addConstraintIfNotExists('bonuses', 'check_amount_positive', 'ALTER TABLE bonuses ADD CONSTRAINT check_amount_positive CHECK (amount >= 0)');

        $this->addConstraintIfNotExists('binary_trees', 'check_volume_positive', 'ALTER TABLE binary_trees ADD CONSTRAINT check_volume_positive CHECK (total_left_volume >= 0 AND total_right_volume >= 0)');
        $this->addConstraintIfNotExists('binary_trees', 'check_consumed_positive', 'ALTER TABLE binary_trees ADD CONSTRAINT check_consumed_positive CHECK (left_consumed >= 0 AND right_consumed >= 0)');
        $this->addConstraintIfNotExists('binary_trees', 'check_pairs_paid_positive', 'ALTER TABLE binary_trees ADD CONSTRAINT check_pairs_paid_positive CHECK (direct_pairs_paid >= 0 AND spillover_pairs_paid >= 0)');

        $this->addConstraintIfNotExists('admin_codes', 'check_admin_code_status', 'ALTER TABLE admin_codes ADD CONSTRAINT check_admin_code_status CHECK (status IN ("issued", "unused", "used", "expired"))');
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
            $table->dropIndexIfExists('admin_codes_status_index');
            $table->dropIndexIfExists('admin_codes_tracker_index');
            $table->dropIndexIfExists('admin_codes_batch_id_index');
            $table->dropIndexIfExists('admin_codes_issued_to_user_id_index');
            $table->dropIndexIfExists('admin_codes_used_by_user_id_index');
        });

        // Drop indexes from bonuses table
        Schema::table('bonuses', function (Blueprint $table) {
            $table->dropIndexIfExists('bonuses_status_created_at_index');
            $table->dropIndexIfExists('bonuses_user_id_status_index');
        });

        // Drop indexes from binary_trees table
        Schema::table('binary_trees', function (Blueprint $table) {
            $table->dropIndexIfExists('binary_trees_parent_id_index');
        });

        // Drop check constraints
        DB::statement('ALTER TABLE bonuses DROP CONSTRAINT IF EXISTS check_bonus_status');
        DB::statement('ALTER TABLE bonuses DROP CONSTRAINT IF EXISTS check_reward_type');
        DB::statement('ALTER TABLE bonuses DROP CONSTRAINT IF EXISTS check_amount_positive');

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

    /**
     * Check if a foreign key exists
     *
     * @param string $table
     * @param string $foreignKey
     * @return bool
     */
    private function foreignKeyExists($table, $foreignKey)
    {
        $foreignKeys = $this->getForeignKeys($table);
        return in_array($foreignKey, $foreignKeys);
    }

    /**
     * Get foreign keys for a table
     */
    private function getForeignKeys(string $table): array
    {
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        $foreignKeys = [];

        try {
            $indexes = $dbSchemaManager->listTableForeignKeys($table);
            foreach ($indexes as $index) {
                $foreignKeys[] = $index->getName();
            }
        } catch (\Exception $e) {
            // If we can't get foreign keys, return empty array
        }

        return $foreignKeys;
    }

    /**
     * Add constraint if it doesn't exist
     *
     * @param string $table
     * @param string $constraint
     * @param string $sql
     */
    private function addConstraintIfNotExists($table, $constraint, $sql)
    {
        try {
            DB::statement($sql);
        } catch (\Exception $e) {
            // Constraint might already exist, continue
        }
    }
};