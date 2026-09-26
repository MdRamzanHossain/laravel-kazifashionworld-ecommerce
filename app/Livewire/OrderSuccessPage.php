<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class OrderSuccessPage extends Component
{
    public $order;

    public function mount($orderNumber)
    {
        $this->order = Order::with('orderItems')
            ->where('order_number', $orderNumber)
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.order-success-page');
    }
}