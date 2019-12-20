<?php

namespace Vanguard\Http\Controllers\Api\Profile;

use Vanguard\Events\User\UpdatedProfileDetails;
use Vanguard\Http\Controllers\Api\ApiController;
use Vanguard\Http\Requests\User\UpdateProfileDetailsRequest;
use Vanguard\Http\Requests\User\UpdateProfileLoginDetailsRequest;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\Transformers\UserTransformer;

/**
 * Class DetailsController
 * @package Vanguard\Http\Controllers\Api\Profile
 */
class AuthDetailsController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Updates user profile details.
     * @param UpdateProfileLoginDetailsRequest $request
     * @param UserRepository $users
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProfileLoginDetailsRequest $request, UserRepository $users)
    {
        $user = $request->user();

        $data = $request->only(['email', 'username', 'password']);

        $user = $users->update($user->id, $data);

        return $this->respondWithItem($user, new UserTransformer);
    }
}
