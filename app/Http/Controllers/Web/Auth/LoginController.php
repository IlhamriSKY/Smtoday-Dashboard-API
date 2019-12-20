<?php

namespace Vanguard\Http\Controllers\Web\Auth;

use Auth;
use Authy;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Vanguard\Events\User\LoggedIn;
use Vanguard\Events\User\LoggedOut;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Http\Requests\Auth\LoginRequest;
use Vanguard\Repositories\User\UserRepository;
use Vanguard\Services\Auth\ThrottlesLogins;
use Vanguard\Services\Auth\TwoFactor\Contracts\Authenticatable;

class LoginController extends Controller
{
    use ThrottlesLogins;

    /**
     * @var UserRepository
     */
    private $users;

    /**
     * Create a new authentication controller instance.
     * @param UserRepository $users
     */
    public function __construct(UserRepository $users)
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');

        $this->users = $users;
    }

    /**
     * Show the application login form.
     *
     * @return Response
     */
    public function show()
    {
        return view('auth.login', [
            'socialProviders' => config('auth.social.providers')
        ]);
    }

    /**
     * @param LoginRequest $request
     * @return RedirectResponse|Response
     * @throws BindingResolutionException
     */
    public function login(LoginRequest $request)
    {
        // In case that request throttling is enabled, we have to check if user can perform this request.
        // We'll key this by the username and the IP address of the client making these requests into this application.
        $throttles = setting('throttle_enabled');

        //Redirect URL that can be passed as hidden field.
        $to = $request->has('to') ? "?to=" . $request->get('to') : '';

        if ($throttles && $this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }

        $credentials = $request->getCredentials();

        if (! Auth::validate($credentials)) {
            // If the login attempt was unsuccessful we will increment the number of attempts
            // to login and redirect the user back to the login form. Of course, when this
            // user surpasses their maximum number of attempts they will get locked out.
            if ($throttles) {
                $this->incrementLoginAttempts($request);
            }

            return redirect()->to('login' . $to)
                ->withErrors(trans('auth.failed'));
        }

        $user = Auth::getProvider()->retrieveByCredentials($credentials);

        if ($user->isBanned()) {
            return redirect()->to('login' . $to)
                ->withErrors(__('Your account is banned by administrator.'));
        }

        Auth::login($user, setting('remember_me') && $request->get('remember'));

        return $this->authenticated($request, $throttles, $user);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  Request $request
     * @param  bool $throttles
     * @param $user
     * @return RedirectResponse|Response
     */
    protected function authenticated(Request $request, $throttles, $user)
    {
        if ($throttles) {
            $this->clearLoginAttempts($request);
        }

        if (setting('2fa.enabled') && Authy::isEnabled($user)) {
            return $this->logoutAndRedirectToTokenPage($request, $user);
        }

        event(new LoggedIn);

        if ($request->has('to')) {
            return redirect()->to($request->get('to'));
        }

        return redirect()->intended();
    }

    /**
     * @param Request $request
     * @param Authenticatable $user
     * @return RedirectResponse
     */
    protected function logoutAndRedirectToTokenPage(Request $request, Authenticatable $user)
    {
        Auth::logout();

        $request->session()->put('auth.2fa.id', $user->id);

        return redirect()->route('auth.token');
    }

    /**
     * Log the user out of the application.
     *
     * @return Response
     */
    public function logout()
    {
        event(new LoggedOut);

        Auth::logout();

        return redirect('login');
    }
}
