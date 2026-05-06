<?php

namespace Moii\EducationClasses\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Moii\EducationClasses\Services\ClassService;
use Moii\EducationClasses\Traits\HandlesTenantAppContext;
use Moii\EducationClasses\Traits\ApiResponseTrait;

class ClassController extends Controller
{
    use HandlesTenantAppContext, ApiResponseTrait;

    protected ClassService $classService;

    public function __construct(ClassService $classService)
    {
        $this->classService = $classService;
    }

    /**
     * Get paginated list of classes for the current tenant and app.
     * Rate limit key: moii-education-classes:list
     * Permission: classes.view
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);
        $perPage = $request->query('per_page', 15);

        $classes = $this->classService->getAllClasses($perPage, $tenantId, $appId);

        return $this->successResponse($classes);
    }

    /**
     * Get a specific class by ID.
     * Rate limit key: moii-education-classes:read
     * Permission: classes.view
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $class = $this->classService->getClassById($id, $tenantId, $appId);

        if (!$class) return $this->notFoundResponse();

        return $this->successResponse($class);
    }

    /**
     * Create a new class.
     * Rate limit key: moii-education-classes:create
     * Permission: classes.create
     */
    public function store(Request $request): JsonResponse
    {
        if ($error = $this->requireContext($request)) return $error;

        $validated = $request->validate([
            'code' => 'required|string',
            'name' => 'required|string',
            'grade' => 'nullable|string',
            'section' => 'nullable|string',
            'academic_year' => 'required|string',
            'capacity' => 'nullable|integer|min:1',
            'teacher_id' => 'nullable|uuid',
            'status' => 'nullable|string|in:active,inactive,archived',
            'metadata' => 'nullable|array',
        ]);

        $validated['tenant_id'] = $this->getTenantUuid($request);
        $validated['app_id'] = $this->getAppUuid($request);

        $class = $this->classService->createClass($validated);

        return $this->createdResponse($class);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $class = $this->classService->getClassById($id, $tenantId, $appId);
        if (!$class) return $this->notFoundResponse();

        $validated = $request->validate([
            'name' => 'nullable|string',
            'section' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'teacher_id' => 'nullable|uuid',
            'status' => 'nullable|string|in:active,inactive,archived',
            'metadata' => 'nullable|array',
        ]);

        $class = $this->classService->updateClass($class, $validated);

        return $this->successResponse($class);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $class = $this->classService->getClassById($id, $tenantId, $appId);
        if (!$class) return $this->notFoundResponse();

        $this->classService->deleteClass($class);

        return $this->successResponse(null, 'Class deleted successfully');
    }

    public function capacityStatus(Request $request, string $id): JsonResponse
    {
        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $class = $this->classService->getClassById($id, $tenantId, $appId);
        if (!$class) return $this->notFoundResponse();

        $status = $this->classService->getClassCapacityStatus($class);

        return $this->successResponse($status);
    }

    public function getByGrade(Request $request, string $grade): JsonResponse
    {
        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $classes = $this->classService->getClassesByGrade($grade, $tenantId, $appId);

        return $this->successResponse($classes);
    }

    public function getActive(Request $request): JsonResponse
    {
        $tenantId = $this->getTenantUuid($request);
        $appId = $this->getAppUuid($request);

        $classes = $this->classService->getActiveClasses($tenantId, $appId);

        return $this->successResponse($classes);
    }
}
