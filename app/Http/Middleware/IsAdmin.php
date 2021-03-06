<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class IsAdmin
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param Guard $auth
     *
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (!$this->auth->check()) {
            return redirect()->guest('login');
        }

        $id = $this->auth->id();
        if (is_null($id)) {
            return response('Authorized but has no id.', 500);
        }

        $foundUser = User::query()->where('id', $id)->first();
        if (is_null($foundUser)) {
            return response('Authorized and has id but could not find user', 500);
        }

        $isAdmin = false;
        $isAttendee = false;
        foreach ($foundUser->roles as $role) {
            if ($role->name === 'administrator') {
                $isAdmin = true;
            }
            if ($role->name === 'attendee') {
                $isAttendee = true;
            }
        }

        if (!($isAdmin || $isAttendee)) {
            return response('Unauthorized.', 401);
        }

        if (!$isAdmin) {
            return Redirect::route('showUserHome');
        }

        return $next($request);
    }
}