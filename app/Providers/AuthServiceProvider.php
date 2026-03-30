<?php

namespace App\Providers;

use App\Models\ScoringSheet;
use App\Policies\ScoringSheetPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        ScoringSheet::class => ScoringSheetPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
