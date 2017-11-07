<?php

namespace Hypersistence\Auth;

use Illuminate\Support\Facades\Auth;
use Hypersistence\Auth\HypersistenceUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class HypersistenceAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::provider('hypersistence', function ($app, array $config) {
            // Return an instance of Illuminate\Contracts\Auth\UserProvider...
            return new HypersistenceUserProvider($app['hash'], $config['model']);
        });
    }
}