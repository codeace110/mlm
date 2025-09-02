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
        Schema::create('package_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 20);
            $table->unsignedBigInteger('package_id');
            $table->integer('quantity');
            $table->decimal('total_amount', 10, 2);
            $table->enum('method', ['cebuana_lhuillier', 'mlhuillier', 'palawan_pawnshop', 'gcash', 'paymaya']);
            $table->json('account_details');
            $table->enum('status', ['pending', 'approved', 'denied'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by', 20)->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
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
        Schema::dropIfExists('package_purchases');
    }
};
