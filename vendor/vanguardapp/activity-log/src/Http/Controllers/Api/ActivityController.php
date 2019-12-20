<?php

namespace Vanguard\UserActivity\Http\Controllers\Api;

use Vanguard\UserActivity\Http\Requests\Activity\GetActivitiesRequest;
use Vanguard\UserActivity\Repositories\Activity\ActivityRepository;
use Vanguard\Http\Controllers\Api\ApiController;
use Vanguard\UserActivity\Transformers\ActivityTransformer;

/**
 * Class ActivityController
 * @package Vanguard\Http\Controllers\Api
 */
class ActivityController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:users.activity');
    }

    /**
     * Paginate user activities.
     * @param GetActivitiesRequest $request
     * @param ActivityRepository $activities
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(GetActivitiesRequest $request, ActivityRepository $activities)
    {
        $result = $activities->paginateActivities(
            $request->per_page ?: 20,
            $request->search
        );

        return $this->respondWithPagination(
            $result,
            new ActivityTransformer
        );
    }
}
