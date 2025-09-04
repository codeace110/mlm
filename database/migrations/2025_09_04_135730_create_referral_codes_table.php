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
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('assigned_to', 20)->nullable();
            $table->string('used_by', 20)->nullable();
            $table->enum('status', ['available', 'assigned', 'used', 'expired'])->default('available');
            $table->string('generated_by', 20);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('used_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('generated_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referral_codes');
    }
};
