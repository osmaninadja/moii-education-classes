<?php

namespace Moii\EducationClasses\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ClassEnrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenant_id',
        'app_id',
        'class_id',
        'student_id',
        'status',
        'enrolled_at',
        'unenrolled_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'unenrolled_at' => 'datetime',
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
        return \Moii\EducationClasses\Database\Factories\ClassEnrollmentFactory::new();
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Guarded helper to get the student.
     */
    public function getStudent()
    {
        if (class_exists('Moii\Students\Models\Student')) {
            return \Moii\Students\Models\Student::find($this->student_id);
        }
        return null;
    }
}
