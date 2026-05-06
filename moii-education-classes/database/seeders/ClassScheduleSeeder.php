<?php

namespace Moii\EducationClasses\Database\Seeders;

use Illuminate\Database\Seeder;
use Moii\EducationClasses\Models\ClassSchedule;
use Moii\EducationClasses\Models\SchoolClass;

class ClassScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $class = SchoolClass::first() ?? SchoolClass::factory()->create();

        ClassSchedule::create([
            'tenant_id' => $class->tenant_id,
            'app_id' => $class->app_id,
            'class_id' => $class->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'room_number' => 'A101',
            'is_active' => true,
        ]);

        ClassSchedule::create([
            'tenant_id' => $class->tenant_id,
            'app_id' => $class->app_id,
            'class_id' => $class->id,
            'day_of_week' => 3,
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'room_number' => 'A102',
            'is_active' => true,
        ]);
    }
}
