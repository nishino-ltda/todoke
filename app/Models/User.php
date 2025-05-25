<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'phone',
        'photoUrl',
        'status',
        'locale',
        'locked_at',
        'failed_attempts'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'failed_attempts'
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
            'password' => 'hashed',
            'type' => 'string',
            'locked_at' => 'datetime',
        ];
    }

    /**
     * Check if account is locked
     */
    public function isLocked(): bool
    {
        return !is_null($this->locked_at);
    }

    /**
     * Lock the user account
     */
    public function lockAccount(): void
    {
        $this->update([
            'locked_at' => now(),
            'failed_attempts' => 0
        ]);
    }

    /**
     * Unlock the user account
     */
    public function unlockAccount(): void
    {
        $this->update([
            'locked_at' => null,
            'failed_attempts' => 0
        ]);
    }

    /**
     * Record a failed login attempt
     */
    public function recordFailedAttempt(): void
    {
        $this->increment('failed_attempts');
    }

    /**
     * Reset failed attempts counter
     */
    public function resetFailedAttempts(): void
    {
        $this->update(['failed_attempts' => 0]);
    }

    /**
     * Get the possible user types
     */
    public static function getTypes(): array
    {
        return ['customer', 'courier', 'partner', 'admin'];
    }

    public function deliveriesAsClient()
    {
        return $this->hasMany(Delivery::class, 'customer_id');
    }

    public function deliveriesAsCourier()
    {
        return $this->hasMany(Delivery::class, 'courier_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'partner_id');
    }
}
