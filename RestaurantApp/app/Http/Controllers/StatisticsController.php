<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
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

        $completedOrders = $this->getOrdersForPeriod($period)->map(function (array $order): array {
            $order['total'] = collect($order['items'])
                ->reduce(fn (float $carry, array $item): float => $carry + ($item['qty'] * $item['price']), 0);

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

        $topItems = $completedOrders
            ->flatMap(fn (array $order) => $order['items'])
            ->groupBy('name')
            ->map(function (Collection $items, string $name) {
                $qty = $items->sum('qty');
                $revenue = $items->reduce(
                    fn (float $carry, array $item): float => $carry + ($item['qty'] * $item['price']),
                    0,
                );

                return [
                    'name' => $name,
                    'qty' => $qty,
                    'revenue' => $revenue,
                ];
            })
            ->sortByDesc('revenue')
            ->values()
            ->take(5);

        $recentOrders = $completedOrders->sortByDesc('closed_at')->values();

        return view('statistics', [
            'totalSales' => $totalSales,
            'orderCount' => $orderCount,
            'averageOrderValue' => $averageOrderValue,
            'salesByType' => $salesByType,
            'topItems' => $topItems,
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
