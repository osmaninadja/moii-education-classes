<?php

namespace Moii\EducationClasses\Observers;

use Moii\EducationClasses\Models\ClassEnrollment;

class EnrollmentObserver
{
    /**
     * Handle the ClassEnrollment "created" event.
     */
    public function created(ClassEnrollment $enrollment): void
    {
        $this->dispatchHook('class.student_enrolled', $enrollment);
    }

    /**
     * Handle the ClassEnrollment "deleted" event.
     */
    public function deleted(ClassEnrollment $enrollment): void
    {
        $this->dispatchHook('class.student_unenrolled', $enrollment);
    }

    protected function dispatchHook(string $hook, $data): void
    {
        if (class_exists('Moii\Hooks\Services\HookService')) {
            app('Moii\Hooks\Services\HookService')->doAction($hook, $data);
        }
    }
}
