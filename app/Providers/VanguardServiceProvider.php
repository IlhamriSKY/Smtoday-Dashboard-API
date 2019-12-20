<?php

namespace Vanguard\Providers;

use Vanguard\Plugins\VanguardServiceProvider as BaseVanguardServiceProvider;
use Vanguard\Support\Plugins\Dashboard\Widgets\BannedUsers;
use Vanguard\Support\Plugins\Dashboard\Widgets\LatestRegistrations;
use Vanguard\Support\Plugins\Dashboard\Widgets\NewUsers;
use Vanguard\Support\Plugins\Dashboard\Widgets\RegistrationHistory;
use Vanguard\Support\Plugins\Dashboard\Widgets\TotalUsers;
use Vanguard\Support\Plugins\Dashboard\Widgets\UnconfirmedUsers;
use Vanguard\Support\Plugins\Dashboard\Widgets\UserActions;
use Vanguard\UserActivity\Widgets\ActivityWidget;

// Smtoday
use Vanguard\Support\Plugins\Dashboard\Widgets\Smtoday\IklanHistory;
// Iklantexts
use Vanguard\Support\Plugins\Dashboard\Widgets\Smtoday\Iklantext\TotalIklantexts;
use Vanguard\Support\Plugins\Dashboard\Widgets\Smtoday\Iklantext\UnSendIklantexts;
use Vanguard\Support\Plugins\Dashboard\Widgets\Smtoday\Iklantext\SendIklantexts;
use Vanguard\Support\Plugins\Dashboard\Widgets\Smtoday\Iklantext\BannedIklantexts;
// Iklantexts
use Vanguard\Support\Plugins\Dashboard\Widgets\Smtoday\Iklanimage\TotalIklanimages;
use Vanguard\Support\Plugins\Dashboard\Widgets\Smtoday\Iklanimage\UnSendIklanimages;
use Vanguard\Support\Plugins\Dashboard\Widgets\Smtoday\Iklanimage\SendIklanimages;
use Vanguard\Support\Plugins\Dashboard\Widgets\Smtoday\Iklanimage\BannedIklanimages;

class VanguardServiceProvider extends BaseVanguardServiceProvider
{
    /**
     * List of registered plugins.
     *
     * @return array
     */
    protected function plugins()
    {
        return [
            \Vanguard\Support\Plugins\Dashboard\Dashboard::class,
            \Vanguard\Support\Plugins\Smtoday\Iklan\Iklantextandimage::class,
            \Vanguard\Support\Plugins\Smtoday\Berita\Berita::class,
            \Vanguard\Support\Plugins\Users::class,
            \Vanguard\Support\Plugins\ActiveUsers::class,
            \Vanguard\UserActivity\UserActivity::class,
            \Vanguard\Support\Plugins\RolesAndPermissions::class,
            \Vanguard\Support\Plugins\Settings::class,
            \Vanguard\Announcements\Announcements::class,
        ];
    }

    /**
     * Dashboard widgets.
     *
     * @return array
     */
    protected function widgets()
    {
        return [
            UserActions::class,
            TotalUsers::class,
            NewUsers::class,
            BannedUsers::class,
            UnconfirmedUsers::class,
            TotalIklantexts::class,
            UnSendIklantexts::class,
            SendIklantexts::class,
            BannedIklantexts::class,
            TotalIklanimages::class,
            UnSendIklanimages::class,
            SendIklanimages::class,
            BannedIklanimages::class,
            IklanHistory::class,
            // RegistrationHistory::class,
            LatestRegistrations::class,
            ActivityWidget::class,
        ];
    }
}
