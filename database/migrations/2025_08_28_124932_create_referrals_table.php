<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id') // who owns the code
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('code', 32)->unique(); // referral code
            $table->boolean('is_used')->default(false); // track usage
            $table->foreignId('used_by')->nullable() // who used it
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
