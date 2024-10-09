<?php

namespace App\Features\SyncUserAttributes\Application\Providers;

use App\Features\SyncUserAttributes\Application\Contracts\ApiLimitsInterface;
use App\Features\SyncUserAttributes\Application\Service\SyncUserAttributesInteractor;
use App\Features\SyncUserAttributes\Application\Service\SyncUserAttributesInterface;
use App\Features\SyncUserAttributes\Infrastructure\Repository\Redis\ApiLimitsRepository;
use Illuminate\Support\ServiceProvider;

class SyncUserAttributeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ApiLimitsInterface::class, ApiLimitsRepository::class);
        $this->app->bind(SyncUserAttributesInterface::class, SyncUserAttributesInteractor::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
