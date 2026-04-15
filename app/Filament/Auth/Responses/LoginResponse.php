<?php

namespace App\Filament\Auth\Responses;

use App\Filament\Pages\StrategicDashboard;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        return redirect()->intended(StrategicDashboard::getUrl(panel: 'admin') ?? Filament::getUrl());
    }
}
