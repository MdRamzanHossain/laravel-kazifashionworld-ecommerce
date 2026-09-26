<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginPage extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email'    => 'required|string',
        'password' => 'required|string',
    ];

    public function mount()
    {
        if (Auth::check()) {
            return redirect()->intended('/');
        }
    }

    public function login()
    {
        $this->validate();

        $fieldType = filter_var($this->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$fieldType => $this->email, 'password' => $this->password], $this->remember)) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                $this->addError('email', 'Your account has been deactivated. Please contact support.');
                return;
            }

            session()->regenerate();
            session()->flash('success', "Welcome back, {$user->name}!");

            if ($user->isAdmin() || $user->isManager()) {
                return redirect()->intended('/admin');
            }

            return redirect()->intended('/');
        }

        $this->addError('email', 'Invalid credentials. Please check your email/phone and password.');
    }

    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
