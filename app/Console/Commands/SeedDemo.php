<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Datos de la DEMO: 1 director, 3 ponentes, 3 cursos completos y 12 alumnos con progreso variado.
 * Idempotente: los usuarios se actualizan por correo y un curso que ya existe no se vuelve a llenar (usa --reset).
 * Solo corre con DEMO_MODE=true y DEMO_PASSWORD; los correos son DEMO_EMAIL_BASE con plus-addressing.
 * Nunca crea ni modifica un super admin.
 */
class SeedDemo extends Command
{
    protected $signature = 'app:seed-demo {--reset : Borra los cursos demo (con su contenido y entregas) y los vuelve a crear}';

    protected $description = 'Siembra los datos de la DEMO (solo con DEMO_MODE=true)';

    private const STAFF = [
        'director' => ['director', 'Mtra. Elena Navarro Ruiz'],
        'ponente1' => ['ponente', 'Laura Méndez Ortiz'],
        'ponente2' => ['ponente', 'Andrés Salgado Pérez'],
        'ponente3' => ['ponente', 'Sofía Herrera Campos'],
    ];

    private const STUDENTS = [
        'alumno01' => 'Diego Ramírez', 'alumno02' => 'Ana Sofía Torres', 'alumno03' => 'Luis Herrera', 'alumno04' => 'Mariana Cruz',
        'alumno05' => 'Jorge Pineda', 'alumno06' => 'Valeria Ochoa', 'alumno07' => 'Fernanda Ríos', 'alumno08' => 'Emilio Castro',
        'alumno09' => 'Regina Salas', 'alumno10' => 'Tomás Vega', 'alumno11' => 'Camila Duarte', 'alumno12' => 'Iván Morales',
    ];

    /** Por curso: alumno => [temas completados en orden, ¿lo último entregado sin calificar?, ¿lo último devuelto?] */
    private const PROGRESS = [
        'demo-estadistica-descriptiva' => ['alumno01' => [5, false, false], 'alumno02' => [3, true, false], 'alumno03' => [2, false, false],
            'alumno04' => [1, false, false], 'alumno05' => [0, false, false], 'alumno06' => [2, false, true]],
        'demo-python-desde-cero' => ['alumno01' => [4, false, false], 'alumno07' => [2, false, false], 'alumno08' => [1, true, false],
            'alumno09' => [0, false, false], 'alumno05' => [0, false, false]],
        'demo-redaccion-academica' => ['alumno10' => [3, false, false], 'alumno11' => [1, false, false], 'alumno12' => [0, false, false],
            'alumno02' => [2, true, false]],
    ];

    private const FEEDBACK = [
        'Muy bien planteado; cuida la ortografía en la última parte.',
        'Buen trabajo. Agrega un ejemplo propio para que la idea quede más clara.',
        'Cumple lo pedido, pero la explicación es breve: desarrolla el porqué.',
        'Excelente: claro, ordenado y con evidencia.',
        'Revisa el procedimiento: el resultado no corresponde con los datos.',
    ];

    public function handle(): int
    {
        $demo = config('site.demo');
        if (! $demo['enabled']) {
            $this->error('DEMO_MODE no está activo: no se siembra nada.');

            return self::FAILURE;
        }
        if (blank($demo['password']) || blank($demo['email_base']) || ! str_contains($demo['email_base'], '@')) {
            $this->error('Faltan DEMO_PASSWORD o DEMO_EMAIL_BASE (un correo, p. ej. tu@gmail.com).');

            return self::FAILURE;
        }

        DB::transaction(function () use ($demo) {
            if ($this->option('reset')) {
                Course::withTrashed()->where('slug', 'like', 'demo-%')->get()->each->forceDelete();
            }

            $users = [];
            foreach (self::STAFF + array_map(fn ($name) => ['alumno', $name], self::STUDENTS) as $tag => [$role, $name]) {
                $users[$tag] = $this->user($tag, $role, $name, $demo);
            }

            foreach (require database_path('demo/courses.php') as $data) {
                $this->course($data, $users);
            }
        });

        $this->info('Listo. Usuarios demo: '.$this->email('director', $demo['email_base']).' y similares (+gg-ponente1…3, +gg-alumno01…12).');

        return self::SUCCESS;
    }

    private function email(string $tag, string $base): string
    {
        [$local, $domain] = explode('@', Str::lower(trim($base)), 2);

        return "$local+gg-$tag@$domain";
    }

    private function user(string $tag, string $role, string $name, array $demo): ?User
    {
        $email = $this->email($tag, $demo['email_base']);
        $user = User::withTrashed()->where('email', $email)->first();

        if ($user?->hasRole('super_admin')) {
            $this->warn("$email es un super admin: no se toca.");

            return null;
        }

        $user ??= new User(['email' => $email]);
        $user->fill(['name' => $name, 'password' => $demo['password']]);
        $user->forceFill(['role_id' => Role::where('slug', $role)->value('id'), 'is_active' => true, 'email_verified_at' => $user->email_verified_at ?? now(), 'deleted_at' => null])->save();

        return $user;
    }

