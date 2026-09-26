<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Component;

class TrackOrderPage extends Component
{
    public string $orderNumber = '';
    public string $phone = '';
    public ?Order $order = null;
    public bool $searched = false;

    public function mount(?string $orderNumber = null): void
    {
        $inputOrder = $orderNumber ?: request()->query('order_number') ?: request()->query('order');

        if ($inputOrder) {
            $this->orderNumber = trim($inputOrder);
            $this->searchOrder();
        }
    }

    public function searchOrder(): void
    {
        $this->orderNumber = trim($this->orderNumber);
        $this->phone = trim($this->phone);

        if (empty($this->orderNumber) && empty($this->phone)) {
            $this->addError('search', 'Please enter your Order Number or Phone Number.');
            $this->order = null;
            $this->searched = false;
            return;
        }

        $this->resetErrorBag();

        $query = Order::with(['orderItems']);

        if (!empty($this->orderNumber)) {
            $query->where('order_number', $this->orderNumber);
        }

        if (!empty($this->phone)) {
            $query->where('customer_phone', $this->phone);
        }

        $this->order = $query->latest()->first();
        $this->searched = true;

        if (!$this->order) {
            session()->flash('error', 'No order found matching the provided details. Please verify your Order Number or Phone Number.');
        }
    }

    public function getStatusStep(): int
    {
        if (!$this->order) {
            return 0;
        }

        return match (strtolower((string) $this->order->order_status)) {
            'pending'    => 1,
            'processing' => 2,
            'shipped'    => 3,
            'delivered'  => 4,
            'cancelled'  => -1,
            default      => 1,
        };
    }

    public function render()
    {
        return view('livewire.track-order-page', [
            'statusStep' => $this->getStatusStep(),
        ]);
    }
}
