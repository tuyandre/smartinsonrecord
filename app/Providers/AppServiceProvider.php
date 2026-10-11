<?php

namespace App\Providers;

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
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // `@can('cows.edit')` / `$user->can('users.view')` resolve against the user's role permissions.
        \Illuminate\Support\Facades\Gate::before(
            fn ($user, string $ability) => $user->hasPermission($ability) ? true : null
        );
    }
}
