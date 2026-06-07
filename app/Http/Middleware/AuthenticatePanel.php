<?php

namespace App\Http\Middleware;

use Filament\Http\Middleware\Authenticate as FilamentAuthenticate;

class AuthenticatePanel extends FilamentAuthenticate
{
    protected function redirectTo($request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        return url('/admin/login');
    }
}
