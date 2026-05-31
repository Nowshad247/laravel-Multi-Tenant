<?php

use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
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
            Route::get('/central/admin/show-user/{user}', [ManageUserController::class, 'show'])
                ->middleware('role:Super Admin|Editor')
                ->name('users.show');

            Route::put('/users/{user}', [ManageUserController::class, 'update'])
                ->middleware('role:Super Admin|Editor')
                ->name('users.update');

            Route::put('/users/{user}/role', [ManageUserController::class, 'updateRole'])
                ->middleware('role:Super Admin')
                ->name('users.role.update');
            Route::post('/users/{user}/avatar', [ManageUserController::class, 'updateAvatar'])
                ->middleware('role:Super Admin|Editor')
                ->name('users.avatar.update');
            Route::delete('/users/{user}/avatar', [ManageUserController::class, 'deleteAvatar'])
                ->middleware('role:Super Admin|Editor')
                ->name('users.avatar.delete');

            Route::middleware(['role:Super Admin'])->group(function () {
                Route::get('/userspermissions', [RoleController::class, 'index'])
                    ->name('users.permissions');
                Route::get('/role/create', [RoleController::class, 'create'])
                    ->name('role.create');
                Route::post('/role/store', [RoleController::class, 'store'])
                    ->name('role.store');
                Route::get('/role/edit/{id}', [RoleController::class, 'edit'])
                    ->name('role.edit');
                Route::put('/role/update/{id}', [RoleController::class, 'update'])
                    ->name('role.update');
                Route::delete('/role/delete/{id}', [RoleController::class, 'destroy'])
                    ->name('role.delete');
            });

            // site settings Configuration
            Route::middleware(['role:Super Admin'])->group(function () {
                Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
                Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
            });

            Route::get('/dashboard', function () {
                return Inertia::render('dashboard');
            })->name('central.dashboard');

            Route::get('/basic', function () {
                return Inertia::render('basic');
            })->name('central.basic');
        });

        Route::get('/show', [StudentController::class, 'index'])->name('student.show');

    });
}
require __DIR__.'/settings.php';
