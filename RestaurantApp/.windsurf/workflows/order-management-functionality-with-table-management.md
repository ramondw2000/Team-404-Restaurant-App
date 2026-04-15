# Order Management & Table Management Overhaul

## Overview

Overhaul the Order Management and Table Management pages so they operate on real database data (real `Menu`, `Dish`, `FloorPlanElement` models) rather than hardcoded arrays. The flow begins on the Table Management page, where staff selects a table and accepts or resumes an order, navigates to a Livewire-powered Order Management page to build the order, and places it — after which the table is marked Occupied and staff is redirected back to Table Management.

---

## 1. Data Model

### 1.1 New Models & Migrations

#### `Order`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `floor_plan_element_id` | bigint FK → `floor_plan_elements.id` | NOT NULL; deletion blocked if active orders exist |
| `status` | enum `draft\|active\|completed\|cancelled` | Default `draft` |
| `notes` | text nullable | Order-level notes |
| `timestamps` | | |

- **Enum class:** `App\Enums\OrderStatus` with cases `Draft`, `Active`, `Completed`, `Cancelled`.
- **Relationships:** `Order belongsTo FloorPlanElement`; `Order hasMany OrderItem`.
- Create factory + seeder.

#### `OrderItem`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `order_id` | bigint FK → `orders.id` | NOT NULL, cascade delete |
| `dish_id` | bigint FK → `dishes.id` | NOT NULL |
| `quantity` | unsignedTinyInteger | Default 1 |
| `unit_price` | decimal(8,2) | Snapshot of `Dish::price` at time of adding |
| `notes` | text nullable | Per-item special instructions |
| `status` | enum `pending\|preparing\|ready\|served` | Default `pending` |
| `course` | unsignedTinyInteger | Default 1 (kitchen use only, not shown in UI) |
| `timestamps` | | |

- **Enum class:** `App\Enums\OrderItemStatus` with cases `Pending`, `Preparing`, `Ready`, `Served`.
- **Relationships:** `OrderItem belongsTo Order`; `OrderItem belongsTo Dish`.
- Create factory + seeder.

### 1.2 FloorPlanElement Guard

In `FloorPlanElement` (or via an Eloquent observer / service), block soft-deletion of any element that has orders in `draft` or `active` status. Return a validation error: *"This table has active orders. Complete or cancel them before removing the table."*

---

## 2. Table Management Changes

### 2.1 Accept Order Button in Table Sheet

The existing `showTableSheet` slide-in panel (`openTableSheet` / `closeTableSheet` in `TableManagement.php`) gains an **Accept Order** button.

- **Always visible** on any table in view mode (edit mode excluded).
- **If the table already has a `draft` or `active` order:**
  - Show a confirmation dialog: *"A draft order already exists for this table — resume it or start a new one?"*
  - **Resume** → navigate to `route('orders.create', $element)` with the existing draft order pre-loaded.
  - **Start New** → cancel the existing draft (set status = `cancelled`, revert table to `Available`), then navigate to `route('orders.create', $element)`.
- **If no active order exists:** navigate directly to `route('orders.create', $element)`.

### 2.2 Table Status Automation

| Event | Table Status Change |
|---|---|
| Staff clicks **Accept Order** (draft created) | → `Occupied` (set immediately when Order is created with status `draft`) |
| Order status → `completed` | → `Available` |
| Order status → `cancelled` | → `Available` |

The status update is triggered by an Eloquent observer on `Order` (`created`, `updated`).

### 2.3 No Table Switching Within Order Management

There is no in-page table switcher on the Order Management page. Staff must click **Back to Table Management** to select a different table.

---

## 3. Order Management Page

### 3.1 Routing

```
GET  /orders/create/{floorPlanElement}   → App\Livewire\Orders\OrderPage
```

