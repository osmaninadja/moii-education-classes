<?php

use Illuminate\Support\Facades\Route;
use Moii\EducationClasses\Http\Controllers\ClassController;
use Moii\EducationClasses\Http\Controllers\ClassEnrollmentController;
use Moii\EducationClasses\Http\Controllers\TimetableController;

Route::middleware(['api', 'auth:sanctum'])->group(function () {

    // Classes
    Route::middleware(['rate_limit:moii-education-classes:list', 'permission:classes.view.active'])->group(function () {
        Route::get('/classes/active', [ClassController::class, 'getActive']);
    });
    Route::middleware(['rate_limit:moii-education-classes:list', 'permission:classes.view.by_grade'])->group(function () {
        Route::get('/classes/grade/{grade}', [ClassController::class, 'getByGrade']);
    });
    Route::middleware(['rate_limit:moii-education-classes:list', 'permission:classes.view'])->group(function () {
        Route::get('/classes', [ClassController::class, 'index']);
    });
    Route::middleware(['rate_limit:moii-education-classes:create', 'permission:classes.create'])->group(function () {
        Route::post('/classes', [ClassController::class, 'store']);
    });
    Route::middleware(['rate_limit:moii-education-classes:read', 'permission:classes.view'])->group(function () {
        Route::get('/classes/{id}', [ClassController::class, 'show']);
        Route::get('/classes/{id}/capacity-status', [ClassController::class, 'capacityStatus']);
    });
    Route::middleware(['rate_limit:moii-education-classes:update', 'permission:classes.update'])->group(function () {
        Route::put('/classes/{id}', [ClassController::class, 'update']);
    });
    Route::middleware(['rate_limit:moii-education-classes:delete', 'permission:classes.delete'])->group(function () {
        Route::delete('/classes/{id}', [ClassController::class, 'destroy']);
    });

    // Enrollments
    Route::middleware(['rate_limit:moii-education-classes:enrollments:create', 'permission:classes.enrollments.create'])->group(function () {
        Route::post('/classes/{id}/enrollments', [ClassEnrollmentController::class, 'store']);
        Route::post('/classes/{id}/enrollments/bulk', [ClassEnrollmentController::class, 'bulkEnroll']);
    });
    Route::middleware(['rate_limit:moii-education-classes:enrollments:list', 'permission:classes.enrollments.view'])->group(function () {
        Route::get('/classes/{id}/enrollments', [ClassEnrollmentController::class, 'index']);
    });
    Route::middleware(['rate_limit:moii-education-classes:enrollments.delete', 'permission:classes.enrollments.delete'])->group(function () {
        Route::delete('/classes/{id}/enrollments/{studentId}', [ClassEnrollmentController::class, 'destroy']);
    });

    // Schedules
    Route::middleware(['rate_limit:moii-education-classes:schedules:create', 'permission:classes.schedules.create'])->group(function () {
        Route::post('/classes/{id}/schedules', [TimetableController::class, 'store']);
    });
    Route::middleware(['rate_limit:moii-education-classes:schedules:list', 'permission:classes.schedules.view'])->group(function () {
        Route::get('/classes/{id}/schedules', [TimetableController::class, 'index']);
        Route::get('/classes/{id}/schedules/{dayOfWeek}', [TimetableController::class, 'getByDay']);
    });
    Route::middleware(['rate_limit:moii-education-classes:schedules.update', 'permission:classes.schedules.update'])->group(function () {
        Route::put('/classes/{id}/schedules/{scheduleId}', [TimetableController::class, 'update']);
    });
    Route::middleware(['rate_limit:moii-education-classes:schedules.delete', 'permission:classes.schedules.delete'])->group(function () {
        Route::delete('/classes/{id}/schedules/{scheduleId}', [TimetableController::class, 'destroy']);
    });

})->where('id', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
  ->where('studentId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}')
  ->where('scheduleId', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
