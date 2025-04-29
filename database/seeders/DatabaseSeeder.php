<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Addon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a partner user if it doesn't exist
        $partner = User::where('email', 'partner@example.com')->first();
        if (!$partner) {
            $partner = User::factory()->create([
                'name' => 'Restaurant Partner',
                'email' => 'partner@example.com',
                'type' => 'partner',
            ]);
        }
        
        // Create a customer user if it doesn't exist
        $customer = User::where('email', 'customer@example.com')->first();
        if (!$customer) {
            $customer = User::factory()->create([
                'name' => 'Test Customer',
                'email' => 'customer@example.com',
                'type' => 'customer',
            ]);
        }
        
        // Check if products already exist for this partner
        $productCount = Product::where('partner_id', $partner->id)->count();
        if ($productCount < 5) {
            // Create products for the partner
            $products = Product::factory()
                ->count(5 - $productCount)
                ->forPartner($partner->id)
                ->create();
        }
        
        // Get all products for this partner
        $products = Product::where('partner_id', $partner->id)->get();
            
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
    }
}
