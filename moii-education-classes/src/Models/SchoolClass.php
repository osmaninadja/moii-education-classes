<?php

namespace Moii\EducationClasses\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Moii\EducationClasses\Services\EnrollmentService;

class SchoolClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenant_id',
        'app_id',
        'code',
        'name',
        'grade',
        'section',
        'academic_year',
        'capacity',
        'teacher_id',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
        'capacity' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected static function newFactory()
    {
        return \Moii\EducationClasses\Database\Factories\ClassFactory::new();
    }

    public function enrollments()
    {
        return $this->hasMany(ClassEnrollment::class, 'class_id');
    }

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class, 'class_id');
    }

    public function enrollStudent(string $studentId, string $status = 'active'): ClassEnrollment
    {
        return app(EnrollmentService::class)->enrollStudent($this, $studentId, [
            'status' => $status,
        ]);
    }

    public function dropStudent(string $studentId): ClassEnrollment
    {
        return app(EnrollmentService::class)->dropStudent($this, $studentId);
    }

    public function completeStudent(string $studentId): ClassEnrollment
    {
        return app(EnrollmentService::class)->completeStudent($this, $studentId);
    }

    public function getActiveEnrollments()
    {
        return $this->enrollments()->where('status', 'active')->get();
    }

    public function getEnrolledStudents()
    {
        $enrollments = $this->getActiveEnrollments();

        if (!class_exists('Moii\Students\Models\Student')) {
            return $enrollments;
        }

        return $enrollments
            ->map(fn (ClassEnrollment $enrollment) => $enrollment->getStudent())
            ->filter()
            ->values();
    }

    /**
     * Guarded helper to get the teacher.
     */
    public function getTeacher()
    {
        if (class_exists('Moii\Teachers\Models\Teacher')) {
            return \Moii\Teachers\Models\Teacher::find($this->teacher_id);
        }
        return null;
    }
}
