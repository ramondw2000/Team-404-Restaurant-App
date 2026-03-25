<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->get('period', 'day');
        $validPeriods = ['day', 'week', 'month', 'year'];
        if (!in_array($period, $validPeriods)) {
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
        $baseOrders = [
            [
                'id' => 'ORD-045',
                'type' => 'restaurant',
                'location' => 'Table B7',
                'waiter' => 'Elena V.',
                'closed_at' => '18:14',
                'date' => now()->format('Y-m-d'),
                'items' => [
                    ['name' => 'Grilled Salmon', 'qty' => 2, 'price' => 22.00],
                    ['name' => 'Beef Tenderloin', 'qty' => 1, 'price' => 28.00],
                    ['name' => 'Verdure Grigliate', 'qty' => 3, 'price' => 6.00],
                    ['name' => 'Vino Rosso della Casa', 'qty' => 1, 'price' => 6.50],
                ],
            ],
            [
                'id' => 'ORD-041',
                'type' => 'room_service',
                'location' => 'Room 312',
                'waiter' => 'Marco D.',
                'closed_at' => '17:45',
                'date' => now()->format('Y-m-d'),
                'items' => [
                    ['name' => 'Vegan Buddha Bowl', 'qty' => 1, 'price' => 11.50],
                    ['name' => 'Focaccia al Rosmarino', 'qty' => 1, 'price' => 5.50],
                    ['name' => 'Succo di Frutta', 'qty' => 2, 'price' => 4.00],
                ],
            ],
            [
                'id' => 'ORD-040',
                'type' => 'restaurant',
                'location' => 'Table B2',
                'waiter' => 'Sofia R.',
                'closed_at' => '17:38',
                'date' => now()->subDay()->format('Y-m-d'),
                'items' => [
                    ['name' => 'Minestrone Soup', 'qty' => 2, 'price' => 7.00],
                    ['name' => 'Pollo alla Cacciatora', 'qty' => 2, 'price' => 17.50],
                    ['name' => 'Caffè Affogato', 'qty' => 2, 'price' => 5.00],
                ],
            ],
            [
                'id' => 'ORD-039',
                'type' => 'room_service',
                'location' => 'Room 204',
                'waiter' => 'Marco D.',
                'closed_at' => '17:10',
                'date' => now()->subDay()->format('Y-m-d'),
                'items' => [
                    ['name' => 'Mushroom Risotto', 'qty' => 1, 'price' => 15.00],
                    ['name' => 'Gelato al Limone', 'qty' => 1, 'price' => 5.50],
                    ['name' => 'Acqua Minerale', 'qty' => 1, 'price' => 3.00],
                ],
            ],
            [
                'id' => 'ORD-038',
                'type' => 'restaurant',
                'location' => 'Table A1',
                'waiter' => 'Elena V.',
                'closed_at' => '16:30',
                'date' => now()->subWeek()->format('Y-m-d'),
                'items' => [
                    ['name' => 'Pasta Carbonara', 'qty' => 3, 'price' => 14.00],
                    ['name' => 'Tiramisu', 'qty' => 2, 'price' => 6.50],
                ],
            ],
            [
                'id' => 'ORD-037',
                'type' => 'restaurant',
                'location' => 'Table C3',
                'waiter' => 'Sofia R.',
                'closed_at' => '15:45',
                'date' => now()->subMonth()->format('Y-m-d'),
                'items' => [
                    ['name' => 'Lasagna Bolognese', 'qty' => 2, 'price' => 13.50],
                    ['name' => 'Panna Cotta', 'qty' => 2, 'price' => 5.00],
                    ['name' => 'Vino Bianco', 'qty' => 1, 'price' => 18.00],
                ],
            ],
            [
                'id' => 'ORD-036',
                'type' => 'room_service',
                'location' => 'Room 105',
                'waiter' => 'Marco D.',
                'closed_at' => '14:20',
                'date' => now()->subMonths(2)->format('Y-m-d'),
                'items' => [
                    ['name' => 'Margherita Pizza', 'qty' => 1, 'price' => 10.00],
                    ['name' => 'Caesar Salad', 'qty' => 1, 'price' => 9.50],
                ],
            ],
        ];

        $orders = collect($baseOrders);
        $today = now()->format('Y-m-d');
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $startOfYear = now()->startOfYear()->format('Y-m-d');

        return $orders->filter(function ($order) use ($period, $today, $startOfWeek, $startOfMonth, $startOfYear) {
            $orderDate = $order['date'];
            return match ($period) {
                'day' => $orderDate === $today,
                'week' => $orderDate >= $startOfWeek,
                'month' => $orderDate >= $startOfMonth,
                'year' => $orderDate >= $startOfYear,
                default => true,
            };
        })->values();
    }
}
