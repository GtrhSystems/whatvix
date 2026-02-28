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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_uid')->nullable();
            $table->string('invoice_no')->nullable();
            $table->string('payment_id')->nullable();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('gateway_id')->constrained('gateways')->cascadeOnDelete();
            $table->enum('payment_status', ['unpaid', 'paid', 'canceled', 'rejected'])->default('unpaid');
            $table->double('amount')->nullable();
            $table->double('tax')->nullable();
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('pending');
            $table->date('will_expire')->nullable();
            $table->text('meta')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
