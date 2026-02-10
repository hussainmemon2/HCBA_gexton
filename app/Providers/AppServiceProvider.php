<?php

namespace App\Providers;

use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\Vendor;
use App\Models\WelfareClaim;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\RateLimiter;
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
        Paginator::useBootstrap();
        RateLimiter::for('api', function ($request) {
        return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
    });
        Relation::morphMap([
            'vendor'    => Vendor::class,
            'committee' => Committee::class,
            'welfare'   => WelfareClaim::class,
            'member'    => CommitteeMember::class,
        ]);
    }
}
