<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class KitchenOrderController extends Controller
{
    public function index(): View
    {
        /** @var array<string, array{label: string, bg: string, icon: string}> */
        $allergenConfig = config('restaurant.allergens');

        $orders = [
            [
                'id' => 'ORD-047', 'type' => 'restaurant', 'table' => 'A3',  'room' => null,  'time' => '18:32', 'waiter' => 'Sofia R.',
                'dishes' => [
                    ['name'=>'Spaghetti Bolognese', 'qty'=>1, 'allergens'=>['gluten','wheat','milk'], 'notes'=>'Extra sauce on the side',                'status'=>'pending'],
                    ['name'=>'Margherita Pizza',     'qty'=>2, 'allergens'=>['gluten','wheat','milk'], 'notes'=>'Well done crust',                        'status'=>'pending'],
                    ['name'=>'Caesar Salad',         'qty'=>1, 'allergens'=>['gluten','milk'],         'notes'=>'No croutons — guest has gluten allergy', 'status'=>'pending'],
                ],
            ],
            [
                'id' => 'ORD-046', 'type' => 'room_service', 'table' => null, 'room' => '204', 'time' => '18:28', 'waiter' => 'Marco D.',
                'dishes' => [
                    ['name'=>'Mushroom Risotto', 'qty'=>1, 'allergens'=>['milk'],  'notes'=>'No parmesan, dairy allergy', 'status'=>'pending'],
                    ['name'=>'Panna Cotta',       'qty'=>1, 'allergens'=>['milk'],  'notes'=>'',                          'status'=>'pending'],
                    ['name'=>'Acqua Minerale',    'qty'=>2, 'allergens'=>[],        'notes'=>'Still water, no ice',       'status'=>'served'],
                ],
            ],
            [
                'id' => 'ORD-045', 'type' => 'restaurant', 'table' => 'B7',  'room' => null,  'time' => '18:14', 'waiter' => 'Elena V.',
                'dishes' => [
                    ['name'=>'Grilled Salmon',        'qty'=>2, 'allergens'=>[], 'notes'=>'One medium, one well done', 'status'=>'served'],
                    ['name'=>'Beef Tenderloin',        'qty'=>1, 'allergens'=>[], 'notes'=>'Medium rare',               'status'=>'served'],
                    ['name'=>'Verdure Grigliate',      'qty'=>3, 'allergens'=>[], 'notes'=>'',                          'status'=>'served'],
                    ['name'=>'Vino Rosso della Casa',  'qty'=>1, 'allergens'=>[], 'notes'=>'',                          'status'=>'served'],
                ],
            ],
            [
                'id' => 'ORD-044', 'type' => 'room_service', 'table' => null, 'room' => '118', 'time' => '18:09', 'waiter' => 'Marco D.',
                'dishes' => [
                    ['name'=>'Bruschetta al Pomodoro', 'qty'=>1, 'allergens'=>['gluten','wheat'],              'notes'=>'',                               'status'=>'served'],
                    ['name'=>'Pasta Carbonara',         'qty'=>1, 'allergens'=>['gluten','wheat','milk'],       'notes'=>'No guanciale, vegetarian guest', 'status'=>'pending'],
                    ['name'=>'Tiramisu',                'qty'=>2, 'allergens'=>['gluten','wheat','milk','nuts'],'notes'=>'Nut allergy — check recipe!',    'status'=>'pending'],
                ],
            ],
            [
                'id' => 'ORD-043', 'type' => 'restaurant', 'table' => 'A12', 'room' => null,  'time' => '18:05', 'waiter' => 'Sofia R.',
                'dishes' => [
                    ['name'=>'Antipasto Misto',  'qty'=>1, 'allergens'=>['milk'], 'notes'=>'No olives',                    'status'=>'pending'],
                    ['name'=>'Osso Buco',         'qty'=>2, 'allergens'=>[],       'notes'=>'',                             'status'=>'pending'],
                    ['name'=>'Polenta e Funghi',  'qty'=>1, 'allergens'=>['milk'], 'notes'=>'Dairy-free alternative please','status'=>'pending'],
                    ['name'=>'Patate al Forno',   'qty'=>2, 'allergens'=>[],       'notes'=>'Extra crispy',                 'status'=>'pending'],
                ],
            ],
            [
                'id' => 'ORD-042', 'type' => 'restaurant', 'table' => 'C2',  'room' => null,  'time' => '17:58', 'waiter' => 'Elena V.',
                'dishes' => [
                    ['name'=>'Caprese Salad',             'qty'=>2, 'allergens'=>['milk'], 'notes'=>'Extra basil',                    'status'=>'served'],
                    ['name'=>'Risotto ai Frutti di Mare', 'qty'=>1, 'allergens'=>['milk'], 'notes'=>'',                               'status'=>'served'],
                    ['name'=>'Branzino al Forno',         'qty'=>1, 'allergens'=>[],       'notes'=>'Lemon on the side',              'status'=>'pending'],
                    ['name'=>'Gelato al Limone',          'qty'=>3, 'allergens'=>[],       'notes'=>'One scoop only for table guest', 'status'=>'pending'],
                ],
            ],
            [
                'id' => 'ORD-041', 'type' => 'room_service', 'table' => null, 'room' => '312', 'time' => '17:45', 'waiter' => 'Marco D.',
                'dishes' => [
                    ['name'=>'Vegan Buddha Bowl',     'qty'=>1, 'allergens'=>[],               'notes'=>'No sesame seeds',   'status'=>'served'],
                    ['name'=>'Focaccia al Rosmarino', 'qty'=>1, 'allergens'=>['gluten','wheat'],'notes'=>'',                 'status'=>'served'],
                    ['name'=>'Succo di Frutta',       'qty'=>2, 'allergens'=>[],               'notes'=>'Orange juice only', 'status'=>'served'],
                ],
            ],
            [
                'id' => 'ORD-040', 'type' => 'restaurant', 'table' => 'B2',  'room' => null,  'time' => '17:38', 'waiter' => 'Sofia R.',
                'dishes' => [
                    ['name'=>'Minestrone Soup',       'qty'=>2, 'allergens'=>[],      'notes'=>'Extra bread on the side', 'status'=>'served'],
                    ['name'=>'Pollo alla Cacciatora', 'qty'=>2, 'allergens'=>[],      'notes'=>'',                        'status'=>'served'],
                    ['name'=>'Caffè Affogato',        'qty'=>2, 'allergens'=>['milk'],'notes'=>'Decaf espresso',          'status'=>'served'],
                ],
            ],
        ];

        foreach ($orders as &$order) {
            $statuses             = array_column($order['dishes'], 'status');
            $order['cnt_pending'] = count(array_filter($statuses, fn($s) => $s === 'pending'));
            $order['cnt_ready']   = count(array_filter($statuses, fn($s) => $s === 'ready'));
            $order['cnt_served']  = count(array_filter($statuses, fn($s) => $s === 'served'));
            $order['cnt_total']   = count($statuses);
            $order['overall']     = $order['cnt_served'] === $order['cnt_total'] ? 'completed'
                                  : ($order['cnt_ready'] > 0 ? 'ready' : 'pending');
        }
        unset($order);

        $countActive    = count(array_filter($orders, fn($o) => $o['overall'] !== 'completed'));
        $countCompleted = count(array_filter($orders, fn($o) => $o['overall'] === 'completed'));
        $totalPending   = array_sum(array_column($orders, 'cnt_pending'));
        $totalReady     = array_sum(array_column($orders, 'cnt_ready'));

        return view('kitchen-orders', compact(
            'allergenConfig',
            'orders',
            'countActive',
            'countCompleted',
            'totalPending',
            'totalReady',
        ));
    }
}
