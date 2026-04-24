<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class BarDrinkSeeder extends Seeder
{
    /**
     * Seed 20 bar drinks (is_bar_item=true). Idempotent on dish name + ingredient name.
     */
    public function run(): void
    {
        $ingredients = $this->ensureIngredients();
        $this->ensureDrinks($ingredients);
    }

    /**
     * @return array<string, Ingredient>
     */
    private function ensureIngredients(): array
    {
        /** @var array<string, array{allergens: list<string>, dietary: list<string>}> $data */
        $data = [
            'Water' => ['allergens' => [], 'dietary' => ['vegan']],
            'Sugar' => ['allergens' => [], 'dietary' => ['vegan']],
            'Lemon' => ['allergens' => [], 'dietary' => ['vegan']],
            'Mixed Fruit' => ['allergens' => [], 'dietary' => ['vegan']],
            'Coffee' => ['allergens' => [], 'dietary' => ['vegan']],
            'Cream' => ['allergens' => ['milk'], 'dietary' => ['vegetarian']],
            'Red Wine' => ['allergens' => [], 'dietary' => ['vegan']],
            'White Wine' => ['allergens' => [], 'dietary' => ['vegan']],
            'Prosecco' => ['allergens' => [], 'dietary' => ['vegan']],
            'Aperol' => ['allergens' => [], 'dietary' => ['vegan']],
            'Campari' => ['allergens' => [], 'dietary' => ['vegan']],
            'Gin' => ['allergens' => [], 'dietary' => ['vegan']],
            'Tonic Water' => ['allergens' => [], 'dietary' => ['vegan']],
            'White Rum' => ['allergens' => [], 'dietary' => ['vegan']],
            'Mint' => ['allergens' => [], 'dietary' => ['vegan']],
            'Lime' => ['allergens' => [], 'dietary' => ['vegan']],
            'Vodka' => ['allergens' => [], 'dietary' => ['vegan']],
            'Coffee Liqueur' => ['allergens' => [], 'dietary' => ['vegan']],
            'Sweet Vermouth' => ['allergens' => [], 'dietary' => ['vegan']],
            'Whiskey' => ['allergens' => [], 'dietary' => ['vegan']],
            'Tequila' => ['allergens' => [], 'dietary' => ['vegan']],
            'Triple Sec' => ['allergens' => [], 'dietary' => ['vegan']],
            'Cola' => ['allergens' => [], 'dietary' => ['vegan']],
            'Lager' => ['allergens' => ['gluten', 'wheat'], 'dietary' => ['vegan']],
            'Peach Purée' => ['allergens' => [], 'dietary' => ['vegan']],
            'Milk' => ['allergens' => ['milk'], 'dietary' => ['vegetarian']],
            'Cocoa' => ['allergens' => [], 'dietary' => ['vegan']],
            'Tea Leaves' => ['allergens' => [], 'dietary' => ['vegan']],
            'Limoncello' => ['allergens' => [], 'dietary' => ['vegan']],
            'Grappa' => ['allergens' => [], 'dietary' => ['vegan']],
        ];

        $map = [];
        foreach ($data as $name => $attrs) {
            $map[$name] = Ingredient::firstOrCreate(
                ['name' => $name],
                ['allergens' => $attrs['allergens'], 'dietary' => $attrs['dietary']],
            );
        }

        return $map;
    }

    /**
     * @param  array<string, Ingredient>  $ing
     */
    private function ensureDrinks(array $ing): void
    {
        /** @var array<string, array{price: float, color: string, description: string, ingredients: list<string>}> $drinks */
        $drinks = [
            'Negroni' => ['price' => 8.50, 'color' => '#a02020', 'description' => 'Classic Italian aperitivo: gin, Campari, sweet vermouth.', 'ingredients' => ['Gin', 'Campari', 'Sweet Vermouth']],
            'Aperol Spritz' => ['price' => 7.00, 'color' => '#e06010', 'description' => 'Aperol, prosecco, splash of soda.', 'ingredients' => ['Aperol', 'Prosecco', 'Water']],
            'Campari Soda' => ['price' => 6.00, 'color' => '#c41e3a', 'description' => 'Bittersweet Campari topped with soda water.', 'ingredients' => ['Campari', 'Water']],
            'Gin Tonic' => ['price' => 7.50, 'color' => '#7faecf', 'description' => 'Premium gin with chilled tonic and lime.', 'ingredients' => ['Gin', 'Tonic Water', 'Lime']],
            'Mojito' => ['price' => 8.00, 'color' => '#7fbf6f', 'description' => 'White rum, fresh mint, lime, sugar.', 'ingredients' => ['White Rum', 'Mint', 'Lime', 'Sugar', 'Water']],
            'Espresso Martini' => ['price' => 9.00, 'color' => '#3a2010', 'description' => 'Vodka shaken with espresso and coffee liqueur.', 'ingredients' => ['Vodka', 'Coffee', 'Coffee Liqueur']],
            'Bellini' => ['price' => 8.00, 'color' => '#f5b8a3', 'description' => 'Prosecco with white peach purée.', 'ingredients' => ['Prosecco', 'Peach Purée']],
            'Manhattan' => ['price' => 9.50, 'color' => '#7a1f1f', 'description' => 'Whiskey, sweet vermouth, dash of bitters.', 'ingredients' => ['Whiskey', 'Sweet Vermouth']],
            'Margarita' => ['price' => 8.50, 'color' => '#d8e85a', 'description' => 'Tequila, triple sec, fresh lime.', 'ingredients' => ['Tequila', 'Triple Sec', 'Lime']],
            'Whiskey Sour' => ['price' => 9.00, 'color' => '#b07030', 'description' => 'Whiskey, lemon, sugar — classic and tart.', 'ingredients' => ['Whiskey', 'Lemon', 'Sugar']],
            'Cuba Libre' => ['price' => 7.50, 'color' => '#3a1a08', 'description' => 'White rum, cola, lime.', 'ingredients' => ['White Rum', 'Cola', 'Lime']],
            'Birra alla Spina' => ['price' => 5.00, 'color' => '#e8b830', 'description' => 'Italian lager on tap.', 'ingredients' => ['Lager']],
            'Prosecco Glass' => ['price' => 6.50, 'color' => '#f0e090', 'description' => 'Single glass of crisp Italian prosecco.', 'ingredients' => ['Prosecco']],
            'Vino Rosso (Glass)' => ['price' => 6.50, 'color' => '#6a1020', 'description' => 'House red wine by the glass.', 'ingredients' => ['Red Wine']],
            'Vino Bianco (Glass)' => ['price' => 6.50, 'color' => '#c8b840', 'description' => 'House white wine by the glass.', 'ingredients' => ['White Wine']],
            'Limoncello Shot' => ['price' => 4.50, 'color' => '#c8d820', 'description' => 'Chilled lemon liqueur, served straight.', 'ingredients' => ['Limoncello']],
            'Grappa' => ['price' => 5.00, 'color' => '#e8d8b0', 'description' => 'Italian grape brandy digestif.', 'ingredients' => ['Grappa']],
            'Espresso' => ['price' => 2.50, 'color' => '#3a2010', 'description' => 'Single shot of Italian espresso.', 'ingredients' => ['Coffee']],
            'Cappuccino' => ['price' => 3.50, 'color' => '#a07050', 'description' => 'Espresso with steamed and foamed milk.', 'ingredients' => ['Coffee', 'Milk']],
            'Hot Chocolate' => ['price' => 4.00, 'color' => '#4a2010', 'description' => 'Rich Italian-style cocoa with milk.', 'ingredients' => ['Cocoa', 'Milk', 'Sugar']],
        ];

        foreach ($drinks as $name => $attrs) {
            /** @var Dish $dish */
            $dish = Dish::updateOrCreate(
                ['name' => $name],
                [
                    'description' => $attrs['description'],
                    'price' => $attrs['price'],
                    'color' => $attrs['color'],
                    'is_available' => true,
                    'is_bar_item' => true,
                ],
            );

            $ingredientIds = collect($attrs['ingredients'])->map(fn (string $n): int => $ing[$n]->id)->all();
            $dish->ingredients()->sync($ingredientIds);
        }
    }
}
