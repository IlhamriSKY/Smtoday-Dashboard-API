<?php

namespace Vanguard\Announcements\Http\Controllers\Web;

use Vanguard\Announcements\Repositories\AnnouncementsRepository;
use Vanguard\Http\Controllers\Controller;

class AnnouncementListController extends Controller
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
     * Displays the plugin index page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $announcements = $this->announcements->paginate(7);
        $announcements->load('creator');

        return view('announcements::list', compact('announcements'));
    }
}
