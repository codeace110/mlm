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
        Schema::create('binary_trees', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 20);
            $table->string('left_child_id', 20)->nullable();
            $table->string('right_child_id', 20)->nullable();
            $table->decimal('left_volume', 15, 2)->default(0);
            $table->decimal('right_volume', 15, 2)->default(0);
            $table->decimal('carryover_left', 15, 2)->default(0);
            $table->decimal('carryover_right', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('left_child_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('right_child_id')->references('id')->on('users')->nullOnDelete();
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('binary_trees');
    }
};
