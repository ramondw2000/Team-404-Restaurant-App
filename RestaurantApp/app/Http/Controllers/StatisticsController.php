<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    public function index(): View
    {
        $completedOrders = collect([
            [
                'id' => 'ORD-045',
                'type' => 'restaurant',
                'location' => 'Table B7',
                'waiter' => 'Elena V.',
                'closed_at' => '18:14',
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
                'items' => [
                    ['name' => 'Mushroom Risotto', 'qty' => 1, 'price' => 15.00],
                    ['name' => 'Gelato al Limone', 'qty' => 1, 'price' => 5.50],
                    ['name' => 'Acqua Minerale', 'qty' => 1, 'price' => 3.00],
                ],
            ],
        ])->map(function (array $order): array {
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
            'completedOrders' => $completedOrders,
            'totalSales' => $totalSales,
            'orderCount' => $orderCount,
            'averageOrderValue' => $averageOrderValue,
            'salesByType' => $salesByType,
            'topItems' => $topItems,
            'recentOrders' => $recentOrders,
        ]);
    }
}
