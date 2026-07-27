---
description: Create and maintain shadcn-like reusable Blade components for the Molveno Lake Resort restaurant application
---

# Shadcn-like Component Library Specification

This workflow defines a set of reusable, composable Blade components inspired by shadcn/ui, tailored for this Laravel + Tailwind CSS v3 + Alpine.js v3 application. All components live in `resources/views/components/ui/` and are used via the `<x-ui.component-name />` prefix.

## Design Tokens

All components follow the **Molveno Lake Resort** brand:

- **Primary filled**: `bg-molveno-blue-500 hover:bg-molveno-blue-700 text-white`
- **Focus ring**: `focus:ring-2 focus:ring-molveno-blue-300 focus:ring-offset-2`
- **Border radius**: `rounded-lg` (default), `rounded-xl` (cards, sheets, modals), `rounded-full` (badges, pills)
- **Font**: Figtree (already loaded via Bunny Fonts)
- **Transition**: `transition-colors duration-150` (default for interactive elements)
- **Border color**: `border-gray-200` (default)
- **Background**: `bg-white` (surfaces), `bg-[#eaf4fa]` (page background)

Every component MUST:
1. Accept `$attributes` and merge them so consumers can add custom classes, ids, data-attributes, etc.
2. Use Tailwind CSS utility classes exclusively — no custom CSS unless absolutely necessary.
3. Support being used in both standard Blade views and Livewire components.

---

## Components

### 1. `<x-ui.button />`

A versatile button component replacing `primary-button`, `secondary-button`, `danger-button`, and all inline button styles.

**Location**: `resources/views/components/ui/button.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `variant` | string | `'primary'` | One of: `primary`, `secondary`, `danger`, `ghost`, `outline` |
| `size` | string | `'default'` | One of: `sm`, `default`, `lg` |
| `as` | string | `'button'` | Render as `button` or `a` (for link buttons) |
| `href` | string\|null | `null` | When set, renders as `<a>` automatically |
| `disabled` | bool | `false` | Disables the button |

**Variant styles**:
- **primary**: `bg-molveno-blue-500 hover:bg-molveno-blue-700 text-white font-semibold shadow-sm`
- **secondary**: `bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 font-medium`
- **danger**: `bg-red-600 hover:bg-red-700 text-white font-semibold`
- **ghost**: `text-gray-500 hover:text-gray-700 hover:bg-gray-100 font-medium` (for icon-only action buttons)
- **outline**: `bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold shadow-sm`

**Size styles**:
- **sm**: `px-3 py-1.5 text-xs rounded-lg gap-1.5`
- **default**: `px-4 py-2 text-sm rounded-lg gap-2`
- **lg**: `px-5 py-2.5 text-base rounded-lg gap-2`

**All buttons get**: `inline-flex items-center justify-center transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-molveno-blue-300 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed`

**Usage examples**:
```blade
<x-ui.button>Save Account</x-ui.button>
<x-ui.button variant="secondary" size="sm">Cancel</x-ui.button>
<x-ui.button variant="danger" onclick="confirmDelete()">Delete</x-ui.button>
<x-ui.button variant="ghost" size="sm" class="p-1.5">
    {{-- icon-only ghost button --}}
    <svg>...</svg>
</x-ui.button>
<x-ui.button href="{{ route('dashboard') }}" variant="primary">Go to Dashboard</x-ui.button>
```

---

### 2. `<x-ui.input />`

A styled text input replacing `text-input`, `.sheet-input`, and all inline input styles.

**Location**: `resources/views/components/ui/input.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `type` | string | `'text'` | HTML input type |
| `disabled` | bool | `false` | Disables the input |
| `error` | bool | `false` | Applies error border styling |

**Base styles**: `w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-900 bg-white placeholder-gray-400 outline-none transition-[border-color,box-shadow] duration-150 focus:border-molveno-blue-500 focus:ring-2 focus:ring-molveno-blue-300 font-[inherit] disabled:opacity-50 disabled:cursor-not-allowed`

**Error state**: `border-red-500 focus:border-red-500 focus:ring-red-200`

**Usage**:
```blade
<x-ui.input name="email" type="email" placeholder="name@molvenoresort.it" />
<x-ui.input name="name" :error="$errors->has('name')" />
```

