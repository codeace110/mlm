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
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 32)->unique()->after('id');
            $table->foreignId('sponsor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('placement_side', 5)->nullable(); // 'left' or 'right'
            
            // enforce only one left & one right per sponsor
            $table->unique(['sponsor_id','placement_side'], 'unique_sponsor_side');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // drop unique index by name
            $table->dropUnique('unique_sponsor_side');

            // drop columns
            $table->dropColumn(['referral_code', 'sponsor_id', 'placement_side']);
        });
    }
};