- Named route: `orders.create`
- `{floorPlanElement}` is a route-model-bound `FloorPlanElement` (no soft-deleted elements).
- The Livewire component receives the element and either finds/resumes the existing `draft` order or creates a new one.
- **Permission guard:** `Create Order` permission required to access this route.

### 3.2 Livewire Component: `App\Livewire\Orders\OrderPage`

**Architecture:** Livewire page shell + Alpine.js cart UI.

- Rendered with `->layout('layouts.molveno')`.
- Page title: `#[Title('New Order')]`.
- Livewire manages: active menu/category selection, dish querying (search + category filter), draft order creation.
- Alpine.js manages: cart state (items array, quantities, notes), order bar open/close, review screen open/close, add-dish modal open/close.
- On **Place Order** (review screen confirm), a single Livewire method call receives the full Alpine cart array, writes all `OrderItem` records, transitions `Order::status` → `active`, and redirects.

#### Key Livewire Properties
```php
public FloorPlanElement $table;
public ?int $activeMenuId = null;
public ?int $activeCategoryId = null;
public string $search = '';
public int $orderId; // the persisted draft order id
```

#### Key Livewire Computed Properties
```php
#[Computed] public function menus(): Collection  // published menus only
#[Computed] public function categories(): Collection  // categories of activeMenuId
#[Computed] public function dishes(): Collection  // filtered by category + search, available=true only
```

#### Key Livewire Methods
```php
public function mount(FloorPlanElement $floorPlanElement): void
public function selectMenu(int $menuId): void
public function selectCategory(?int $categoryId): void
public function placeOrder(array $cartItems, ?string $orderNotes): void
public function cancelOrder(): void
```

`placeOrder` receives an array of `[dish_id, qty, notes]` items from Alpine, bulk-inserts `OrderItem` records (with unit_price snapshot from `Dish::price`), sets `Order::status = active`, updates `FloorPlanElement::status = Occupied`, shows a success toast, then redirects to `route('table-management')` with the originating floor plan ID after a 3-second delay (via a Livewire `$this->js(...)` call or `dispatch('redirect')`).

### 3.3 Page Layout

```
┌─ layouts.molveno nav ─────────────────────────────────┐
│  ┌─ sidebar (hidden on mobile) ──┐  ┌─ main content ─┐│
│  │  Menu list (published only)   │  │  page-header   ││
│  │  └ Category list (expandable) │  │  search input  ││
│  │                               │  │  category tabs ││
│  │  [Back to Table Management]   │  │  dish grid     ││
│  └───────────────────────────────┘  └────────────────┘│
│                                                        │
│  [Fixed order bar at bottom — Alpine.js]               │
└────────────────────────────────────────────────────────┘
```

### 3.4 Sidebar (`<x-ui.sidebar>` — new reusable Blade component)

**Extract the sidebar shell into `resources/views/components/ui/sidebar.blade.php`.**

- Handles: fixed left panel on `md+`, full-height overlay on mobile (triggered by a button), scrollable content area, close button.
- Content passed as default slot.
- Used by: Dishes page sidebar (refactored to use the shell) and Order Management sidebar.
- **Mobile:** A filter/menu icon button in the `<x-ui.page-header>` actions slot opens the sidebar as a full-height left overlay (using Alpine.js `x-show` + transition). Tapping outside or the ✕ closes it. Mechanism mirrors `<x-ui.sheet>` but anchored left.

**Order Management sidebar content** (a new `App\Livewire\Orders\Sidebar` component):
- Read-only list of published menus.
- Each menu is expandable (chevron toggle) to reveal its categories.
- Selecting a menu/category dispatches `setMenuCategory` to `OrderPage`.
- Active menu/category highlighted.
- **Back to Table Management** link at the bottom: `route('table-management', ['floorPlan' => $table->floor_plan_id])`.

### 3.5 Page Header

Uses `<x-ui.page-header>`:
- **Title:** `New Order — {table->table_name}`
- **Subtitle:** Floor plan name (`$table->floorPlan->name`)
- **Actions slot:** Mobile sidebar toggle button (hidden on `md+`)

