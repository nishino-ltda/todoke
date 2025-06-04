<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Models\Node;
use App\Models\Product;
use App\Policies\NodePolicy;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Node::class => NodePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define admin ability
        Gate::define('admin', function ($user) {
            return $user->tokenCan('admin');
        });
    }
}
