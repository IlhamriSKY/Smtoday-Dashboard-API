<?php

namespace Vanguard\Http\Controllers\Web\Auth;

use Auth;
use Authy;
use Laravel\Socialite\Contracts\User as SocialUser;
use Socialite;
use Vanguard\Events\User\LoggedIn;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\Services\Auth\Social\SocialManager;

class SocialAuthController extends Controller
{
    /**
     * @var UserRepository
     */
    private $users;

    /**
     * @var SocialManager
     */
    private $socialManager;

    public function __construct(UserRepository $users, SocialManager $socialManager)
    {
        $this->middleware('guest');

        $this->users = $users;
        $this->socialManager = $socialManager;
    }

    /**
     * Redirect user to specified provider in order to complete the authentication process.
     *
     * @param $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider($provider)
    {
        if (strtolower($provider) == 'facebook') {
            return Socialite::driver('facebook')->with(['auth_type' => 'rerequest'])->redirect();
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle response authentication provider.
     *
     * @param $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback($provider)
    {
        if (request()->get('error')) {
            return redirect('login')
                ->withErrors(__('Something went wrong during the authentication process. Please try again.'));
        }

        $socialUser = $this->getUserFromProvider($provider);

        $user = $this->users->findBySocialId($provider, $socialUser->getId());

        if (! $user) {
            if (! setting('reg_enabled')) {
                return redirect('login')
                    ->withErrors(__('Only users who already created an account can log in.'));
            }

            if (! $socialUser->getEmail()) {
                return redirect('login')
                    ->withErrors(__('You have to provide your email address.'));
            }

            $user = $this->socialManager->associate($socialUser, $provider);
        }

        return $this->loginAndRedirect($user);
    }

    /**
     * Get user from authentication provider.
     *
     * @param $provider
     * @return SocialUser
     */
    private function getUserFromProvider($provider)
    {
        return Socialite::driver($provider)->user();
    }

    /**
     * Log provided user in and redirect him to intended page.
     *
     * @param $user
     * @return \Illuminate\Http\RedirectResponse
     */
    private function loginAndRedirect($user)
    {
        if ($user->isBanned()) {
            return redirect()->to('login')
                ->withErrors(__('Your account is banned by administrator.'));
        }

        if (setting('2fa.enabled') && Authy::isEnabled($user)) {
            session()->put('auth.2fa.id', $user->id);
            return redirect()->route('auth.token');
        }

        Auth::login($user);

        event(new LoggedIn);

        return redirect()->intended('/');
    }
}
