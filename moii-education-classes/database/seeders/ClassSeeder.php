<?php

namespace Moii\EducationClasses\Database\Seeders;

use Illuminate\Database\Seeder;
use Moii\EducationClasses\Models\SchoolClass;
use Illuminate\Support\Str;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Str::uuid();
        $appId = Str::uuid();

        SchoolClass::create([
            'tenant_id' => $tenantId,
            'app_id' => $appId,
            'code' => 'CS101',
            'name' => 'Computer Science 101',
            'grade' => '10',
            'section' => 'A',
            'academic_year' => '2024-2025',
            'capacity' => 15,
            'status' => 'active',
        ]);

        SchoolClass::create([
            'tenant_id' => $tenantId,
            'app_id' => $appId,
            'code' => 'ENG201',
            'name' => 'English Literature 201',
            'grade' => '11',
            'section' => 'B',
            'academic_year' => '2024-2025',
            'capacity' => 20,
            'status' => 'active',
        ]);
    }
}