    private function course(array $data, array $users): void
    {
        $ponente = $users[$data['ponente']] ?? null;
        if (! $ponente || Course::withTrashed()->where('slug', $data['slug'])->exists()) {
            $this->line("Curso «{$data['title']}»: ya existe o falta su ponente; se deja como está.");

            return;
        }

        $course = new Course(['title' => $data['title'], 'summary' => $data['summary'], 'description' => $data['description'], 'status' => 'published',
            'unlock_mode' => 'sequential', 'starts_on' => now()->subWeeks(3)->toDateString(), 'ends_on' => now()->addWeeks(6)->toDateString()]);
        $course->slug = $data['slug'];
        $course->ponente_id = $ponente->id;
        if (Schema::hasColumn('courses', 'catalog_price')) {
            $course->catalog_price = $data['price']; // database/changes/002_catalogo.sql
        }
        $course->save();

        foreach ($data['blocks'] as $b => $block) {
            $blockModel = $course->blocks()->create(['title' => $block['title'], 'description' => $block['description'], 'position' => $b + 1]);
            foreach ($block['topics'] as $t => $topic) {
                $topicModel = $blockModel->topics()->create(['title' => $topic['title'], 'content' => $topic['content'], 'position' => $t + 1, 'is_required' => $topic['required'] ?? true]);
                foreach ($topic['tasks'] as $k => $task) {
                    $topicModel->tasks()->create(['title' => $task['title'], 'instructions' => $task['instructions'], 'max_score' => $task['max'], 'weight' => $task['weight'] ?? 1,
                        'due_at' => now('America/Mexico_City')->addDays($task['due'])->setTime(23, 59)->utc(), 'is_required' => true, 'allow_late' => $task['due'] < 0, 'position' => $k + 1]);
                }
            }
        }

        foreach ($data['materials'] as $material) {
            $m = $course->materials()->make(['title' => $material['title']] + (isset($material['url'])
                ? ['kind' => 'link', 'url' => $material['url']]
                : $this->pdf($material['file'], Str::slug($material['title']).'.pdf')));
            $m->uploaded_by = $ponente->id;
            $m->save();
        }

        foreach (self::PROGRESS[$data['slug']] ?? [] as $tag => [$level, $lastUngraded, $lastReturned]) {
            if ($student = $users[$tag] ?? null) {
                $course->enrollments()->create(['user_id' => $student->id, 'status' => 'active', 'enrolled_at' => now()->subWeeks(3)]);
                $this->progress($course, $student, $ponente, $level, $lastUngraded, $lastReturned);
            }
        }

        $this->info("Curso «{$data['title']}» creado.");
    }

    /** Completa los primeros $level temas en orden: visto + entregas (calificadas, salvo lo último si se pide). */
    private function progress(Course $course, User $student, User $ponente, int $level, bool $lastUngraded, bool $lastReturned): void
    {
        $topics = $course->load('blocks.topics.tasks')->blocks->flatMap->topics->values();

        foreach ($topics->take($level) as $i => $topic) {
            DB::table('topic_progress')->insertOrIgnore(['user_id' => $student->id, 'topic_id' => $topic->id, 'completed_at' => now()->subDays(20 - $i * 3)]);
            $last = $i === $level - 1;

            foreach ($topic->tasks as $task) {
                $submittedAt = min(now()->subDay(), $task->due_at->copy()->subDays(2));
                $status = $last && $lastReturned ? 'returned' : ($last && $lastUngraded ? 'submitted' : 'graded');
                $percent = 55 + crc32($student->email.$task->id) % 46; // 55–100 %: algunos quedan "por reforzar"

                (new Submission(['body' => 'Entrega de demostración de '.$student->name.' para «'.$task->title.'».']))->forceFill([
                    'task_id' => $task->id, 'user_id' => $student->id, 'status' => $status, 'submitted_at' => $submittedAt,
                    'score' => $status === 'graded' ? round($task->max_score * $percent / 100 * 2) / 2 : null,
                    'feedback' => $status === 'submitted' ? null : ($status === 'returned' ? 'Le falta la explicación del procedimiento; complétala y vuelve a entregar.' : self::FEEDBACK[$task->id % count(self::FEEDBACK)]),
                    'graded_by' => $status === 'submitted' ? null : $ponente->id,
                    'graded_at' => $status === 'submitted' ? null : min(now(), $submittedAt->copy()->addDay()),
                ])->save();
            }
        }
    }

    /** PDF mínimo de una página con una línea de texto, guardado como material privado con nombre aleatorio. */
    private function pdf(string $text, string $name): array
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], iconv('UTF-8', 'Windows-1252//TRANSLIT', $text));
        $stream = "BT /F1 12 Tf 50 780 Td ($text) Tj ET";
        $objects = ['<< /Type /Catalog /Pages 2 0 R >>', '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>',
            '<< /Length '.strlen($stream)." >>\nstream\n$stream\nendstream"];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n$object\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n".implode('', array_map(fn ($o) => sprintf("%010d 00000 n \n", $o), $offsets))
            .'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";

        $path = 'materials/'.Str::random(40).'.pdf';
        Storage::disk('local')->put($path, $pdf);

        return ['kind' => 'file', 'file_path' => $path, 'original_name' => $name, 'mime_type' => 'application/pdf', 'size_bytes' => strlen($pdf)];
    }
}
