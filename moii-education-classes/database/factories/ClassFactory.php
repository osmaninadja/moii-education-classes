<?php

namespace Moii\EducationClasses\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Moii\EducationClasses\Models\SchoolClass;
use Illuminate\Support\Str;

class ClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) Str::uuid(),
            'app_id' => (string) Str::uuid(),
            'code' => $this->faker->unique()->bothify('??###'),
            'name' => $this->faker->words(3, true),
            'grade' => (string) $this->faker->numberBetween(1, 12),
            'section' => $this->faker->randomElement(['A', 'B', 'C']),
            'academic_year' => '2024-2025',
            'capacity' => 15,
            'status' => 'active',
        ];
    }
}
