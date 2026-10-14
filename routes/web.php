<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/dashboard', fn () => view('dashboard', ['role' => auth()->user()->role]))
    ->middleware('auth')
    ->name('dashboard');
