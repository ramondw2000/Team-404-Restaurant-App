<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tablemanagement', function () {
    return view('tablemanagement');
});

Route::get('/statisticsOvervieuw', function () {
    return view('statisticsOvervieuw');
});


Route::get('/dishes', function () {
    return view('dishes');
});

Route::get('/orders', function () {
    return view('orders');
});

Route::get('/accounts',             [AccountController::class, 'index'])  ->name('accounts.index');
Route::post('/accounts',            [AccountController::class, 'store'])  ->name('accounts.store');
Route::put('/accounts/{account}',   [AccountController::class, 'update']) ->name('accounts.update');
Route::delete('/accounts/{account}',[AccountController::class, 'destroy'])->name('accounts.destroy');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
