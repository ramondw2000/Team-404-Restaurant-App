# Dishes Page Overhaul — Full Specification

## Overview

Overhaul the dishes page into a full menu management system with global ingredients, multiple menus with custom sub-categories, and allergen/dietary auto-derivation from ingredients. Convert from vanilla JS + Blade to Livewire. Single page with sidebar navigation.

---

## Data Model

### Ingredient (new model)

| Field | Type | Notes |
|-------|------|-------|
| id | bigint | PK |
| name | string(255) | Unique |
| allergens | json | Array of allergen keys (gluten, nuts, milk, wheat, fish, egg) |
| dietary | json | Array of dietary keys (vegetarian, vegan) |
| timestamps | | |

- Global library — one master list shared across all dishes.
- **Cannot be deleted while referenced by any dish** (block deletion).
- Dedicated CRUD interface (ingredient library tab) + quick-create inline from dish edit form.

### Dish (modified model)

| Field | Type | Notes |
|-------|------|-------|
| id | bigint | PK |
| name | string(255) | |
| description | text, nullable | |
| price | decimal(8,2) | |
| color | string, default `#309bcf` | Card background hex color |
| photo_path | string, nullable | |
| timestamps | | |

**Removed from dish:** `category`, `allergens`, `dietary` — these are now derived.

- **Allergens/dietary are auto-derived** from the dish's ingredients using "least permissive" logic:
  - Allergens: union of all ingredient allergens.
  - Dietary: intersection — dish is only "vegan" if ALL ingredients are vegan.
- No manual override. Single source of truth = ingredients.
- Allergen/dietary icons still displayed on dish cards (computed, not stored).

### DishIngredient (pivot, new)

| Field | Type | Notes |
|-------|------|-------|
| dish_id | FK → dishes | |
| ingredient_id | FK → ingredients | |

Simple many-to-many. No quantity/unit data.

### Menu (new model)

| Field | Type | Notes |
|-------|------|-------|
| id | bigint | PK |
| name | string(255) | |
| description | text, nullable | |
| status | string | `draft` or `published` |
| timestamps | | |

### MenuCategory (new model)

| Field | Type | Notes |
|-------|------|-------|
| id | bigint | PK |
| menu_id | FK → menus | |
| name | string(255) | |
| sort_order | integer | For drag-sort ordering |
| timestamps | | |

- Predefined defaults (Starters, Mains, Desserts, Drinks, Sides) seeded when creating a new menu, but fully customizable — users can add, rename, remove, reorder.
- Categories orderable via drag-and-drop within a menu.

### MenuCategoryDish (pivot, new)

| Field | Type | Notes |
|-------|------|-------|
| menu_category_id | FK → menu_categories | |
| dish_id | FK → dishes | |
| sort_order | integer | For ordering within category |

- A dish can appear on **multiple menus** and in **different categories** per menu.
- Dish has **no inherent category** — category exists only in context of a menu assignment.
- When a category is deleted, its dish assignments are removed (dishes unassigned from that menu).

---

## Page Layout

### Structure: Sidebar + Main Content

```
┌──────────────────────────────────────────────┐
│  Header / Page Title                         │
├────────────┬─────────────────────────────────┤
│  SIDEBAR   │  MAIN CONTENT AREA              │
│            │                                  │
│ ▼ Library  │  [Search] [Filters] [Sort]       │
│   All Dish │                                  │
│   Ingredi. │  ┌─────┐ ┌─────┐ ┌─────┐       │
│            │  │Card │ │Card │ │Card │        │
│ ▼ Menus    │  └─────┘ └─────┘ └─────┘       │
│  ▼ Lunch   │  ┌─────┐ ┌─────┐ ┌─────┐       │
│    Starter │  │Card │ │Card │ │Card │        │
│    Mains   │  └─────┘ └─────┘ └─────┘       │
│    Dessert │                                  │
│  ▶ Dinner  │  [1] [2] [3] ... [Pg Size ▼]   │
│            │                                  │
│ [+ Menu]   │                                  │
├────────────┴─────────────────────────────────┤
│  (Slide-in sheet overlays main content →)    │
└──────────────────────────────────────────────┘
```

### Sidebar (left)

- **Expandable tree navigation:**
  - **Library** section (always present):
    - "All Dishes" — shows full dish grid
    - "Ingredients" — shows ingredient library
  - **Menus** section:
    - Each menu listed, expandable to show its sub-categories
    - Click a category → main area shows dishes in that category
    - Click menu name → shows all dishes in that menu grouped by category
    - Draft menus visually distinguished (e.g. muted/italic)
  - **"+ New Menu"** button at bottom of menus section
