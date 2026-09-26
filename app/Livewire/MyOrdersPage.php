<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class MyOrdersPage extends Component
{
    use WithPagination;

    public function render()
    {
        // Fetch orders by user_id or logged-in user's phone number
        $orders = Order::where(function ($query) {
                if (auth()->check()) {
                    $query->where('user_id', auth()->id())
                          ->orWhere('customer_phone', auth()->user()->phone ?? null);
                }
            })
            ->with('orderItems')
            ->latest()
            ->paginate(5);

        return view('livewire.my-orders-page', [
            'orders' => $orders,
        ]);
    }
}