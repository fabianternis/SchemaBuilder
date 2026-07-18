<?php

namespace App\Providers;

use App\Services\Socialite\HackClubProvider;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;

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
     *
     * Registers the custom HackClub OAuth 2.0 driver with Socialite so that
     * `Socialite::driver('hackclub')` resolves to HackClubProvider.
     */
    public function boot(): void
    {
        $this->app->make(SocialiteFactory::class)->extend('hackclub', function ($app) {
            $config = $app['config']['services.hackclub'];

            /** @var \Laravel\Socialite\SocialiteManager $socialite */
            $socialite = $app->make(SocialiteFactory::class);

            return $socialite->buildProvider(HackClubProvider::class, $config);
        });
    }
}
