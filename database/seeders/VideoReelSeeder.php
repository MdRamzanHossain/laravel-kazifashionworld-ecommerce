<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\VideoReel;
use Illuminate\Database\Seeder;

class VideoReelSeeder extends Seeder
{
    public function run(): void
    {
        // Refresh data to match exact requested videos
        VideoReel::truncate();

        $products = Product::all();
        $p1 = $products->get(0);
        $p2 = $products->get(1);
        $p3 = $products->get(2);
        $p4 = $products->get(3);
        $p5 = $products->get(4);
        $p6 = $products->get(5);

        // High quality vertical beauty reel video clips matching user reference
        $reels = [
            [
                'title'                  => 'Mars Cosmetics Oil Blotter Gel Compact (5gm)',
                'overlay_heading'        => 'OIL BLOTTER GEL COMPACT',
                'badge_text'             => '✨ Viral',
                'video_url'              => 'https://admin.beautybooth.com.bd/uploads/video/influencer-maekup-web-1778557796.mp4',
                'poster_image'           => 'https://cms.beautybooth.com.bd/uploads/all/mars-cosmetics-oil-blotter-gel-compact-5gm_63.webp',
                'product_id'             => $p1?->id,
                'custom_category_name'   => 'Make up',
                'custom_product_name'    => 'Mars Cosmetics Oil Blotter Gel Compact (5gm)',
                'custom_price'           => 749,
                'custom_original_price'  => 1000,
                'custom_product_image'   => 'https://cms.beautybooth.com.bd/uploads/all/mars-cosmetics-oil-blotter-gel-compact-5gm_63.webp',
                'sort_order'             => 1,
                'is_active'              => true,
            ],
            [
                'title'                  => 'Koji White 4% Kojic Acid Dark Spot Corrector Soap (200gm)',
                'overlay_heading'        => 'KOJIC ACID DARK SPOT FIX',
                'badge_text'             => '🌸 Best Seller',
                'video_url'              => 'https://admin.beautybooth.com.bd/uploads/video/koji-brand-web-1767152926.mp4',
                'poster_image'           => 'https://cms.beautybooth.com.bd/uploads/all/koji-white-4-kojic-acid-dark-spot-corrector-soap-350_96.webp',
                'product_id'             => $p2?->id,
                'custom_category_name'   => 'Skin Care',
                'custom_product_name'    => 'Koji White 4% Kojic Acid Dark Spot Soap',
                'custom_price'           => 1049,
                'custom_original_price'  => 1300,
                'custom_product_image'   => 'https://cms.beautybooth.com.bd/uploads/all/koji-white-4-kojic-acid-dark-spot-corrector-soap-350_96.webp',
                'sort_order'             => 2,
                'is_active'              => true,
            ],
            [
                'title'                  => 'Blink Portable High Frequency LZ-006A',
                'overlay_heading'        => 'PORTABLE HIGH FREQUENCY SPA',
                'badge_text'             => '👑 Luxury',
                'video_url'              => 'https://admin.beautybooth.com.bd/uploads/video/gulfan-web-1766895605.mp4',
                'poster_image'           => 'https://cms.beautybooth.com.bd/uploads/all/RAWT3EcUJoztXPPNeW7v6eiyDxaADKmqwgI8EGKB.jpg',
                'product_id'             => $p3?->id,
                'custom_category_name'   => 'Accessories',
                'custom_product_name'    => 'Blink Portable High Frequency LZ-006A',
                'custom_price'           => 2399,
                'custom_original_price'  => 3000,
                'custom_product_image'   => 'https://cms.beautybooth.com.bd/uploads/all/RAWT3EcUJoztXPPNeW7v6eiyDxaADKmqwgI8EGKB.jpg',
                'sort_order'             => 3,
                'is_active'              => true,
            ],
            [
                'title'                  => 'Laenita Minimerry Long Wear Cushion #23 Sand (13gm)',
                'overlay_heading'        => 'LONG WEAR AIR CUSHION #23',
                'badge_text'             => '🔥 Trending',
                'video_url'              => 'https://admin.beautybooth.com.bd/uploads/video/makeup-web-1761025429.mp4',
                'poster_image'           => 'https://cms.beautybooth.com.bd/uploads/all/minimerry-laenita-long-wear-cushion-23-sand-13gm_10.webp',
                'product_id'             => $p4?->id,
                'custom_category_name'   => 'Make up',
                'custom_product_name'    => 'Laenita Minimerry Long Wear Cushion #23',
                'custom_price'           => 1093,
                'custom_original_price'  => 1150,
                'custom_product_image'   => 'https://cms.beautybooth.com.bd/uploads/all/minimerry-laenita-long-wear-cushion-23-sand-13gm_10.webp',
                'sort_order'             => 4,
                'is_active'              => true,
            ],
            [
                'title'                  => 'Blink Electric Bath Brush Set Waterproof Silicone Body Brush',
                'overlay_heading'        => 'WATERPROOF BODY SCRUBBER',
                'badge_text'             => '🌿 100% Silicone',
                'video_url'              => 'https://admin.beautybooth.com.bd/uploads/video/sunscreen-web-1778556885.mp4',
                'poster_image'           => 'https://cms.beautybooth.com.bd/uploads/all/blink-electric-bath-brush-set-waterproof-silicone-body-brush--yellow_86.webp',
                'product_id'             => $p5?->id,
                'custom_category_name'   => 'Accessories',
                'custom_product_name'    => 'Blink Electric Bath Brush Set',
                'custom_price'           => 1850,
                'custom_original_price'  => 2600,
                'custom_product_image'   => 'https://cms.beautybooth.com.bd/uploads/all/blink-electric-bath-brush-set-waterproof-silicone-body-brush--yellow_86.webp',
                'sort_order'             => 5,
                'is_active'              => true,
            ],
            [
                'title'                  => 'Anua Niacinamide 10% + TXA 4% Dark Spot Correcting Serum (30ml)',
                'overlay_heading'        => 'ANUA NIACINAMIDE + TXA 4%',
                'badge_text'             => '⚡ K-Beauty Hit',
                'video_url'              => 'https://admin.beautybooth.com.bd/uploads/video/influencer-web-1760855011.mp4',
                'poster_image'           => 'https://cms.beautybooth.com.bd/uploads/all/anua-niacinamide-10--txa-4-dark-spot-correcting-serum-30ml-1_60.webp',
                'product_id'             => $p6?->id,
                'custom_category_name'   => 'Skin Care',
                'custom_product_name'    => 'Anua Niacinamide 10% + TXA 4% Serum',
                'custom_price'           => 1850,
                'custom_original_price'  => 2300,
                'custom_product_image'   => 'https://cms.beautybooth.com.bd/uploads/all/anua-niacinamide-10--txa-4-dark-spot-correcting-serum-30ml-1_60.webp',
                'sort_order'             => 6,
                'is_active'              => true,
            ],
            [
                'title'                  => 'Medicube Collagen Night Wrapping Mask (75ml)',
                'overlay_heading'        => 'MEDICUBE NIGHT COLLAGEN MASK',
                'badge_text'             => '✨ Glass Skin',
                'video_url'              => 'https://admin.beautybooth.com.bd/uploads/video/dfd-edfsds-web-1760593721.mp4',
                'poster_image'           => 'https://cms.beautybooth.com.bd/uploads/all/medicube-collagen-night-wrapping-mask-75ml_32.webp',
                'product_id'             => $p1?->id,
                'custom_category_name'   => 'Skin Care',
                'custom_product_name'    => 'Medicube Collagen Night Wrapping Mask',
                'custom_price'           => 1799,
                'custom_original_price'  => 2300,
                'custom_product_image'   => 'https://cms.beautybooth.com.bd/uploads/all/medicube-collagen-night-wrapping-mask-75ml_32.webp',
                'sort_order'             => 7,
                'is_active'              => true,
            ],
            [
                'title'                  => 'Blink LR-0917 Ultrasonic Skin Scrubber #White',
                'overlay_heading'        => 'ULTRASONIC PORE SCRUBBER',
                'badge_text'             => '💎 Deep Clean',
                'video_url'              => 'https://admin.beautybooth.com.bd/uploads/video/dfd-dfdf-1756882823.mp4',
                'poster_image'           => 'https://cms.beautybooth.com.bd/uploads/all/blink-lr-0917-ultrasonic-skin-scrubber---white_95.webp',
                'product_id'             => $p2?->id,
                'custom_category_name'   => 'Accessories',
                'custom_product_name'    => 'Blink LR-0917 Ultrasonic Skin Scrubber',
                'custom_price'           => 1249,
                'custom_original_price'  => 1750,
                'custom_product_image'   => 'https://cms.beautybooth.com.bd/uploads/all/blink-lr-0917-ultrasonic-skin-scrubber---white_95.webp',
                'sort_order'             => 8,
                'is_active'              => true,
            ],
        ];

        foreach ($reels as $data) {
            VideoReel::create($data);
        }
    }
}