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
        // Skip this migration entirely as it's causing conflicts
        // The later migration 2025_09_27_151933_add_missing_fields_to_admin_codes_table.php
        // will handle all the necessary changes properly
        return true;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_codes', function (Blueprint $table) {
            // Drop foreign keys if they exist
            $foreignKeys = $this->getForeignKeys('admin_codes');
            if (in_array('admin_codes_assigned_to_foreign', $foreignKeys)) {
                $table->dropForeign(['assigned_to']);
            }
            if (in_array('admin_codes_generated_by_foreign', $foreignKeys)) {
                $table->dropForeign(['generated_by']);
            }

            // Drop indexes if they exist
            $table->dropIndexIfExists('admin_codes_status_created_at_index');
            $table->dropIndexIfExists('admin_codes_assigned_to_status_index');

            // Remove added fields
            $table->dropColumn(['assigned_to', 'generated_by', 'expires_at']);
        });
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
     * Check if a foreign key exists
     */
    private function foreignKeyExists(string $table, string $column): bool
    {
        $foreignKeys = $this->getForeignKeys($table);
        return in_array("{$table}_{$column}_foreign", $foreignKeys);
    }

    /**
     * Check if an index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        $conn = Schema::getConnection();
        $dbSchemaManager = $conn->getDoctrineSchemaManager();
        $indexes = [];

        try {
            $indexObjects = $dbSchemaManager->listTableIndexes($table);
            foreach ($indexObjects as $indexObject) {
                $indexes[] = $indexObject->getName();
            }
        } catch (\Exception $e) {
            // If we can't get indexes, return false
        }

        return in_array($index, $indexes);
    }
};