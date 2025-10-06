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
            if (!Schema::hasColumn('binary_trees', 'spillover_pairs_paid')) {
                $table->integer('spillover_pairs_paid')->default(0)->after('direct_pairs_paid');
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
            $table->dropColumn('spillover_pairs_paid');
        });
    }
};
