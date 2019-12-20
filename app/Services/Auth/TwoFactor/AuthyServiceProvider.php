<?php

namespace Vanguard\Services\Auth\TwoFactor;

use Illuminate\Support\ServiceProvider;

class AuthyServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('authy', Authy::class);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return ['authy'];
    }

}