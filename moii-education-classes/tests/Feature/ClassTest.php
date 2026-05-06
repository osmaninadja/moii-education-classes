<?php

namespace Moii\EducationClasses\Tests\Feature;

use Moii\EducationClasses\Tests\TestCase;
use Moii\EducationClasses\Models\SchoolClass;
use Moii\EducationClasses\Models\ClassEnrollment;
use Moii\EducationClasses\Models\ClassSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class ClassTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_enforces_capacity_limits()
    {
        $class = SchoolClass::factory()->create(['capacity' => 1]);
        $service = app(\Moii\EducationClasses\Services\EnrollmentService::class);
        
        $service->enrollStudent($class, (string) Str::uuid());

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Class has reached its maximum capacity.');
        
        $service->enrollStudent($class, (string) Str::uuid());
    }

    /** @test */
    public function it_prevents_duplicate_enrollment()
    {
        $class = SchoolClass::factory()->create();
        $studentId = (string) Str::uuid();
        $service = app(\Moii\EducationClasses\Services\EnrollmentService::class);
        
        $service->enrollStudent($class, $studentId);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User is already enrolled in this class.');
        
        $service->enrollStudent($class, $studentId);
    }

    /** @test */
    public function it_detects_schedule_conflicts()
    {
        $class = SchoolClass::factory()->create();
        $service = app(\Moii\EducationClasses\Services\TimetableService::class);
        
        $service->createScheduleEntry($class, [
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Schedule conflict detected for this class.');
        
        $service->createScheduleEntry($class, [
            'day_of_week' => 1,
            'start_time' => '09:30:00',
            'end_time' => '10:30:00',
        ]);
    }

    /** @test */
    public function it_enforces_immutable_fields()
    {
        $class = SchoolClass::factory()->create(['code' => 'IMMUTABLE']);
        $service = app(\Moii\EducationClasses\Services\ClassService::class);
        
        $service->updateClass($class, ['code' => 'CHANGED', 'name' => 'New Name']);
        
        $this->assertEquals('IMMUTABLE', $class->fresh()->code);
        $this->assertEquals('New Name', $class->fresh()->name);
    }

    /** @test */
    public function it_preserves_historical_rows_when_dropping_or_completing_enrollments()
    {
        $class = SchoolClass::factory()->create(['capacity' => 2]);
        $studentId = (string) Str::uuid();
        $service = app(\Moii\EducationClasses\Services\EnrollmentService::class);

        $enrollment = $service->enrollStudent($class, $studentId);
        $dropped = $service->dropStudent($class, $studentId);

        $this->assertEquals($enrollment->id, $dropped->id);
        $this->assertEquals('dropped', $dropped->fresh()->status);
        $this->assertNotNull($dropped->fresh()->unenrolled_at);
        $this->assertDatabaseHas('class_enrollments', [
            'id' => $enrollment->id,
            'status' => 'dropped',
            'deleted_at' => null,
        ]);

        $reactivated = $service->enrollStudent($class, $studentId);
        $completed = $service->completeStudent($class, $studentId);

        $this->assertEquals($enrollment->id, $reactivated->id);
        $this->assertEquals('completed', $completed->fresh()->status);
        $this->assertDatabaseCount('class_enrollments', 1);
    }

    /** @test */
    public function it_exposes_school_class_integration_helpers()
    {
        $class = SchoolClass::factory()->create(['capacity' => 2]);
        $studentId = (string) Str::uuid();

        $enrollment = $class->enrollStudent($studentId);

        $this->assertInstanceOf(ClassEnrollment::class, $enrollment);
        $this->assertTrue($class->fresh()->getActiveEnrollments()->contains('student_id', $studentId));

        $class->dropStudent($studentId);

        $this->assertFalse($class->fresh()->getActiveEnrollments()->contains('student_id', $studentId));
    }

    /** @test */
    public function it_registers_observers_without_hooks_package_failures()
    {
        $class = SchoolClass::factory()->create();

        $this->assertInstanceOf(SchoolClass::class, $class);
    }
}
