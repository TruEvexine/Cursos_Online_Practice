<?php

namespace App\Support;

use App\Models\Block;
use App\Models\Course;
use App\Models\Submission;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Desbloqueo y progreso de un alumno en un curso (PLAN §4). Se calcula en el servidor y solo lee.
 * Sin alumno (dueño, super admin, director, vista previa) todo está abierto y no hay progreso.
 *
 * - sequential: un tema se abre cuando todos los temas obligatorios anteriores están completos.
 * - free: todo abierto.
 * - Completo = marcado como visto y cada tarea obligatoria con entrega 'submitted' o 'graded' ('returned' no cuenta).
 */
class CourseProgress
{
    /** @var array<int, array{state: string, seen: bool, done: int, total: int}> */
    private array $topics = [];

    /** @var array<int, Topic> primer tema obligatorio sin completar antes de cada tema bloqueado */
    private array $blockers = [];

    public function __construct(public readonly Course $course, public readonly ?User $student)
    {
        $course->loadMissing('blocks.topics.tasks');
        $topics = $course->blocks->flatMap->topics;

        $seen = $student
            ? DB::table('topic_progress')->where('user_id', $student->id)->whereIn('topic_id', $topics->pluck('id'))->pluck('topic_id')->flip()
            : collect();
        $statuses = $student
            ? Submission::where('user_id', $student->id)->whereIn('task_id', $topics->flatMap->tasks->pluck('id'))->pluck('status', 'task_id')
            : collect();

        $blocker = null;
        foreach ($topics as $topic) {
            $required = $topic->tasks->where('is_required', true);
            $tasksDone = $required->filter(fn ($task) => in_array($statuses[$task->id] ?? null, ['submitted', 'graded'], true))->count();
            $isSeen = $seen->has($topic->id);
            $completed = $isSeen && $tasksDone === $required->count();
            $open = ! $student || $course->unlock_mode === 'free' || $blocker === null;

            $this->topics[$topic->id] = [
                'state' => match (true) {
                    ! $student => 'available',
                    ! $open => 'locked',
                    $completed => 'completed',
                    $isSeen || $tasksDone > 0 => 'in_progress',
                    default => 'available',
                },
                'seen' => $isSeen,
                'done' => ($isSeen ? 1 : 0) + $tasksDone,
                'total' => 1 + $required->count(),
            ];
            if (! $open) {
                $this->blockers[$topic->id] = $blocker;
            }
            if ($topic->is_required && ! $completed && $blocker === null) {
                $blocker = $topic;
            }
        }
    }

    public function state(Topic $topic): string
    {
        return $this->topics[$topic->id]['state'] ?? 'locked';
    }

    public function isOpen(Topic $topic): bool
    {
        return $this->state($topic) !== 'locked';
    }

    public function isSeen(Topic $topic): bool
    {
        return $this->topics[$topic->id]['seen'] ?? false;
    }

    /** "2 de 3 pasos": visto + tareas obligatorias. */
    public function steps(Topic $topic): array
    {
        return [$this->topics[$topic->id]['done'] ?? 0, $this->topics[$topic->id]['total'] ?? 1];
    }

    public function blocker(Topic $topic): ?Topic
    {
        return $this->blockers[$topic->id] ?? null;
    }

    /** [completos, obligatorios] de un bloque. */
    public function blockProgress(Block $block): array
    {
        $required = $block->topics->where('is_required', true);

        return [$required->filter(fn ($t) => $this->state($t) === 'completed')->count(), $required->count()];
    }
}
