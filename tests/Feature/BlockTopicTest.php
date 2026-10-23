<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Course;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BlockTopicTest extends TestCase
{
    use DatabaseTransactions;

    /** @return array{Course, Block, Topic} */
    private function courseWithContent(): array
    {
        $course = Course::factory()->create();
        $block = $course->blocks()->create(['title' => 'Unidad 1', 'position' => 1]);
        $topic = $block->topics()->create(['title' => 'Tema 1', 'content' => 'Hola', 'position' => 1, 'is_required' => true]);

        return [$course, $block, $topic];
    }

    public function test_owner_builds_blocks_and_topics(): void
    {
        $course = Course::factory()->create();
        $owner = $course->ponente;

        $this->actingAs($owner)->get("/cursos/{$course->id}/bloques")->assertOk()->assertSee('Este curso todavía no tiene bloques');

        $this->actingAs($owner)->post("/cursos/{$course->id}/bloques", ['title' => 'Unidad 1', 'position' => 1])
            ->assertRedirect("/cursos/{$course->id}/bloques")->assertSessionHasNoErrors();
        $block = $course->blocks()->sole();

        $this->actingAs($owner)->get("/bloques/{$block->id}/temas/crear")->assertOk();
        $this->actingAs($owner)->post("/bloques/{$block->id}/temas", ['title' => 'Qué es la estadística', 'content' => '# Hola', 'position' => 1, 'is_required' => '0'])
            ->assertSessionHasNoErrors();
        $topic = $block->topics()->sole();
        $this->assertFalse($topic->is_required);

        $this->actingAs($owner)->put("/temas/{$topic->id}", ['title' => 'Tema editado', 'content' => 'Texto', 'position' => 2, 'is_required' => '1'])->assertSessionHasNoErrors();
        $this->actingAs($owner)->put("/bloques/{$block->id}", ['title' => 'Unidad uno', 'position' => 3])->assertSessionHasNoErrors();

        $this->assertSame('Tema editado', $topic->fresh()->title);
        $this->assertTrue($topic->fresh()->is_required);
        $this->assertSame(3, $block->fresh()->position);

        $this->actingAs($owner)->get("/cursos/{$course->id}/bloques")->assertOk()->assertSeeInOrder(['Unidad uno', 'Tema editado']);

        $this->actingAs($owner)->delete("/temas/{$topic->id}")->assertRedirect("/cursos/{$course->id}/bloques");
        $this->assertModelMissing($topic);
    }

    public function test_validation_rejects_bad_position_and_missing_title(): void
    {
        [$course, $block] = $this->courseWithContent();

        $this->actingAs($course->ponente)->post("/cursos/{$course->id}/bloques", ['title' => '', 'position' => -1])->assertSessionHasErrors(['title', 'position']);
        $this->actingAs($course->ponente)->post("/bloques/{$block->id}/temas", ['title' => 'T', 'position' => 'abc'])->assertSessionHasErrors(['position', 'is_required']);
    }

    public function test_deleting_a_block_deletes_its_topics_after_warning_how_many(): void
    {
        [$course, $block, $topic] = $this->courseWithContent();
        $second = $block->topics()->create(['title' => 'Tema 2', 'position' => 2, 'is_required' => true]);

        $this->actingAs($course->ponente)->get("/bloques/{$block->id}/editar")->assertOk()
            ->assertSee('Se borrarán también sus 2 temas')->assertSee('Sí, borrar el bloque y sus 2 temas');

        $this->actingAs($course->ponente)->delete("/bloques/{$block->id}")->assertRedirect("/cursos/{$course->id}/bloques");

        $this->assertModelMissing($block);
        $this->assertModelMissing($topic);
        $this->assertModelMissing($second);
    }

    public function test_ponente_cannot_touch_blocks_or_topics_of_another_ponentes_course(): void
    {
        [$courseA] = $this->courseWithContent();
        [$courseB, $blockB, $topicB] = $this->courseWithContent();
        $ponenteA = $courseA->ponente;

        $this->actingAs($ponenteA);
        $this->get("/cursos/{$courseB->id}/bloques")->assertForbidden();
        $this->post("/cursos/{$courseB->id}/bloques", ['title' => 'Intruso', 'position' => 9])->assertForbidden();
        $this->get("/bloques/{$blockB->id}/editar")->assertForbidden();
        $this->put("/bloques/{$blockB->id}", ['title' => 'Hackeado', 'position' => 1])->assertForbidden();
        $this->delete("/bloques/{$blockB->id}")->assertForbidden();
        $this->get("/bloques/{$blockB->id}/temas/crear")->assertForbidden();
        $this->post("/bloques/{$blockB->id}/temas", ['title' => 'Intruso', 'position' => 9, 'is_required' => '1'])->assertForbidden();
        $this->get("/temas/{$topicB->id}/editar")->assertForbidden();
        $this->put("/temas/{$topicB->id}", ['title' => 'Hackeado', 'position' => 1, 'is_required' => '1'])->assertForbidden();
        $this->delete("/temas/{$topicB->id}")->assertForbidden();

        $this->assertSame('Unidad 1', $blockB->fresh()->title);
        $this->assertSame('Tema 1', $topicB->fresh()->title);
        $this->assertSame(1, $courseB->blocks()->count());
        $this->assertSame(1, $blockB->topics()->count());
    }

    public function test_mixing_ids_in_the_body_cannot_move_content_into_another_course(): void
    {
        [$courseA, $blockA, $topicA] = $this->courseWithContent();
        [$courseB, $blockB] = $this->courseWithContent();

        $this->actingAs($courseA->ponente);
        $this->post("/cursos/{$courseA->id}/bloques", ['title' => 'Nuevo', 'position' => 2, 'course_id' => $courseB->id])->assertSessionHasNoErrors();
        $this->post("/bloques/{$blockA->id}/temas", ['title' => 'Nuevo', 'position' => 2, 'is_required' => '1', 'block_id' => $blockB->id])->assertSessionHasNoErrors();
        $this->put("/temas/{$topicA->id}", ['title' => 'Movido', 'position' => 1, 'is_required' => '1', 'block_id' => $blockB->id])->assertSessionHasNoErrors();
        $this->put("/bloques/{$blockA->id}", ['title' => 'Movido', 'position' => 1, 'course_id' => $courseB->id])->assertSessionHasNoErrors();

        $this->assertSame(1, $courseB->blocks()->count());
        $this->assertSame(1, $blockB->topics()->count());
        $this->assertSame($blockA->id, $topicA->fresh()->block_id);
        $this->assertSame($courseA->id, $blockA->fresh()->course_id);
        $this->assertSame(2, $courseA->blocks()->count());
    }

    public function test_director_and_alumno_cannot_use_the_builder(): void
    {
        [$course, $block, $topic] = $this->courseWithContent();

        foreach (['director', 'alumno'] as $slug) {
            $this->actingAs(User::factory()->role($slug)->create());
            $this->get("/cursos/{$course->id}/bloques")->assertForbidden();
            $this->get("/bloques/{$block->id}/editar")->assertForbidden();
            $this->get("/temas/{$topic->id}/editar")->assertForbidden();
        }
    }

    public function test_super_admin_can_edit_any_course_content(): void
    {
        [, $block] = $this->courseWithContent();

        $this->actingAs(User::factory()->role('super_admin')->create())
            ->put("/bloques/{$block->id}", ['title' => 'Por admin', 'position' => 1])->assertSessionHasNoErrors();

        $this->assertSame('Por admin', $block->fresh()->title);
    }

    public function test_content_of_a_deleted_course_is_not_reachable(): void
    {
        [$course, $block, $topic] = $this->courseWithContent();
        $course->delete();

        $this->actingAs($course->ponente);
        $this->get("/bloques/{$block->id}/editar")->assertNotFound();
        $this->get("/temas/{$topic->id}/editar")->assertNotFound();
        $this->put("/temas/{$topic->id}", ['title' => 'X', 'position' => 1, 'is_required' => '1'])->assertForbidden();
    }

    public function test_markdown_strips_html_and_unsafe_links(): void
    {
        $topic = new Topic(['content' => implode("\n\n", [
            '# Título',
            '<script>alert(1)</script>',
            '<img src="x" onerror="alert(2)">',
            '<a href="https://ejemplo.com" onclick="alert(3)">html</a>',
            '[js](javascript:alert(4)) [js2](JaVaScRiPt:alert(5)) <javascript:alert(6)>',
            '[datos](data:text/html;base64,PHNjcmlwdD5hbGVydCg3KTwvc2NyaXB0Pg==)',
            '[vb](vbscript:msgbox(8))',
            '[externo](https://ejemplo.com/a)',
        ])]);

        $html = $topic->contentHtml();

        $this->assertStringContainsString('<h1>Título</h1>', $html);
        foreach (['<script', 'onerror', 'onclick', '<img', 'href="javascript', 'href="data', 'href="vbscript', 'alert(1)'] as $forbidden) {
            $this->assertStringNotContainsStringIgnoringCase($forbidden, $html, $forbidden);
        }
        // Los enlaces inseguros quedan como texto inerte; el único href es el https.
        $this->assertSame(1, substr_count($html, 'href='));
        $this->assertStringContainsString('<a rel="noopener noreferrer" target="_blank" href="https://ejemplo.com/a">', $html);
    }

    public function test_markdown_turns_http_links_into_text_and_blocks_data_images(): void
    {
        $html = (new Topic(['content' => implode("\n\n", [
            '[inseguro](http://ejemplo.com/a) <http://ejemplo.com/b> www.ejemplo.com',
            '![pixel](data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==)',
            '![http](http://ejemplo.com/foto.png)',
            '[seguro](HTTPS://ejemplo.com/c)',
        ])]))->contentHtml();

        $this->assertStringNotContainsString('href="http:', $html);
        $this->assertStringNotContainsString('data:', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('inseguro', $html);
        $this->assertStringContainsString('pixel', $html);
        $this->assertSame(1, substr_count($html, 'href='));
        $this->assertStringContainsString('href="HTTPS://ejemplo.com/c"', $html);
    }
}