**Also supports `<textarea>`**: When `type="textarea"` is passed, render a `<textarea>` element instead with the same styling plus `resize-none`.

**Also supports `<select>`**: When `type="select"` is passed, render a `<select>` element with the same base styling. The `$slot` contains the `<option>` elements.

---

### 3. `<x-ui.input-group />`

Bundles label + input + error message into a single component.

**Location**: `resources/views/components/ui/input-group.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `label` | string | required | Label text |
| `name` | string | required | Input name (also used for error bag lookup) |
| `type` | string | `'text'` | Passed to `<x-ui.input>` |
| `placeholder` | string\|null | `null` | Placeholder text |
| `value` | string\|null | `null` | Input value (falls back to `old($name)`) |
| `required` | bool | `false` | Shows red asterisk on label |
| `hint` | string\|null | `null` | Optional hint text below the input |
| `id` | string\|null | `null` | Falls back to `$name` |

**Renders**:
```blade
<div class="flex flex-col gap-1.5">
    <label class="text-sm font-semibold text-gray-700" for="{{ $id ?? $name }}">
        {{ $label }}
        @if($required) <span class="text-red-400">*</span> @endif
    </label>
    <x-ui.input :type="$type" :name="$name" :id="$id ?? $name"
                 :placeholder="$placeholder"
                 :value="$value ?? old($name)"
                 :error="$errors->has($name)"
                 {{ $attributes }} />
    @if($hint)
        <p class="text-xs text-gray-400">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
```

**Slot override**: If the default `$slot` is not empty, it replaces the `<x-ui.input>` entirely (for custom inputs like checkbox groups, file uploads, etc.).

**Usage**:
```blade
<x-ui.input-group label="Full Name" name="name" placeholder="e.g. Sofia Ricci" required />
<x-ui.input-group label="Password" name="password" type="password" hint="Minimum 8 characters" />
<x-ui.input-group label="Category" name="category" type="select">
    <option value="" disabled selected>Select…</option>
    <option>Starters</option>
    <option>Mains</option>
</x-ui.input-group>
```

---

### 4. `<x-ui.form />`

A form wrapper that auto-includes CSRF token and method spoofing.

**Location**: `resources/views/components/ui/form.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `method` | string | `'POST'` | HTTP method (GET, POST, PUT, PATCH, DELETE) |
| `action` | string | `''` | Form action URL |

**Behavior**:
- Always renders `@csrf` (except for GET forms).
- If method is PUT, PATCH, or DELETE, renders `@method('...')` and sets the actual form method to POST.
- Passes all other attributes through.

**Usage**:
```blade
<x-ui.form method="PUT" action="{{ route('accounts.update', $user) }}" class="flex flex-col gap-5">
    <x-ui.input-group label="Name" name="name" :value="$user->name" />
    <x-ui.button>Update</x-ui.button>
</x-ui.form>
```

---

### 5. `<x-ui.sheet />`

A slide-in side panel replacing the duplicated sheet pattern in accounts and dishes. **Uses Alpine.js**.

**Location**: `resources/views/components/ui/sheet.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `name` | string | required | Unique sheet identifier (used for Alpine events) |
| `side` | string | `'right'` | `'left'` or `'right'` |
| `maxWidth` | string | `'md'` | One of: `sm`, `md`, `lg`, `xl` |
| `title` | string\|null | `null` | Sheet header title |
| `subtitle` | string\|null | `null` | Sheet header subtitle |

**Alpine.js events**:
- Open: `$dispatch('open-sheet', 'sheet-name')` or `@open-sheet.window`
- Close: `$dispatch('close-sheet', 'sheet-name')` or `@close-sheet.window`

**Structure**:
```
+------------------------------------------+
| Overlay (bg-black/40, click-to-close)    |
|  +---------------------+                |
|  | Header (title, X)   |                |
|  |---------------------|                |
|  | Body (scrollable)   |  <- $slot      |
|  |                     |                |
|  |---------------------|                |
|  | Footer              |  <- $footer    |
|  +---------------------+                |
+------------------------------------------+
```

**Animation**: `transform translateX(100%)` -> `translateX(0)` for right, `translateX(-100%)` -> `translateX(0)` for left. Cubic-bezier easing: `cubic-bezier(0.32, 0.72, 0, 1)`. Duration: 350ms.

**Features**:
- Escape key closes the sheet.
- Overlay click closes the sheet.
- Body scroll is locked when open.
- Trap focus inside the sheet when open.

**Named slots**: `$slot` (body content), `$footer` (sticky footer with action buttons).

**Usage**:
```blade
<x-ui.sheet name="account-form" title="Add Account" maxWidth="md">
    {{-- form fields here --}}
    <x-slot:footer>
        <x-ui.button variant="secondary" @click="$dispatch('close-sheet', 'account-form')">Cancel</x-ui.button>
        <x-ui.button type="submit">Save Account</x-ui.button>
    </x-slot:footer>
