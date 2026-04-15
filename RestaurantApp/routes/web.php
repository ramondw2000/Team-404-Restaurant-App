<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\BarOrderController;
use App\Http\Controllers\KitchenOrderController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\OrderManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\StatisticsController;
use App\Livewire\Reservations;
use App\Livewire\TableManagement;
use App\Livewire\Dishes\DishesPage;
use App\Livewire\Orders\OrderPage;
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

    Route::get('/tablemanagement', TableManagement::class)
        ->middleware('permission:View Table Management')
        ->name('tablemanagement');

    Route::get('/kitchenorders', [KitchenOrderController::class, 'index'])
        ->middleware('permission:View Kitchen Orders')
        ->name('kitchen-orders');

    Route::get('/barorders', [BarOrderController::class, 'index'])
        ->middleware('permission:View Bar Orders')
        ->name('bar-orders');

    Route::patch('/kitchenorders/items/{orderItem}/ready', [KitchenOrderController::class, 'markDishReady'])
        ->middleware('permission:Mark Orders Ready')
        ->name('kitchen-orders.dish.ready');

    Route::patch('/kitchenorders/orders/{order}/complete', [KitchenOrderController::class, 'completeOrder'])
        ->middleware('permission:Mark Orders Ready')
        ->name('kitchen-orders.order.complete');

    Route::livewire('/reservations', Reservations::class)
        ->middleware('permission:View Reservations')
        ->name('reservations.index');

    Route::post('/reservations', [ReservationController::class, 'store'])
        ->middleware('permission:Create Reservation')
        ->name('reservations.store');

    Route::put('/reservations/{reservation}', [ReservationController::class, 'update'])
        ->middleware('permission:Edit Reservation')
        ->name('reservations.update');

    Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])
        ->middleware('permission:Edit Reservation')
        ->name('reservations.updateStatus');

    Route::livewire('/dishes', DishesPage::class)
        ->middleware('permission:View Dishes')
        ->name('dishes');

    Route::post('/dishes', [DishController::class, 'store'])
        ->middleware('role:management|chef|bar_staff')
        ->name('dishes.store');

    Route::post('/dishes/{dish}/update', [DishController::class, 'update'])
        ->middleware('role:management|chef|bar_staff')
        ->name('dishes.update');

    Route::delete('/dishes/{dish}', [DishController::class, 'destroy'])
        ->middleware('role:management|chef|bar_staff')
        ->name('dishes.destroy');

    Route::get('/maintenance', [MaintenanceController::class, 'index'])
        ->name('maintenance');

    Route::patch('/maintenance/{task}/notes', [MaintenanceController::class, 'updateNotes'])
        ->name('maintenance.updateNotes');

    Route::patch('/maintenance/{task}/done', [MaintenanceController::class, 'markAsDone'])
        ->name('maintenance.markAsDone');

    Route::livewire('/orders/create/{floorPlanElement}', OrderPage::class)
        ->middleware('permission:Create Order')
        ->name('orders.create');
});

require __DIR__.'/auth.php';
