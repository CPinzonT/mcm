<?php

namespace App\Providers;

use App\Models\CastigoCase;
use App\Models\Client;
use App\Models\PortfolioDocument;
use App\Policies\CastigoCasePolicy;
use App\Policies\ClientPolicy;
use App\Policies\PortfolioDocumentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(PortfolioDocument::class, PortfolioDocumentPolicy::class);
        Gate::policy(CastigoCase::class, CastigoCasePolicy::class);
    }
}
