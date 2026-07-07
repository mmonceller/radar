<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Homepage;
use App\Livewire\CreateRequest;
use App\Livewire\ShowRequest;
// Import the profile controller or views provided by Breeze
use App\Http\Controllers\ProfileController;
use Livewire\Volt\Volt;


Route::get('/', Homepage::class)->name('home');

// Add inside your protected middleware block:
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Homepage::class)->name('dashboard');
    Route::get('/requests/create', CreateRequest::class)->name('requests.create');

    // Core Breeze Profile Routes that navigation.blade.php requires:
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public Accessible Route - uses Implicit Route Model Binding looking for {request} matches
Route::get('/requests/{request}', ShowRequest::class)->name('requests.show');

// Fallback lookup alias for the named profile route
Route::get('/profile-view', Homepage::class)->name('profile');


// Place this completely outside any middleware at the top of routes/web.php to test:
Volt::route('/requests/create', 'create-request')->name('requests.create');
require __DIR__.'/auth.php';