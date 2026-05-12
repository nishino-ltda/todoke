<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements CanResetPassword
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
        'cpf',
        'photoUrl',
        'status',
        'locale',
        'locked_at',
        'failed_attempts',
        'license_number',
        'vehicle_type',
        'license_file_path',
        'business_name',
        'business_type',
        'tax_id',
        'address',
        'business_document_path'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['all_roles'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'failed_attempts',
        'license_file_path',
        'business_document_path'
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
            'vehicle_type' => 'string',
            'business_type' => 'string'
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

    public function roleRecords(): HasMany
    {
        return $this->hasMany(RoleUser::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->type === $role || $this->roleRecords()->where('role', $role)->exists();
    }

    public function allRoles(): array
    {
        $roles = [$this->type];
        foreach ($this->roleRecords as $record) {
            $roles[] = $record->role;
        }
        return array_unique($roles);
    }

    /**
     * Accessor for all_roles attribute.
     */
    public function getAllRolesAttribute(): array
    {
        return $this->allRoles();
    }

    public function addRole(string $role): void
    {
        if ($this->type !== $role) {
            $this->roleRecords()->firstOrCreate(['role' => $role]);
        }
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
}
