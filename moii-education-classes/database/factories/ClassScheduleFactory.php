<?php

namespace Moii\EducationClasses\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Moii\EducationClasses\Models\ClassSchedule;
use Moii\EducationClasses\Models\SchoolClass;
use Illuminate\Support\Str;

class ClassScheduleFactory extends Factory
{
    protected $model = ClassSchedule::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) Str::uuid(),
            'app_id' => (string) Str::uuid(),
            'class_id' => SchoolClass::factory(),
            'day_of_week' => $this->faker->numberBetween(0, 6),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'is_active' => true,
        ];
    }
}
