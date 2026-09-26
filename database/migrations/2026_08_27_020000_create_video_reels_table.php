<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_reels', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('overlay_heading')->nullable(); // e.g. "Looking for KOREAN GLASS LIKE SKIN?"
            $table->string('badge_text')->nullable(); // e.g. "Viral", "Trending", "Must Have"
            $table->string('video_file')->nullable();
            $table->text('video_url')->nullable();
            $table->string('poster_image')->nullable();
            
            // Attached Product Relationship & Overrides
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('custom_product_name')->nullable();
            $table->string('custom_category_name')->nullable(); // e.g. "Skin Care", "Accessories", "Make up"
            $table->decimal('custom_price', 10, 2)->nullable();
            $table->decimal('custom_original_price', 10, 2)->nullable();
            $table->string('custom_product_image')->nullable();
            $table->string('custom_product_url')->nullable();

            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_reels');
    }
};