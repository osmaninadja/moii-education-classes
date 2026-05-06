<?php

namespace Moii\EducationClasses\Observers;

use Moii\EducationClasses\Models\SchoolClass;

class ClassObserver
{
    /**
     * Handle the SchoolClass "created" event.
     */
    public function created(SchoolClass $class): void
    {
        $this->dispatchHook('class.created', $class);
    }

    /**
     * Handle the SchoolClass "updated" event.
     */
    public function updated(SchoolClass $class): void
    {
        $this->dispatchHook('class.updated', $class);
    }

    /**
     * Handle the SchoolClass "deleted" event.
     */
    public function deleted(SchoolClass $class): void
    {
        $this->dispatchHook('class.deleted', $class);
    }

    protected function dispatchHook(string $hook, $data): void
    {
        if (class_exists('Moii\Hooks\Services\HookService')) {
            app('Moii\Hooks\Services\HookService')->doAction($hook, $data);
        }
    }
}
