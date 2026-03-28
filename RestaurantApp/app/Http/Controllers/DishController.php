<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DishController extends Controller
{
    public function index(): View
    {
        $dishes = Dish::all()->map(function ($dish) {
            return [
                'id' => $dish->id,
                'name' => $dish->name,
                'description' => $dish->description,
                'price' => $dish->price,
                'category' => $dish->category,
                'allergens' => $dish->allergens ?? [],
                'dietary' => $dish->dietary ?? [],
                'color' => $dish->color,
                'photo_path' => $dish->photo_path,
            ];
        })->toArray();

        // Seed with default data if empty
        if (empty($dishes)) {
            $defaultDishes = [
                /* ── Starters ─────────────────────────────────────── */
                ['name' => 'Bruschetta al Pomodoro',      'price' => 7.00,  'category' => 'Starters', 'allergens' => ['gluten', 'wheat'],              'dietary' => ['vegan'],                  'color' => '#c07830'],
                ['name' => 'Caprese Salad',                'price' => 9.00,  'category' => 'Starters', 'allergens' => ['milk'],                        'dietary' => ['vegetarian'],             'color' => '#5a9e6e'],
                ['name' => 'Caesar Salad',                 'price' => 10.50, 'category' => 'Starters', 'allergens' => ['gluten', 'milk'],               'dietary' => [],                         'color' => '#6b9e7e'],
                ['name' => 'Minestrone Soup',              'price' => 7.00,  'category' => 'Starters', 'allergens' => [],                              'dietary' => ['vegan', 'vegetarian'],     'color' => '#7a9e6e'],
                ['name' => 'Arancini di Riso',             'price' => 9.50,  'category' => 'Starters', 'allergens' => ['gluten', 'wheat', 'milk'],       'dietary' => ['vegetarian'],             'color' => '#d4a050'],
                ['name' => 'Antipasto Misto',              'price' => 11.00, 'category' => 'Starters', 'allergens' => ['milk'],                        'dietary' => [],                         'color' => '#c06050'],
                ['name' => 'Vitello Tonnato',              'price' => 12.50, 'category' => 'Starters', 'allergens' => [],                              'dietary' => [],                         'color' => '#d4c090'],
                ['name' => 'Insalata di Mare',             'price' => 10.00, 'category' => 'Starters', 'allergens' => [],                              'dietary' => [],                         'color' => '#3a8eb0'],
                ['name' => 'Panzanella',                   'price' => 8.50,  'category' => 'Starters', 'allergens' => ['gluten', 'wheat'],              'dietary' => ['vegan', 'vegetarian'],     'color' => '#e07030'],
                ['name' => 'Focaccia al Rosmarino',        'price' => 5.50,  'category' => 'Starters', 'allergens' => ['gluten', 'wheat'],              'dietary' => ['vegan'],                  'color' => '#a07030'],
                /* ── Mains ────────────────────────────────────────── */
                ['name' => 'Spaghetti Bolognese',          'price' => 14.50, 'category' => 'Mains',    'allergens' => ['gluten', 'wheat', 'milk'],       'dietary' => [],                         'color' => '#c0603a'],
                ['name' => 'Margherita Pizza',             'price' => 12.00, 'category' => 'Mains',    'allergens' => ['gluten', 'wheat', 'milk'],       'dietary' => ['vegetarian'],             'color' => '#d4a836'],
                ['name' => 'Grilled Salmon',               'price' => 18.00, 'category' => 'Mains',    'allergens' => [],                              'dietary' => [],                         'color' => '#3a6ec0'],
                ['name' => 'Mushroom Risotto',             'price' => 13.00, 'category' => 'Mains',    'allergens' => ['milk'],                        'dietary' => ['vegetarian'],             'color' => '#7a5c3a'],
                ['name' => 'Penne Arrabbiata',             'price' => 11.00, 'category' => 'Mains',    'allergens' => ['gluten', 'wheat'],              'dietary' => ['vegan'],                  'color' => '#c05050'],
                ['name' => 'Beef Tenderloin',              'price' => 26.00, 'category' => 'Mains',    'allergens' => [],                              'dietary' => [],                         'color' => '#7a3a2a'],
                ['name' => 'Pasta Carbonara',              'price' => 14.00, 'category' => 'Mains',    'allergens' => ['gluten', 'wheat', 'milk'],       'dietary' => [],                         'color' => '#b08a40'],
                ['name' => 'Vegan Buddha Bowl',            'price' => 11.50, 'category' => 'Mains',    'allergens' => [],                              'dietary' => ['vegan', 'vegetarian'],     'color' => '#3a8e5a'],
                ['name' => 'Risotto ai Frutti di Mare',   'price' => 19.50, 'category' => 'Mains',    'allergens' => ['milk'],                        'dietary' => [],                         'color' => '#005693'],
                ['name' => 'Tagliatelle al Ragù',          'price' => 13.50, 'category' => 'Mains',    'allergens' => ['gluten', 'wheat', 'milk'],       'dietary' => [],                         'color' => '#8a3020'],
                ['name' => 'Lasagne al Forno',             'price' => 15.00, 'category' => 'Mains',    'allergens' => ['gluten', 'wheat', 'milk'],       'dietary' => ['vegetarian'],             'color' => '#c04030'],
                ['name' => 'Osso Buco',                    'price' => 23.00, 'category' => 'Mains',    'allergens' => [],                              'dietary' => [],                         'color' => '#7a5020'],
                ['name' => 'Saltimbocca alla Romana',      'price' => 20.00, 'category' => 'Mains',    'allergens' => ['gluten', 'wheat'],              'dietary' => [],                         'color' => '#b06050'],
                ['name' => 'Branzino al Forno',            'price' => 22.00, 'category' => 'Mains',    'allergens' => [],                              'dietary' => [],                         'color' => '#4a7e9e'],
                ['name' => 'Pollo alla Cacciatora',        'price' => 17.50, 'category' => 'Mains',    'allergens' => [],                              'dietary' => [],                         'color' => '#c07840'],
                ['name' => 'Gnocchi al Gorgonzola',        'price' => 14.00, 'category' => 'Mains',    'allergens' => ['gluten', 'wheat', 'milk', 'nuts'], 'dietary' => ['vegetarian'],             'color' => '#6a5e9e'],
                ['name' => 'Ribollita',                    'price' => 12.00, 'category' => 'Mains',    'allergens' => ['gluten', 'wheat'],              'dietary' => ['vegan', 'vegetarian'],     'color' => '#5a7e4a'],
                ['name' => 'Polenta e Funghi',             'price' => 13.00, 'category' => 'Mains',    'allergens' => ['milk'],                        'dietary' => ['vegetarian'],             'color' => '#9a7040'],
                /* ── Desserts ─────────────────────────────────────── */
                ['name' => 'Tiramisu',                     'price' => 7.50,  'category' => 'Desserts', 'allergens' => ['gluten', 'wheat', 'milk', 'nuts'], 'dietary' => ['vegetarian'],             'color' => '#8e3a59'],
                ['name' => 'Panna Cotta',                  'price' => 6.50,  'category' => 'Desserts', 'allergens' => ['milk'],                        'dietary' => ['vegetarian'],             'color' => '#309bcf'],
                ['name' => 'Mixed Nut Tart',               'price' => 8.00,  'category' => 'Desserts', 'allergens' => ['gluten', 'wheat', 'nuts', 'milk'], 'dietary' => ['vegetarian'],             'color' => '#6b4e2a'],
                ['name' => 'Cannoli Siciliani',             'price' => 7.00,  'category' => 'Desserts', 'allergens' => ['gluten', 'wheat', 'milk'],       'dietary' => ['vegetarian'],             'color' => '#e0a040'],
                ['name' => 'Torta della Nonna',             'price' => 7.50,  'category' => 'Desserts', 'allergens' => ['gluten', 'wheat', 'milk', 'nuts'], 'dietary' => ['vegetarian'],             'color' => '#c09060'],
                ['name' => 'Gelato al Limone',              'price' => 5.50,  'category' => 'Desserts', 'allergens' => [],                              'dietary' => ['vegan', 'vegetarian'],     'color' => '#e8c830'],
                ['name' => 'Semifreddo al Cioccolato',      'price' => 7.00,  'category' => 'Desserts', 'allergens' => ['milk'],                        'dietary' => ['vegetarian'],             'color' => '#4a2010'],
                ['name' => 'Crostata di Ricotta',           'price' => 8.00,  'category' => 'Desserts', 'allergens' => ['gluten', 'wheat', 'milk'],       'dietary' => ['vegetarian'],             'color' => '#d4a870'],
                ['name' => 'Caffè Affogato',                'price' => 5.00,  'category' => 'Desserts', 'allergens' => ['milk'],                        'dietary' => ['vegetarian'],             'color' => '#3a2010'],
                /* ── Drinks ───────────────────────────────────────── */
                ['name' => 'Acqua Minerale',               'price' => 3.00,  'category' => 'Drinks',   'allergens' => [],                              'dietary' => ['vegan', 'vegetarian'],     'color' => '#90c0e0'],
                ['name' => 'Vino Rosso della Casa',         'price' => 6.50,  'category' => 'Drinks',   'allergens' => [],                              'dietary' => ['vegan', 'vegetarian'],     'color' => '#6a1020'],
                ['name' => 'Vino Bianco della Casa',        'price' => 6.50,  'category' => 'Drinks',   'allergens' => [],                              'dietary' => ['vegan', 'vegetarian'],     'color' => '#c8b840'],
                ['name' => 'Limoncello',                   'price' => 5.00,  'category' => 'Drinks',   'allergens' => [],                              'dietary' => ['vegan', 'vegetarian'],     'color' => '#c8d820'],
                ['name' => 'Spritz Aperol',                'price' => 6.00,  'category' => 'Drinks',   'allergens' => [],                              'dietary' => ['vegan', 'vegetarian'],     'color' => '#e06010'],
                ['name' => 'Succo di Frutta',              'price' => 4.00,  'category' => 'Drinks',   'allergens' => [],                              'dietary' => ['vegan', 'vegetarian'],     'color' => '#d04060'],
                /* ── Sides ────────────────────────────────────────── */
                ['name' => 'Pane e Coperto',               'price' => 3.50,  'category' => 'Sides',    'allergens' => ['gluten', 'wheat'],              'dietary' => ['vegan'],                  'color' => '#c0a060'],
                ['name' => 'Verdure Grigliate',             'price' => 6.00,  'category' => 'Sides',    'allergens' => [],                              'dietary' => ['vegan', 'vegetarian'],     'color' => '#5a9e4a'],
                ['name' => 'Patate al Forno',               'price' => 5.50,  'category' => 'Sides',    'allergens' => [],                              'dietary' => ['vegan', 'vegetarian'],     'color' => '#c09030'],
            ];

            foreach ($defaultDishes as $dishData) {
                Dish::create($dishData);
            }

            $dishes = Dish::all()->map(function ($dish) {
                return [
                    'id' => $dish->id,
                    'name' => $dish->name,
                    'price' => $dish->price,
                    'category' => $dish->category,
                    'allergens' => $dish->allergens ?? [],
                    'dietary' => $dish->dietary ?? [],
                    'color' => $dish->color,
                    'photo_path' => $dish->photo_path,
                ];
            })->toArray();
        }

        /** @var array<string, array{label: string, bg: string, icon: string}> */
        $allergenConfig = config('restaurant.allergens');

        return view('dishes', compact('dishes', 'allergenConfig'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|in:Starters,Mains,Desserts,Drinks,Sides',
            'allergens' => 'nullable|array',
            'allergens.*' => 'string|in:gluten,nuts,milk,wheat,fish,egg',
            'dietary' => 'nullable|array',
            'dietary.*' => 'string|in:vegetarian,vegan',
            'color' => 'nullable|string|max:7',
            'photo' => 'nullable|image|max:5120',
        ]);

        $validated['allergens'] = $validated['allergens'] ?? [];
        $validated['dietary'] = $validated['dietary'] ?? [];
        $validated['color'] = $validated['color'] ?? '#309bcf';

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('dishes', 'public');
        }

        unset($validated['photo']);
        Dish::create($validated);

        return redirect()->route('dishes')->with('success', 'Dish created successfully.');
    }

    public function update(Request $request, Dish $dish): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|in:Starters,Mains,Desserts,Drinks,Sides',
            'allergens' => 'nullable|array',
            'allergens.*' => 'string|in:gluten,nuts,milk,wheat,fish,egg',
            'dietary' => 'nullable|array',
            'dietary.*' => 'string|in:vegetarian,vegan',
            'color' => 'nullable|string|max:7',
            'photo' => 'nullable|image|max:5120',
        ]);

        $validated['allergens'] = $validated['allergens'] ?? [];
        $validated['dietary'] = $validated['dietary'] ?? [];

        if ($request->hasFile('photo')) {
            if ($dish->photo_path) {
                Storage::disk('public')->delete($dish->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('dishes', 'public');
        }

        unset($validated['photo']);
        $dish->update($validated);

        return redirect()->route('dishes')->with('success', 'Dish updated successfully.');
    }

    public function destroy(Dish $dish): RedirectResponse
    {
        if ($dish->photo_path) {
            Storage::disk('public')->delete($dish->photo_path);
        }

        $dish->delete();

        return redirect()->route('dishes')->with('success', 'Dish deleted successfully.');
    }
}
