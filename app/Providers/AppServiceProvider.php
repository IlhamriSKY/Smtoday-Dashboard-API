<?php

namespace Vanguard\Providers;

use Carbon\Carbon;
use Vanguard\Repositories\Country\CountryRepository;
use Vanguard\Repositories\Country\EloquentCountry;
use Vanguard\Repositories\Permission\EloquentPermission;
use Vanguard\Repositories\Permission\PermissionRepository;
use Vanguard\Repositories\Role\EloquentRole;
use Vanguard\Repositories\Role\RoleRepository;
use Vanguard\Repositories\Session\DbSession;
use Vanguard\Repositories\Session\SessionRepository;
use Vanguard\Repositories\User\EloquentUser;
use Vanguard\Repositories\User\UserRepository;

use Vanguard\Repositories\Smtoday\Iklantext\EloquentIklantext;
use Vanguard\Repositories\Smtoday\Iklantext\IklantextRepository;

use Vanguard\Repositories\Smtoday\Iklanimage\EloquentIklanimage;
use Vanguard\Repositories\Smtoday\Iklanimage\IklanimageRepository;

use Vanguard\Repositories\Smtoday\Beritatext\EloquentBeritatext;
use Vanguard\Repositories\Smtoday\Beritatext\BeritatextRepository;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale(config('app.locale'));
        config(['app.name' => setting('app_name')]);
        \Illuminate\Database\Schema\Builder::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UserRepository::class, EloquentUser::class);
        $this->app->singleton(RoleRepository::class, EloquentRole::class);
        $this->app->singleton(PermissionRepository::class, EloquentPermission::class);
        $this->app->singleton(SessionRepository::class, DbSession::class);
        $this->app->singleton(CountryRepository::class, EloquentCountry::class);

        $this->app->singleton(IklantextRepository::class, EloquentIklantext::class);
        $this->app->singleton(IklanimageRepository::class, EloquentIklanimage::class);
        $this->app->singleton(BeritatextRepository::class, EloquentBeritatext::class);

        if ($this->app->environment('local')) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }
    }
}
