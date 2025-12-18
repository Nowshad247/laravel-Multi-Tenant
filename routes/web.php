<?php

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
    Route::get('/basic',function(){
        return Inertia::render('Basic/Basic');
    })->name('basic');
    Route::post('/basic-submit',[StudentController::class,'create'])->name('basic.submit');
});

Route::get('/show',[StudentController::class,'index'])->name('student.show');
require __DIR__.'/settings.php';
