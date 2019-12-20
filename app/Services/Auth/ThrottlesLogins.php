<?php

namespace Vanguard\Services\Auth;

use Illuminate\Foundation\Auth\ThrottlesLogins as ThrottlesLoginsBase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait ThrottlesLogins
{
    use ThrottlesLoginsBase;

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Determine how many retries are left for the user.
     *
     * @param Request $request
     * @return int
     */
    protected function retriesLeft(Request $request)
    {
        $attempts = $this->limiter()->attempts(
            $this->throttleKey($request)
        );

        return $this->maxAttempts() - $attempts + 1;
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        return redirect('login')
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([
                $this->username() => $this->getLockoutErrorMessage($seconds),
            ]);
    }

    /**
     * Get the login lockout error message.
     *
     * @param  int  $seconds
     * @return string
     */
    protected function getLockoutErrorMessage($seconds)
    {
        return trans('auth.throttle', ['seconds' => $seconds]);
    }

    /** @inheritDoc */
    protected function maxAttempts()
    {
        return setting('throttle_attempts', 5);
    }

    /** @inheritDoc */
    protected function decayMinutes()
    {
        $lockout = (int) setting('throttle_lockout_time');

        return $lockout <= 1 ? 1 : $lockout;
    }
}
