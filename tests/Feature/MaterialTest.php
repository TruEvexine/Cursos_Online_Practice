<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaterialTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function upload(Course $course, UploadedFile $file, array $extra = [])
    {
        return $this->actingAs($course->ponente)->post("/cursos/{$course->id}/materiales", ['title' => 'Apuntes', 'kind' => 'file', 'file' => $file] + $extra);
    }

    public function test_owner_uploads_a_file_with_a_random_private_name_and_enrolled_student_downloads_it(): void
    {
        $student = User::factory()->role('alumno')->create();
        $course = Course::factory()->enroll($student)->create();
        $topic = $course->blocks()->create(['title' => 'B', 'position' => 1])->topics()->create(['title' => 'T', 'position' => 1, 'is_required' => true]);

        $this->upload($course, UploadedFile::fake()->create('../../Apuntes clase 1.pdf', 300, 'application/pdf'), ['topic_id' => $topic->id])
            ->assertRedirect("/cursos/{$course->id}/materiales")->assertSessionHasNoErrors();

        $material = $course->materials()->sole();
        $this->assertMatchesRegularExpression('#^materials/[A-Za-z0-9]{40}\.pdf$#', $material->file_path);
        $this->assertSame('Apuntes clase 1.pdf', $material->original_name);
        $this->assertSame($topic->id, $material->topic_id);
        $this->assertSame($course->ponente_id, $material->uploaded_by);
        Storage::disk('local')->assertExists($material->file_path);

        $this->actingAs($student)->get("/materiales/{$material->id}/descargar")->assertOk()
            ->assertDownload('Apuntes clase 1.pdf')->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_rejects_disallowed_types_even_when_renamed(): void
    {
        $course = Course::factory()->create();
        $html = tempnam(sys_get_temp_dir(), 'm');
        file_put_contents($html, '<html><script>alert(1)</script></html>');

        foreach ([
            UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml'),
            UploadedFile::fake()->create('pagina.html', 10, 'text/html'),
            UploadedFile::fake()->create('todo.zip', 10, 'application/zip'),
            UploadedFile::fake()->create('programa.exe', 10, 'application/x-msdownload'),
            new UploadedFile($html, 'disfrazado.pdf', null, null, true),
        ] as $file) {
            $this->upload($course, $file)->assertSessionHasErrors('file');
        }

        $this->assertSame(0, $course->materials()->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_file_over_the_limit_and_failed_php_upload_show_a_clear_message(): void
    {
        $course = Course::factory()->create();

        $this->upload($course, UploadedFile::fake()->create('grande.pdf', Material::MAX_KB + 1, 'application/pdf'))
            ->assertSessionHasErrors(['file' => 'El archivo pesa más de '.Material::maxUploadMb().' MB.']);

        // upload_max_filesize: PHP marca el archivo con error.
        $tooBig = new UploadedFile(tempnam(sys_get_temp_dir(), 'm'), 'grande.pdf', 'application/pdf', UPLOAD_ERR_INI_SIZE, true);
        $this->upload($course, $tooBig)->assertSessionHasErrors(['file' => 'El archivo no se pudo subir: pesa más de '.Material::maxUploadMb().' MB, el máximo que acepta el servidor.']);

        // post_max_size: PHP descarta todo el envío; se muestra una página clara, no un error genérico.
        $this->actingAs($course->ponente)->call('POST', "/cursos/{$course->id}/materiales", [], [], [], ['CONTENT_LENGTH' => 1024 ** 3])
            ->assertStatus(413)->assertSee('El archivo es demasiado grande')->assertSee('Volver al formulario');
    }

    public function test_links_and_videos_must_be_https(): void
    {
        $course = Course::factory()->create();
        $this->actingAs($course->ponente);

        foreach (['http://ejemplo.com', 'javascript:alert(1)', 'data:text/html,hola', 'ftp://ejemplo.com/a'] as $url) {
            $this->post("/cursos/{$course->id}/materiales", ['title' => 'X', 'kind' => 'link', 'url' => $url])->assertSessionHasErrors('url');
        }

        $this->post("/cursos/{$course->id}/materiales", ['title' => 'Clase grabada', 'kind' => 'video', 'url' => 'https://www.youtube.com/watch?v=abc'])
            ->assertSessionHasNoErrors();
        $video = $course->materials()->sole();
        $this->assertNull($video->file_path);

        $this->get("/cursos/{$course->id}/materiales")->assertOk()
            ->assertSee('href="https://www.youtube.com/watch?v=abc" target="_blank" rel="noopener noreferrer"', false)
            ->assertDontSee('<iframe', false);
        $this->get("/materiales/{$video->id}/descargar")->assertNotFound();
    }

    public function test_topic_must_belong_to_the_same_course(): void
    {
        $course = Course::factory()->create();
        $foreignTopic = Course::factory()->create()->blocks()->create(['title' => 'B', 'position' => 1])
            ->topics()->create(['title' => 'Ajeno', 'position' => 1, 'is_required' => true]);

        $this->actingAs($course->ponente)->post("/cursos/{$course->id}/materiales", ['title' => 'X', 'kind' => 'link', 'url' => 'https://a.mx', 'topic_id' => $foreignTopic->id])
            ->assertSessionHasErrors('topic_id');
    }

    public function test_who_can_download(): void
    {
        $active = User::factory()->role('alumno')->create();
        $completed = User::factory()->role('alumno')->create();
        $removed = User::factory()->role('alumno')->create();
        $course = Course::factory()->enroll($active)->enroll($completed, 'completed')->enroll($removed, 'removed')->create();
        $this->upload($course, UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'));
        $url = '/materiales/'.$course->materials()->sole()->id.'/descargar';

        foreach ([$active, $completed, $course->ponente, User::factory()->role('director')->create(), User::factory()->role('super_admin')->create()] as $user) {
            $this->actingAs($user)->get($url)->assertOk();
        }
        foreach ([$removed, User::factory()->role('alumno')->create(), Course::factory()->create()->ponente] as $user) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }

        $course->update(['status' => 'draft']);
        $this->actingAs($active)->get($url)->assertForbidden();

        $course->delete();
        $this->actingAs($course->ponente)->get($url)->assertNotFound();
    }

    public function test_other_ponente_cannot_manage_materials_and_owner_deletes_file(): void
    {
        $course = Course::factory()->create();
        $this->upload($course, UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'));
        $material = $course->materials()->sole();
        $intruder = Course::factory()->create()->ponente;

        $this->actingAs($intruder)->get("/cursos/{$course->id}/materiales")->assertForbidden();
        $this->actingAs($intruder)->post("/cursos/{$course->id}/materiales", ['title' => 'X', 'kind' => 'link', 'url' => 'https://a.mx'])->assertForbidden();
        $this->actingAs($intruder)->delete("/materiales/{$material->id}")->assertForbidden();
        $this->actingAs(User::factory()->role('director')->create())->delete("/materiales/{$material->id}")->assertForbidden();
        $this->assertModelExists($material);

        $this->actingAs($course->ponente)->delete("/materiales/{$material->id}")->assertRedirect("/cursos/{$course->id}/materiales");
        $this->assertModelMissing($material);
        Storage::disk('local')->assertMissing($material->file_path);
    }
}
