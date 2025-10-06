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
        Schema::table('admin_codes', function (Blueprint $table) {
            $table->uuid('batch_id')->nullable()->after('id');
            $table->string('batch_name', 255)->nullable()->after('batch_id');
            $table->index(['batch_id', 'batch_name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admin_codes', function (Blueprint $table) {
            $table->dropIndex(['batch_id', 'batch_name']);
            $table->dropColumn(['batch_id', 'batch_name']);
        });
    }
};