### 3.6 Dish Grid & Filtering

- Search (`wire:model.live.debounce.300ms`) and category tab selection are both Livewire-driven — each change re-queries dishes from the DB.
- Only dishes with `available = true` are shown.
- Only dishes belonging to the active menu (via `menu_category_dish`) are shown.
- No menu selected → empty state with prompt to pick a menu from the sidebar.
- Dish cards retain the existing card design from `<x-ordermanagement.dish-card>`.
- Clicking a dish opens the **Add Dish modal** (Alpine.js) with qty + notes fields. Course is not shown.

### 3.7 Order Bar & Review Screen (Alpine.js)

Retain the existing Alpine.js cart behaviour:
- Fixed bottom bar showing item count and total.
- Review screen modal listing all cart items with qty/notes.
- **Place Order** button in review screen calls `$wire.placeOrder(cartItems, orderNotes)`.
- **Cancel Order** button calls `$wire.cancelOrder()` which sets `Order::status = cancelled`, reverts `FloorPlanElement::status = Available`, and redirects to `route('table-management', ['floorPlan' => $table->floor_plan_id])`.

---

## 4. CompletedOrderTable Migration

`App\Livewire\CompletedOrderTable` is updated to query real `Order` + `OrderItem` data:
- Replace the hardcoded `getCompletedOrders()` array with an Eloquent query on `Order::where('status', 'completed')`.
- Eager-load `order->items->dish` and `order->floorPlanElement`.
- Map to the same array shape the existing view expects (id, type, location, waiter, customer, closed_at, items, payment_method, is_refunded) so the view requires minimal changes.
- `waiter` defaults to `'—'` (no user tracking on orders at this stage).
- `customer` defaults to `'—'`.
- `payment_method` and `is_refunded` default to `null` / `false` until a payment flow is built.

`StatisticsController` is **out of scope** — leave hardcoded for now.

---

## 5. Permission Gating

Use existing permissions from `PermissionRegistry` — no new permissions added:

| Action | Permission |
|---|---|
| Navigate to order management page | `Create Order` |
| Add/remove items from cart | `Edit Order` |
| Place order (submit draft) | `Create Order` |
| Cancel order | `Cancel Order` |
| Accept Order button visible in table sheet | `Create Order` |

---

## 6. Filtering Logic Summary

| Dimension | Mechanism |
|---|---|
| Menu selection | Livewire property `$activeMenuId` → re-queries DB |
| Category selection | Livewire property `$activeCategoryId` → re-queries DB |
| Text search | Livewire property `$search` with `debounce.300ms` → re-queries DB |
| Availability | Always filtered: `available = true` in query |
| Menu status | Always filtered: only published menus shown |

---

## 7. Implementation Steps

1. **Create enums:** `OrderStatus`, `OrderItemStatus`
2. **Create models + migrations:** `Order`, `OrderItem` (with factories + seeders)
3. **Add FloorPlanElement guard** (observer or service) blocking deletion with active orders
4. **Add Order observer** for table status automation (`Occupied` on create, `Available` on complete/cancel)
5. **Create `<x-ui.sidebar>` Blade shell component** and refactor Dishes sidebar to use it
6. **Create `App\Livewire\Orders\Sidebar`** — read-only menu/category picker Livewire component
7. **Create `App\Livewire\Orders\OrderPage`** — full Livewire page component with Alpine cart bridge
8. **Register route** `orders.create` and add permission middleware
9. **Update `TableManagement.php`** — add Accept Order button logic + resume/new confirmation in table sheet
10. **Update `CompletedOrderTable.php`** — replace hardcoded data with real Eloquent queries
11. **Write Pest feature tests** for: order creation flow, table status transitions, permission gating, FloorPlanElement deletion guard, CompletedOrderTable query
