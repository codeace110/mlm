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
        // Use raw SQL to add columns to avoid migration conflicts
        $sql1 = "ALTER TABLE binary_trees ADD COLUMN left_spillover DECIMAL(15,2) DEFAULT 0";
        $sql2 = "ALTER TABLE binary_trees ADD COLUMN right_spillover DECIMAL(15,2) DEFAULT 0";

        try {
            DB::statement($sql1);
        } catch (\Exception $e) {
            // Column might already exist, continue
        }

        try {
            DB::statement($sql2);
        } catch (\Exception $e) {
            // Column might already exist, continue
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('binary_trees', function (Blueprint $table) {
            $columns = Schema::getColumnListing('binary_trees');

            if (in_array('left_spillover', $columns)) {
                $table->dropColumn('left_spillover');
            }
            if (in_array('right_spillover', $columns)) {
                $table->dropColumn('right_spillover');
            }
            if (in_array('left_spillover', $columns)) {
                $table->dropColumn('left_spillover');
            }
            if (in_array('right_spillover', $columns)) {
                $table->dropColumn('right_spillover');
            }
        });
    }
};
