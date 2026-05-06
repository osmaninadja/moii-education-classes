<?php

namespace Moii\EducationClasses\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\SanctumServiceProvider;
use Moii\EducationClasses\Providers\ClassesServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            // Core dependencies
            \Moii\Users\Providers\UsersServiceProvider::class,
            \Moii\Hooks\Providers\HooksServiceProvider::class,
            // This package
            ClassesServiceProvider::class,
            // Auth
            SanctumServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('moii-education-classes.routes.enabled', true);
        $app['config']->set('moii-education-classes.observers.enabled', true);
    }

    protected function getAuthToken($user = null)
    {
        $user = $user ?? \Moii\Users\Models\User::factory()->create();
        return $user->createToken('test-token')->plainTextToken;
    }

    protected function withTenantContext($tenantId, $appId)
    {
        return $this->withHeaders([
            'X-Tenant-ID' => $tenantId,
            'X-App-ID' => $appId,
        ]);
    }
}
