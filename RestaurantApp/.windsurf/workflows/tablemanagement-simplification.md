---
description: 
---

# Table Management Simplification — Specification

## Overview

Replace the current freeform image-upload-and-crop table management system with a preset-based element system. Elements are shipped as static image files organized by shape and seat count. Users drag preset elements onto the floor plan canvas — no image uploads, no cropping.

---

## 1. Preset Element System

### 1.1 Filesystem Structure

Preset element images are shipped with the application in `public/elements/`, organized by shape subdirectory with files named by seat count:

```
public/elements/
  round/
    2.svg
    4.svg
    6.svg
    8.svg
    10.svg
  rectangular/
    2.svg
    4.png
    ...
```

- **Supported formats:** SVG, PNG, JPG, WebP — any standard image format.
- **Adding a new shape** = creating a new subdirectory and adding seat-count-named images.
- **Seat counts** are always in increments of 2: **2, 4, 6, 8, 10**.
- Not every shape needs every seat count — only variants with a corresponding image file are available.
- Shape names are derived from the folder name (title-cased for display: `round/` → "Round").

### 1.2 Configuration File

A PHP config file (`config/table-elements.php`) defines default sizing for each shape+seats variant:

```php
return [
    'round' => [
        'label' => 'Round Table',
        'variants' => [
            2  => ['width' => 6.0,  'height' => 6.0],
            4  => ['width' => 8.0,  'height' => 8.0],
            6  => ['width' => 10.0, 'height' => 10.0],
            8  => ['width' => 12.0, 'height' => 12.0],
            10 => ['width' => 14.0, 'height' => 14.0],
        ],
    ],
    'rectangular' => [
        'label' => 'Rectangular Table',
        'variants' => [
            2  => ['width' => 8.0,  'height' => 5.0],
            4  => ['width' => 10.0, 'height' => 6.0],
            // ...
        ],
    ],
];
```

- `width` and `height` are percentages of the canvas.
- The config is the source of truth for default element sizes.
- If an image file exists in the filesystem but has no config entry, it is ignored (config is authoritative).

### 1.3 Seat Count ↔ Image Relationship

- Each placed element's image is determined by its `shape` + `seat_count`.
- **Changing seat count on a placed element swaps the displayed image** to the corresponding variant.
- When seat count changes, the element **scales proportionally** from its current size relative to the ratio of old default size → new default size.
- Seat count is locked to available variants per shape (user selects from a dropdown, cannot type arbitrary numbers).

---

## 2. Database Changes

### 2.1 Migration: `floor_plan_elements` Table

**Remove columns:**
- `image_id` (foreign key to images table)
- `is_table`

**Add columns:**
- `shape` (string, not null) — e.g., `'round'`, `'rectangular'`
- `seat_count` (unsigned tinyint, not null) — e.g., `2`, `4`, `6`, `8`, `10`

**Keep columns (unchanged):**
- `id`, `floor_plan_id`, `x`, `y`, `width`, `height`, `rotation`, `z_index`
- `table_name` (now effectively required — every element is a table)
- `status` (default: `'available'`)
- `timestamps`, `soft_deletes`

### 2.2 Migration: `images` Table

**Remove columns:**
- `crop_x`, `crop_y`, `crop_w`, `crop_h`

**Keep everything else** — the Image model is retained for floor plan background images only.

### 2.3 Data Migration Strategy

- **Preserve** existing `floor_plans` records and their background images.
- **Delete** all existing `floor_plan_elements` rows (they reference the old image-based system).
- Orphaned `images` records (no longer referenced by any floor plan as background) may be cleaned up.

---

## 3. UI Changes

### 3.1 Edit Mode — Sidebar

Replace the current image library with a **preset element palette**:

- Elements are grouped by shape (collapsible sections: "Round Table", "Rectangular Table", etc.).
- Each variant is shown as a draggable thumbnail with seat count label.
- **Drag-and-drop** from sidebar to canvas places the element at the drop position with its default size.
- When an element is selected on the canvas, the sidebar switches to the **properties panel** (same panel area, contextual).

