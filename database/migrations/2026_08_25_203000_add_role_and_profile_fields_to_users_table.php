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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('role')->default('customer')->after('password'); // admin, manager, customer
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('avatar')->nullable()->after('is_active');
            $table->text('shipping_address')->nullable()->after('avatar');
            $table->string('city')->nullable()->after('shipping_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'role',
                'is_active',
                'avatar',
                'shipping_address',
                'city',
            ]);
        });
    }
};
