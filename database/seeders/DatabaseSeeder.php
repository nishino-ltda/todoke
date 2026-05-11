<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Addon;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users for all types if they don't exist
        $users = [
            'admin' => [
                'name' => 'Admin User',
                'email' => 'admin@todoke.test',
                'type' => 'admin',
                'password' => Hash::make('password123'),
                'created_at' => now()->subDays(7),
            ],
            'courier' => [
                'name' => 'Test Courier',
                'email' => 'courier@todoke.test',
                'type' => 'courier',
                'password' => Hash::make('password123'),
                'created_at' => now()->subDays(2),
            ],
            'partner' => [
                'name' => 'Restaurant Partner',
                'email' => 'partner@todoke.test',
                'type' => 'partner',
                'password' => Hash::make('password123'),
                'created_at' => now()->subDays(7),
            ],
            'customer' => [
                'name' => 'Test Customer',
                'email' => 'customer@todoke.test',
                'type' => 'customer',
                'password' => Hash::make('password123'),
                'created_at' => now()->subDays(30),
            ],
            'support' => [
                'name' => 'Support Agent',
                'email' => 'support@todoke.test',
                'type' => 'admin',
                'password' => Hash::make('password123'),
                'created_at' => now()->subDays(7),
            ],
            'locked' => [
                'name' => 'Locked User',
                'email' => 'locked@todoke.test',
                'type' => 'customer',
                'password' => Hash::make('password123'),
                'locked_at' => now(),
                'failed_attempts' => 5,
                'created_at' => now()->subDays(30),
            ]
        ];

        foreach ($users as $type => $data) {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                if (isset($data['locked_at'])) {
                    $user = User::factory()->create($data);
                } else {
                    $user = User::factory()->create($data);
                }
            }
        }

        // Add secondary roles for multirole users
        $courier = User::where('email', 'courier@todoke.test')->first();
        if ($courier && !$courier->hasRole('customer')) {
            $courier->addRole('customer');
        }

        $partner = User::where('email', 'partner@todoke.test')->first();
        if ($partner && !$partner->hasRole('customer')) {
            $partner->addRole('customer');
        }

        // Create 3 new users for "New Users" widget testing
        $newUsers = [
            [
                'name' => 'New Customer',
                'email' => 'newcustomer@todoke.test',
                'type' => 'customer',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'created_at' => now(),
            ],
            [
                'name' => 'New Courier',
                'email' => 'newcourier@todoke.test',
                'type' => 'courier',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'created_at' => now(),
            ],
            [
                'name' => 'New Partner',
                'email' => 'newpartner@todoke.test',
                'type' => 'partner',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'created_at' => now(),
            ],
        ];

        foreach ($newUsers as $data) {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                $user = User::factory()->create($data);
                if (in_array($data['type'], ['courier', 'partner'])) {
                    $user->addRole('customer');
                }
            }
        }
        
        // Call the new PartnerProductSeeder for rich data
        $this->call(PartnerProductSeeder::class);
    }
}
