<?php

namespace Vanguard\Announcements\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Events\EmailNotificationRequested;
use Vanguard\Announcements\Http\Requests\AnnouncementRequest;
use Vanguard\Announcements\Repositories\AnnouncementsRepository;
use Vanguard\Announcements\Transformers\AnnouncementTransformer;
use Vanguard\Http\Controllers\Api\ApiController;

/**
 * Class AnnouncementsController
 * @package Vanguard\Announcements\Http\Controllers\Web
 */
class AnnouncementsController extends ApiController
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

        $this->middleware('permission:announcements.manage')->except('index', 'show');
    }

    /**
     * Displays the plugin index page.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {
        $this->validate($request, ['per_page' => 'numeric|max:50']);

        return $this->respondWithPagination(
            $this->announcements->paginate($request->per_page ?? 10),
            new AnnouncementTransformer
        );
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

        return $this->setStatusCode(201)
            ->respondWithItem($announcement, new AnnouncementTransformer);
    }

    /**
     * Renders "view announcement" page.
     *
     * @param Announcement $announcement
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Announcement $announcement)
    {
        return $this->respondWithItem($announcement, new AnnouncementTransformer);
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
        $announcement = $this->announcements->update(
            $announcement,
            $request->title,
            $request->body
        );

        return $this->respondWithItem($announcement, new AnnouncementTransformer);
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

        return $this->respondWithSuccess();
    }
}
