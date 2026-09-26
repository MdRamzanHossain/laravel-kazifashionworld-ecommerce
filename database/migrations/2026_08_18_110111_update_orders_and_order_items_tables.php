<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_number')) $table->string('order_number')->nullable();
            if (!Schema::hasColumn('orders', 'customer_name')) $table->string('customer_name')->nullable();
            if (!Schema::hasColumn('orders', 'customer_phone')) $table->string('customer_phone')->nullable();
            if (!Schema::hasColumn('orders', 'shipping_address')) $table->text('shipping_address')->nullable();
            if (!Schema::hasColumn('orders', 'subtotal')) $table->decimal('subtotal', 10, 2)->default(0);
            if (!Schema::hasColumn('orders', 'shipping_fee')) $table->decimal('shipping_fee', 10, 2)->default(0);
            if (!Schema::hasColumn('orders', 'grand_total')) $table->decimal('grand_total', 10, 2)->default(0);
            if (!Schema::hasColumn('orders', 'payment_method')) $table->string('payment_method')->default('cod');
            if (!Schema::hasColumn('orders', 'payment_status')) $table->string('payment_status')->default('pending');
            if (!Schema::hasColumn('orders', 'order_status')) $table->string('order_status')->default('pending');
        });

        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->string('product_name');
                $table->decimal('price', 10, 2);
                $table->integer('quantity');
                $table->decimal('total', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        //
    }
};