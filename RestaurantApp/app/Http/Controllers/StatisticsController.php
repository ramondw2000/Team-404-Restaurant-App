<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->get('period', 'day');
        $validPeriods = ['day', 'week', 'month', 'year'];
        if (! in_array($period, $validPeriods)) {
            $period = 'day';
        }

        $taxRate = (float) config('tax.rate');

        $completedOrders = $this->getOrdersForPeriod($period)->map(function (array $order) use ($taxRate): array {
            $subtotal = collect($order['items'])
                ->reduce(fn (float $carry, array $item): float => $carry + ($item['qty'] * $item['price']), 0.0);

            $tax = round($subtotal * $taxRate, 2);
            $subtotal = round($subtotal, 2);

            $order['subtotal'] = $subtotal;
            $order['tax'] = $tax;
            $order['total'] = round($subtotal + $tax, 2);

            return $order;
        });

        $totalSales = $completedOrders->sum('total');
        $orderCount = $completedOrders->count();
        $averageOrderValue = $orderCount > 0 ? $totalSales / $orderCount : 0;

        $groupedSales = $completedOrders->groupBy('type');

        $salesByType = collect([
            [
                'key' => 'restaurant',
                'label' => 'Restaurant Floor',
                'orders' => $groupedSales->get('restaurant', collect())->count(),
                'sales' => $groupedSales->get('restaurant', collect())->sum('total'),
            ],
            [
                'key' => 'room_service',
                'label' => 'Room Service',
                'orders' => $groupedSales->get('room_service', collect())->count(),
                'sales' => $groupedSales->get('room_service', collect())->sum('total'),
            ],
        ])->map(function (array $type) use ($totalSales): array {
            $type['share'] = $totalSales > 0 ? round(($type['sales'] / $totalSales) * 100) : 0;

            return $type;
        });

        $allItems = $completedOrders
            ->flatMap(fn (array $order) => $order['items'])
            ->groupBy('name')
            ->map(function (Collection $items, string $name) use ($taxRate) {
                $qty = $items->sum('qty');
                $subtotal = $items->reduce(
                    fn (float $carry, array $item): float => $carry + ($item['qty'] * $item['price']),
                    0.0,
                );
                $revenue = round($subtotal * (1 + $taxRate), 2);
                $isBarItem = $items->first()['is_bar_item'] ?? false;

                return [
                    'name' => $name,
                    'qty' => $qty,
                    'revenue' => $revenue,
                    'is_bar_item' => $isBarItem,
                ];
            });

        $dishItems = $allItems->filter(fn (array $item): bool => ! $item['is_bar_item']);
        $barItems = $allItems->filter(fn (array $item): bool => $item['is_bar_item']);

        $topItems = $dishItems->sortByDesc('qty')->values()->take(5);
        $leastSoldDishes = $dishItems->filter(fn (array $item): bool => $item['qty'] > 0)
            ->sortBy('qty')->values()->take(5);

        $topBarDrinks = $barItems->sortByDesc('qty')->values()->take(5);
        $leastSoldBarDrinks = $barItems->filter(fn (array $item): bool => $item['qty'] > 0)
            ->sortBy('qty')->values()->take(5);

        $totalBarRevenue = $barItems->sum('revenue');
        $totalDishRevenue = $dishItems->sum('revenue');

        $soldNames = $allItems->pluck('name')->all();

        $unsoldDishes = Dish::where('is_available', true)
            ->where('is_bar_item', false)
            ->whereNotIn('name', $soldNames)
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $unsoldBarDrinks = Dish::where('is_available', true)
            ->where('is_bar_item', true)
            ->whereNotIn('name', $soldNames)
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $recentOrders = $completedOrders->sortByDesc('closed_at')->values();

        return view('statistics', [
            'totalSales' => $totalSales,
            'orderCount' => $orderCount,
            'averageOrderValue' => $averageOrderValue,
            'salesByType' => $salesByType,
            'topItems' => $topItems,
            'leastSoldDishes' => $leastSoldDishes,
            'topBarDrinks' => $topBarDrinks,
            'leastSoldBarDrinks' => $leastSoldBarDrinks,
            'totalBarRevenue' => $totalBarRevenue,
            'totalDishRevenue' => $totalDishRevenue,
            'unsoldDishes' => $unsoldDishes,
            'unsoldBarDrinks' => $unsoldBarDrinks,
            'recentOrders' => $recentOrders,
            'period' => $period,
        ]);
    }

    private function getOrdersForPeriod(string $period): Collection
    {
        $query = Order::with(['items.dish', 'floorPlanElement', 'reservation', 'user'])
            ->where('status', OrderStatus::Completed);

        match ($period) {
            'day' => $query->whereDate('updated_at', today()),
            'week' => $query->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year),
            'year' => $query->whereYear('updated_at', now()->year),
        };

        $orders = $query->orderByDesc('updated_at')->get();

        return $orders->map(function (Order $order): array {
            $items = $order->items->map(fn ($item): array => [
                'name' => $item->dish?->name ?? 'Unknown',
                'qty' => $item->quantity,
                'price' => (float) $item->unit_price,
                'is_bar_item' => (bool) $item->dish?->is_bar_item,
            ])->all();

            return [
                'id' => 'ORD-'.str_pad((string) $order->id, 3, '0', STR_PAD_LEFT),
                'type' => 'restaurant',
                'location' => $order->floorPlanElement?->table_name ?? '—',
                'waiter' => $order->user?->name ?? '—',
                'customer' => $order->reservation?->guest_name ?? '—',
                'closed_at' => $order->updated_at?->format('H:i') ?? '—',
                'date' => $order->updated_at?->format('Y-m-d'),
                'items' => $items,
                'paid' => $order->paid,
            ];
        });
    }
}
