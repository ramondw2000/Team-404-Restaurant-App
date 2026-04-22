<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class BarOrderController extends Controller
{
    public function index(): View
    {
        $orders = [
            [
                'id' => 'ORD-047', 'type' => 'table', 'table' => 'A3', 'time' => '18:32', 'waiter' => 'Sofia R.',
                'drinks' => [
                    ['name' => 'Aperol Spritz', 'qty' => 2, 'notes' => 'Extra ice', 'status' => 'pending'],
                    ['name' => 'Acqua Minerale', 'qty' => 3, 'notes' => 'Still water, no ice', 'status' => 'pending'],
                    ['name' => 'Espresso Martini', 'qty' => 1, 'notes' => '', 'status' => 'pending'],
                ],
            ],
            [
                'id' => 'ORD-046', 'type' => 'bar', 'table' => null, 'time' => '18:28', 'waiter' => 'Marco D.',
                'drinks' => [
                    ['name' => 'Negroni', 'qty' => 1, 'notes' => 'Less Campari', 'status' => 'pending'],
                    ['name' => 'Birra alla Spina', 'qty' => 2, 'notes' => '', 'status' => 'pending'],
                ],
            ],
            [
                'id' => 'ORD-045', 'type' => 'table', 'table' => 'B7', 'time' => '18:14', 'waiter' => 'Elena V.',
                'drinks' => [
                    ['name' => 'Vino Rosso della Casa', 'qty' => 1, 'notes' => '', 'status' => 'served'],
                    ['name' => 'Limoncello', 'qty' => 2, 'notes' => 'Chilled glasses', 'status' => 'served'],
                    ['name' => 'Caffè Americano', 'qty' => 1, 'notes' => '', 'status' => 'served'],
                ],
            ],
            [
                'id' => 'ORD-044', 'type' => 'bar', 'table' => null, 'time' => '18:09', 'waiter' => 'Marco D.',
                'drinks' => [
                    ['name' => 'Mojito', 'qty' => 2, 'notes' => 'One virgin', 'status' => 'pending'],
                    ['name' => 'Prosecco', 'qty' => 1, 'notes' => '', 'status' => 'pending'],
                    ['name' => 'Succo di Frutta', 'qty' => 1, 'notes' => 'Orange juice only', 'status' => 'served'],
                ],
            ],
            [
                'id' => 'ORD-043', 'type' => 'table', 'table' => 'A12', 'time' => '18:05', 'waiter' => 'Sofia R.',
                'drinks' => [
                    ['name' => 'Vino Bianco', 'qty' => 2, 'notes' => '', 'status' => 'pending'],
                    ['name' => 'San Pellegrino', 'qty' => 3, 'notes' => 'With lemon slices', 'status' => 'pending'],
                ],
            ],
            [
                'id' => 'ORD-042', 'type' => 'table', 'table' => 'C2', 'time' => '17:58', 'waiter' => 'Elena V.',
                'drinks' => [
                    ['name' => 'Grappa', 'qty' => 1, 'notes' => '', 'status' => 'served'],
                    ['name' => 'Caffè Affogato', 'qty' => 2, 'notes' => 'Decaf espresso', 'status' => 'served'],
                    ['name' => 'Amaretto Sour', 'qty' => 1, 'notes' => '', 'status' => 'served'],
                ],
            ],
            [
                'id' => 'ORD-041', 'type' => 'bar', 'table' => null, 'time' => '17:45', 'waiter' => 'Marco D.',
                'drinks' => [
                    ['name' => 'Gin Tonic', 'qty' => 2, 'notes' => 'Hendricks with cucumber', 'status' => 'served'],
                    ['name' => 'Bellini', 'qty' => 1, 'notes' => '', 'status' => 'served'],
                ],
            ],
            [
                'id' => 'ORD-040', 'type' => 'table', 'table' => 'B2', 'time' => '17:38', 'waiter' => 'Sofia R.',
                'drinks' => [
                    ['name' => 'Campari Soda', 'qty' => 1, 'notes' => '', 'status' => 'pending'],
                    ['name' => 'Chinotto', 'qty' => 2, 'notes' => '', 'status' => 'pending'],
                    ['name' => 'Espresso', 'qty' => 3, 'notes' => 'One decaf', 'status' => 'served'],
                ],
            ],
        ];

        foreach ($orders as &$order) {
            $statuses             = array_column($order['drinks'], 'status');
            $order['cnt_pending'] = count(array_filter($statuses, fn ($s) => $s === 'pending'));
            $order['cnt_ready']   = count(array_filter($statuses, fn ($s) => $s === 'ready'));
            $order['cnt_served']  = count(array_filter($statuses, fn ($s) => $s === 'served'));
            $order['cnt_total']   = count($statuses);
            $order['overall']     = $order['cnt_served'] === $order['cnt_total'] ? 'completed'
                                  : ($order['cnt_ready'] > 0 ? 'ready' : 'pending');
        }
        unset($order);

        $countActive    = count(array_filter($orders, fn ($o) => $o['overall'] !== 'completed'));
        $countCompleted = count(array_filter($orders, fn ($o) => $o['overall'] === 'completed'));
        $totalPending   = array_sum(array_column($orders, 'cnt_pending'));
        $totalReady     = array_sum(array_column($orders, 'cnt_ready'));

        return view('bar-orders', compact(
            'orders',
            'countActive',
            'countCompleted',
            'totalPending',
            'totalReady',
        ));
    }
}
