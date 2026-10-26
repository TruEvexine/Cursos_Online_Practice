<?php

use App\Http\Controllers\BlockController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TopicController;
use Illuminate\Support\Facades\Route;

// Portada pública y páginas informativas.
Route::get('/', LandingController::class)->name('home');
Route::view('/sobre-nosotros', 'public.about')->name('about');
Route::view('/aviso-de-privacidad', 'public.privacy')->name('privacy');
Route::view('/terminos', 'public.terms')->name('terms');

Route::middleware(['auth', 'verified'])->group(function () {
    // Cada rol tiene su propio inicio; /dashboard (destino tras el login) manda al que le toca.
    Route::get('/dashboard', fn () => redirect()->route(auth()->user()->role->slug.'.home'))->name('dashboard');

    $home = fn (string $area) => fn () => view('home', ['area' => $area]);
    Route::get('/admin', $home('super_admin'))->middleware('role:super_admin')->name('super_admin.home');
    Route::get('/direccion', $home('director'))->middleware('role:director,super_admin')->name('director.home');
    Route::get('/ponente', $home('ponente'))->middleware('role:ponente')->name('ponente.home');
    Route::get('/alumno', [ReaderController::class, 'myCourses'])->middleware('role:alumno')->name('alumno.home');

    // Permisos en CoursePolicy (cada acción del controlador la autoriza).
    Route::resource('cursos', CourseController::class)->parameters(['cursos' => 'course'])->except('show')->names('courses');

    // Lectura: alumno inscrito, director y vista previa del dueño o super admin (CoursePolicy::view).
    Route::get('/cursos/{course}', [ReaderController::class, 'course'])->name('reader.course');
    Route::get('/temas/{topic}', [ReaderController::class, 'topic'])->name('reader.topic');
    Route::post('/temas/{topic}/visto', [ReaderController::class, 'markSeen'])->name('reader.seen');

    // Rutas shallow: el curso se deduce del bloque o tema, así que no se pueden mezclar ids de cursos distintos.
    Route::get('/cursos/{course}/bloques', [BlockController::class, 'index'])->name('blocks.index');
    Route::post('/cursos/{course}/bloques', [BlockController::class, 'store'])->name('blocks.store');
    Route::get('/bloques/{block}/editar', [BlockController::class, 'edit'])->name('blocks.edit');
    Route::put('/bloques/{block}', [BlockController::class, 'update'])->name('blocks.update');
    Route::delete('/bloques/{block}', [BlockController::class, 'destroy'])->name('blocks.destroy');

    Route::get('/bloques/{block}/temas/crear', [TopicController::class, 'create'])->name('topics.create');
    Route::post('/bloques/{block}/temas', [TopicController::class, 'store'])->name('topics.store');
    Route::get('/temas/{topic}/editar', [TopicController::class, 'edit'])->name('topics.edit');
    Route::put('/temas/{topic}', [TopicController::class, 'update'])->name('topics.update');
    Route::delete('/temas/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');

    Route::get('/cursos/{course}/materiales', [MaterialController::class, 'index'])->name('materials.index');
    Route::post('/cursos/{course}/materiales', [MaterialController::class, 'store'])->name('materials.store');
    Route::delete('/materiales/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');
    Route::get('/materiales/{material}/descargar', [MaterialController::class, 'download'])->name('materials.download');

    Route::get('/cursos/{course}/alumnos', [StudentController::class, 'index'])->name('students.index');
    Route::post('/cursos/{course}/alumnos', [StudentController::class, 'store'])->name('students.store');
    Route::delete('/inscripciones/{enrollment}', [StudentController::class, 'destroy'])->name('students.destroy');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/usuarios/{user}/foto', [ProfileController::class, 'avatar'])->name('avatar');
});