- Categories within a menu are **drag-sortable** in the sidebar.
- Sidebar **stays visible** when slide-in sheet is open. Sheet overlays main content only.

### Main Content Area

Depends on sidebar selection:

#### "All Dishes" view
- Search bar + filter pills (allergen/dietary, derived from ingredients) + sort options
- Dish card grid (current visual style maintained — color cards, photo, allergen icons)
- **Pagination:** Traditional page numbers at bottom
  - **Page size:** User-configurable dropdown (12 / 20 / 40), stored in session
- "+ New Dish" button

#### "Ingredients" view
- Searchable table/list of all ingredients
- Columns: Name, Allergens (icons), Dietary (icons), # Dishes using
- Inline or modal CRUD
- Delete blocked if ingredient in use (show count of dishes using it)

#### Menu view (specific menu selected)
- Menu header: name, description, status badge (Draft/Published), edit/delete controls
- Dishes grouped by category with category headers
- "Add Dishes" button per category → search/select from dish library → assign
- Sort options within category: alphabetical, price, or manual drag ordering

### Slide-in Sheet (dish create/edit)

Keeps current right-side slide-in pattern. Extended with:

1. **Basic Info** — name, description, price, color, photo (existing fields)
2. **Ingredients** — inline editable table:
   - Rows of assigned ingredients with remove button
   - "Add row" at bottom → type-ahead search of ingredient library
   - "Create new ingredient" quick-action if not found
   - Computed allergen/dietary summary shown below table (read-only, auto-derived)
3. **Menu Assignments** — list of menus + category this dish belongs to:
   - Each row: Menu name → Category dropdown
   - "+ Add to menu" button
   - Remove assignment button
   - (Two-way: can also assign from menu view)

---

## Behavior & Business Rules

### Allergen/Dietary Derivation
- **Allergens:** Union of all ingredient allergens. If any ingredient has "gluten", dish has "gluten".
- **Dietary:** Intersection. Dish is "vegan" only if ALL ingredients are vegan. Dish is "vegetarian" only if ALL ingredients are vegetarian (or vegan).
- Computed at read time (accessor on model), not stored.

### Menu States
- **Draft:** Visible in management UI, visually distinguished. Can be edited freely.
- **Published:** Active menu. No restrictions on editing.
- No scheduling — manual publish/unpublish only.

### Category Deletion
- Deleting a menu category **removes all dish assignments** for that category.
- Dishes themselves are NOT deleted — just unassigned from that menu.

### Ingredient Deletion
- **Blocked** if any dish uses it. UI shows count of affected dishes.
- Must remove ingredient from all dishes first before deletion allowed.

### Dish Deletion
- Removes all menu assignments (cascades pivot records).
- Removes all ingredient associations (cascades pivot records).
- Cleans up photo from storage (existing behavior).

### Sort Options (within menu category view)
- Alphabetical (A-Z, Z-A)
- Price (low-high, high-low)
- Manual ordering (drag-and-drop, persisted via `sort_order` on pivot)

---

## Tech Stack

### Full Livewire Conversion
- Convert entire dishes page from vanilla JS + Blade to **Livewire components**.
- Leverage Livewire for: search/filter reactivity, pagination, inline editing, drag-and-drop (via Alpine.js integration), sheet open/close state.

### Component Architecture (suggested)
- `DishesPage` — parent Livewire component (page-level)
- `Sidebar` — Livewire component for tree navigation + menu/category management
- `DishGrid` — dish card grid with pagination, search, filters
- `DishSheet` — slide-in create/edit form
- `IngredientLibrary` — ingredient CRUD table
- `MenuView` — menu-specific dish display grouped by category

### Pagination
- Livewire's built-in pagination.
- Page size persisted in session (12 / 20 / 40).
- Traditional numbered page links.

---

## Data Migration & Seeding

### Fresh Start
- New seeder replaces existing 37-dish seeder.
- Seed data includes:
  - Sample ingredients with allergen/dietary data
  - Sample dishes with ingredient associations
  - One or two sample menus (e.g. "Lunch Menu" published, "Seasonal Special" draft) with categories and dish assignments
- Old `category`, `allergens`, `dietary` columns removed from dishes migration.

---

## Existing Features to Preserve

- Dish card visual style (color background, photo, allergen/dietary icons, hover effects)
- Photo upload with drag-drop (5MB limit, storage cleanup)
- Search functionality (now Livewire-powered)
- Filter pills for allergens/dietary (now filtering based on computed values from ingredients)
- Allergen icon system from `config/restaurant.php`
- Blue theme color palette (#309bcf, #005693, #eaf4fa)
- Responsive grid layout (adapt column count for sidebar presence)