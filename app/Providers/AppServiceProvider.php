<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\SystemSetting;
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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
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
        static $hasSystemSettingsTable;

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

        $hasSystemSettingsTable ??= Schema::hasTable('system_settings');

        if ($hasSystemSettingsTable) {
            $settings = SystemSetting::valuesFor([
                'mail_mailer',
                'mail_smtp_host',
                'mail_smtp_port',
                'mail_smtp_username',
                'mail_smtp_password',
                'mail_smtp_encryption',
                'mail_from_address',
                'mail_from_name',
            ]);

            if (filled($settings->get('mail_mailer'))) {
                Config::set('mail.default', $settings->get('mail_mailer'));
            }

            if (filled($settings->get('mail_smtp_host'))) {
                Config::set('mail.mailers.smtp.host', $settings->get('mail_smtp_host'));
            }

            if (filled($settings->get('mail_smtp_port'))) {
                Config::set('mail.mailers.smtp.port', (int) $settings->get('mail_smtp_port'));
            }

            if (filled($settings->get('mail_smtp_username'))) {
                Config::set('mail.mailers.smtp.username', $settings->get('mail_smtp_username'));
            }

            if (filled($settings->get('mail_smtp_password'))) {
                Config::set('mail.mailers.smtp.password', $settings->get('mail_smtp_password'));
            }

            if (filled($settings->get('mail_smtp_encryption'))) {
                Config::set('mail.mailers.smtp.encryption', $settings->get('mail_smtp_encryption'));
            }

            if (filled($settings->get('mail_from_address'))) {
                Config::set('mail.from.address', $settings->get('mail_from_address'));
            }

            if (filled($settings->get('mail_from_name'))) {
                Config::set('mail.from.name', $settings->get('mail_from_name'));
            }
        }
    }
}
