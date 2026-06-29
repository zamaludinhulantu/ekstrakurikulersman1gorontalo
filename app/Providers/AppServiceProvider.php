<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Assessment;
use App\Models\Extracurricular;
use App\Models\Registration;
use App\Models\Schedule;
use App\Policies\AnnouncementPolicy;
use App\Policies\AssessmentPolicy;
use App\Policies\ExtracurricularPolicy;
use App\Policies\RegistrationPolicy;
use App\Policies\SchedulePolicy;
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
        $sharedHostingPublicPath = base_path('../public_html');

        if (is_dir($sharedHostingPublicPath)) {
            $this->app->usePublicPath($sharedHostingPublicPath);
        }

        Paginator::useBootstrapFive();

        Gate::policy(Extracurricular::class, ExtracurricularPolicy::class);
        Gate::policy(Registration::class, RegistrationPolicy::class);
        Gate::policy(Schedule::class, SchedulePolicy::class);
        Gate::policy(Assessment::class, AssessmentPolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
    }
}
