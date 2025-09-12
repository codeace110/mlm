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
        Schema::create('bonus_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('package_value', 10, 2)->default(1200.00);
            $table->decimal('direct_bonus_percent', 5, 2)->default(100.00);
            $table->decimal('pair_bonus_amount', 10, 2)->default(240.00);
            $table->enum('balancer_ratio', ['1:1', '2:1', '3:1'])->default('1:1');
            $table->decimal('matching_bonus_percent', 5, 2)->default(20.00);
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
        Schema::dropIfExists('bonus_settings');
    }
};
