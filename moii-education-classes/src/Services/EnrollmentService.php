<?php

namespace Moii\EducationClasses\Services;

use Moii\EducationClasses\Models\SchoolClass;
use Moii\EducationClasses\Models\ClassEnrollment;
use Illuminate\Support\Facades\DB;

class EnrollmentService
{
    public function enrollStudent(SchoolClass $class, $studentId, array $data = [])
    {
        $status = $data['status'] ?? 'active';
        $existing = ClassEnrollment::where('class_id', $class->id)
            ->where('student_id', $studentId)
            ->first();

        if ($existing && $existing->status === 'active') {
            throw new \Exception('User is already enrolled in this class.');
        }

        if ($status === 'active') {
            $this->ensureCapacityAvailable($class);
        }

        if ($existing) {
            $existing->update([
                'status' => $status,
                'enrolled_at' => $data['enrolled_at'] ?? now(),
                'unenrolled_at' => null,
            ]);

            return $existing;
        }

        return ClassEnrollment::create([
            'tenant_id' => $class->tenant_id,
            'app_id' => $class->app_id,
            'class_id' => $class->id,
            'student_id' => $studentId,
            'status' => $status,
            'enrolled_at' => $data['enrolled_at'] ?? now(),
        ]);
    }

    public function unenrollStudent(SchoolClass $class, $studentId)
    {
        return $this->changeEnrollmentStatus($class, $studentId, 'dropped');
    }

    public function dropStudent(SchoolClass $class, $studentId)
    {
        return $this->changeEnrollmentStatus($class, $studentId, 'dropped');
    }

    public function completeStudent(SchoolClass $class, $studentId)
    {
        return $this->changeEnrollmentStatus($class, $studentId, 'completed');
    }

    public function bulkEnroll(SchoolClass $class, array $studentIds, array $commonData = [])
    {
        return DB::transaction(function () use ($class, $studentIds, $commonData) {
            $enrollments = [];
            foreach ($studentIds as $studentId) {
                $enrollments[] = $this->enrollStudent($class, $studentId, $commonData);
            }
            return $enrollments;
        });
    }

    public function getClassEnrollments(SchoolClass $class, $status = 'active')
    {
        return $class->enrollments()
            ->where('status', $status)
            ->get();
    }

    public function getEnrollmentCount(SchoolClass $class, $status = 'active')
    {
        return $class->enrollments()
            ->where('status', $status)
            ->count();
    }

    public function isEnrolled(SchoolClass $class, $studentId)
    {
        return ClassEnrollment::where('class_id', $class->id)
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->exists();
    }

    protected function changeEnrollmentStatus(SchoolClass $class, $studentId, string $status)
    {
        $enrollment = ClassEnrollment::where('class_id', $class->id)
            ->where('student_id', $studentId)
            ->first();

        if (!$enrollment) {
            throw new \Exception('Enrollment not found.');
        }

        return $this->updateEnrollmentStatus($enrollment, $status);
    }

    protected function updateEnrollmentStatus(ClassEnrollment $enrollment, string $status)
    {
        $enrollment->update([
            'status' => $status,
            'unenrolled_at' => now(),
        ]);

        return $enrollment;
    }

    protected function ensureCapacityAvailable(SchoolClass $class): void
    {
        $enrolledCount = $class->enrollments()->where('status', 'active')->count();

        if ($enrolledCount >= $class->capacity) {
            throw new \Exception('Class has reached its maximum capacity.');
        }
    }
}