</x-ui.sheet>

{{-- trigger --}}
<x-ui.button @click="$dispatch('open-sheet', 'account-form')">Add Account</x-ui.button>
```

---

### 6. `<x-ui.divider />`

A simple visual separator.

**Location**: `resources/views/components/ui/divider.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `orientation` | string | `'horizontal'` | `'horizontal'` or `'vertical'` |
| `dashed` | bool | `false` | Dashed style instead of solid |

**Styles**:
- **Horizontal**: `w-full border-t border-gray-200` (or `border-dashed`)
- **Vertical**: `h-full w-px bg-gray-200` (or `border-l border-dashed border-gray-200`)

**Usage**:
```blade
<x-ui.divider />
<x-ui.divider dashed />
<x-ui.divider orientation="vertical" class="h-5 mx-1" />
```

---

### 7. `<x-ui.card />`

A generic card container.

**Location**: `resources/views/components/ui/card.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `padding` | string | `'default'` | `'none'`, `'sm'`, `'default'`, `'lg'` |
| `headerColor` | string\|null | `null` | Optional colored header strip (any Tailwind bg class, e.g. `'bg-primary'`) |

**Base styles**: `bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden`

**Padding map**:
- **none**: no padding on the body
- **sm**: `p-4`
- **default**: `p-6`
- **lg**: `p-8`

**Named slots**: `$header` (optional card header), `$slot` (body), `$footer` (optional card footer with `border-t border-gray-100 bg-gray-50`).

**Header strip**: When `headerColor` is set, renders a colored bar at the top: `<div class="px-6 py-4 text-white {{ $headerColor }}">{{ $header }}</div>`.

**Usage**:
```blade
<x-ui.card>
    <p>Simple card content.</p>
</x-ui.card>

<x-ui.card headerColor="bg-primary">
    <x-slot:header>
        <h3 class="text-lg font-bold">Total Sales</h3>
    </x-slot:header>
    <p class="text-3xl font-bold">{{ $totalSales }}</p>
</x-ui.card>

<x-ui.card padding="none">
    <x-slot:header>
        <div class="px-6 py-4 border-b border-gray-100">Table Header</div>
    </x-slot:header>
    <table>...</table>
    <x-slot:footer>
        <div class="px-6 py-4">Pagination here</div>
    </x-slot:footer>
</x-ui.card>
```

---

### 8. `<x-ui.image />`

An image component with placeholder fallback and aspect ratio support.

**Location**: `resources/views/components/ui/image.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `src` | string\|null | `null` | Image URL |
| `alt` | string | `''` | Alt text |
| `aspect` | string\|null | `null` | CSS aspect ratio (e.g. `'16/9'`, `'1/1'`, `'5/6'`) |
| `placeholderColor` | string | `'#309bcf'` | Background color when no src |
| `placeholderIcon` | string\|null | `null` | Optional named placeholder icon slot |
| `rounded` | string | `'xl'` | Border radius class suffix |

**Behavior**:
- When `src` is provided and non-empty: renders an `<img>` tag with `object-cover` inside a wrapper div.
- When `src` is null/empty: renders a colored background div with a centered placeholder icon (defaults to a generic dish/image SVG icon at 30% opacity).

**Usage**:
```blade
<x-ui.image src="{{ $dish->image_url }}" alt="{{ $dish->name }}" aspect="5/6" />
<x-ui.image :src="null" placeholderColor="#c07830" aspect="16/9" />
```

---

### 9. `<x-ui.toast />`

A floating notification system replacing the static `flash-message` component. **Uses Alpine.js**.

