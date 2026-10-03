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
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'is_full_width')) {
                $table->boolean('is_full_width')->default(false)->after('content');
            }
            if (!Schema::hasColumn('pages', 'hide_header_hero')) {
                $table->boolean('hide_header_hero')->default(false)->after('is_full_width');
            }
            if (!Schema::hasColumn('pages', 'custom_css')) {
                $table->longText('custom_css')->nullable()->after('hide_header_hero');
            }
            if (!Schema::hasColumn('pages', 'custom_js')) {
                $table->longText('custom_js')->nullable()->after('custom_css');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['is_full_width', 'hide_header_hero', 'custom_css', 'custom_js']);
        });
    }
};
