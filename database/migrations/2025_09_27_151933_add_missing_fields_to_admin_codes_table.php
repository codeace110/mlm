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
        // Skip this migration as it's causing conflicts with other migrations
        // The schema will be handled by the original table creation and other migrations
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
            // Drop foreign keys
            $table->dropForeign(['used_by']);

            // Drop indexes
            $table->dropIndexIfExists('admin_codes_used_by_index');
            $table->dropIndexIfExists('admin_codes_used_at_index');
            $table->dropIndexIfExists('admin_codes_assigned_at_index');

            // Remove added fields
            $table->dropColumn(['used_by', 'used_at', 'assigned_at', 'expired_at']);
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

    /**
     * Check if a foreign key exists
     *
     * @param string $table
     * @param string $foreignKey
     * @return bool
     */
    private function foreignKeyExists($table, $foreignKey)
    {
        $foreignKeys = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys($table);

        foreach ($foreignKeys as $fk) {
            if ($fk->getName() === $foreignKey) {
                return true;
            }
        }

        return false;
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
};
