<?php

namespace Moii\EducationClasses\Providers;

use Illuminate\Support\ServiceProvider;
use Moii\EducationClasses\Models\ClassEnrollment;
use Moii\EducationClasses\Models\SchoolClass;
use Moii\EducationClasses\Observers\ClassObserver;
use Moii\EducationClasses\Observers\EnrollmentObserver;
use Moii\EducationClasses\Services\ClassService;
use Moii\EducationClasses\Services\EnrollmentService;
use Moii\EducationClasses\Services\TimetableService;
use Moii\Mail\Traits\RegistersSeeders;

class ClassesServiceProvider extends ServiceProvider
{
    use RegistersSeeders;

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/moii-education-classes.php',
            'moii-education-classes'
        );

        $this->app->singleton(ClassService::class);
        $this->app->singleton(EnrollmentService::class);
        $this->app->singleton(TimetableService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        if (config('moii-education-classes.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        }

        if (config('moii-education-classes.observers.enabled', true)) {
            SchoolClass::observe(ClassObserver::class);
            ClassEnrollment::observe(EnrollmentObserver::class);
        }

        if ($this->app->runningInConsole()) {
            $this->registerSeeders();

            $this->publishes([
                __DIR__ . '/../../config/moii-education-classes.php' => config_path('moii-education-classes.php'),
            ], 'moii-education-classes-config');

            $this->publishes([
                __DIR__ . '/../../database/migrations' => database_path('migrations'),
            ], 'moii-education-classes-migrations');
        }
    }

    protected function registerSeeders(): void
    {
        $this->registerSeedersFrom(
            'Moii\\EducationClasses\\Database\\Seeders',
            __DIR__ . '/../../database/seeders'
        );
    }
}
