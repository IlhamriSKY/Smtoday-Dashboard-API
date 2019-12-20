<?php

namespace Vanguard\Http\Controllers\Api\Users;

use Vanguard\Http\Controllers\Api\ApiController;
use Vanguard\Http\Requests\Activity\GetActivitiesRequest;
use Vanguard\Repositories\Activity\ActivityRepository;
use Vanguard\Transformers\ActivityTransformer;
use Vanguard\User;

/**
 * Class ActivityController
 * @package Vanguard\Http\Controllers\Api\Users
 */
class ActivityController extends ApiController
{
    /**
     * @var ActivityRepository
     */
    private $activities;

    public function __construct(ActivityRepository $activities)
    {
        $this->middleware('auth');
        $this->middleware('permission:users.activity');

        $this->activities = $activities;
    }

    /**
     * Get activities for specified user.
     * @param User $user
     * @param GetActivitiesRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(User $user, GetActivitiesRequest $request)
    {
        $activities = $this->activities->paginateActivitiesForUser(
            $user->id,
            $request->per_page ?: 20,
            $request->search
        );

        return $this->respondWithPagination(
            $activities,
            new ActivityTransformer
        );
    }
}
