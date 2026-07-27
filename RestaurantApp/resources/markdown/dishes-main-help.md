# Dishes View

The Dishes view is the master list of every dish in the system. It is the default view of Menu Management and the place to add, edit, retire, and find dishes.

## What is a dish?

A **dish** is a single sellable item — a starter, main, dessert, side, or drink. Each dish has:
- A name, description, price, and photo
- A category (Starters, Mains, Desserts, Drinks, Sides)
- Dietary flags (Vegetarian, Vegan) — derived from ingredients
- An allergen list — derived from ingredients
- A list of **ingredients** drawn from the [Ingredient Library](#) (use the sidebar to switch views)
- An **available** flag — when off, the dish stays in the database but is hidden from the menu

Allergens and dietary flags are **automatically rolled up** from the dish's ingredients. You don't set them on the dish — you set them on the ingredients, and the dish inherits.

## Searching & Filtering

### Search box
Type any part of a name or description. Filters live as you type.

### Dietary pills
- **Vegetarian** — Show only vegetarian dishes
- **Vegan** — Show only vegan dishes

Click again to deselect.

### Free-from pills
Each pill matches an allergen. Activating *Gluten-free* shows only dishes with **no gluten** in any ingredient. Combine multiple pills (e.g. Gluten-free + Dairy-free) to narrow further.

### Sort
- **Name** — A→Z (click again for Z→A)
- **Price** — Low to high (click again for high to low)
- **Newest** — Most recently added first

### Show
Choose how many dishes appear per page: 12, 20, or 40.

## Adding a Dish

Click **Add Dish** in the page header to open the dish sheet. See the help inside that sheet for the full workflow.

## Editing or Removing a Dish

- Click any dish card to open the dish sheet pre-filled.
- Update fields and click **Save**.
- Use the **Delete** action inside the sheet to remove the dish permanently.
- To temporarily hide a dish, toggle **Available** off — it stays in the database but disappears from the menu.

## Reading a Dish Card

- **Photo** background tinted by dish colour
- **Name** + **price**
- **Category** badge
- **Allergen icons** — small coloured circles; hover for names
- **Dietary icons** — vegetarian / vegan markers
- **Dimmed card** = unavailable

If a card looks dim and grey, that dish is currently marked unavailable.

## Tips

- Keep dish photos consistent in aspect ratio for a tidy grid.
- Take seasonal items off the menu by marking *Unavailable* — don't delete; you can re-enable next season.
- Search matches both name and description, so use keywords in descriptions to help future-you find dishes.
- For allergen control, edit the **ingredient** rather than the dish. Changes flow into every dish that uses that ingredient.
