<?php

namespace Vanguard\Support\Plugins\Dashboard;

use Vanguard\Plugins\Plugin;
use Vanguard\Support\Sidebar\Item;

class Dashboard extends Plugin
{
    public function sidebar()
    {
        return Item::create(__('Dashboard'))
            ->route('dashboard')
            ->icon('fas fa-home')
            ->active("/");
    }
}
