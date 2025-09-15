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
        Schema::create('admin_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('distributor_id', 20); // The distributor who gets the code
            $table->enum('status', ['issued', 'unused', 'used'])->default('issued');
            $table->string('used_by_user_id', 20)->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->foreign('distributor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('used_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_codes');
    }
};
