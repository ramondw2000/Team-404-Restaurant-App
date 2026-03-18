<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('accounts', AccountController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('role:management');

    Route::get('/dishes', function () {
        return view('dishes');
    })->name('dishes');

    Route::get('/statistics', [StatisticsController::class, 'index'])
        ->middleware('role:management')
        ->name('statistics');

    Route::get('/ordermanagement', function () {
        return view('ordermanagement');
    })->name('ordermanagement');

    Route::get('/orders', function () {
        return view('orders');
    })->name('orders');
});

require __DIR__.'/auth.php';
