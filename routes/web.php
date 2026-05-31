<?php

use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Central domain routes
foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            return Inertia::render('welcome', [
                'canRegister' => Features::enabled(Features::registration()),
            ]);
        })->name('central.home');

        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('/manage-users', [ManageUserController::class, 'index'])
                ->middleware('role:Super Admin|Editor')
                ->name('manage-users.index');
            Route::get('/users/{user}', [ManageUserController::class, 'show'])
                ->middleware('role:Super Admin|Editor')
                ->name('users.show');

            Route::put('/users/{user}', [ManageUserController::class, 'update'])
                ->middleware('role:Super Admin|Editor')
                ->name('users.update');
            Route::get('/dashboard', function () {
                return Inertia::render('dashboard');
            })->name('central.dashboard');

            Route::get('/basic', function () {
                return Inertia::render('basic');
            })->name('central.basic');

            Route::post('/basic-submit', [StudentController::class, 'create'])->name('basic.submit');
        });

        Route::get('/show', [StudentController::class, 'index'])->name('student.show');

    });
}
require __DIR__.'/settings.php';
