# Ingredient Library

The Ingredient Library is the single source of truth for every ingredient the kitchen uses. Dishes are built by linking to ingredients from this library — they don't store allergen or dietary info on their own.

## Why ingredients are central

- **Allergens are set once.** Mark "Wheat flour" as containing gluten once, and every dish that uses it inherits the allergen automatically.
- **Dietary flags propagate.** Mark "Tofu" as vegan; every dish using only vegan-marked ingredients shows up under the Vegan filter automatically.
- **Stock is shared.** Toggle an ingredient out of stock and every dish using it becomes unavailable until you restock.

This means good ingredient hygiene saves work — fix it once, every dish benefits.

## The Table

Each row shows:

| Column | What it means |
| --- | --- |
| **Name** | Ingredient name. *New* / *Updated* badges flag recent edits. |
| **Allergens** | Coloured icons for each allergen the ingredient contains. |
| **Dietary** | Vegetarian / vegan markers. |
| **Dishes** | How many dishes currently use this ingredient. Higher = bigger blast radius if you change it. |
| **Available** | Green toggle = in stock. Grey = out of stock. Toggling auto-disables all dishes that need it. |
| **Actions** | Edit / Delete. Delete is blocked while *Dishes* > 0. |

## Searching

Type any part of an ingredient name in the search box to filter live.

## Adding an Ingredient

1. Click **Add Ingredient** in the top-right.
2. Enter the name.
3. Tick each allergen it contains (Gluten, Dairy, Nuts, etc.).
4. Tick dietary flags (vegetarian / vegan) it qualifies for.
5. Save. The ingredient is now available to attach to dishes.

## Editing or Removing

- Click the **edit** icon on a row to update name, allergens, or dietary flags.
- Edits flow into every dish using this ingredient — verify the dishes list before changing allergens of a widely-used ingredient.
- Click **delete** to remove. If the ingredient is used by any dish, deletion is blocked — first remove it from those dishes.

## Stock Control (Available toggle)

The **Available** column is the critical day-to-day control:

- **Toggle off** when you run out of an ingredient mid-service.
- Every dish that uses it becomes **unavailable** immediately on the order screens.
- **Toggle back on** when restocked — dishes return to the menu automatically.

This is the safest way to handle stock-outs: faster than disabling individual dishes, and prevents servers from taking orders the kitchen can't fulfil.

## Tips

- **Be specific with names.** "Tomato (fresh)" and "Tomato (canned)" may have different allergens if canned versions add sulphites.
- **Audit periodically.** Sort by *Dishes* descending to find your most-used ingredients — those are the highest-impact rows to keep accurate.
- **Don't delete with history.** If an ingredient was used historically, mark it unavailable instead so old orders/reports still resolve.
