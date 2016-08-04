<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {

        // Override auth is has app secret
        // This is for creating new users from node
        if($request->header('api-secret') == env("API_SECRET")) {
            return $next($request);
        }

        // If not authorised
        if ($this->auth->guard($guard)->guest()) {
            return response()->json(['error' => [
                    'message' => "Hold it cowboy! Get outta here ya hear!",
                    'status_code' => 401,
                    'path' => $request->path(),
                    'method' => $request->method()
                ]],401);
        }

        return $next($request);
    }
}
