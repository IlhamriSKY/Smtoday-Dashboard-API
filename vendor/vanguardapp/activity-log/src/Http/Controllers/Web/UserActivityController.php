<?php

namespace Vanguard\UserActivity\Http\Controllers\Web;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Vanguard\User;
use Vanguard\UserActivity\Repositories\Activity\ActivityRepository;
use Vanguard\Http\Controllers\Controller;

/**
 * Class ActivityController
 * @package Vanguard\Http\Controllers
 */
class UserActivityController extends Controller
{
    /**
     * @var ActivityRepository
     */
    private $activities;

    /**
     * @param ActivityRepository $activities
     */
    public function __construct(ActivityRepository $activities)
    {
        $this->activities = $activities;
    }

    /**
     * Displays the activity log page for specific user.
     *
     * @param User $user
     * @param Request $request
     * @return Factory|View
     */
    public function index(User $user, Request $request)
    {
        $activities = $this->activities->paginateActivitiesForUser(
            $user->id,
            $perPage = 20,
            $request->search
        );

        return view('user-activity::index', [
            'user' => $user,
            'adminView' => true,
            'activities' => $activities
        ]);
    }

    /**
     * Display user activity log.
     *
     * @param Request $request
     * @return Factory|View
     */
    public function show(Request $request)
    {
        $user = auth()->user();

        $activities = $this->activities->paginateActivitiesForUser(
            $user->id,
            $perPage = 20,
            $request->get('search')
        );

        return view('user-activity::index', compact('activities', 'user'));
    }
}
