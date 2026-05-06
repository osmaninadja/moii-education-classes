<?php

namespace Moii\EducationClasses\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Moii\EducationClasses\Models\ClassEnrollment;
use Moii\EducationClasses\Models\SchoolClass;

class ClassEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $class = SchoolClass::first() ?? SchoolClass::factory()->create();

        ClassEnrollment::create([
            'tenant_id' => $class->tenant_id,
            'app_id' => $class->app_id,
            'class_id' => $class->id,
            'student_id' => (string) Str::uuid(),
            'status' => 'active',
            'enrolled_at' => now(),
        ]);
    }
}
