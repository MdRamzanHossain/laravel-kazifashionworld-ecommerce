<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updateStatus($orderId, $newStatus)
    {
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        if (!in_array($newStatus, $validStatuses)) {
            session()->flash('error', 'Invalid order status selected.');
            return;
        }

        $order = Order::findOrFail($orderId);
        $order->update([
            'order_status' => $newStatus,
        ]);

        session()->flash('success', "Order #{$order->order_number} status updated to " . ucfirst($newStatus) . ".");
    }

    public function render()
    {
        $orders = Order::with('orderItems')
            ->when($this->search, function ($query) {
                $query->where('order_number', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_phone', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('order_status', $this->statusFilter);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.order-manager', [
            'orders' => $orders,
        ]);
    }
}