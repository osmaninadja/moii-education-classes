<?php

namespace Moii\EducationClasses\Services;

use Illuminate\Database\Eloquent\Builder;
use Moii\EducationClasses\Models\SchoolClass;

class ClassService
{
    protected function applyTenantAppScope(Builder $query, ?string $tenantId, ?string $appId): Builder
    {
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        if ($appId) {
            $query->where('app_id', $appId);
        }

        return $query;
    }

    public function getAllClasses($perPage = 15, ?string $tenantId = null, ?string $appId = null)
    {
        $query = $this->applyTenantAppScope(SchoolClass::query()->orderBy('name'), $tenantId, $appId);

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    public function getClassById(string $id, ?string $tenantId = null, ?string $appId = null)
    {
        $query = SchoolClass::where('id', $id);
        $query = $this->applyTenantAppScope($query, $tenantId, $appId);

        return $query->first();
    }

    public function createClass(array $data)
    {
        $data['status'] = $data['status'] ?? config('moii-education-classes.default_status', 'active');
        $data['capacity'] = $data['capacity'] ?? config('moii-education-classes.default_capacity', 15);

        return SchoolClass::create($data);
    }

    public function updateClass(SchoolClass $class, array $data)
    {
        $immutableFields = config('moii-education-classes.immutable_fields', ['code', 'grade', 'academic_year']);

        foreach ($immutableFields as $field) {
            unset($data[$field]);
        }

        $class->update($data);
        return $class;
    }

    public function deleteClass(SchoolClass $class)
    {
        return $class->delete();
    }

    public function getClassesByGrade(string $grade, ?string $tenantId = null, ?string $appId = null)
    {
        $query = SchoolClass::where('grade', $grade);
        $query = $this->applyTenantAppScope($query, $tenantId, $appId);

        return $query->get();
    }

    public function getActiveClasses(?string $tenantId = null, ?string $appId = null)
    {
        $query = SchoolClass::where('status', 'active');
        $query = $this->applyTenantAppScope($query, $tenantId, $appId);

        return $query->get();
    }

    public function getClassCapacityStatus(SchoolClass $class)
    {
        $enrolledCount = $class->enrollments()->where('status', 'active')->count();
        $capacity = $class->capacity;

        return [
            'enrolled' => $enrolledCount,
            'capacity' => $capacity,
            'is_full' => $enrolledCount >= $capacity,
            'remaining' => max(0, $capacity - $enrolledCount),
        ];
    }
}
