<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestSeeder extends Seeder
{
    public function run()
    {
        // Create test partner user
        $partner = User::firstOrCreate(
            ['email' => 'partner@example.com'],
            [
                'name' => 'Test Partner',
                'password' => Hash::make('password123'),
                'type' => 'partner'
            ]
        );

        // Create default region
        $region = \App\Models\Region::firstOrCreate(
            ['name' => 'Test Region'],
            [
                'partner_id' => $partner->id,
                'polygon' => 'POLYGON((0 0, 1 0, 1 1, 0 1, 0 0))'
            ]
        );

        // Create test restaurant node
        $restaurant = \App\Models\Node::firstOrCreate(
            ['identifier' => 'test-restaurant'],
            [
                'partner_id' => $partner->id,
                'type' => 'partner',
                'capacity' => 50,
                'status' => 'active',
                'region_id' => $region->id,
                'current_position' => 'POINT(-57.6516 -19.0086)'
            ]
        );

        // Create test products
        $products = [
            [
                'name' => 'Margherita Pizza',
                'description' => 'Classic pizza with tomato sauce, mozzarella and basil',
                'price' => 12.99,
                'node_id' => $restaurant->id,
                'partner_id' => $partner->id,
                'status' => 'available',
                'category' => 'Pizza'
            ],
            [
                'name' => 'Caesar Salad',
                'description' => 'Fresh romaine lettuce with Caesar dressing, croutons and parmesan',
                'price' => 8.99,
                'node_id' => $restaurant->id,
                'partner_id' => $partner->id,
                'status' => 'available',
                'category' => 'Salad'
            ],
            [
                'name' => 'Pepperoni Pizza',
                'description' => 'Pizza with tomato sauce, mozzarella and pepperoni',
                'price' => 14.99,
                'node_id' => $restaurant->id,
                'partner_id' => $partner->id,
                'status' => 'available',
                'category' => 'Pizza'
            ]
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
