<?php

namespace Moii\EducationClasses\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Moii\EducationClasses\Models\SchoolClass;
use Moii\EducationClasses\Models\ClassEnrollment;
use Moii\EducationClasses\Models\ClassSchedule;
use Moii\EducationClasses\Tests\TestCase;

class ClassControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_to_index_returns_401()
    {
        $response = $this->getJson('/classes');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_request_to_store_returns_401()
    {
        $response = $this->postJson('/classes', []);
        $response->assertStatus(401);
    }

    public function test_index_returns_200_with_paginated_list()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        SchoolClass::factory()->count(5)->create(['tenant_id' => $user->tenant_id]);

        $response = $this->withToken($token)->getJson('/classes');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'name', 'capacity', 'grade']
                     ],
                     'current_page',
                     'total',
                     'per_page'
                 ]);
    }

    public function test_index_respects_tenant_isolation()
    {
        $user1 = \Moii\Users\Models\User::factory()->create(['tenant_id' => 'tenant1']);
        $user2 = \Moii\Users\Models\User::factory()->create(['tenant_id' => 'tenant2']);

        SchoolClass::factory()->count(3)->create(['tenant_id' => 'tenant1']);
        SchoolClass::factory()->count(2)->create(['tenant_id' => 'tenant2']);

        $response = $this->withToken($this->getAuthToken($user1))->getJson('/classes');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_store_with_valid_data_creates_record_and_returns_201()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $data = [
            'name' => 'Math 101',
            'capacity' => 30,
            'grade' => '10th',
            'subject' => 'Mathematics'
        ];

        $response = $this->withToken($token)->postJson('/classes', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment($data);

        $this->assertDatabaseHas('classes', $data);
    }

    public function test_store_without_required_fields_returns_422()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $response = $this->withToken($token)->postJson('/classes', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'capacity', 'grade']);
    }

    public function test_store_with_non_integer_capacity_returns_422()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $data = [
            'name' => 'Math 101',
            'capacity' => 'thirty',
            'grade' => '10th'
        ];

        $response = $this->withToken($token)->postJson('/classes', $data);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['capacity']);
    }

    public function test_show_with_valid_uuid_returns_200_with_class_data()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);

        $response = $this->withToken($token)->getJson("/classes/{$class->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $class->id, 'name' => $class->name]);
    }

    public function test_show_with_non_existent_uuid_returns_404()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $uuid = (string) \Illuminate\Support\Str::uuid();

        $response = $this->withToken($token)->getJson("/classes/{$uuid}");

        $response->assertStatus(404);
    }

    public function test_show_with_invalid_uuid_format_returns_422()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $response = $this->withToken($token)->getJson('/classes/invalid-uuid');

        $response->assertStatus(422);
    }

    public function test_show_respects_tenant_isolation_returns_404()
    {
        $user1 = \Moii\Users\Models\User::factory()->create(['tenant_id' => 'tenant1']);
        $user2 = \Moii\Users\Models\User::factory()->create(['tenant_id' => 'tenant2']);

        $class = SchoolClass::factory()->create(['tenant_id' => 'tenant1']);

        $response = $this->withToken($this->getAuthToken($user2))->getJson("/classes/{$class->id}");

        $response->assertStatus(404);
    }

    public function test_update_with_valid_data_updates_record_and_returns_200()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);

        $updateData = ['name' => 'Updated Math 101'];

        $response = $this->withToken($token)->putJson("/classes/{$class->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('classes', array_merge($class->toArray(), $updateData));
    }

    public function test_update_with_partial_data_updates_only_changed_fields()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id, 'name' => 'Original']);

        $response = $this->withToken($token)->putJson("/classes/{$class->id}", ['capacity' => 40]);

        $response->assertStatus(200);

        $class->refresh();
        $this->assertEquals('Original', $class->name);
        $this->assertEquals(40, $class->capacity);
    }

    public function test_update_with_invalid_data_returns_422()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);

        $response = $this->withToken($token)->putJson("/classes/{$class->id}", ['capacity' => 'invalid']);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['capacity']);
    }

    public function test_update_with_invalid_uuid_returns_422()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $response = $this->withToken($token)->putJson('/classes/invalid-uuid', ['name' => 'Test']);

        $response->assertStatus(422);
    }

    public function test_delete_soft_deletes_record_and_returns_204()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);

        $response = $this->withToken($token)->deleteJson("/classes/{$class->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('classes', ['id' => $class->id]);
    }

    public function test_get_after_delete_returns_404()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);

        $this->withToken($token)->deleteJson("/classes/{$class->id}");

        $response = $this->withToken($token)->getJson("/classes/{$class->id}");

        $response->assertStatus(404);
    }

    public function test_delete_with_invalid_uuid_returns_422()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $response = $this->withToken($token)->deleteJson('/classes/invalid-uuid');

        $response->assertStatus(422);
    }

    public function test_get_active_returns_only_active_classes()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        SchoolClass::factory()->count(2)->create(['tenant_id' => $user->tenant_id, 'status' => 'active']);
        SchoolClass::factory()->create(['tenant_id' => $user->tenant_id, 'status' => 'archived']);

        $response = $this->withToken($token)->getJson('/classes/active');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
        foreach ($response->json('data') as $class) {
            $this->assertEquals('active', $class['status']);
        }
    }

    public function test_get_by_grade_filters_by_grade()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        SchoolClass::factory()->count(2)->create(['tenant_id' => $user->tenant_id, 'grade' => '10th']);
        SchoolClass::factory()->create(['tenant_id' => $user->tenant_id, 'grade' => '11th']);

        $response = $this->withToken($token)->getJson('/classes/grade/10th');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_capacity_status_returns_utilization_info()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id, 'capacity' => 30]);
        ClassEnrollment::factory()->count(15)->create(['class_id' => $class->id]);

        $response = $this->withToken($token)->getJson("/classes/{$class->id}/capacity-status");

        $response->assertStatus(200)
                 ->assertJson([
                     'capacity' => 30,
                     'enrolled' => 15,
                     'available' => 15,
                     'utilization_percentage' => 50.0
                 ]);
    }

    public function test_enroll_student_creates_enrollment_and_returns_201()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);
        $student = \Moii\Users\Models\User::factory()->create(['tenant_id' => $user->tenant_id]);

        $response = $this->withToken($token)->postJson("/classes/{$class->id}/enrollments", [
            'student_id' => $student->id
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('class_enrollments', [
            'class_id' => $class->id,
            'student_id' => $student->id
        ]);
    }

    public function test_get_enrollments_returns_list_of_enrolled_students()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);
        $students = \Moii\Users\Models\User::factory()->count(3)->create(['tenant_id' => $user->tenant_id]);

        foreach ($students as $student) {
            ClassEnrollment::factory()->create(['class_id' => $class->id, 'student_id' => $student->id]);
        }

        $response = $this->withToken($token)->getJson("/classes/{$class->id}/enrollments");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_delete_enrollment_removes_enrollment_and_returns_204()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);
        $student = \Moii\Users\Models\User::factory()->create(['tenant_id' => $user->tenant_id]);
        $enrollment = ClassEnrollment::factory()->create(['class_id' => $class->id, 'student_id' => $student->id]);

        $response = $this->withToken($token)->deleteJson("/classes/{$class->id}/enrollments/{$student->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('class_enrollments', [
            'class_id' => $class->id,
            'student_id' => $student->id
        ]);
    }

    public function test_bulk_enroll_creates_multiple_enrollments_atomically()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id, 'capacity' => 10]);
        $students = \Moii\Users\Models\User::factory()->count(3)->create(['tenant_id' => $user->tenant_id]);

        $response = $this->withToken($token)->postJson("/classes/{$class->id}/enrollments/bulk", [
            'student_ids' => $students->pluck('id')->toArray()
        ]);

        $response->assertStatus(201);

        foreach ($students as $student) {
            $this->assertDatabaseHas('class_enrollments', [
                'class_id' => $class->id,
                'student_id' => $student->id
            ]);
        }
    }

    public function test_duplicate_enrollment_returns_validation_error()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);
        $student = \Moii\Users\Models\User::factory()->create(['tenant_id' => $user->tenant_id]);

        ClassEnrollment::factory()->create(['class_id' => $class->id, 'student_id' => $student->id]);

        $response = $this->withToken($token)->postJson("/classes/{$class->id}/enrollments", [
            'student_id' => $student->id
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['student_id']);
    }

    public function test_create_schedule_creates_schedule_and_returns_201()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);

        $scheduleData = [
            'day_of_week' => 'monday',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'room' => '101'
        ];

        $response = $this->withToken($token)->postJson("/classes/{$class->id}/schedules", $scheduleData);

        $response->assertStatus(201)
                 ->assertJsonFragment($scheduleData);

        $this->assertDatabaseHas('class_schedules', array_merge($scheduleData, ['class_id' => $class->id]));
    }

    public function test_get_schedules_returns_all_schedules()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);
        ClassSchedule::factory()->count(3)->create(['class_id' => $class->id]);

        $response = $this->withToken($token)->getJson("/classes/{$class->id}/schedules");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_get_schedules_by_day_filters_by_day()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);
        ClassSchedule::factory()->count(2)->create(['class_id' => $class->id, 'day_of_week' => 'monday']);
        ClassSchedule::factory()->create(['class_id' => $class->id, 'day_of_week' => 'tuesday']);

        $response = $this->withToken($token)->getJson("/classes/{$class->id}/schedules/monday");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_update_schedule_updates_and_returns_200()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);
        $schedule = ClassSchedule::factory()->create(['class_id' => $class->id, 'room' => '101']);

        $updateData = ['room' => '102'];

        $response = $this->withToken($token)->putJson("/classes/{$class->id}/schedules/{$schedule->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('class_schedules', array_merge($schedule->toArray(), $updateData));
    }

    public function test_delete_schedule_deletes_and_returns_204()
    {
        $user = \Moii\Users\Models\User::factory()->create();
        $token = $this->getAuthToken($user);

        $class = SchoolClass::factory()->create(['tenant_id' => $user->tenant_id]);
        $schedule = ClassSchedule::factory()->create(['class_id' => $class->id]);

        $response = $this->withToken($token)->deleteJson("/classes/{$class->id}/schedules/{$schedule->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('class_schedules', ['id' => $schedule->id]);
    }

    // Rate limiting and permission tests would require mocking or actual middleware, but for now, assume they are tested via integration
}