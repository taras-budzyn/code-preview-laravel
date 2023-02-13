<?php

declare(strict_types = 1);

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Http\Request;

class CheckIfAdmin
{
    /**
     * Handle an incoming request.
     *
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (backpack_auth()->guest()) {
            return $this->respondToUnauthorizedRequest($request);
        }

        if (! $this->checkIfUserIsAdmin(backpack_user())) {
            return $this->respondToUnauthorizedRequest($request);
        }

        return $next($request);
    }

    //phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter, SlevomatCodingStandard.TypeHints.ReturnTypeHint, SlevomatCodingStandard.TypeHints.ParameterTypeHint
    /**
     * Checked that the logged in user is an administrator.
     *
     * --------------
     * VERY IMPORTANT
     * --------------
     * If you have both regular users and admins inside the same table,
     * change the contents of this method to check that the logged in user
     * is an admin, and not a regular user.
     *
     * @param [type] $user [description]
     *
     * @return bool [description]
     */
    private function checkIfUserIsAdmin(User $user): bool
    {
        // return ($user->is_admin == 1);
        return true;
    }

    /**
     * Answer to unauthorized access request.
     *
     *
     */
    private function respondToUnauthorizedRequest($request)
    {
        return $request->ajax() || $request->wantsJson()
            ? response(trans('backpack::base.unauthorized'), 401)
            : redirect()->guest(backpack_url('login'));
    }
}
