<?php

namespace Vanguard\Announcements\Http\Controllers\Web;

use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Events\EmailNotificationRequested;
use Vanguard\Announcements\Http\Requests\AnnouncementRequest;
use Vanguard\Announcements\Repositories\AnnouncementsRepository;
use Vanguard\Http\Controllers\Controller;

/**
 * Class AnnouncementsController
 * @package Vanguard\Announcements\Http\Controllers\Web
 */
class AnnouncementsController extends Controller
{
    /**
     * @var AnnouncementsRepository
     */
    private $announcements;

    /**
     * AnnouncementsController constructor.
     * @param AnnouncementsRepository $announcements
     */
    public function __construct(AnnouncementsRepository $announcements)
    {
        $this->announcements = $announcements;

        $this->middleware('permission:announcements.manage')->except('show');
    }

    /**
     * Displays the plugin index page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $announcements = $this->announcements->paginate();
        $announcements->load('creator');

        return view('announcements::index', compact('announcements'));
    }

    /**
     * Shows the create announcement form.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('announcements::add-edit', ['edit' => false]);
    }

    /**
     * Stores the announcement inside the database.
     *
     * @param AnnouncementRequest $request
     * @return mixed
     */
    public function store(AnnouncementRequest $request)
    {
        $announcement = $this->announcements->createFor(
            auth()->user(),
            $request->title,
            $request->body
        );

        if ($request->email_notification) {
            EmailNotificationRequested::dispatch($announcement);
        }

        return redirect()->route('announcements.index')
            ->withSuccess(__('Announcement created successfully.'));
    }

    /**
     * Renders "view announcement" page.
     *
     * @param Announcement $announcement
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Announcement $announcement)
    {
        return view('announcements::show', compact('announcement'));
    }

    /**
     * Renders the form for editing the announcement.
     *
     * @param Announcement $announcement
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Announcement $announcement)
    {
        return view('announcements::add-edit', [
            'edit' => true,
            'announcement' => $announcement
        ]);
    }

    /**
     * Updates announcement details.
     *
     * @param Announcement $announcement
     * @param AnnouncementRequest $request
     * @return mixed
     */
    public function update(Announcement $announcement, AnnouncementRequest $request)
    {
        $this->announcements->update(
            $announcement,
            $request->title,
            $request->body
        );

        return redirect()->route('announcements.index')
            ->withSuccess(__('Announcement updated successfully.'));
    }

    /**
     * Removes announcement from the system.
     *
     * @param Announcement $announcement
     * @return mixed
     */
    public function destroy(Announcement $announcement)
    {
        $this->announcements->delete($announcement);

        return redirect()->route('announcements.index')
            ->withSuccess(__('Announcement deleted successfully.'));
    }
}
