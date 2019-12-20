<?php

namespace Vanguard\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VerifyInstallation
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
        if (! file_exists(base_path('.env')) && ! $request->is('install*')) {
            return redirect()->to('install');
        }

        if (file_exists(base_path('.env')) && $request->is('install*') && ! $request->is('install/complete')) {
            throw new NotFoundHttpException;
        }

        return $next($request);
    }
}