### 3.2 Edit Mode — Properties Panel (Selected Element)

When an element is selected:

- **Table Name**: text input, auto-generated as "Table N" on placement (N = sequential), user-editable.
- **Shape**: display-only label (e.g., "Round Table") — shape cannot be changed after placement.
- **Seats**: dropdown of available seat counts for this shape (2, 4, 6, 8, 10 — filtered to variants that have images). Changing this swaps the image and scales proportionally.
- **Status**: Available / Reserved / Occupied buttons (same as current).
- **Layer Order**: Bring to Front / Send to Back (same as current).
- **Delete Element** button.

### 3.3 Edit Mode — Canvas Interactions

**Keep:**
- Freeform drag to reposition (percentage-based, constrained to canvas bounds)
- Resize from edges/corners
- Rotation handle (Shift/Alt for free rotation, otherwise 15° snap)
- Copy/paste (Ctrl+C / Ctrl+V)
- Right-click context menu (copy, bring to front, send to back, delete)
- Delete key to remove selected element
- Pan (middle-click or background drag) and zoom (scroll wheel)
- Touch support (pinch zoom, one-finger pan)

**Add:**
- **Element edge alignment snap**: when dragging/resizing, thin colored guide lines appear when the element's edges align with another element's edges (Figma-style). Snapping threshold ~5px.
- **Snap toggle**: a checkbox in the toolbar + Shift hotkey to toggle snapping on/off. State persists during the edit session.

**Remove:**
- Image upload functionality (upload button, upload modal)
- Crop tool / crop modal entirely
- Image library management (delete image from library, crop existing image)

### 3.4 View Mode

**No changes** — keep the existing view mode as-is:
- Status badges on elements
- Filter bar (name, seats, status)
- Status summary counts (Available / Reserved / Occupied)
- Click table to open detail sheet
- Immediate status update from detail sheet

### 3.5 Floor Plan Management

**Keep:**
- Create floor plan (name + background image upload — no crop)
- Rename floor plan
- Delete floor plan
- Switch between floor plans
- Replace background image
- Unsaved changes warning / discard confirmation

**Change:**
- Background image upload no longer offers a crop step — image is used as-is.

---

## 4. Removed Features

| Feature | Reason |
|---|---|
| Image upload for elements | Replaced by shipped preset images |
| Crop tool / crop modal | No longer needed — presets are used as-is |
| Image library management | No library — presets are read-only from filesystem |
| `is_table` toggle | Every element is a table — concept removed |
| Decorative/non-table elements | Out of scope — tables only |
| Background image cropping | Simplified — backgrounds used as-is |

---

## 5. Model & Backend Changes

### 5.1 FloorPlanElement Model

- Remove `image()` relationship.
- Remove `is_table` references.
- Add `shape` and `seat_count` attributes.
- Add accessor `imagePath(): string` that resolves `public/elements/{shape}/{seat_count}.{ext}` by checking the filesystem for a matching file.
- `table_name` defaults to auto-generated "Table N" on creation.
- `status` defaults to `TableStatus::Available` on creation.

### 5.2 Image Model

- Remove crop-related attributes and any methods that reference them.
- Model is now only used for floor plan background images.

### 5.3 TableManagement Livewire Component

**Remove:**
- `showCropModal` state and all crop-related methods (`openCropEditor`, `closeCropModal`, `saveCrop`, `uploadElementImage`)
- `imageLibrary` computed property
- `storeUploadedImage` method (for elements — keep for background image upload)
- `deleteImageFromLibrary` method
- All crop-related Blade template sections

**Change:**
- `placeElement()` signature: accept `shape` and `seatCount` instead of `imageId`.
- `elements` computed: resolve image paths from shape+seat_count instead of image relationship.
- `updateElementProperties()`: when seat count changes, swap the image path and scale proportionally.
- Properties panel: render shape-specific seat count dropdown instead of is_table toggle + manual seat input.
- Sidebar: render preset palette from config instead of image library.
- Background image upload: remove crop step.

