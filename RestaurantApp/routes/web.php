<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\KitchenOrderController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\StatisticsController;
use App\Livewire\Dishes\DishesPage;
use App\Livewire\Orders\BarOrderPage;
use App\Livewire\Orders\OrderPage;
use App\Livewire\Reservations;
use App\Livewire\TableManagement;
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

    Route::get('/impersonation/status', [ImpersonationController::class, 'status'])
        ->name('impersonation.status');

    Route::get('/accounts', [AccountController::class, 'index'])
        ->middleware('permission:View Account Management')
        ->name('accounts.index');

    Route::post('/accounts', [AccountController::class, 'store'])
        ->middleware('permission:Create User')
        ->name('accounts.store');

    Route::put('/accounts/{account}', [AccountController::class, 'update'])
        ->middleware('permission:Edit User')
        ->name('accounts.update');

    Route::delete('/accounts/{account}', [AccountController::class, 'destroy'])
        ->middleware('permission:Delete User')
        ->name('accounts.destroy');

    Route::get('/statistics', [StatisticsController::class, 'index'])
        ->middleware('permission:View Statistics')
        ->name('statistics');

    Route::get('/tablemanagement', TableManagement::class)
        ->middleware('permission:View Table Management')
        ->name('tablemanagement');

    Route::get('/kitchenorders/poll', [KitchenOrderController::class, 'poll'])
        ->middleware('permission:View Kitchen Orders')
        ->name('kitchen-orders.poll');

    Route::patch('/kitchenorders/items/{orderItem}/ready', [KitchenOrderController::class, 'markDishReady'])
        ->middleware('permission:Mark Orders Ready')
        ->name('kitchen-orders.dish.ready');

    Route::patch('/kitchenorders/orders/{order}/complete', [KitchenOrderController::class, 'completeOrder'])
        ->middleware('permission:Mark Orders Ready')
        ->name('kitchen-orders.order.complete');

    Route::delete('/kitchenorders/orders/{order}', [KitchenOrderController::class, 'deleteOrder'])
        ->name('kitchen-orders.order.delete');

    // Combined Orders page with Kitchen/Bar toggle
    Route::get('/orders', function () {
        return view('orders');
    })->middleware('permission:View Kitchen Orders|View Bar Orders')
        ->name('orders');

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
        ->middleware('permission:Add Dishes')
        ->name('dishes.store');

    Route::post('/dishes/{dish}/update', [DishController::class, 'update'])
        ->middleware('permission:Edit Dishes')
        ->name('dishes.update');

    Route::delete('/dishes/{dish}', [DishController::class, 'destroy'])
        ->middleware('permission:Delete Dishes')
        ->name('dishes.destroy');

    Route::patch('/dishes/{dish}/toggle-availability', [DishController::class, 'toggleAvailability'])
        ->middleware('permission:Edit Dishes')
        ->name('dishes.toggle-availability');

    Route::get('/maintenance', [MaintenanceController::class, 'index'])
        ->middleware('permission:View Maintenance')
        ->name('maintenance');

    Route::post('/maintenance', [MaintenanceController::class, 'store'])
        ->middleware('permission:Create Maintenance Task')
        ->name('maintenance.store');

    Route::patch('/maintenance/{task}/notes', [MaintenanceController::class, 'updateNotes'])
        ->middleware('permission:Edit Maintenance Task')
        ->name('maintenance.updateNotes');

    Route::patch('/maintenance/{task}/done', [MaintenanceController::class, 'markAsDone'])
        ->middleware('permission:Complete Maintenance Task')
        ->name('maintenance.markAsDone');

    Route::patch('/maintenance/{task}/assign', [MaintenanceController::class, 'assign'])
        ->middleware('permission:View Maintenance')
        ->name('maintenance.assign');

    Route::patch('/maintenance/{task}/unassign', [MaintenanceController::class, 'unassign'])
        ->middleware('permission:View Maintenance')
        ->name('maintenance.unassign');

    Route::patch('/maintenance/{task}/status', [MaintenanceController::class, 'transitionStatus'])
        ->middleware('permission:View Maintenance')
        ->name('maintenance.transitionStatus');

    Route::livewire('/orders/create/{floorPlanElement}', OrderPage::class)
        ->middleware('permission:Create Order')
        ->name('orders.create');

    Route::livewire('/bar-orders/create', BarOrderPage::class)
        ->middleware('permission:Create Bar Order')
        ->name('bar-orders.create');
});

require __DIR__.'/auth.php';
