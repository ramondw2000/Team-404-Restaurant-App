<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\Menu;
use Illuminate\Database\Seeder;

class DishSeeder extends Seeder
{
    public function run(): void
    {
        $ingredients = $this->seedIngredients();
        $dishes = $this->seedDishes($ingredients);
        $this->seedMenus($dishes);
    }

    /**
     * @return array<string, Ingredient>
     */
    private function seedIngredients(): array
    {
        /** @var array<string, array{allergens: list<string>, dietary: list<string>}> $data */
        $data = [
            'Tomato' => ['allergens' => [], 'dietary' => ['vegan']],
            'Basil' => ['allergens' => [], 'dietary' => ['vegan']],
            'Olive Oil' => ['allergens' => [], 'dietary' => ['vegan']],
            'Bread' => ['allergens' => ['gluten', 'wheat'], 'dietary' => ['vegan']],
            'Mozzarella' => ['allergens' => ['milk'], 'dietary' => ['vegetarian']],
            'Parmesan' => ['allergens' => ['milk'], 'dietary' => ['vegetarian']],
            'Pasta' => ['allergens' => ['gluten', 'wheat'], 'dietary' => ['vegan']],
            'Egg' => ['allergens' => ['egg'], 'dietary' => ['vegetarian']],
            'Cream' => ['allergens' => ['milk'], 'dietary' => ['vegetarian']],
            'Beef Mince' => ['allergens' => [], 'dietary' => []],
            'Salmon Fillet' => ['allergens' => ['fish'], 'dietary' => []],
            'Chicken Breast' => ['allergens' => [], 'dietary' => []],
            'Arborio Rice' => ['allergens' => [], 'dietary' => ['vegan']],
            'Mushroom' => ['allergens' => [], 'dietary' => ['vegan']],
            'Lettuce' => ['allergens' => [], 'dietary' => ['vegan']],
            'Croutons' => ['allergens' => ['gluten', 'wheat'], 'dietary' => ['vegan']],
            'Mixed Vegetables' => ['allergens' => [], 'dietary' => ['vegan']],
            'Walnuts' => ['allergens' => ['nuts'], 'dietary' => ['vegan']],
            'Gorgonzola' => ['allergens' => ['milk', 'nuts'], 'dietary' => ['vegetarian']],
            'Ricotta' => ['allergens' => ['milk'], 'dietary' => ['vegetarian']],
            'Mascarpone' => ['allergens' => ['milk'], 'dietary' => ['vegetarian']],
            'Coffee' => ['allergens' => [], 'dietary' => ['vegan']],
            'Sugar' => ['allergens' => [], 'dietary' => ['vegan']],
            'Lemon' => ['allergens' => [], 'dietary' => ['vegan']],
            'Potato' => ['allergens' => [], 'dietary' => ['vegan']],
            'Prosciutto' => ['allergens' => [], 'dietary' => []],
            'Veal Shank' => ['allergens' => [], 'dietary' => []],
            'Sea Bass' => ['allergens' => ['fish'], 'dietary' => []],
            'Polenta' => ['allergens' => [], 'dietary' => ['vegan']],
            'Mixed Fruit' => ['allergens' => [], 'dietary' => ['vegan']],
            'Red Wine' => ['allergens' => [], 'dietary' => ['vegan']],
            'White Wine' => ['allergens' => [], 'dietary' => ['vegan']],
            'Water' => ['allergens' => [], 'dietary' => ['vegan']],
            'Aperol' => ['allergens' => [], 'dietary' => ['vegan']],
            'Prosecco' => ['allergens' => [], 'dietary' => ['vegan']],
            'Chocolate' => ['allergens' => ['milk'], 'dietary' => ['vegetarian']],
        ];

        $map = [];

        foreach ($data as $name => $attrs) {
            $map[$name] = Ingredient::create([
                'name' => $name,
                'allergens' => $attrs['allergens'],
                'dietary' => $attrs['dietary'],
            ]);
        }

        return $map;
    }

