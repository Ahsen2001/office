<?php

namespace App\Providers;

use App\Models\ApplicationDocument;
use App\Policies\ApplicationDocumentPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(ApplicationDocument::class, ApplicationDocumentPolicy::class);
        Paginator::useBootstrapFive();
    }
}
