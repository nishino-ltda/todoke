<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Addon;
use App\Models\Node;
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
                'password' => Hash::make('password123')
            ],
            'courier' => [
                'name' => 'Test Courier',
                'email' => 'courier@todoke.test',
                'type' => 'courier',
                'password' => Hash::make('password123')
            ],
            'partner' => [
                'name' => 'Restaurant Partner',
                'email' => 'partner@todoke.test',
                'type' => 'partner',
                'password' => Hash::make('password123')
            ],
            'customer' => [
                'name' => 'Test Customer',
                'email' => 'customer@todoke.test',
                'type' => 'customer',
                'password' => Hash::make('password123')
            ],
            'support' => [
                'name' => 'Support Agent',
                'email' => 'support@todoke.test',
                'type' => 'admin',
                'password' => Hash::make('password123')
            ],
            'locked' => [
                'name' => 'Locked User',
                'email' => 'locked@todoke.test',
                'type' => 'customer',
                'password' => Hash::make('password123'),
                'locked_at' => now(),
                'failed_attempts' => 5
            ]
        ];

        foreach ($users as $type => $data) {
            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                $user = User::factory()->create($data);
            }
        }
        
        // Get the partner user for product/addon creation
        $partner = User::where('email', 'partner@todoke.test')->first();
        if (!$partner) {
            throw new \Exception('Partner user not found');
        }

        // Create default region if none exists
        $region = \App\Models\Region::firstOrCreate(
            ['name' => 'Default Region'],
            [
                'partner_id' => $partner->id,
                'polygon' => 'POLYGON((0 0, 1 0, 1 1, 0 1, 0 0))'
            ]
        );
        
        // Get or create the test restaurant node
        $restaurant = Node::firstOrCreate(
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
        
        // Check if products already exist for this restaurant
        $productCount = Product::where('node_id', $restaurant->id)->count();
        if ($productCount < 5) {
            // Create products for the restaurant
            $products = Product::factory()
                ->count(5 - $productCount)
                ->create(['node_id' => $restaurant->id]);
        }
        
        // Get all products for this restaurant
        $products = Product::where('node_id', $restaurant->id)->get();
            
        // Check if addons already exist for this partner
        $addonCount = Addon::where('partner_id', $partner->id)->count();
        if ($addonCount < 10) {
            // Create addons for the partner
            $addons = Addon::factory()
                ->count(10 - $addonCount)
                ->forPartner($partner->id)
                ->create();
        }
        
        // Get all addons for this partner
        $addons = Addon::where('partner_id', $partner->id)->get();
            
        // Associate addons with products if not already associated
        foreach ($products as $product) {
            // Check if product already has addons
            $existingAddonCount = $product->addons()->count();
            
            if ($existingAddonCount == 0 && $addons->count() > 0) {
                // Randomly assign 1-3 addons to each product
                $addonCount = min(rand(1, 3), $addons->count());
                $product->addons()->attach(
                    $addons->random($addonCount)->pluck('id')->toArray()
                );
            }
        }

        // Create restaurant nodes for partners
        $partner = User::where('type', 'partner')->first();
        if ($partner) {
            $restaurants = [
                [
                    'partner_id' => $partner->id,
                    'type' => 'partner',
                    'identifier' => 'tia-mary-corumba',
                    'capacity' => 50,
                    'status' => 'active'
                ],
                [
                    'partner_id' => $partner->id,
                    'type' => 'partner',
                    'identifier' => 'cantina-da-vila',
                    'capacity' => 30,
                    'status' => 'active'
                ],
                [
                    'partner_id' => $partner->id,
                    'type' => 'partner',
                    'identifier' => 'hot-dogs-california',
                    'capacity' => 40,
                    'status' => 'active'
                ],
                [
                    'partner_id' => $partner->id,
                    'type' => 'partner',
                    'identifier' => '便宜的日本料理',
                    'capacity' => 25,
                    'status' => 'active'
                ]
            ];

            foreach ($restaurants as $restaurant) {
                \App\Models\Node::firstOrCreate(
                    ['identifier' => $restaurant['identifier']],
                    array_merge($restaurant, [
                        'region_id' => $region->id,
                        'current_position' => 'POINT(-57.6516 -19.0086)'
                    ])
                );
            }
        }

    }
}
