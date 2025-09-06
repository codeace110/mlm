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
        Schema::create('test_users', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('referral_code')->nullable();
            $table->string('sponsor_id', 20)->nullable();
            $table->string('placement_side', 5)->nullable();
            $table->boolean('is_admin')->default(false);
            $table->enum('status', ['pending', 'approved', 'denied'])->default('pending');
            $table->decimal('account_balance', 15, 2)->default(0);
            $table->integer('level')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_users');
    }
};
