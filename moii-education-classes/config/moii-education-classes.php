<?php

return [
    'tables' => [
        'classes' => 'classes',
        'enrollments' => 'class_enrollments',
        'schedules' => 'class_schedules',
    ],
    'routes' => [
        'enabled' => env('MOII_CLASSES_ROUTES_ENABLED', true),
        'prefix' => 'classes',
    ],
    'observers' => [
        'enabled' => env('MOII_CLASSES_OBSERVERS_ENABLED', true),
    ],
    'default_status' => env('MOII_CLASSES_DEFAULT_STATUS', 'active'),
    'default_capacity' => env('MOII_CLASSES_DEFAULT_CAPACITY', 15),
    'grades' => [],
    'sections' => [],
    'cache_ttl' => env('MOII_CLASSES_CACHE_TTL', 600),
    'immutable_fields' => ['code', 'grade', 'academic_year'],
    'editable_fields' => ['name', 'section', 'capacity', 'teacher_id', 'status', 'metadata'],
];
