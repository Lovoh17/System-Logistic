<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = auth()->user();
        
        if ($user->hasRole('cajero')) {
            return new RedirectResponse(url('/ventas'));
        }
        
        return new RedirectResponse(url('/admin'));
    }
}