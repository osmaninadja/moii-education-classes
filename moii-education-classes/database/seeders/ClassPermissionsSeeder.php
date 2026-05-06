<?php

namespace Moii\EducationClasses\Database\Seeders;

use Illuminate\Database\Seeder;

class ClassPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        if (!class_exists(\Spatie\Permission\Models\Permission::class)) {
            return;
        }

        $permissionClass = \Spatie\Permission\Models\Permission::class;

        foreach ([
            'classes.view',
            'classes.create',
            'classes.update',
            'classes.delete',
            'classes.enrollments.create',
            'classes.enrollments.view',
            'classes.enrollments.delete',
            'classes.enrollments.bulk',
            'classes.schedules.create',
            'classes.schedules.view',
            'classes.schedules.update',
            'classes.schedules.delete',
            'classes.view.active',
            'classes.view.by_grade',
            'classes.capacity.view',
        ] as $permissionName) {
            $permissionClass::firstOrCreate(['name' => $permissionName]);
        }
    }
}