**Location**: `resources/views/components/ui/toast.blade.php`

**This is a global component** — include it once in the layout (e.g. `layouts/app.blade.php` or in each page before `</body>`).

**Props on the container**: None — it listens for Alpine events.

**Alpine.js events** to trigger a toast:
```js
$dispatch('toast', { message: 'Account saved!', type: 'success' })
$dispatch('toast', { message: 'Something went wrong.', type: 'error' })
$dispatch('toast', { message: 'Check your input.', type: 'warning' })
$dispatch('toast', { message: 'Order exported.', type: 'info' })
```

**Types & styles**:
| Type | Icon | Colors |
|---|---|---|
| `success` | Checkmark | `bg-green-50 border-green-200 text-green-800` |
| `error` | X circle | `bg-red-50 border-red-200 text-red-800` |
| `warning` | Alert triangle | `bg-amber-50 border-amber-200 text-amber-800` |
| `info` | Info circle | `bg-blue-50 border-blue-200 text-blue-800` |

**Behavior**:
- Toasts appear in the **bottom-right corner** (`fixed bottom-6 right-6 z-[100]`).
- Stack vertically with `gap-3`.
- Auto-dismiss after **5 seconds** (configurable via `duration` in event detail).
- Slide-in from the right with fade.
- Each toast has a close button (X).
- Maximum 5 visible toasts; oldest dismissed first if limit exceeded.

**Laravel session flash integration**: The toast container also auto-reads `session('success')`, `session('error')`, `session('warning')`, `session('info')` and fires a toast on page load.

**Usage**:
```blade
{{-- In layout, before </body> --}}
<x-ui.toast />

{{-- Trigger from Alpine --}}
<button @click="$dispatch('toast', { message: 'Saved!', type: 'success' })">Save</button>

{{-- From Laravel controller --}}
return redirect()->back()->with('success', 'Account created successfully.');
```

---

### 10. `<x-ui.badge />`

A colored pill/badge replacing role badges, count pills, and status indicators.

**Location**: `resources/views/components/ui/badge.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `variant` | string | `'neutral'` | One of: `primary`, `success`, `warning`, `danger`, `neutral`, or `custom` |
| `size` | string | `'default'` | `'sm'` or `'default'` |
| `dot` | bool | `false` | Show a leading status dot |
| `dotColor` | string\|null | `null` | Custom dot color class (e.g. `'bg-green-500'`) |

**Variant styles**:
- **primary**: `bg-molveno-blue-100 text-molveno-blue-700`
- **success**: `bg-green-100 text-green-700`
- **warning**: `bg-amber-100 text-amber-700`
- **danger**: `bg-red-100 text-red-700`
- **neutral**: `bg-gray-100 text-gray-600`
- **custom**: No preset — consumer provides classes via `$attributes`

**Size styles**:
- **sm**: `px-2 py-0.5 text-[0.65rem]`
- **default**: `px-2.5 py-1 text-xs`

**Base**: `inline-flex items-center gap-1.5 rounded-full font-semibold`

**Usage**:
```blade
<x-ui.badge variant="success" dot>Active</x-ui.badge>
<x-ui.badge variant="primary">{{ $count }}</x-ui.badge>
<x-ui.badge variant="warning" dot dotColor="bg-amber-400">Ready</x-ui.badge>
```

---

### 11. `<x-ui.modal />`

A centered modal dialog replacing both the Breeze modal and the custom delete modal. **Uses Alpine.js**.

**Location**: `resources/views/components/ui/modal.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `name` | string | required | Unique modal identifier |
| `maxWidth` | string | `'md'` | One of: `sm`, `md`, `lg`, `xl`, `2xl` |
| `closeable` | bool | `true` | Can be closed by overlay click / Escape |

**Alpine.js events**:
- Open: `$dispatch('open-modal', 'modal-name')`
- Close: `$dispatch('close-modal', 'modal-name')`

**Structure**:
- Overlay with `bg-black/40` and fade transition.
- Centered panel with scale + fade transition.
- Focus trapping.
- Named slots: `$slot` (body), `$footer` (action buttons row).

