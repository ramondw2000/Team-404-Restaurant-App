<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\KitchenOrderController;
use App\Http\Controllers\OrderManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'role:management|receptionist|server|chef|bar_staff'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('accounts', AccountController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('role:management');

    Route::get('/statistics', [StatisticsController::class, 'index'])
        ->middleware('role:management')
        ->name('statistics');

    Route::livewire('/tablemanagement', TableManagement::class)
        ->middleware('role:management|receptionist|server|maintenance_crew')
        ->name('tablemanagement');

    Route::get('/ordermanagement', [OrderManagementController::class, 'index'])
        ->middleware('role:management|receptionist|server')
        ->name('ordermanagement');

    Route::get('/kitchenorders', [KitchenOrderController::class, 'index'])
        ->middleware('role:management|receptionist|chef|bar_staff')
        ->name('kitchen-orders');

    Route::get('/dishes', [DishController::class, 'index'])
        ->middleware('role:management|chef|bar_staff')
        ->name('dishes');
});

require __DIR__.'/auth.php';
