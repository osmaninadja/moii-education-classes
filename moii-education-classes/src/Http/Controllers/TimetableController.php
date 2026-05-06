<?php

namespace Moii\EducationClasses\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Moii\EducationClasses\Services\TimetableService;
use Moii\EducationClasses\Services\ClassService;
use Moii\EducationClasses\Models\ClassSchedule;
use Moii\EducationClasses\Traits\HandlesTenantAppContext;
use Moii\EducationClasses\Traits\ApiResponseTrait;

class TimetableController extends Controller
{
    use HandlesTenantAppContext, ApiResponseTrait;

    protected TimetableService $timetableService;
    protected ClassService $classService;

    public function __construct(TimetableService $timetableService, ClassService $classService)
    {
        $this->timetableService = $timetableService;
        $this->classService = $classService;
    }

    public function index(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $class = $this->classService->getClassById($id, $tenantId, $appId);
        if (!$class) return $this->notFoundResponse();

        $timetable = $this->timetableService->getClassTimetable($class);

        return $this->successResponse($timetable);
    }

    public function store(Request $request, string $id): JsonResponse
    {
        if ($error = $this->requireContext($request)) return $error;

        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $class = $this->classService->getClassById($id, $tenantId, $appId);
        if (!$class) return $this->notFoundResponse();

        $validated = $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'room_number' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $schedule = $this->timetableService->createScheduleEntry($class, $validated);
            return $this->createdResponse($schedule);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function update(Request $request, string $id, string $scheduleId): JsonResponse
    {
        if ($error = $this->requireContext($request)) return $error;

        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $schedule = ClassSchedule::where('id', $scheduleId)
            ->where('class_id', $id)
            ->where('tenant_id', $tenantId)
            ->where('app_id', $appId)
            ->first();

        if (!$schedule) return $this->notFoundResponse();

        $validated = $request->validate([
            'day_of_week' => 'nullable|integer|between:0,6',
            'start_time' => 'nullable|date_format:H:i:s',
            'end_time' => 'nullable|date_format:H:i:s|after:start_time',
            'room_number' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $schedule = $this->timetableService->updateScheduleEntry($schedule, $validated);
            return $this->successResponse($schedule);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(Request $request, string $id, string $scheduleId): JsonResponse
    {
        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $schedule = ClassSchedule::where('id', $scheduleId)
            ->where('class_id', $id)
            ->where('tenant_id', $tenantId)
            ->where('app_id', $appId)
            ->first();

        if (!$schedule) return $this->notFoundResponse();

        $this->timetableService->deleteScheduleEntry($schedule);

        return $this->successResponse(null, 'Schedule entry deleted successfully');
    }

    public function getByDay(Request $request, string $id, int $dayOfWeek): JsonResponse
    {
        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $class = $this->classService->getClassById($id, $tenantId, $appId);
        if (!$class) return $this->notFoundResponse();

        $timetable = $this->timetableService->getClassScheduleForDay($class, $dayOfWeek);

        return $this->successResponse($timetable);
    }
}
