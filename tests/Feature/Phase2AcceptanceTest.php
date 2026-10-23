<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Fase 2, "hecho cuando": un ponente arma un curso completo y un alumno inscrito lo lee; otro alumno recibe 403. */
class Phase2AcceptanceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_ponente_builds_a_course_enrolled_student_reads_it_and_another_gets_403(): void
    {
        Storage::fake('local');
        $ponente = User::factory()->role('ponente')->create();
        $student = User::factory()->role('alumno')->create();
        $outsider = User::factory()->role('alumno')->create();

        $this->actingAs($ponente)->post('/cursos', ['title' => 'Estadística I', 'status' => 'published'])->assertSessionHasNoErrors();
        $course = Course::where('ponente_id', $ponente->id)->sole();
        $this->post("/cursos/{$course->id}/bloques", ['title' => 'Unidad 1', 'description' => 'Distinguir variables.', 'position' => 1])->assertSessionHasNoErrors();
        $block = $course->blocks()->sole();
        $this->post("/bloques/{$block->id}/temas", ['title' => 'Qué es un dato', 'content' => 'Un **dato** es un valor.', 'position' => 1, 'is_required' => '1'])->assertSessionHasNoErrors();
        $topic = $block->topics()->sole();
        $this->post("/cursos/{$course->id}/materiales", ['title' => 'Guía', 'kind' => 'file', 'topic_id' => $topic->id, 'file' => UploadedFile::fake()->create('guia.pdf', 50, 'application/pdf')])->assertSessionHasNoErrors();
        $this->post("/cursos/{$course->id}/alumnos", ['email' => $student->email])->assertSessionHasNoErrors();
        $material = $course->materials()->sole();

        $this->actingAs($student)->get('/alumno')->assertSee('Estadística I');
        $this->get("/cursos/{$course->id}")->assertOk()->assertSee('Qué es un dato');
        $this->get("/temas/{$topic->id}")->assertOk()->assertSee('<strong>dato</strong>', false)->assertSee('Guía');
        $this->get("/materiales/{$material->id}/descargar")->assertOk()->assertDownload('guia.pdf');

        $this->actingAs($outsider)->get('/alumno')->assertDontSee('Estadística I');
        $this->get("/cursos/{$course->id}")->assertForbidden();
        $this->get("/temas/{$topic->id}")->assertForbidden();
        $this->get("/materiales/{$material->id}/descargar")->assertForbidden();
    }

    public function test_director_reads_any_course_but_cannot_change_anything(): void
    {
        $student = User::factory()->role('alumno')->create();
        $course = Course::factory()->draft()->enroll($student)->create(['title' => 'Curso de otro']);
        $block = $course->blocks()->create(['title' => 'Unidad 1', 'position' => 1]);
        $topic = $block->topics()->create(['title' => 'Tema', 'content' => 'Hola', 'position' => 1, 'is_required' => true]);
        $director = User::factory()->role('director')->create();

        $this->actingAs($director)->get('/cursos')->assertOk()->assertSee('Curso de otro')
            ->assertSee(route('reader.course', $course), false)->assertDontSee(route('courses.edit', $course), false)->assertDontSee('Nuevo curso');
        $this->get("/cursos/{$course->id}")->assertOk()->assertDontSee('así ve el curso un alumno');
        $this->get("/temas/{$topic->id}")->assertOk();

        $this->get('/cursos/crear')->assertForbidden();
        $this->post('/cursos', ['title' => 'X', 'status' => 'draft'])->assertForbidden();
        $this->get("/cursos/{$course->id}/editar")->assertForbidden();
        $this->put("/cursos/{$course->id}", ['title' => 'Cambiado', 'status' => 'published'])->assertForbidden();
        $this->delete("/cursos/{$course->id}")->assertForbidden();
        $this->post("/cursos/{$course->id}/bloques", ['title' => 'X', 'position' => 2])->assertForbidden();
        $this->put("/temas/{$topic->id}", ['title' => 'X', 'position' => 1, 'is_required' => '1'])->assertForbidden();
        $this->get("/cursos/{$course->id}/materiales")->assertForbidden();
        $this->get("/cursos/{$course->id}/alumnos")->assertForbidden();
        $this->delete('/inscripciones/'.$course->enrollments()->sole()->id)->assertForbidden();

        $this->assertSame('Curso de otro', $course->fresh()->title);
        $this->assertSame('active', $course->enrollments()->sole()->status);
    }
}
