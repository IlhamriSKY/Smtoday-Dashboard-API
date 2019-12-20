<?php

namespace Vanguard\Http\Middleware;

use Closure;
use Vanguard\Repositories\User\UserRepository;

class VerifyTwoFactorPhone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next)
    {
        $user = $this->getUser($request);

        if ($user->two_factor_country_code && $user->two_factor_phone) {
            return $next($request);
        }

        abort(404);
    }

    /**
     * @param $request
     * @return mixed
     */
    private function getUser($request)
    {
        if ($userId = $request->get('user')) {
            return app(UserRepository::class)->find($userId);
        }

        return $request->user();
    }
}
