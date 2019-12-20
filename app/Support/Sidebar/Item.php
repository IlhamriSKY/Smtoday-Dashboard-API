<?php

namespace Vanguard\Support\Sidebar;

use Closure;
use Illuminate\Support\Collection;
use Vanguard\User;

class Item
{
    protected $title;
    protected $route;
    protected $href;
    protected $icon;
    protected $activePath;
    protected $permissions;
    protected $children;

    /**
     * Item constructor.
     * @param $title
     */
    public function __construct($title)
    {
        $this->title = $title;
    }

    /**
     * Factory method to easily create a new Item instance
     * with a given title.
     *
     * @param $title
     * @return Item
     */
    public static function create($title)
    {
        return new self($title);
    }

    /**
     * The route to which the rendered item
     * should point to.
     *
     * @param $route
     * @return $this
     */
    public function route($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * An URL to be used if there is no named route
     * defined for the navigation item or if it is an
     * external URL.
     *
     * If this attribute is set it will have higher
     * priority than the $route attribute.
     *
     * @param $href
     * @return $this
     */
    public function href($href)
    {
        $this->href = $href;

        return $this;
    }

    /**
     * Sidebar navigation icon.
     *
     * @param $icon
     * @return $this
     */
    public function icon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * The path which indicates when this navigation
     * item should be marked as active. It can contain
     * wildcard characters.
     *
     * Example:
     *
     * 'users*' (the item will be active whenever a current URL start with "user")
     *
     * @param $path
     * @return $this
     */
    public function active($path)
    {
        $this->activePath = $path;

        return $this;
    }

    /**
     * Active path getter.
     *
     * @return mixed
     */
    public function getActivePath()
    {
        return $this->activePath;
    }

    /**
     * If item has secondary navigation links, this
     * method will return all the URL patterns when
     * this navigation item should be expanded.
     *
     * @return array|null
     */
    public function getExpandedPath()
    {
        if (! $this->children->count()) {
            return null;
        }

        return $this->children->toBase()->map(function (Item $item) {
            return $item->getActivePath();
        })->toArray();
    }

    /**
     * Returns the "href" attribute (the URL) for the navigation item.
     *
     * @return string|null
     */
    public function getHref()
    {
        if ($this->href) {
            return $this->href;
        }

        return $this->route ? route($this->route) : null;
    }

    /**
     * Icon getter.
     *
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Title getter.
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the permissions required for rendering the
     * navigation item.
     *
     * @param string|array $permissions
     * @return $this
     */
    public function permissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Permissions getter.
     *
     * @return mixed
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Checks if item has nested "children" items.
     *
     * @return bool
     */
    public function isDropdown()
    {
        return $this->children && $this->children->count();
    }

    /**
     * Get the collection of nested items.
     *
     * @return mixed
     */
    public function children()
    {
        return $this->children;
    }

    /**
     * Attach an array of children to the item.
     *
     * @param array $children
     * @return $this
     */
    public function addChildren(array $children)
    {
        if (is_null($this->children)) {
            $this->children = new Collection;
        }

        foreach ($children as $child) {
            $this->children->push($child);
        }

        return $this;
    }

    /**
     * Check if the specified user can view the item.
     *
     * @param User $user
     * @return bool|mixed
     */
    public function authorize(User $user)
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
}
