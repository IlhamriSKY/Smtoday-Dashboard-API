<?php

namespace Vanguard\Plugins;

use Illuminate\Support\ServiceProvider;

abstract class VanguardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $instances = [];

        foreach ($this->plugins() as $plugin) {
            $instances[$plugin] = $this->app->register($plugin);
        }

        Vanguard::plugins($instances);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $widgets = collect($this->widgets())->map(function ($class) {
            return $this->app->make($class);
        })->toArray();

        Vanguard::widgets($widgets);

        \Blade::directive('hook', function ($name) {
            return "<?php if (\Vanguard\Plugins\Vanguard::hasHook($name)) { 
                collect(\Vanguard\Plugins\Vanguard::getHookHandlers($name))
                    ->each(function (\$hook) {
                        echo resolve(\$hook)->handle();
                    });
            } ?>";
        });
    }

    /**
     * Dashboard widgets.
     *
     * @return array
     */
    abstract protected function widgets();

    /**
     * List of registered plugins.
     *
     * @return array
     */
    abstract protected function plugins();
}