**Usage**:
```blade
<x-ui.modal name="delete-confirm" maxWidth="sm">
    <div class="p-6">
        <h3 class="text-sm font-bold text-gray-900">Delete Account</h3>
        <p class="text-sm text-gray-500 mt-2">Are you sure? This cannot be undone.</p>
    </div>
    <x-slot:footer>
        <div class="px-6 py-4 flex justify-end gap-2 border-t border-gray-100">
            <x-ui.button variant="secondary" @click="$dispatch('close-modal', 'delete-confirm')">Cancel</x-ui.button>
            <x-ui.button variant="danger">Delete</x-ui.button>
        </div>
    </x-slot:footer>
</x-ui.modal>
```

---

### 12. `<x-ui.table />`, `<x-ui.th />`, `<x-ui.td />`

Composable table elements with consistent styling.

**Location**: `resources/views/components/ui/table.blade.php`, `th.blade.php`, `td.blade.php`

**`<x-ui.table>`**: Wraps content in `<table class="w-full text-sm">`. Accepts `$slot`.

**`<x-ui.th>`**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `align` | string | `'left'` | `'left'`, `'center'`, `'right'` |
| `sortable` | bool | `false` | Makes the header clickable with sort indicator |
| `sorted` | string\|null | `null` | `'asc'`, `'desc'`, or `null` |

**Styles**: `px-4 py-3 font-semibold text-gray-500 text-xs uppercase tracking-wide`

**`<x-ui.td>`**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `align` | string | `'left'` | `'left'`, `'center'`, `'right'` |

**Styles**: `px-4 py-3`

**Usage**:
```blade
<x-ui.card padding="none">
    <x-ui.table>
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <x-ui.th>User</x-ui.th>
                <x-ui.th>Email</x-ui.th>
                <x-ui.th align="right">Actions</x-ui.th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <x-ui.td>John Doe</x-ui.td>
                <x-ui.td>john@example.com</x-ui.td>
                <x-ui.td align="right">...</x-ui.td>
            </tr>
        </tbody>
    </x-ui.table>
</x-ui.card>
```

---

### 13. `<x-ui.dropdown />`

A refreshed dropdown menu matching the Molveno design. **Uses Alpine.js**.

**Location**: `resources/views/components/ui/dropdown.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `align` | string | `'right'` | `'left'`, `'right'`, `'top'` |
| `width` | string | `'48'` | Tailwind width class (e.g. `'48'` -> `w-48`) |

**Named slots**: `$trigger`, `$content`.

**Styles**: `rounded-xl shadow-xl border border-gray-200 bg-white py-1.5` with scale + fade transitions.

**Also include `<x-ui.dropdown-link />`**: A styled link item for inside the dropdown: `block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors`.

---

### 14. `<x-ui.avatar />`

An initials avatar circle.

**Location**: `resources/views/components/ui/avatar.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `name` | string | required | Full name (initials extracted automatically) |
| `size` | string | `'default'` | `'sm'`, `'default'`, `'lg'` |
| `color` | string | `'bg-molveno-blue-500'` | Background color class |

**Size map**:
- **sm**: `w-7 h-7 text-[0.6rem]`
- **default**: `w-9 h-9 text-xs`
- **lg**: `w-12 h-12 text-sm`

**Base**: `rounded-full flex items-center justify-center shrink-0 text-white font-bold`

**Usage**:
```blade
<x-ui.avatar name="Sofia Ricci" />
<x-ui.avatar name="Marco D." size="lg" color="bg-amber-500" />
```

---

### 15. `<x-ui.empty-state />`

A placeholder for empty lists/tables/grids.

**Location**: `resources/views/components/ui/empty-state.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `title` | string | `'No results found'` | Main message |
| `description` | string\|null | `null` | Secondary text |

**Named slots**: `$icon` (custom icon), `$action` (optional button/link below the text).

**Default icon**: A generic clipboard/document SVG in `text-gray-300`.

**Usage**:
```blade
<x-ui.empty-state title="No accounts found." />

<x-ui.empty-state title="No dishes match your filters." description="Try adjusting your search or filters.">
    <x-slot:action>
        <x-ui.button variant="ghost" size="sm" onclick="resetFilters()">Clear all filters</x-ui.button>
    </x-slot:action>
</x-ui.empty-state>
```

---

### 16. `<x-ui.page-header />`

A consistent page header block.

**Location**: `resources/views/components/ui/page-header.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `title` | string | required | Page title |
| `subtitle` | string\|null | `null` | Subtitle / breadcrumb text |

