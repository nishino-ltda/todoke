<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\NotificationServiceInterface;
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            NotificationServiceInterface::class,
            NotificationService::class
        );
        
        $this->app->bind(
            \App\Repositories\VotingRoundRepositoryInterface::class,
            \App\Repositories\VotingRoundRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
