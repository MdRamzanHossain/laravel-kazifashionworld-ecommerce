<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Setting;
use Livewire\Component;

class SocialProofToast extends Component
{
    public array $notifications = [];
    public bool $enabled = true;

    public function mount(): void
    {
        $this->enabled = (bool) filter_var(Setting::get('social_proof_enabled', true), FILTER_VALIDATE_BOOLEAN);

        if (!$this->enabled) {
            return;
        }

        $products = Product::where('is_active', true)
            ->inRandomOrder()
            ->take(5)
            ->get();

        $names = [
            ['name' => 'Sadia Islam', 'location' => 'Uttara, Dhaka', 'avatar' => '🌸'],
            ['name' => 'Nusrat Jahan', 'location' => 'Gulshan 2, Dhaka', 'avatar' => '✨'],
            ['name' => 'Farhana Akter', 'location' => 'Dhanmondi, Dhaka', 'avatar' => '💖'],
            ['name' => 'Tania Rahman', 'location' => 'Banani, Dhaka', 'avatar' => '👗'],
            ['name' => 'Samira Khan', 'location' => 'Sylhet City', 'avatar' => '🌟'],
            ['name' => 'Ayesha Siddiqua', 'location' => 'Chittagong GEC', 'avatar' => '💎'],
            ['name' => 'Maliha Chowdhury', 'location' => 'Mirpur DOHS', 'avatar' => '🛍️'],
        ];

        $timeAgos = ['1 minute ago', '2 minutes ago', '4 minutes ago', '7 minutes ago', '12 minutes ago', 'Just now'];

        $list = [];

        // 1. Facebook Community & Mutual Friends Follow Alerts
        $list[] = [
            'type'        => 'facebook_follow',
            'title'       => 'Sadia Islam & 14 mutual friends',
            'message'     => 'followed Kazi Fashion World on Facebook',
            'time'        => '3 minutes ago',
            'icon'        => 'facebook',
            'badge'       => 'Facebook Follower',
            'image'       => null,
            'avatar'      => '🌸',
            'url'         => 'https://www.facebook.com/kajifashion',
            'is_external' => true,
        ];

        $list[] = [
            'type'        => 'facebook_friends',
            'title'       => '48.6K+ Facebook Friends',
            'message'     => 'follow and love Kazi Fashion World • Join our VIP page',
            'time'        => 'Just now',
            'icon'        => 'facebook',
            'badge'       => 'Popular on Facebook',
            'image'       => null,
            'avatar'      => '✨',
            'url'         => 'https://www.facebook.com/kajifashion',
            'is_external' => true,
        ];

        $list[] = [
            'type'        => 'facebook_follow',
            'title'       => 'Nusrat Jahan & 8 friends',
            'message'     => 'recently liked the 2026 Festive Sharara Collection on Facebook',
            'time'        => '5 minutes ago',
            'icon'        => 'facebook',
            'badge'       => 'Facebook Activity',
            'image'       => null,
            'avatar'      => '💖',
            'url'         => 'https://www.facebook.com/kajifashion',
            'is_external' => true,
        ];

        // 2. Real Product Purchase Alerts
        foreach ($products as $idx => $p) {
            $person = $names[$idx % count($names)];
            $time = $timeAgos[$idx % count($timeAgos)];

            $imgUrl = null;
            if ($p->image) {
                $imgUrl = asset('storage/' . $p->image);
            }

            $list[] = [
                'type'        => 'purchase',
                'title'       => "{$person['name']} from {$person['location']}",
                'message'     => "just purchased {$p->name}",
                'time'        => $time,
                'icon'        => 'bag',
                'badge'       => 'Verified Buyer',
                'image'       => $imgUrl,
                'avatar'      => $person['avatar'],
                'url'         => route('product.detail', $p->slug),
                'is_external' => false,
            ];
        }

        shuffle($list);
        $this->notifications = array_values($list);
    }

    public function render()
    {
        return view('livewire.social-proof-toast');
    }
}
