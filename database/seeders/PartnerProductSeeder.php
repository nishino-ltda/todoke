<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Addon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PartnerProductSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            [
                'name' => 'Roberto da Silva',
                'email' => 'bella.italia@todoke.test',
                'business_name' => 'Pizzaria Bella Itália',
                'business_type' => 'restaurant',
                'category' => 'Pizza',
                'products' => [
                    [
                        'name' => 'Pizza de Calabresa',
                        'description' => 'Molho de tomate, mussarela, calabresa fatiada e cebola.',
                        'price' => 45.90,
                        'category' => 'Pizza',
                        'image' => 'pizza1.jpeg'
                    ],
                    [
                        'name' => 'Pizza Margherita',
                        'description' => 'Molho de tomate, mussarela, manjericão fresco e tomate.',
                        'price' => 42.50,
                        'category' => 'Pizza',
                        'image' => 'pizza2.jpg'
                    ],
                    [
                        'name' => 'Pizza Portuguesa',
                        'description' => 'Presunto, ovos, cebola, ervilha e mussarela.',
                        'price' => 48.00,
                        'category' => 'Pizza',
                        'image' => 'pizza3.avif'
                    ],
                ]
            ],
            [
                'name' => 'Carlos Alberto',
                'email' => 'porto.burguer@todoke.test',
                'business_name' => 'Burguer do Porto',
                'business_type' => 'restaurant',
                'category' => 'Burger',
                'products' => [
                    [
                        'name' => 'X-Salada Especial',
                        'description' => 'Pão brioche, blend 160g, queijo prato, alface, tomate e maionese da casa.',
                        'price' => 28.90,
                        'category' => 'Burger',
                        'image' => 'xsalada.jpeg'
                    ],
                    [
                        'name' => 'X-Bacon Crocante',
                        'description' => 'Pão australiano, blend 160g, muito bacon crocante e cheddar.',
                        'price' => 32.50,
                        'category' => 'Burger',
                        'image' => 'xbacon.jpeg'
                    ],
                    [
                        'name' => 'Burguer Artesanal',
                        'description' => 'Blend de costela 180g, cebola caramelizada e queijo brie.',
                        'price' => 38.00,
                        'category' => 'Burger',
                        'image' => 'xsalada2.webp'
                    ],
                ]
            ],
            [
                'name' => 'Ana Beatriz',
                'email' => 'acai.vila@todoke.test',
                'business_name' => 'Açaí da Vila',
                'business_type' => 'restaurant',
                'category' => 'Açaí',
                'products' => [
                    [
                        'name' => 'Açaí 500ml',
                        'description' => 'Açaí puro batido na hora. Escolha seus acompanhamentos.',
                        'price' => 18.00,
                        'category' => 'Açaí',
                        'image' => 'acai1.jpeg'
                    ],
                    [
                        'name' => 'Copo da Felicidade',
                        'description' => 'Açaí, leite ninho, nutella, morango e granola.',
                        'price' => 25.00,
                        'category' => 'Açaí',
                        'image' => 'acai2.jpg'
                    ],
                ]
            ],
            [
                'name' => 'Yuki Tanaka',
                'email' => 'sushi.garden@todoke.test',
                'business_name' => 'Sushi Garden',
                'business_type' => 'restaurant',
                'category' => 'Japonesa',
                'products' => [
                    [
                        'name' => 'Combo 20 Peças',
                        'description' => '10 hossomaki, 5 uramaki e 5 niguiri variados.',
                        'price' => 55.00,
                        'category' => 'Japonesa',
                        'image' => 'sushi.png'
                    ],
                    [
                        'name' => 'Temaki de Salmão',
                        'description' => 'Salmão fresco picado com cebolinha e cream cheese.',
                        'price' => 22.00,
                        'category' => 'Japonesa',
                        'image' => 'temaki.webp'
                    ],
                    [
                        'name' => 'Hot Roll',
                        'description' => '10 unidades de sushi frito com recheio de salmão e tarê.',
                        'price' => 26.50,
                        'category' => 'Japonesa',
                        'image' => 'hot roll.jpg'
                    ],
                ]
            ],
            [
                'name' => 'Maria Oliveira',
                'email' => 'cantina.mary@todoke.test',
                'business_name' => 'Cantina da Mary',
                'business_type' => 'restaurant',
                'category' => 'Brasileira',
                'products' => [
                    [
                        'name' => 'Feijoada Completa',
                        'description' => 'Acompanha arroz, couve, farofa e laranja. Serve 2 pessoas.',
                        'price' => 65.00,
                        'category' => 'Brasileira',
                        'image' => 'feijoada.avif'
                    ],
                    [
                        'name' => 'Marmitex Executiva',
                        'description' => 'Arroz, feijão, bife acebolado ou frango grelhado e salada.',
                        'price' => 22.00,
                        'category' => 'Brasileira',
                        'image' => 'marmitex executiva.jpeg'
                    ],
                ]
            ],
        ];

        // Ensure storage directory exists
        $storagePath = storage_path('app/public/products');
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        foreach ($partners as $p) {
            $user = User::updateOrCreate(
                ['email' => $p['email']],
                [
                    'name' => $p['name'],
                    'business_name' => $p['business_name'],
                    'slug' => Str::slug($p['business_name']),
                    'business_type' => $p['business_type'],
                    'type' => 'partner',
                    'password' => Hash::make('password123'),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            // Ensure they have the customer role too
            $user->addRole('customer');

            foreach ($p['products'] as $prod) {
                $imageUrl = null;
                if (isset($prod['image'])) {
                    $sourcePath = database_path('seeders/products/' . $prod['image']);
                    $destPath = 'products/' . $prod['image'];
                    
                    if (file_exists($sourcePath)) {
                        copy($sourcePath, storage_path('app/public/' . $destPath));
                        $imageUrl = $destPath;
                    }
                }

                Product::updateOrCreate(
                    [
                        'partner_id' => $user->id,
                        'name' => $prod['name']
                    ],
                    [
                        'description' => $prod['description'],
                        'price' => $prod['price'],
                        'category' => $prod['category'],
                        'imageUrl' => $imageUrl,
                        'status' => 'available',
                    ]
                );
            }

            // Add some generic addons for restaurants
            if ($p['category'] === 'Pizza' || $p['category'] === 'Burger') {
                $addons = [
                    ['name' => 'Bacon Extra', 'price' => 5.00],
                    ['name' => 'Queijo Extra', 'price' => 4.00],
                    ['name' => 'Maionese da Casa', 'price' => 2.50],
                ];

                foreach ($addons as $a) {
                    Addon::updateOrCreate(
                        ['partner_id' => $user->id, 'name' => $a['name']],
                        ['price' => $a['price']]
                    );
                }
            }
        }
    }
}
