<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'google_id',
        'facebook_id',
        'password',
        'role',
        'is_active',
        'avatar',
        'shipping_address',
        'city',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->roles()->exists();
    }

    /**
     * Orders placed by this user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Loyalty transactions for this user.
     */
    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /**
     * SMS logs sent to this user.
     */
    public function smsLogs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }

    /**
     * Check if user is an Administrator.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user is a Shop Manager.
     */
    public function isManager(): bool
    {
        return $this->hasRole('order_manager') || $this->hasRole('super_admin');
    }

    /**
     * Check if user is a Customer.
     */
    public function isCustomer(): bool
    {
        return !$this->roles()->exists();
    }

    /**
     * Scope query to admins only.
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope query to managers only.
     */
    public function scopeManagers(Builder $query): Builder
    {
        return $query->where('role', 'manager');
    }

    /**
     * Scope query to customers only.
     */
    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('role', 'customer')->orWhereNull('role');
        });
    }

    /**
     * Scope query to active users only.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
