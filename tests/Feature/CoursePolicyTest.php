<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class CoursePolicyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_owner_and_super_admin_manage_the_course_other_roles_do_not(): void
    {
        $course = Course::factory()->create();
        $owner = $course->ponente;

        foreach (['update', 'delete', 'manageStudents', 'view'] as $ability) {
            $this->assertTrue(Gate::forUser($owner)->allows($ability, $course), "dueño: $ability");
            $this->assertTrue(Gate::forUser(User::factory()->role('super_admin')->create())->allows($ability, $course), "super admin: $ability");
        }

        foreach (['ponente', 'director', 'alumno'] as $slug) {
            $other = User::factory()->role($slug)->create();
            foreach (['update', 'delete', 'manageStudents'] as $ability) {
                $this->assertFalse(Gate::forUser($other)->allows($ability, $course), "$slug: $ability");
            }
        }
    }

    public function test_only_ponente_and_super_admin_create_courses(): void
    {
        foreach (['ponente' => true, 'super_admin' => true, 'director' => false, 'alumno' => false] as $slug => $allowed) {
            $this->assertSame($allowed, Gate::forUser(User::factory()->role($slug)->create())->allows('create', Course::class), $slug);
        }
    }

    public function test_director_views_any_course_other_ponente_does_not(): void
    {
        $course = Course::factory()->draft()->create();

        $this->assertTrue(Gate::forUser(User::factory()->role('director')->create())->allows('view', $course));
        $this->assertFalse(Gate::forUser(User::factory()->role('ponente')->create())->allows('view', $course));
    }

    public function test_ponente_who_changed_role_no_longer_owns_the_course(): void
    {
        $course = Course::factory()->create();
        $course->ponente->forceFill(['role_id' => User::factory()->role('alumno')->make()->role_id])->save();

        $this->assertFalse(Gate::forUser($course->ponente->fresh())->allows('update', $course));
    }

    public function test_student_views_only_published_courses_with_active_or_completed_enrollment(): void
    {
        $student = User::factory()->create();

        $cases = [
            'inscrito activo' => [Course::factory()->enroll($student)->create(), true],
            'completado' => [Course::factory()->enroll($student, 'completed')->create(), true],
            'quitado' => [Course::factory()->enroll($student, 'removed')->create(), false],
            'no inscrito' => [Course::factory()->create(), false],
            'borrador' => [Course::factory()->draft()->enroll($student)->create(), false],
            'archivado' => [Course::factory()->state(['status' => 'archived'])->enroll($student)->create(), false],
        ];

        foreach ($cases as $label => [$course, $allowed]) {
            $this->assertSame($allowed, Gate::forUser($student)->allows('view', $course), $label);
        }
    }

    public function test_slug_is_generated_and_unique(): void
    {
        $a = Course::factory()->create(['title' => 'Álgebra básica']);
        $b = Course::factory()->create(['title' => 'Álgebra básica']);

        $this->assertStringStartsWith('algebra-basica-', $a->slug);
        $this->assertNotSame($a->slug, $b->slug);
    }
}
