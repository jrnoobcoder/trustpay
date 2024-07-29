<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_links', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('agent_id')->nullable();
			$table->string('customer_name');
			$table->string('customer_email');
			$table->string('customer_phone');
			$table->string('stripe_id')->nullable();
			$table->unsignedBigInteger('amount')->nullable();
			$table->string('currency')->nullable();
			$table->string('description')->nullable();
			$table->text('payment_link')->nullable();
			$table->string('client_secret')->nullable();
            $table->string('payment_intent_id')->nullable();
			$table->enum('payment_status', ['paid', 'pending', 'processing', 'failed'])->default('processing');
			$table->unsignedBigInteger('payment_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_links');
    }
};
