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
            if (!Schema::hasColumn('binary_trees', 'left_child_id')) {
                $table->string('left_child_id', 20)->nullable()->after('parent_id');
            }
            if (!Schema::hasColumn('binary_trees', 'right_child_id')) {
                $table->string('right_child_id', 20)->nullable()->after('left_child_id');
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
            $table->dropColumn(['left_child_id', 'right_child_id']);
        });
    }
};