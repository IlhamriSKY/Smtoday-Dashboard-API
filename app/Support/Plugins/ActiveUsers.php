<?php
namespace Vanguard\Support\Plugins;

use Vanguard\Plugins\Plugin;
use Vanguard\Support\Sidebar\Item;

class ActiveUsers extends Plugin
{
    public function sidebar()
    {
        return Item::create(__('Active Users'))
            ->route('active-users')
            ->icon('fas fa-users')
            ->active("active-users*")
            ->permissions('users.activity');
    }
}
