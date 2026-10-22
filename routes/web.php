<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    // Cada rol tiene su propio inicio; /dashboard (destino tras el login) manda al que le toca.
    Route::get('/dashboard', fn () => redirect()->route(auth()->user()->role->slug.'.home'))->name('dashboard');

    $home = fn (string $area) => fn () => view('home', ['area' => $area]);
    Route::get('/admin', $home('super_admin'))->middleware('role:super_admin')->name('super_admin.home');
    Route::get('/direccion', $home('director'))->middleware('role:director,super_admin')->name('director.home');
    Route::get('/ponente', $home('ponente'))->middleware('role:ponente')->name('ponente.home');
    Route::get('/alumno', $home('alumno'))->middleware('role:alumno')->name('alumno.home');

    // Permisos en CoursePolicy (cada acción del controlador la autoriza).
    Route::resource('cursos', CourseController::class)->parameters(['cursos' => 'course'])->except('show')->names('courses');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/usuarios/{user}/foto', [ProfileController::class, 'avatar'])->name('avatar');
});
