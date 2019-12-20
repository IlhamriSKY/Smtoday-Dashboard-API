<?php

namespace Vanguard\Announcements\Hooks;

use Vanguard\Announcements\Repositories\AnnouncementsRepository;
use Vanguard\Plugins\Contracts\Hook;

class NavbarItemsHook implements Hook
{
    /**
     * @var AnnouncementsRepository
     */
    private $announcements;

    public function __construct(AnnouncementsRepository $announcements)
    {
        $this->announcements = $announcements;
    }

    /**
     * Execute the hook action.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function handle()
    {
        $announcements = $this->announcements->latest(5);
        $announcements->load('creator');

        return view('announcements::partials.navbar.list', compact('announcements'));
    }
}
