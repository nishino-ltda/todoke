<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $password = 'Password123';
        
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make($password),
            'remember_token' => Str::random(10),
            'type' => fake()->randomElement(['customer', 'courier', 'partner']),
            'status' => 'active'
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'admin',
            'email' => 'admin@todoke.com',
            'password' => Hash::make('Admin123')
        ]);
    }

    public function courier(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'courier',
            'email' => 'courier@example.com',
            'password' => Hash::make('Password123')
        ]);
    }

    public function partner(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'partner',
            'email' => 'partner_' . uniqid() . '@example.com',
            'password' => Hash::make('Password123'),
            'status' => 'active'
        ]);
    }
}
