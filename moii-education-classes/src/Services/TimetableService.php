<?php

namespace Moii\EducationClasses\Services;

use Moii\EducationClasses\Models\SchoolClass;
use Moii\EducationClasses\Models\ClassSchedule;

class TimetableService
{
    public function createScheduleEntry(SchoolClass $class, array $data)
    {
        $this->ensureValidTimeRange($data['start_time'], $data['end_time']);

        if ($this->scheduleConflictExists($class->id, $data['day_of_week'], $data['start_time'], $data['end_time'])) {
            throw new \Exception('Schedule conflict detected for this class.');
        }

        return ClassSchedule::create([
            'tenant_id' => $class->tenant_id,
            'app_id' => $class->app_id,
            'class_id' => $class->id,
            'day_of_week' => $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'room_number' => $data['room_number'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function updateScheduleEntry(ClassSchedule $schedule, array $data)
    {
        $startTime = $data['start_time'] ?? $schedule->start_time;
        $endTime = $data['end_time'] ?? $schedule->end_time;
        $dayOfWeek = $data['day_of_week'] ?? $schedule->day_of_week;

        $this->ensureValidTimeRange($startTime, $endTime);

        if ($this->scheduleConflictExists($schedule->class_id, $dayOfWeek, $startTime, $endTime, $schedule->id)) {
            throw new \Exception('Schedule conflict detected for this class.');
        }

        $schedule->update($data);
        return $schedule;
    }

    public function deleteScheduleEntry(ClassSchedule $schedule)
    {
        return $schedule->delete();
    }

    public function getClassTimetable(SchoolClass $class)
    {
        return ClassSchedule::where('class_id', $class->id)
            ->where('tenant_id', $class->tenant_id)
            ->where('app_id', $class->app_id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function getTimetableConflicts(SchoolClass $class)
    {
        return ClassSchedule::where('class_id', $class->id)
            ->where('tenant_id', $class->tenant_id)
            ->where('app_id', $class->app_id)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    public function getClassScheduleForDay(SchoolClass $class, $dayOfWeek)
    {
        return ClassSchedule::where('class_id', $class->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('tenant_id', $class->tenant_id)
            ->where('app_id', $class->app_id)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();
    }

    protected function scheduleConflictExists($classId, $dayOfWeek, $startTime, $endTime, $excludeId = null)
    {
        $query = ClassSchedule::where('class_id', $classId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($q2) use ($startTime, $endTime) {
                    $q2->where('start_time', '<', $endTime)
                       ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    protected function ensureValidTimeRange(string $startTime, string $endTime): void
    {
        if (strtotime($endTime) <= strtotime($startTime)) {
            throw new \Exception('Schedule end time must be after start time.');
        }
    }
}
