<?php

namespace Vanguard\Plugins;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;

abstract class Widget
{
    /**
     * The number of columns that widget should take on the dashboard.
     * Possible values are from 1 to 12. Set the width to NULL
     * if you don't want to disable the width class.
     *
     * @var string|null
     */
    public $width = null;

    /**
     * Permissions required for for viewing the widget.
     * @var string|array
     */
    protected $permissions;

    /**
     * Renders the widget HTML.
     *
     * @return mixed
     */
    abstract public function render();

    /**
     * Authorize the request to verify if a user should be able to
     * see the widget on the dashboard.
     *
     * @param Authenticatable $user
     * @return bool
     */
    public function authorize(Authenticatable $user)
    {
        if (is_object($this->permissions) && $this->permissions instanceof Closure) {
            return call_user_func($this->permissions, $user);
        }

        foreach ((array) $this->permissions as $permission) {
            if (! $user->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Set permissions required for viewing the widget on the dashboard.
     *
     * @param $permissions
     * @return $this
     */
    public function permissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Custom scripts that are required by this widget to work
     * and that should be rendered on the dashboard only.
     *
     * @return null
     */
    public function scripts()
    {
        return null;
    }
}
