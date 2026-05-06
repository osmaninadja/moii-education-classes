<?php

namespace Moii\EducationClasses\Database\Seeders;

use Illuminate\Database\Seeder;

class ClassRateLimitSeeder extends Seeder
{
    public function run(): void
    {
        $limiterRuleClass = \Moii\Limiter\Models\LimiterRule::class;

        $limiterRules = [
            'moii-education-classes:list' => ['max_attempts' => 100, 'decay_minutes' => 1],
            'moii-education-classes:create' => ['max_attempts' => 50, 'decay_minutes' => 1],
            'moii-education-classes:read' => ['max_attempts' => 200, 'decay_minutes' => 1],
            'moii-education-classes:update' => ['max_attempts' => 50, 'decay_minutes' => 1],
            'moii-education-classes:delete' => ['max_attempts' => 50, 'decay_minutes' => 1],
            'moii-education-classes:enrollments:list' => ['max_attempts' => 100, 'decay_minutes' => 1],
            'moii-education-classes:enrollments:create' => ['max_attempts' => 50, 'decay_minutes' => 1],
            'moii-education-classes:schedules:list' => ['max_attempts' => 100, 'decay_minutes' => 1],
            'moii-education-classes:schedules:create' => ['max_attempts' => 50, 'decay_minutes' => 1],
        ];

        foreach ($limiterRules as $key => $config) {
            $limiterRuleClass::updateOrCreate(
                ['key' => $key],
                $config
            );
        }
    }
}