    /**
     * @param  array<string, Ingredient>  $ing
     * @return array<string, Dish>
     */
    private function seedDishes(array $ing): array
    {
        /** @var array<string, array{price: float, color: string, ingredients: list<string>, description?: string}> $data */
        $data = [
            // Starters
            'Bruschetta al Pomodoro' => ['price' => 7.00, 'color' => '#c07830', 'ingredients' => ['Bread', 'Tomato', 'Basil', 'Olive Oil']],
            'Caprese Salad' => ['price' => 9.00, 'color' => '#5a9e6e', 'ingredients' => ['Tomato', 'Mozzarella', 'Basil', 'Olive Oil']],
            'Caesar Salad' => ['price' => 10.50, 'color' => '#6b9e7e', 'ingredients' => ['Lettuce', 'Croutons', 'Parmesan', 'Chicken Breast']],
            'Minestrone Soup' => ['price' => 7.00, 'color' => '#7a9e6e', 'ingredients' => ['Mixed Vegetables', 'Tomato', 'Olive Oil', 'Basil']],
            'Arancini di Riso' => ['price' => 9.50, 'color' => '#d4a050', 'ingredients' => ['Arborio Rice', 'Mozzarella', 'Bread', 'Egg']],
            'Antipasto Misto' => ['price' => 11.00, 'color' => '#c06050', 'ingredients' => ['Prosciutto', 'Mozzarella', 'Olive Oil']],
            'Insalata di Mare' => ['price' => 10.00, 'color' => '#3a8eb0', 'ingredients' => ['Salmon Fillet', 'Lettuce', 'Olive Oil', 'Lemon']],
            'Panzanella' => ['price' => 8.50, 'color' => '#e07030', 'ingredients' => ['Bread', 'Tomato', 'Basil', 'Olive Oil']],
            'Focaccia al Rosmarino' => ['price' => 5.50, 'color' => '#a07030', 'ingredients' => ['Bread', 'Olive Oil']],

            // Mains
            'Spaghetti Bolognese' => ['price' => 14.50, 'color' => '#c0603a', 'ingredients' => ['Pasta', 'Beef Mince', 'Tomato', 'Parmesan']],
            'Margherita Pizza' => ['price' => 12.00, 'color' => '#d4a836', 'ingredients' => ['Bread', 'Tomato', 'Mozzarella', 'Basil']],
            'Grilled Salmon' => ['price' => 18.00, 'color' => '#3a6ec0', 'ingredients' => ['Salmon Fillet', 'Lemon', 'Olive Oil']],
            'Mushroom Risotto' => ['price' => 13.00, 'color' => '#7a5c3a', 'ingredients' => ['Arborio Rice', 'Mushroom', 'Parmesan', 'Cream']],
            'Penne Arrabbiata' => ['price' => 11.00, 'color' => '#c05050', 'ingredients' => ['Pasta', 'Tomato', 'Olive Oil', 'Basil']],
            'Beef Tenderloin' => ['price' => 26.00, 'color' => '#7a3a2a', 'ingredients' => ['Beef Mince', 'Olive Oil', 'Potato']],
            'Pasta Carbonara' => ['price' => 14.00, 'color' => '#b08a40', 'ingredients' => ['Pasta', 'Egg', 'Parmesan', 'Cream']],
            'Vegan Buddha Bowl' => ['price' => 11.50, 'color' => '#3a8e5a', 'ingredients' => ['Mixed Vegetables', 'Arborio Rice', 'Olive Oil', 'Lemon']],
            'Tagliatelle al Ragù' => ['price' => 13.50, 'color' => '#8a3020', 'ingredients' => ['Pasta', 'Beef Mince', 'Tomato', 'Parmesan']],
            'Lasagne al Forno' => ['price' => 15.00, 'color' => '#c04030', 'ingredients' => ['Pasta', 'Mozzarella', 'Ricotta', 'Tomato']],
            'Osso Buco' => ['price' => 23.00, 'color' => '#7a5020', 'ingredients' => ['Veal Shank', 'Tomato', 'Olive Oil']],
            'Saltimbocca alla Romana' => ['price' => 20.00, 'color' => '#b06050', 'ingredients' => ['Chicken Breast', 'Prosciutto', 'Bread']],
            'Branzino al Forno' => ['price' => 22.00, 'color' => '#4a7e9e', 'ingredients' => ['Sea Bass', 'Lemon', 'Olive Oil', 'Potato']],
            'Pollo alla Cacciatora' => ['price' => 17.50, 'color' => '#c07840', 'ingredients' => ['Chicken Breast', 'Tomato', 'Mushroom', 'Olive Oil']],
            'Gnocchi al Gorgonzola' => ['price' => 14.00, 'color' => '#6a5e9e', 'ingredients' => ['Potato', 'Gorgonzola', 'Walnuts', 'Bread']],
            'Ribollita' => ['price' => 12.00, 'color' => '#5a7e4a', 'ingredients' => ['Bread', 'Mixed Vegetables', 'Tomato', 'Olive Oil']],
            'Polenta e Funghi' => ['price' => 13.00, 'color' => '#9a7040', 'ingredients' => ['Polenta', 'Mushroom', 'Parmesan', 'Cream']],

            // Desserts
            'Tiramisu' => ['price' => 7.50, 'color' => '#8e3a59', 'ingredients' => ['Mascarpone', 'Coffee', 'Egg', 'Bread', 'Sugar']],
            'Panna Cotta' => ['price' => 6.50, 'color' => '#309bcf', 'ingredients' => ['Cream', 'Sugar']],
            'Mixed Nut Tart' => ['price' => 8.00, 'color' => '#6b4e2a', 'ingredients' => ['Walnuts', 'Bread', 'Sugar', 'Cream']],
            'Cannoli Siciliani' => ['price' => 7.00, 'color' => '#e0a040', 'ingredients' => ['Ricotta', 'Bread', 'Sugar']],
            'Torta della Nonna' => ['price' => 7.50, 'color' => '#c09060', 'ingredients' => ['Ricotta', 'Egg', 'Bread', 'Sugar', 'Walnuts']],
            'Gelato al Limone' => ['price' => 5.50, 'color' => '#e8c830', 'ingredients' => ['Lemon', 'Sugar', 'Water']],
            'Semifreddo al Cioccolato' => ['price' => 7.00, 'color' => '#4a2010', 'ingredients' => ['Chocolate', 'Cream', 'Sugar']],
            'Crostata di Ricotta' => ['price' => 8.00, 'color' => '#d4a870', 'ingredients' => ['Ricotta', 'Bread', 'Egg', 'Sugar']],
            'Caffè Affogato' => ['price' => 5.00, 'color' => '#3a2010', 'ingredients' => ['Coffee', 'Cream', 'Sugar']],

            // Drinks
            'Acqua Minerale' => ['price' => 3.00, 'color' => '#90c0e0', 'ingredients' => ['Water']],
            'Vino Rosso della Casa' => ['price' => 6.50, 'color' => '#6a1020', 'ingredients' => ['Red Wine']],
            'Vino Bianco della Casa' => ['price' => 6.50, 'color' => '#c8b840', 'ingredients' => ['White Wine']],
            'Limoncello' => ['price' => 5.00, 'color' => '#c8d820', 'ingredients' => ['Lemon', 'Sugar']],
            'Spritz Aperol' => ['price' => 6.00, 'color' => '#e06010', 'ingredients' => ['Aperol', 'Prosecco', 'Water']],
            'Succo di Frutta' => ['price' => 4.00, 'color' => '#d04060', 'ingredients' => ['Mixed Fruit', 'Sugar', 'Water']],

            // Sides
            'Pane e Coperto' => ['price' => 3.50, 'color' => '#c0a060', 'ingredients' => ['Bread', 'Olive Oil']],
            'Verdure Grigliate' => ['price' => 6.00, 'color' => '#5a9e4a', 'ingredients' => ['Mixed Vegetables', 'Olive Oil']],
            'Patate al Forno' => ['price' => 5.50, 'color' => '#c09030', 'ingredients' => ['Potato', 'Olive Oil']],
        ];

        $map = [];

        foreach ($data as $name => $attrs) {
            $dish = Dish::create([
                'name' => $name,
                'price' => $attrs['price'],
                'color' => $attrs['color'],
            ]);

            $ingredientIds = collect($attrs['ingredients'])->map(fn (string $n): int => $ing[$n]->id);
            $dish->ingredients()->attach($ingredientIds);

            $map[$name] = $dish;
        }

        return $map;
    }

