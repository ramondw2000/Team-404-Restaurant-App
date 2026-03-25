<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\ImpersonationController;
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
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Impersonation routes must be before the accounts resource to prevent route conflict
    // with DELETE /accounts/{account}
    Route::post('/accounts/impersonate/{target}', [ImpersonationController::class, 'start'])
        ->name('impersonation.start');

    Route::delete('/accounts/impersonate', [ImpersonationController::class, 'stop'])
        ->name('impersonation.stop');

    Route::resource('accounts', AccountController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('permission:View Account Management');

    Route::get('/statistics', [StatisticsController::class, 'index'])
        ->middleware('permission:View Statistics')
        ->name('statistics');

    Route::livewire('/tablemanagement', TableManagement::class)
        ->middleware('permission:View Table Management')
        ->name('tablemanagement');

    Route::get('/ordermanagement', [OrderManagementController::class, 'index'])
        ->middleware('permission:View Orders')
        ->name('ordermanagement');

    Route::get('/kitchenorders', [KitchenOrderController::class, 'index'])
        ->middleware('permission:View Kitchen Orders')
        ->name('kitchen-orders');

    Route::get('/dishes', [DishController::class, 'index'])
        ->middleware('permission:View Dishes')
        ->name('dishes');
});

require __DIR__.'/auth.php';