**Add:**
- `presetElements` computed property: reads `config/table-elements.php`, validates against filesystem, returns available shapes with their variants.
- `snapEnabled` boolean state (default: true), toggled by checkbox and Shift key.
- Auto-generation logic for `table_name` on element placement.

### 5.4 Alpine.js / JavaScript

**Remove:**
- `window.cropTool()` Alpine component entirely.
- `window.setupLibraryDrag()` — replace with new preset drag setup.
- Crop-related rendering logic in `canvasElement`.

**Change:**
- `canvasElement`: image source comes from a static asset path (`/elements/{shape}/{seats}.ext`) instead of a dynamic uploaded image URL. No crop clipping needed — render image directly.
- Library drag: drag data transfers `shape` and `seatCount` instead of `imageId`.

**Add:**
- Edge alignment snap logic in `canvasElement` drag/resize handlers: compare current element edges against all other element edges, show guide lines when within threshold, snap position.
- Snap toggle state (synced with Livewire `snapEnabled` via `$wire`).
- Guide line rendering (absolutely positioned thin lines on canvas, shown/hidden dynamically during drag).

---

## 6. Element Placement Defaults

When a new element is placed on the canvas:

| Property | Default Value |
|---|---|
| `shape` | From dragged preset |
| `seat_count` | From dragged preset |
| `table_name` | Auto-generated: "Table N" (N = next sequential number across all elements on the floor plan) |
| `status` | `Available` |
| `width` / `height` | From `config/table-elements.php` for this shape+seats variant |
| `rotation` | 0 |
| `z_index` | Max z_index + 1 (on top) |
| `x` / `y` | Drop position on canvas |

---

## 7. Proportional Scaling on Seat Count Change

When a user changes the seat count of a placed element:

1. Look up the **old** default size from config for `(shape, old_seat_count)`.
2. Look up the **new** default size from config for `(shape, new_seat_count)`.
3. Compute scale factor: `new_default / old_default` for both width and height.
4. Apply: `new_size = current_size * scale_factor`.
5. Clamp to canvas bounds (0–100%).
6. Swap the displayed image to the new variant.

---

## 8. Edge Alignment Snap System

### Behavior
- When dragging or resizing an element, compare its four edges (top, bottom, left, right) against the four edges of every other visible element.
- If any edge is within the snap threshold (~5px screen distance), snap the dragged edge to align exactly.
- Display a thin colored line (e.g., 1px cyan/blue) spanning the canvas at the aligned position.
- Multiple guides can appear simultaneously (e.g., aligned on both X and Y).
- Guides disappear when the drag/resize ends.

### Toggle
- A checkbox labeled "Snap to elements" in the edit mode toolbar (top bar area).
- Pressing **Shift** toggles the checkbox on/off.
- When disabled, no snapping occurs and no guides are shown.
- Default: **enabled**.

---

## 9. Files to Modify

| File | Action |
|---|---|
| `database/migrations/` | New migration: alter `floor_plan_elements` (drop `image_id`, `is_table`; add `shape`, `seat_count`). Alter `images` (drop crop columns). Delete existing elements. |
| `config/table-elements.php` | **New file** — preset element configuration with default sizes per variant. |
| `public/elements/` | **New directory** — shipped preset element images organized by shape. |
| `app/Models/FloorPlanElement.php` | Update attributes, remove image relationship, add image path accessor. |
| `app/Models/Image.php` | Remove crop attributes/methods. |
| `app/Livewire/TableManagement.php` | Major refactor: remove crop/upload/library, add preset system, snap state, auto-naming. |
| `resources/views/livewire/table-management.blade.php` | Major refactor: remove crop modal/upload UI, replace library with preset palette, add snap toggle, update properties panel. |
| `resources/js/app.js` | Remove cropTool, update canvasElement for static images, add snap/guide logic, update drag data. |

---

## 10. Out of Scope

- Non-table decorative elements (walls, bars, plants).
- Grid-based positioning.
- Click-to-place mode (keeping drag-and-drop only).
- Multi-select / bulk operations.
- Undo/redo system.
- Floor plan templates.