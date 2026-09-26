<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class ProfilePage extends Component
{
    public $name;
    public $email;
    public $phone;
    public $city;
    public $shipping_address;

    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public $activeTab = 'profile'; // 'profile', 'security', 'orders'

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $this->name             = $user->name;
        $this->email            = $user->email;
        $this->phone            = $user->phone;
        $this->city             = $user->city ?? 'inside_dhaka';
        $this->shipping_address = $user->shipping_address;
    }

    public function updateProfile()
    {
        $user = Auth::user();

        $this->validate([
            'name'             => 'required|string|min:3|max:255',
            'email'            => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'            => 'required|string|min:11|max:20',
            'city'             => 'nullable|string',
            'shipping_address' => 'nullable|string',
        ]);

        $user->update([
            'name'             => $this->name,
            'email'            => $this->email,
            'phone'            => $this->phone,
            'city'             => $this->city,
            'shipping_address' => $this->shipping_address,
        ]);

        session()->flash('profile_success', 'Profile details updated successfully!');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password'          => 'required|string',
            'new_password'              => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Current password does not match our records.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        session()->flash('password_success', 'Password updated successfully!');
    }

    public function render()
    {
        $user = Auth::user();
        $orders = $user ? $user->orders()->with('orderItems')->latest()->paginate(5) : collect();

        $totalSpent = $user ? $user->orders()->where('payment_status', 'paid')->sum('grand_total') : 0;
        $totalOrders = $user ? $user->orders()->count() : 0;

        return view('livewire.profile-page', [
            'user'        => $user,
            'orders'      => $orders,
            'totalSpent'  => $totalSpent,
            'totalOrders' => $totalOrders,
        ]);
    }
}