**Named slots**: `$actions` (right-aligned action buttons).

**Styles**: `flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3`

**Usage**:
```blade
<x-ui.page-header title="Account Management" subtitle="Manage staff accounts — Molveno Lake Resort">
    <x-slot:actions>
        <x-ui.button @click="$dispatch('open-sheet', 'account-form')">
            <svg>...</svg> Add Account
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>
```

---

### 17. `<x-ui.tab-group />` and `<x-ui.tab />`

Reusable filter pill / tab group replacing the duplicated tab patterns.

**Location**: `resources/views/components/ui/tab-group.blade.php`, `tab.blade.php`

**`<x-ui.tab-group>`**: A flex wrapper: `flex items-center gap-2 flex-wrap`. Accepts `$slot`.

**`<x-ui.tab>`**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `active` | bool | `false` | Whether this tab is currently active |
| `count` | int\|null | `null` | Optional count badge |
| `value` | string\|null | `null` | Data attribute for JS/Livewire filtering |

**Active style**: `bg-molveno-blue-500 border-molveno-blue-500 text-white`
**Inactive style**: `bg-white border-gray-200 text-gray-600 hover:border-molveno-blue-300 hover:text-molveno-blue-700`

**Base**: `inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border cursor-pointer transition-colors duration-150 shadow-sm`

**Count badge**: Renders a `<span>` inside the tab. Active: `bg-white/25 text-white`. Inactive: `bg-gray-100 text-gray-500`.

**Usage**:
```blade
<x-ui.tab-group>
    <x-ui.tab :active="true" :count="$counts['all']" value="all" onclick="switchTab(this)">All</x-ui.tab>
    <x-ui.tab :count="$counts['management']" value="management" onclick="switchTab(this)">Management</x-ui.tab>
</x-ui.tab-group>
```

---

### 18. `<x-ui.search-input />`

A search input with built-in search icon.

**Location**: `resources/views/components/ui/search-input.blade.php`

**Props**:
| Prop | Type | Default | Description |
|---|---|---|---|
| `placeholder` | string | `'Search...'` | Placeholder text |
| `id` | string\|null | `null` | Input id |

**Structure**: A `relative` wrapper containing:
1. Magnifying glass SVG icon absolutely positioned on the left.
2. An `<x-ui.input>` with left padding (`pl-10`) to accommodate the icon.

**Supports all `$attributes`** passed through to the inner input (for `wire:model`, `oninput`, etc.).

**Usage**:
```blade
<x-ui.search-input placeholder="Search dishes…" id="search-input" />
<x-ui.search-input wire:model.live.debounce.300ms="search" placeholder="Search orders..." />
```

---

## File Structure

All new components go under:

```
resources/views/components/ui/
├── avatar.blade.php
├── badge.blade.php
├── button.blade.php
├── card.blade.php
├── divider.blade.php
├── dropdown.blade.php
├── dropdown-link.blade.php
├── empty-state.blade.php
├── form.blade.php
├── image.blade.php
├── input.blade.php
├── input-group.blade.php
├── modal.blade.php
├── page-header.blade.php
├── search-input.blade.php
├── sheet.blade.php
├── tab.blade.php
├── tab-group.blade.php
├── table.blade.php
├── td.blade.php
├── th.blade.php
└── toast.blade.php
```

## Implementation Notes

1. **Do NOT delete** existing Breeze components (`primary-button`, `secondary-button`, `danger-button`, `text-input`, `input-label`, `input-error`, `modal`, `dropdown`, `dropdown-link`, `nav-link`, `responsive-nav-link`). They may be used by auth views. New pages should use `x-ui.*` components.
2. **Existing page-specific components** (under `components/accounts/`, `components/dishes/`, `components/orders/`, `components/ordermanagement/`) should gradually adopt `x-ui.*` components internally but do not need to be removed.
3. Each component should be **self-contained** — no external CSS dependencies beyond Tailwind and no external JS beyond Alpine.js.
4. **Test each component** with a Pest feature test that renders it and asserts the expected HTML structure.
5. When refactoring an existing page to use these components, create a **migration PR per page** rather than changing everything at once.