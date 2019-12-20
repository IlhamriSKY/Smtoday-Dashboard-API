<?php

namespace Vanguard\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory;

class UseApiGuard
{
    /**
     * The Auth Factory implementation.
     *
     * @var Factory
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Factory  $auth
     */
    public function __construct(Factory $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->auth->shouldUse('api');
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
