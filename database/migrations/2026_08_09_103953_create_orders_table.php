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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Nullable for guest checkout
        $table->string('order_number')->unique();
        $table->string('customer_name');
        $table->string('customer_phone');
        $table->text('shipping_address');
        $table->string('district')->default('Dhaka');
        $table->decimal('shipping_cost', 8, 2)->default(60.00); // e.g., 60 BDT Dhaka, 120 Outside
        $table->decimal('total_amount', 10, 2);
        $table->string('payment_method')->default('cod'); // cod, bkash, nagad, sslcommerz
        $table->string('payment_status')->default('pending'); // pending, paid, failed
        $table->string('order_status')->default('pending'); // pending, processing, shipped, delivered, cancelled
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
