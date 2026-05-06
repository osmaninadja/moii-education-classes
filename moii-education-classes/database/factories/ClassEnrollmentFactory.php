<?php

namespace Moii\EducationClasses\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Moii\EducationClasses\Models\ClassEnrollment;
use Moii\EducationClasses\Models\SchoolClass;
use Illuminate\Support\Str;

class ClassEnrollmentFactory extends Factory
{
    protected $model = ClassEnrollment::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) Str::uuid(),
            'app_id' => (string) Str::uuid(),
            'class_id' => SchoolClass::factory(),
            'student_id' => (string) Str::uuid(),
            'status' => 'active',
            'enrolled_at' => now(),
        ];
    }
}
