<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DishController extends Controller
{
    /**
     * Store a newly created dish.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:7'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'is_bar_item' => ['nullable', 'boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'color' => $validated['color'] ?? '#3b82f6',
            'is_bar_item' => (bool) ($validated['is_bar_item'] ?? false),
        ];

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('dishes', 'public');
        }

        $dish = Dish::create($data);

        // Sync ingredients if provided
        if ($request->has('ingredients')) {
            $dish->ingredients()->sync($request->input('ingredients'));
        }

        // Sync menu categories if provided
        if ($request->has('menu_categories')) {
            $dish->menuCategories()->sync($request->input('menu_categories'));
        }

        return redirect()->route('dishes')->with('success', 'Dish created successfully.');
    }

    /**
     * Update the specified dish.
     */
    public function update(Request $request, Dish $dish): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:7'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'is_bar_item' => ['nullable', 'boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'color' => $validated['color'] ?? $dish->color,
            'is_bar_item' => (bool) ($validated['is_bar_item'] ?? false),
        ];

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($dish->photo_path) {
                Storage::disk('public')->delete($dish->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('dishes', 'public');
        }

        $dish->update($data);

        // Sync ingredients if provided
        if ($request->has('ingredients')) {
            $dish->ingredients()->sync($request->input('ingredients'));
        }

        // Sync menu categories if provided
        if ($request->has('menu_categories')) {
            $dish->menuCategories()->sync($request->input('menu_categories'));
        }

        return redirect()->route('dishes')->with('success', 'Dish updated successfully.');
    }

    /**
     * Remove the specified dish.
     */
    public function destroy(Dish $dish): RedirectResponse
    {
        $dish->delete();

        return redirect()->route('dishes')->with('success', 'Dish deleted successfully.');
    }

    /**
     * Toggle the availability status of the specified dish.
     */
    public function toggleAvailability(Dish $dish): RedirectResponse
    {
        $dish->update(['is_available' => ! $dish->is_available]);

        $status = $dish->is_available ? 'available' : 'unavailable';

        return redirect()->route('dishes')->with('success', "Dish marked as {$status}.");
    }
}
