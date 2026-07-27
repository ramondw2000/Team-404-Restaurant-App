<?php

namespace App\Providers;

use App\Enums\OrderStatus;
use App\Models\FloorPlanElement;
use App\Models\Order;
use App\Observers\OrderObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            if ($user->roles()->where('is_administrator', true)->exists()) {
                return true;
            }
        });

        Order::observe(OrderObserver::class);

        /** Block soft-deletion of floor plan elements with active orders. */
        FloorPlanElement::deleting(function (FloorPlanElement $element): void {
            $hasActiveOrders = $element->orders()
                ->whereIn('status', [OrderStatus::Draft->value, OrderStatus::Active->value])
                ->exists();

            if ($hasActiveOrders) {
                throw ValidationException::withMessages([
                    'floor_plan_element' => 'This table has active orders. Complete or cancel them before removing the table.',
                ]);
            }
        });
    }
}
