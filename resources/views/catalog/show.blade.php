@php $user = auth()->user(); @endphp
<x-layouts.public :title="$course->title.' — Gaining Grades'" :description="$course->summary ?? $course->title">
    <article class="mx-auto grid max-w-[1120px] gap-10 px-4 py-16 lg:grid-cols-[2fr_1fr]">
        <div>
            <a href="{{ route('catalog.index') }}" class="inline-flex min-h-11 items-center text-primary underline">← Catálogo</a>
            <x-course-cover :course="$course" class="mt-4 aspect-[16/6] rounded-card" />
            <h1 class="mt-6 font-serif text-5xl font-semibold [overflow-wrap:anywhere]">{{ $course->title }}</h1>
            <p class="mt-3 text-lg text-muted">Imparte {{ $course->ponente->name }}
                @if ($course->starts_on) · {{ $course->ends_on ? 'del '.$course->starts_on->isoFormat('D MMM').' al '.$course->ends_on->isoFormat('D MMM YYYY') : 'inicia el '.$course->starts_on->isoFormat('D MMM YYYY') }}@endif
            </p>
            @if ($course->description)<p class="mt-6 max-w-[65ch] whitespace-pre-line text-lg">{{ $course->description }}</p>@endif

            <h2 class="mt-10 font-serif text-3xl font-semibold">Temario</h2>
            <ol class="mt-4 space-y-6">
                @foreach ($course->blocks as $block)
                    <li>
                        <h3 class="font-serif text-xl font-semibold">Misión {{ $loop->iteration }}: {{ $block->title }}</h3>
                        @if ($block->description)<p class="mt-1 text-muted">{{ $block->description }}</p>@endif
                        <ul class="mt-2 space-y-1">
                            @foreach ($block->topics as $topic)
                                <li class="flex gap-3"><span aria-hidden="true" class="mt-2 size-3 shrink-0 rounded-full border-2 border-primary"></span>{{ $topic->title }}</li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ol>
        </div>

        <aside aria-labelledby="inscripcion" class="self-start rounded-card bg-surface p-6 lg:sticky lg:top-6">
            <h2 id="inscripcion" class="font-serif text-2xl font-semibold">Inscripción</h2>
            <x-course-price :course="$course" class="mt-2 block font-serif text-4xl font-semibold" />
            @if ($course->catalog_price > 0)
                <p class="mt-2 rounded-field bg-warning-soft px-3 py-2 text-warning-ink"><strong>Modo prueba:</strong> el pago es simulado, no se cobra nada.</p>
            @endif

            <div class="mt-6">
                @guest
                    <p>Necesitas una cuenta de alumno para inscribirte.</p>
                    <div class="mt-3 flex flex-wrap gap-3">
                        @if (Route::has('register'))<a href="{{ route('register') }}" class="inline-flex min-h-11 items-center rounded-field bg-primary px-5 font-semibold text-canvas hover:bg-primary-hover">Crear cuenta</a>@endif
                        <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center rounded-field border border-line-strong px-5 font-semibold hover:bg-sunken">Iniciar sesión</a>
                    </div>
                @else
                    @if (in_array($enrollment?->status, ['active', 'completed'], true))
                        <a href="{{ route('reader.course', $course) }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-field bg-primary px-5 font-semibold text-canvas hover:bg-primary-hover">Ir al curso</a>
                    @elseif ($enrollment?->status === 'removed')
                        <p>Tu ponente te quitó de este curso. Si crees que es un error, escríbele.</p>
                    @elseif (! $user->hasRole('alumno'))
                        <p>Solo las cuentas de alumno se inscriben desde el catálogo.</p>
                    @elseif ($course->catalog_price > 0)
                        <a href="{{ route('catalog.checkout', $course) }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-field bg-primary px-5 font-semibold text-canvas hover:bg-primary-hover">Inscribirme</a>
                    @else
                        <form method="POST" action="{{ route('catalog.enroll', $course) }}">
                            @csrf
                            <button type="submit" class="min-h-11 w-full rounded-field bg-primary px-5 font-semibold text-canvas hover:bg-primary-hover">Inscribirme gratis</button>
                        </form>
                    @endif
                @endguest
            </div>
        </aside>
    </article>
</x-layouts.public>
