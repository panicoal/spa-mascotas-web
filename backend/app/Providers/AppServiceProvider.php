<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Pet;

use App\Policies\PetPolicy;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [

        Pet::class => PetPolicy::class,
    ];
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
        //
    }
}
