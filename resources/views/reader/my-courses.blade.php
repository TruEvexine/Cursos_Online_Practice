<x-layouts.app title="Mis cursos — Gaining Grades">
    <p class="mb-2 text-muted">Hola, {{ auth()->user()->name }}</p>
    <h1 class="mb-6 font-serif text-4xl font-semibold">Mis cursos</h1>

    @if ($enrollments->isEmpty())
        <section class="max-w-2xl rounded-card border border-line bg-surface p-6">
            <h2 class="font-serif text-xl font-semibold">Todavía no estás en ningún curso</h2>
            <p class="mt-2 text-muted">Tu ponente te inscribe con tu correo; cuando lo haga, el curso aparecerá aquí.</p>
            <a href="{{ route('profile.edit') }}" class="mt-2 inline-flex min-h-11 items-center font-semibold text-primary underline">Mientras tanto, completa tu perfil</a>
        </section>
    @else
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($enrollments as $enrollment)
                @php $course = $enrollment->course; @endphp
                <li class="flex flex-col overflow-hidden rounded-card border border-line bg-surface transition-colors duration-150 hover:border-line-strong">
                    <x-course-cover :course="$course" class="aspect-[16/7]" />
                    <div class="flex flex-1 flex-col p-5">
                    @if ($enrollment->status === 'completed')
                        <span class="mb-2 self-start rounded-full bg-success-soft px-3 py-0.5 text-sm font-semibold text-success-ink">Terminado</span>
                    @endif
                    <h2 class="font-serif text-xl font-semibold [overflow-wrap:anywhere]">{{ $course->title }}</h2>
                    <p class="mt-1 text-sm text-muted">Imparte {{ $course->ponente->name }}</p>
                    @if ($course->summary)
                        <p class="mt-3">{{ $course->summary }}</p>
                    @endif
                    @if ($course->starts_on)
                        <p class="mt-3 text-sm text-muted">
                            {{ $course->ends_on ? 'Del '.$course->starts_on->isoFormat('D MMM').' al '.$course->ends_on->isoFormat('D MMM YYYY') : 'Inicia el '.$course->starts_on->isoFormat('D MMM YYYY') }}
                        </p>
                    @endif
                    @if ($next = ($pending[$course->id] ?? collect())->first())
                        <p class="mt-3 rounded-field bg-primary-soft px-3 py-2 text-sm">
                            <span class="font-semibold">Próxima entrega:</span> {{ $next->title }}
                            <span class="block text-muted">{{ $next->due_at ? 'Vence el '.$next->due_at->timezone('America/Mexico_City')->isoFormat('D MMM, H:mm') : 'Sin fecha límite' }}{{ $pending[$course->id]->count() > 1 ? ' · '.$pending[$course->id]->count().' pendientes' : '' }}</span>
                        </p>
                    @endif
                    <a href="{{ route('reader.course', $course) }}" class="mt-auto inline-flex min-h-11 items-center self-start pt-4 font-semibold text-primary underline">
                        {{ $enrollment->status === 'completed' ? 'Repasar' : 'Entrar' }}<span class="sr-only"> a «{{ $course->title }}»</span>
                    </a>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.app>
