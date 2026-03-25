<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class OrderManagementController extends Controller
{
    public function index(): View
    {
        /** @var array<string, array{label: string, bg: string, icon: string}> */
        $allergenConfig = config('restaurant.allergens');

        $dishes = [
            /* Starters */
            ['id'=>1,  'name'=>'Bruschetta al Pomodoro',  'price'=>7.00,  'cat'=>'Starters', 'desc'=>'Toasted bread with tomato, garlic and basil',           'allergens'=>['gluten','wheat'],              'dietary'=>['vegan']],
            ['id'=>2,  'name'=>'Caprese Salad',            'price'=>9.00,  'cat'=>'Starters', 'desc'=>'Fresh mozzarella, tomato and basil with olive oil',     'allergens'=>['milk'],                        'dietary'=>['vegetarian']],
            ['id'=>3,  'name'=>'Caesar Salad',             'price'=>10.50, 'cat'=>'Starters', 'desc'=>'Romaine lettuce, parmesan, croutons and Caesar dressing','allergens'=>['gluten','milk'],              'dietary'=>[]],
            ['id'=>4,  'name'=>'Antipasto Misto',          'price'=>12.00, 'cat'=>'Starters', 'desc'=>'Selection of cured meats, cheeses and marinated vegetables','allergens'=>['milk'],                   'dietary'=>[]],
            ['id'=>5,  'name'=>'Minestrone Soup',          'price'=>7.00,  'cat'=>'Starters', 'desc'=>'Hearty vegetable soup with seasonal produce',           'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>6,  'name'=>'Arancini di Riso',         'price'=>9.50,  'cat'=>'Starters', 'desc'=>'Fried rice balls with mozzarella and tomato ragù',      'allergens'=>['gluten','wheat','milk'],       'dietary'=>['vegetarian']],
            /* Pasta & Risotto */
            ['id'=>7,  'name'=>'Spaghetti Bolognese',      'price'=>14.50, 'cat'=>'Pasta',    'desc'=>'Classic slow-cooked meat sauce with fresh spaghetti',  'allergens'=>['gluten','wheat','milk'],       'dietary'=>[]],
            ['id'=>8,  'name'=>'Pasta Carbonara',          'price'=>13.50, 'cat'=>'Pasta',    'desc'=>'Egg, guanciale, pecorino romano and black pepper',      'allergens'=>['gluten','wheat','egg'],        'dietary'=>[]],
            ['id'=>9,  'name'=>'Penne Arrabbiata',         'price'=>11.00, 'cat'=>'Pasta',    'desc'=>'Penne with spicy tomato and garlic sauce',              'allergens'=>['gluten','wheat'],              'dietary'=>['vegan']],
            ['id'=>10, 'name'=>'Mushroom Risotto',         'price'=>15.00, 'cat'=>'Pasta',    'desc'=>'Creamy arborio rice with porcini mushrooms and parmesan','allergens'=>['milk'],                      'dietary'=>['vegetarian']],
            /* Mains */
            ['id'=>11, 'name'=>'Grilled Salmon',           'price'=>22.00, 'cat'=>'Mains',    'desc'=>'Atlantic salmon fillet with seasonal vegetables',       'allergens'=>['fish'],                        'dietary'=>[]],
            ['id'=>12, 'name'=>'Beef Tenderloin',          'price'=>28.00, 'cat'=>'Mains',    'desc'=>'200g prime beef with roasted potatoes and green beans', 'allergens'=>[],                              'dietary'=>[]],
            ['id'=>13, 'name'=>'Osso Buco',                'price'=>24.00, 'cat'=>'Mains',    'desc'=>'Braised veal shank with gremolata and creamy polenta',  'allergens'=>['milk'],                        'dietary'=>[]],
            ['id'=>14, 'name'=>'Pollo alla Cacciatora',    'price'=>17.50, 'cat'=>'Mains',    'desc'=>'Slow-braised chicken with olives, capers and tomatoes', 'allergens'=>[],                              'dietary'=>[]],
            ['id'=>15, 'name'=>'Branzino al Forno',        'price'=>22.00, 'cat'=>'Mains',    'desc'=>'Oven-baked sea bass with lemon and herbs',              'allergens'=>['fish'],                        'dietary'=>[]],
            ['id'=>16, 'name'=>'Vegan Buddha Bowl',        'price'=>11.50, 'cat'=>'Mains',    'desc'=>'Quinoa, roasted vegetables, avocado and tahini dressing','allergens'=>[],                            'dietary'=>['vegan','vegetarian']],
            /* Desserts */
            ['id'=>17, 'name'=>'Tiramisu',                 'price'=>7.50,  'cat'=>'Desserts', 'desc'=>'Mascarpone cream with espresso-soaked ladyfingers',    'allergens'=>['gluten','wheat','milk','nuts'], 'dietary'=>['vegetarian']],
            ['id'=>18, 'name'=>'Panna Cotta',              'price'=>6.50,  'cat'=>'Desserts', 'desc'=>'Vanilla cream with fresh berry coulis',                'allergens'=>['milk'],                        'dietary'=>['vegetarian']],
            ['id'=>19, 'name'=>'Gelato al Limone',         'price'=>5.50,  'cat'=>'Desserts', 'desc'=>'Homemade lemon sorbet — dairy-free',                   'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>20, 'name'=>'Caffè Affogato',           'price'=>5.00,  'cat'=>'Desserts', 'desc'=>'Vanilla ice cream drowned in a shot of espresso',      'allergens'=>['milk'],                        'dietary'=>['vegetarian']],
            /* Drinks */
            ['id'=>21, 'name'=>'Acqua Minerale',           'price'=>3.00,  'cat'=>'Drinks',   'desc'=>'Still or sparkling, 75cl',                             'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>22, 'name'=>'Vino Rosso della Casa',    'price'=>6.50,  'cat'=>'Drinks',   'desc'=>'House red wine, glass',                                'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>23, 'name'=>'Vino Bianco della Casa',   'price'=>6.50,  'cat'=>'Drinks',   'desc'=>'House white wine, glass',                              'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>24, 'name'=>'Succo di Frutta',          'price'=>4.00,  'cat'=>'Drinks',   'desc'=>'Fresh fruit juice — orange, apple or pineapple',       'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            /* Sides */
            ['id'=>25, 'name'=>'Verdure Grigliate',        'price'=>6.00,  'cat'=>'Sides',    'desc'=>'Seasonal grilled vegetables with olive oil',           'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>26, 'name'=>'Patate al Forno',          'price'=>5.50,  'cat'=>'Sides',    'desc'=>'Crispy oven-roasted potatoes with rosemary',           'allergens'=>[],                              'dietary'=>['vegan','vegetarian']],
            ['id'=>27, 'name'=>'Pane e Coperto',           'price'=>3.50,  'cat'=>'Sides',    'desc'=>'Freshly baked bread with olive oil and butter',        'allergens'=>['gluten','wheat','milk'],       'dietary'=>['vegetarian']],
        ];

        $tables = ['A1','A2','A3','A4','A5','B1','B2','B3','B4','B7','C1','C2','C3','A12'];
        $categories = ['Starters','Pasta','Mains','Desserts','Drinks','Sides'];

        return view('ordermanagement', compact('allergenConfig', 'dishes', 'tables', 'categories'));
    }
}
