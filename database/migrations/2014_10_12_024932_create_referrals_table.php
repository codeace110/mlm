<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key for history records

            // New user who signed up
            $table->string('user_id', 20);
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            // The sponsor they used
            $table->string('sponsor_id', 20);
            $table->foreign('sponsor_id')
                ->references('id')->on('users')
                ->cascadeOnDelete();

            // Left / Right placement
            $table->string('placement_side', 5)->nullable();

            // Admin decision
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Who approved/rejected (optional: if you want admin tracking)
            $table->string('approved_by' , 20)->nullable();
            $table->foreign('approved_by')
                ->references('id')->on('users') // if admins are also users
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
