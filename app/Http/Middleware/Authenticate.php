<?php

declare(strict_types = 1);

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * //phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint, SlevomatCodingStandard.TypeHints.ParameterTypeHint
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
