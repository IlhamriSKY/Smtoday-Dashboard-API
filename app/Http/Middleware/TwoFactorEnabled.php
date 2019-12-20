<?php

namespace Vanguard\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TwoFactorEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! setting('2fa.enabled')) {
            throw new NotFoundHttpException;
        }

        return $next($request);
    }
}
