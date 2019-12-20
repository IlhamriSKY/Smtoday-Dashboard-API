<?php

namespace Vanguard\Plugins;

use Illuminate\Support\ServiceProvider;
use Vanguard\Plugins\Console\Commands\GeneratePluginCommand;
use Vanguard\Plugins\Console\Commands\RemovePluginCommand;

class PluginsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GeneratePluginCommand::class,
                RemovePluginCommand::class,
            ]);
        }
    }
}
