@props(['submission', 'task'])

{{-- Estado de la entrega del alumno, siempre en texto (no solo color). --}}
@php
    [$text, $classes] = match ($submission?->status) {
        null => [$task->isPastDue() && ! $task->allow_late ? 'Sin entregar (venció)' : 'Pendiente', 'bg-sunken text-muted'],
        'submitted' => ['Entregada'.($submission->isLate() ? ' tarde' : '').', sin calificar', 'bg-primary-soft text-primary'],
        'returned' => ['Devuelta: corrígela y vuelve a entregar', 'bg-warning-soft text-warning-ink'],
        'graded' => ['Calificada: '.($submission->score + 0).'/'.($task->max_score + 0).' · '.$submission->percent().' %'.($submission->needsReinforcement() ? ' · por reforzar' : ''),
            $submission->needsReinforcement() ? 'bg-warning-soft text-warning-ink' : 'bg-success-soft text-success-ink'],
    };
@endphp
<span {{ $attributes->merge(['class' => "inline-block rounded-full px-3 py-0.5 text-sm font-semibold $classes"]) }}>{{ $text }}</span>
