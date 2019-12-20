<?php

namespace Vanguard\UserActivity;

use Event;
use Route;
use Vanguard\Plugins\Plugin;
use Vanguard\Support\Sidebar\Item;
use Vanguard\UserActivity\Http\View\Composers\ShowUserComposer;
use Vanguard\UserActivity\Listeners\PermissionEventsSubscriber;
use Vanguard\UserActivity\Listeners\RoleEventsSubscriber;
use Vanguard\UserActivity\Listeners\UserEventsSubscriber;
use Vanguard\UserActivity\Listeners\IklantextEventsSubscriber;
use Vanguard\UserActivity\Listeners\IklanimageEventsSubscriber;
use Vanguard\UserActivity\Listeners\BeritatextEventsSubscriber;
use Vanguard\UserActivity\Repositories\Activity\ActivityRepository;
use Vanguard\UserActivity\Repositories\Activity\EloquentActivity;
use Illuminate\Database\Eloquent\Factory;
use View;

class UserActivity extends Plugin
{
    /**
     * {@inheritDoc}
     */
    public function sidebar()
    {
        return Item::create(__('Activity Log'))
            ->route('activity.index')
            ->icon('fas fa-server')
            ->active("activity*")
            ->permissions('users.activity');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ActivityRepository::class, EloquentActivity::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'user-activity');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'user-activity');
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations')
        ], 'migrations');

        $this->app->booted(function () {
            $this->mapWebRoutes();

            if ($this->app['config']->get('auth.expose_api')) {
                $this->mapApiRoutes();
            }
        });

        $this->attachViewComposers();

        $this->registerEventListeners();

        $this->loadTestingFactories();
    }

    /**
     * Map web plugin related routes.
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'namespace' => 'Vanguard\UserActivity\Http\Controllers\Web',
            'middleware' => 'web',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Map API plugin related routes.
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'namespace' => 'Vanguard\UserActivity\Http\Controllers\Api',
            'middleware' => 'api',
            'prefix' => 'api',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });
    }

    /**
     * Register event subscribers for the plugin.
     */
    private function registerEventListeners()
    {
        Event::subscribe(PermissionEventsSubscriber::class);
        Event::subscribe(RoleEventsSubscriber::class);
        Event::subscribe(UserEventsSubscriber::class);
        Event::subscribe(IklantextEventsSubscriber::class);
        Event::subscribe(IklanimageEventsSubscriber::class);
        Event::subscribe(BeritatextEventsSubscriber::class);
    }

    /**
     * Attach view composers to add necessary data to the view.
     */
    private function attachViewComposers()
    {
        View::composer('user.view', ShowUserComposer::class);
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function loadTestingFactories()
    {
        if (! $this->app->environment('production') && $this->app->runningInConsole()) {
            $this->app->make(Factory::class)->load(__DIR__ . '/../database/factories');
        }
    }
}
