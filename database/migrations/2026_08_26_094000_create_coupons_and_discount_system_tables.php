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
        // 1. Create coupons table
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. EID2026, SAVE15
            $table->string('description')->nullable();
            $table->string('type')->default('fixed'); // fixed, percentage
            $table->decimal('value', 10, 2); // amount (BDT) or percentage (%)
            $table->decimal('min_spend', 10, 2)->default(0); // minimum cart subtotal
            $table->decimal('max_discount', 10, 2)->nullable(); // cap for percentage discounts
            $table->integer('usage_limit')->nullable(); // total times across store
            $table->integer('usage_limit_per_user')->default(1); // per user/phone
            $table->integer('used_count')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Add coupon fields to orders table if not present
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->after('customer_phone')->constrained('coupons')->nullOnDelete();
                $table->string('coupon_code')->nullable()->after('coupon_id');
                $table->decimal('discount_amount', 10, 2)->default(0)->after('coupon_code');
            }
        });

        // 3. Create coupon_usages table for audit & per-customer limit enforcement
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_phone')->nullable()->index();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'coupon_id')) {
                $table->dropForeign(['coupon_id']);
                $table->dropColumn(['coupon_id', 'coupon_code', 'discount_amount']);
            }
        });

        Schema::dropIfExists('coupons');
    }
};