    /**
     * @param  array<string, Dish>  $dishes
     */
    private function seedMenus(array $dishes): void
    {
        // Published lunch menu
        $lunch = Menu::create(['name' => 'Lunch Menu', 'description' => 'Available daily from 11:30 to 15:00', 'status' => 'published']);
        $lunch->seedDefaultCategories();
        $lunchCategories = $lunch->categories->keyBy('name');

        $assignments = [
            'Starters' => ['Bruschetta al Pomodoro', 'Caprese Salad', 'Caesar Salad', 'Minestrone Soup', 'Panzanella', 'Focaccia al Rosmarino'],
            'Mains' => ['Spaghetti Bolognese', 'Margherita Pizza', 'Penne Arrabbiata', 'Mushroom Risotto', 'Vegan Buddha Bowl', 'Lasagne al Forno', 'Ribollita'],
            'Desserts' => ['Tiramisu', 'Panna Cotta', 'Gelato al Limone', 'Caffè Affogato'],
            'Drinks' => ['Acqua Minerale', 'Vino Rosso della Casa', 'Vino Bianco della Casa', 'Succo di Frutta'],
            'Sides' => ['Pane e Coperto', 'Verdure Grigliate', 'Patate al Forno'],
        ];

        foreach ($assignments as $catName => $dishNames) {
            $category = $lunchCategories[$catName];
            foreach ($dishNames as $i => $dishName) {
                $category->dishes()->attach($dishes[$dishName]->id, ['sort_order' => $i]);
            }
        }

        // Draft seasonal menu
        $seasonal = Menu::create(['name' => 'Seasonal Special', 'description' => 'Chef\'s seasonal selection', 'status' => 'draft']);
        $seasonal->seedDefaultCategories();
        $seasonalCategories = $seasonal->categories->keyBy('name');

        $seasonalAssignments = [
            'Starters' => ['Insalata di Mare', 'Antipasto Misto', 'Arancini di Riso'],
            'Mains' => ['Grilled Salmon', 'Osso Buco', 'Branzino al Forno', 'Gnocchi al Gorgonzola', 'Polenta e Funghi'],
            'Desserts' => ['Mixed Nut Tart', 'Semifreddo al Cioccolato', 'Torta della Nonna'],
            'Drinks' => ['Limoncello', 'Spritz Aperol'],
        ];

        foreach ($seasonalAssignments as $catName => $dishNames) {
            $category = $seasonalCategories[$catName];
            foreach ($dishNames as $i => $dishName) {
                $category->dishes()->attach($dishes[$dishName]->id, ['sort_order' => $i]);
            }
        }
    }
}
