<?php

namespace Vanguard\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DatabaseSession
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
        // If we are not using database session driver,
        // just display 404 page
        if (config('session.driver') != 'database') {
            throw new NotFoundHttpException("The entity you are looking for does not exist.");
        }

        return $next($request);
    }
}
