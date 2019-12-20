<?php

namespace Vanguard\Plugins;

use Illuminate\Contracts\Auth\Authenticatable;

class Vanguard
{
    /**
     * All of the registered Vanguard plugins.
     *
     * @var array
     */
    public static $plugins = [];

    /**
     * All of the registered Vanguard dashboard widgets.
     *
     * @var array
     */
    public static $widgets = [];

    /**
     * All of the registered Vanguard scripts.
     *
     * @var array
     */
    public static $scripts = [];

    /**
     * All of the registered Vanguard styles.
     *
     * @var array
     */
    public static $styles = [];

    /**
     * All registered Vanguard view hooks.
     *
     * @var array
     */
    public static $hooks = [];

    /**
     * Register a new view hook.
     *
     * @param $name
     * @param $handler
     */
    public static function hook($name, $handler)
    {
        self::$hooks[$name][] = $handler;
    }

    /**
     * Check if there are handlers registered for the
     * provided hook name.
     *
     * @param $name
     * @return bool
     */
    public static function hasHook($name)
    {
        return isset(self::$hooks[$name]);
    }

    /**
     * Get all handlers for a given hook name.
     *
     * @param $name
     * @return mixed
     */
    public static function getHookHandlers($name)
    {
        return data_get(self::$hooks, $name);
    }

    /**
     * Register the given plugins.
     *
     * @param array $plugins
     */
    public static function plugins(array $plugins)
    {
        self::$plugins = array_merge(self::$plugins, $plugins);
    }

    /**
     * Get the list of registered plugins.
     *
     * @return array
     */
    public static function availablePlugins()
    {
        return self::$plugins;
    }

    /**
     * Register the list of given dashboard widgets.
     *
     * @param array $widgets
     */
    public static function widgets(array $widgets)
    {
        self::$widgets = array_merge(self::$widgets, $widgets);
    }

    /**
     * Get the list of widgets available for the provided user.
     *
     * @param Authenticatable $user
     * @return array
     */
    public static function availableWidgets(Authenticatable $user)
    {
        return collect(self::$widgets)->filter->authorize($user)->values();
    }
}
