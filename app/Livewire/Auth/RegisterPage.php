<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class RegisterPage extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $password = '';
    public $password_confirmation = '';

    protected $rules = [
        'name'                  => 'required|string|min:3|max:255',
        'email'                 => 'required|email|max:255|unique:users,email',
        'phone'                 => 'required|string|min:11|max:20',
        'password'              => 'required|string|min:6|confirmed',
    ];

    public function mount()
    {
        if (Auth::check()) {
            return redirect()->intended('/');
        }
    }

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name'      => $this->name,
            'email'     => $this->email,
            'phone'     => $this->phone,
            'role'      => 'customer',
            'is_active' => true,
            'password'  => Hash::make($this->password),
        ]);

        Auth::login($user);
        session()->regenerate();
        session()->flash('success', "Account created successfully! Welcome to BeautyBooth, {$user->name}.");

        return redirect()->intended('/');
    }

    public function render()
    {
        return view('livewire.auth.register-page');
    }
}
