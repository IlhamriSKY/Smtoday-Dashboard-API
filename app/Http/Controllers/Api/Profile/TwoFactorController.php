<?php

namespace Vanguard\Http\Controllers\Api\Profile;

use Authy;
use Vanguard\Events\User\TwoFactorDisabled;
use Vanguard\Events\User\TwoFactorEnabled;
use Vanguard\Http\Controllers\Api\ApiController;
use Vanguard\Http\Requests\TwoFactor\EnableTwoFactorRequest;
use Vanguard\Http\Requests\TwoFactor\VerifyTwoFactorTokenRequest;
use Vanguard\Transformers\UserTransformer;

/**
 * Class TwoFactorController
 * @package Vanguard\Http\Controllers\Api\Profile
 */
class TwoFactorController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Enable 2FA for specified user.
     * @param EnableTwoFactorRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(EnableTwoFactorRequest $request)
    {
        $user = auth()->user();

        if (Authy::isEnabled($user)) {
            return $this->setStatusCode(422)
                ->respondWithError("2FA is already enabled for this user.");
        }

        $user->setAuthPhoneInformation(
            $request->country_code,
            $request->phone_number
        );

        Authy::register($user);

        $user->save();

        Authy::sendTwoFactorVerificationToken($user);

        return $this->respondWithArray([
            'message' => 'Verification token sent.'
        ]);
    }

    /**
     * Verify provided 2FA token.
     *
     * @param VerifyTwoFactorTokenRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(VerifyTwoFactorTokenRequest $request)
    {
        $user = auth()->user();

        if (! Authy::tokenIsValid($user, $request->token)) {
            return $this->setStatusCode(422)
                ->respondWithError("Invalid 2FA token.");
        }

        $user->setTwoFactorAuthProviderOptions(array_merge(
            $user->getTwoFactorAuthProviderOptions(),
            ['enabled' => true]
        ));

        $user->save();

        event(new TwoFactorEnabled);

        return $this->respondWithItem($user, new UserTransformer);
    }

    /**
     * Disable 2FA for currently authenticated user.
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        $user = auth()->user();

        if (! Authy::isEnabled($user)) {
            return $this->setStatusCode(422)
                ->respondWithError("2FA is not enabled for this user.");
        }

        Authy::delete($user);

        $user->save();

        event(new TwoFactorDisabled);

        return $this->respondWithItem($user, new UserTransformer);
    }
}
