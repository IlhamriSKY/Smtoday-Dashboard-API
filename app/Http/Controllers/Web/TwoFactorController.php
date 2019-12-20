<?php

namespace Vanguard\Http\Controllers\Web;

use Vanguard\Events\User\TwoFactorDisabled;
use Vanguard\Events\User\TwoFactorEnabled;
use Vanguard\Events\User\TwoFactorEnabledByAdmin;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\TwoFactor\DisableTwoFactorRequest;
use Vanguard\Http\Requests\TwoFactor\EnableTwoFactorRequest;
use Vanguard\Http\Requests\TwoFactor\ReSendTwoFactorTokenRequest;
use Vanguard\Http\Requests\TwoFactor\VerifyTwoFactorTokenRequest;
use Authy;
use Vanguard\Repositories\User\UserRepository;

/**
 * Class ProfileController
 * @package Vanguard\Http\Controllers
 */
class TwoFactorController extends Controller
{
    public function __construct(UserRepository $users)
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) use ($users) {
            $user = $request->get('user')
                ? $users->find($request->get('user'))
                : auth()->user();

            return Authy::isEnabled($user) ? abort(404) : $next($request);
        })->only('enable', 'verification', 'resend', 'verify');
    }

    /**
     * Enable 2FA for currently logged user.
     *
     * @param EnableTwoFactorRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enable(EnableTwoFactorRequest $request)
    {
        $user = $request->theUser();

        $user->setAuthPhoneInformation($request->country_code, $request->phone_number);

        Authy::register($user);

        $user->save();

        Authy::sendTwoFactorVerificationToken($user);

        return $user->is(auth()->user())
            ? redirect()->route('two-factor.verification')
            : redirect()->route('two-factor.verification', ['user' => $user->id]);
    }

    /**
     * Show the phone verification page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function verification()
    {
        return view('user.two-factor-verification', [
            'user' => request('user')
        ]);
    }

    /**
     * Re-send phone verification token.
     * @param ReSendTwoFactorTokenRequest $request
     */
    public function resend(ReSendTwoFactorTokenRequest $request)
    {
        Authy::sendTwoFactorVerificationToken($request->theUser());
    }

    /**
     * Verify 2FA token and enable 2FA if token is valid.
     *
     * @param VerifyTwoFactorTokenRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(VerifyTwoFactorTokenRequest $request)
    {
        $user = $request->theUser();

        if (! Authy::tokenIsValid($user, $request->token)) {
            return redirect()->route('two-factor.verification')
                ->withErrors(['token' => 'Invalid 2FA token.']);
        }

        $user->setTwoFactorAuthProviderOptions(array_merge(
            $user->getTwoFactorAuthProviderOptions(),
            ['enabled' => true]
        ));

        $user->save();

        $message = __('Two-Factor Authentication enabled successfully.');

        if ($user->is(auth()->user())) {
            event(new TwoFactorEnabled);

            return redirect()->route('profile')->withSuccess($message);
        }

        event(new TwoFactorEnabledByAdmin($user));

        return redirect()->route('users.edit', $user)->withSuccess($message);
    }

    /**
     * Disable 2FA for currently logged user.
     *
     * @param DisableTwoFactorRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disable(DisableTwoFactorRequest $request)
    {
        $user = $request->theUser();

        if (! Authy::isEnabled($user)) {
            abort(404);
        }

        Authy::delete($user);

        $user->save();

        event(new TwoFactorDisabled);

        return redirect()->back()
            ->withSuccess(__('Two-Factor Authentication disabled successfully.'));
    }
}
