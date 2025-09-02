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
        // First, drop the foreign key constraints
        Schema::table('package_purchases', function (Blueprint $table) {
            $table->dropForeign('package_purchases_user_id_foreign');
            $table->dropForeign('package_purchases_approved_by_foreign');
        });

        // Then change the column types
        Schema::table('package_purchases', function (Blueprint $table) {
            $table->string('user_id', 20)->change();
            $table->string('approved_by', 20)->nullable()->change();
        });

        // Finally, recreate the foreign key constraints
        Schema::table('package_purchases', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // First, drop the foreign key constraints
        Schema::table('package_purchases', function (Blueprint $table) {
            $table->dropForeign('package_purchases_user_id_foreign');
            $table->dropForeign('package_purchases_approved_by_foreign');
        });

        // Then revert the column types
        Schema::table('package_purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
            $table->unsignedBigInteger('approved_by')->nullable()->change();
        });

        // Finally, recreate the foreign key constraints
        Schema::table('package_purchases', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};
