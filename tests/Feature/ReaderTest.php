<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReaderTest extends TestCase
{
    use DatabaseTransactions;

    /** @return array{Course, \App\Models\Topic, \App\Models\Topic} */
    private function courseWithTopics(array $attributes = [], ?User $student = null, string $status = 'active'): array
    {
        $factory = Course::factory();
        if ($student) {
            $factory = $factory->enroll($student, $status);
        }
        $course = $factory->create(['title' => 'Estadística'] + $attributes);
        $block = $course->blocks()->create(['title' => 'Unidad 1', 'description' => 'Distinguir tipos de variables.', 'position' => 1]);
        $first = $block->topics()->create(['title' => 'Qué es un dato', 'content' => "# Datos\n\nUn **dato** es un valor.", 'position' => 1, 'is_required' => true]);
        $second = $block->topics()->create(['title' => 'Variables', 'content' => 'Texto', 'position' => 2, 'is_required' => false]);

        return [$course, $first, $second];
    }

    public function test_enrolled_student_lists_and_reads_the_course(): void
    {
        $student = User::factory()->role('alumno')->create();
        [$course, $first, $second] = $this->courseWithTopics([], $student);
        $done = Course::factory()->enroll($student, 'completed')->create(['title' => 'Álgebra terminada']);
        $gone = Course::factory()->enroll($student, 'removed')->create(['title' => 'Curso quitado']);
        $draft = Course::factory()->draft()->enroll($student)->create(['title' => 'Curso en borrador']);

        $this->actingAs($student)->get('/alumno')->assertOk()
            ->assertSee('Estadística')->assertSee('Álgebra terminada')->assertSee('Terminado')
            ->assertDontSee('Curso quitado')->assertDontSee('Curso en borrador');

        $this->get("/cursos/{$course->id}")->assertOk()->assertSee('Temario')->assertSee('Distinguir tipos de variables.')
            ->assertSee('Qué es un dato')->assertDontSee('Vista previa');
        $this->get("/temas/{$first->id}")->assertOk()
            ->assertSee('<h1>Datos</h1>', false)->assertSee('<strong>dato</strong>', false)
            ->assertSee(route('reader.topic', $second), false);
        $this->get("/cursos/{$done->id}")->assertOk();
    }

    public function test_students_without_a_valid_enrollment_get_403(): void
    {
        $removed = User::factory()->role('alumno')->create();
        [$course, $topic] = $this->courseWithTopics([], $removed, 'removed');
        $enrolled = User::factory()->role('alumno')->create();
        [$draft, $draftTopic] = $this->courseWithTopics(['status' => 'draft'], $enrolled);

        foreach ([$removed, User::factory()->role('alumno')->create()] as $user) {
            $this->actingAs($user)->get("/cursos/{$course->id}")->assertForbidden();
            $this->actingAs($user)->get("/temas/{$topic->id}")->assertForbidden();
        }
        $this->actingAs($enrolled)->get("/cursos/{$draft->id}")->assertForbidden();
        $this->actingAs($enrolled)->get("/temas/{$draftTopic->id}")->assertForbidden();
        $this->actingAs(Course::factory()->create()->ponente)->get("/temas/{$topic->id}")->assertForbidden();

        $course->delete();
        $this->actingAs(User::factory()->role('super_admin')->create())->get("/temas/{$topic->id}")->assertNotFound();
    }

    public function test_preview_works_on_drafts_for_owner_and_super_admin_and_never_writes_progress(): void
    {
        [$course, $first, $second] = $this->courseWithTopics(['status' => 'draft']);
        $before = [DB::table('topic_progress')->count(), DB::table('submissions')->count()];

        foreach ([$course->ponente, User::factory()->role('super_admin')->create()] as $user) {
            $this->actingAs($user)->get("/cursos/{$course->id}")->assertOk()->assertSee('así ve el curso un alumno')->assertSee('Volver a editar');
            $this->actingAs($user)->get("/temas/{$first->id}")->assertOk()->assertSee('así ve el curso un alumno');
            $this->actingAs($user)->get("/temas/{$second->id}")->assertOk();
        }
        $this->actingAs($course->ponente)->get("/cursos/{$course->id}/bloques")->assertSee(route('reader.course', $course), false);

        $this->assertSame($before, [DB::table('topic_progress')->count(), DB::table('submissions')->count()]);
    }
}
