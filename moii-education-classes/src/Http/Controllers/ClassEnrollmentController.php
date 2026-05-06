<?php

namespace Moii\EducationClasses\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Moii\EducationClasses\Services\EnrollmentService;
use Moii\EducationClasses\Services\ClassService;
use Moii\EducationClasses\Traits\HandlesTenantAppContext;
use Moii\EducationClasses\Traits\ApiResponseTrait;

class ClassEnrollmentController extends Controller
{
    use HandlesTenantAppContext, ApiResponseTrait;

    protected EnrollmentService $enrollmentService;
    protected ClassService $classService;

    public function __construct(EnrollmentService $enrollmentService, ClassService $classService)
    {
        $this->enrollmentService = $enrollmentService;
        $this->classService = $classService;
    }

    public function index(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $class = $this->classService->getClassById($id, $tenantId, $appId);
        if (!$class) return $this->notFoundResponse();

        $status = $request->query('status', 'active');
        $enrollments = $this->enrollmentService->getClassEnrollments($class, $status);

        return $this->successResponse($enrollments);
    }

    public function store(Request $request, string $id): JsonResponse
    {
        if ($error = $this->requireContext($request)) return $error;

        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $class = $this->classService->getClassById($id, $tenantId, $appId);
        if (!$class) return $this->notFoundResponse();

        $validated = $request->validate([
            'student_id' => 'required|uuid',
            'status' => 'nullable|string|in:active,dropped,completed',
            'enrolled_at' => 'nullable|date',
        ]);

        try {
            $enrollment = $this->enrollmentService->enrollStudent($class, $validated['student_id'], $validated);
            return $this->createdResponse($enrollment);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function bulkEnroll(Request $request, string $id): JsonResponse
    {
        if ($error = $this->requireContext($request)) return $error;

        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $class = $this->classService->getClassById($id, $tenantId, $appId);
        if (!$class) return $this->notFoundResponse();

        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'required|uuid',
            'status' => 'nullable|string|in:active,dropped,completed',
            'enrolled_at' => 'nullable|date',
        ]);

        try {
            $enrollments = $this->enrollmentService->bulkEnroll($class, $validated['student_ids'], $validated);
            return $this->successResponse($enrollments);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    public function destroy(Request $request, string $id, string $studentId): JsonResponse
    {
        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $class = $this->classService->getClassById($id, $tenantId, $appId);
        if (!$class) return $this->notFoundResponse();

        try {
            $this->enrollmentService->unenrollStudent($class, $studentId);
            return $this->successResponse(null, 'Student unenrolled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }
}
