<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard; 
use App\Livewire\Vehiculos;
use App\Livewire\Conductores;
use App\Livewire\Viajes;
use App\Livewire\Mantenimientos;
use App\Livewire\Combustible;



Route::redirect('/', '/login')->name('home');
Route::middleware(['auth', 'verified'])->group(function () {
//Route::view('dashboard', 'dashboard')->name('dashboard');

Route::get('/dashboard', Dashboard::class)->name('dashboard');

// CRUDs
Route::get('/vehiculos', Vehiculos::class)->name('vehiculos.index');
Route::get('/conductores', Conductores::class)->name('conductores.index');
Route::get('/viajes', Viajes::class)->name('viajes.index');
Route::get('/mantenimientos', Mantenimientos::class)->name('mantenimientos.index');
Route::get('/combustible', Combustible::class)->name('combustible.index');


});

require __DIR__.'/settings.php';
