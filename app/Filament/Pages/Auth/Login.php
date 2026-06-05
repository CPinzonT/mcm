<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected string $view = 'filament.pages.auth.login';

    public function getHeading(): string | Htmlable | null
    {
        return 'Bienvenido';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Ingresa con tu cuenta corporativa para acceder al panel MCM.';
    }
}
