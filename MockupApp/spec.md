# Restaurant Table Management System — Full Specification

## Overview

A drag-and-drop restaurant table management system built with Laravel 12, Livewire, Alpine.js, and Tailwind CSS v4. The application allows staff to manage multiple floor plans with interactive table layouts. No authentication is required — the app is single-restaurant and fully open-access.

---

## 1. Application Structure

### 1.1 Single Page Layout

The entire application lives on one page (`/`). The layout consists of:

- **Top bar** (full width):
  - Left: Floor plan dropdown switcher
  - Center: Application title or restaurant name
  - Right: Status summary strip + "Edit" toggle button
- **Canvas area**: Takes up the remaining viewport height; pannable and zoomable
- **Sidebar** (right side): Slides in when edit mode is enabled

### 1.2 Empty State (No Floor Plans)

When no floor plans exist, the canvas area shows a centered empty state with a "Create your first floor plan" call-to-action button that opens the creation modal.

---

## 2. Floor Plans

### 2.1 Floor Plan Switcher

- Top-left dropdown listing all floor plans by name
- Selecting a different floor plan switches the canvas immediately
- Only non-deleted (soft-deleted) floor plans appear in the list

### 2.2 Creating a Floor Plan

- Triggered via a "+" button adjacent to the floor plan dropdown, or via the empty state CTA
- A modal (or sheet) collects:
  - **Name** (required, text)
  - **Background image** (required, file upload — see §6 for constraints)
- On confirmation: the new floor plan is created in the database, the dropdown switches to it, and **edit mode is automatically enabled**

### 2.3 Floor Plan Management

Floor plans can be:
- **Renamed** — inline or via a settings action
- **Soft-deleted** — removed from the dropdown but data is retained in the database (a `deleted_at` timestamp column)

Deletion behavior: all associated elements are also soft-deleted. A soft-deleted floor plan and its elements can be restored later via database tooling (no UI restore needed in v1).

### 2.4 Changing the Background Image

- Available in edit mode via the sidebar (floor plan settings section when no element is selected)
- Uploading a new background replaces the image
- All existing element positions are preserved unchanged

---

## 3. Canvas

### 3.1 Rendering

- The canvas renders the background image of the active floor plan
- Placed elements are rendered on top of the background in z-order
- When no background image has been set, a checkerboard/neutral pattern fills the canvas area

### 3.2 Pan & Zoom

- The canvas is pannable (click-and-drag on empty space) and zoomable (scroll wheel / pinch on touch)
- Works on both desktop and touch devices
- Canvas renders responsively to the viewport

### 3.3 Element Rendering

Each placed element is rendered as:
- Its image (with full transparency support — PNG/SVG alpha channels render correctly)
- A colored status badge/label overlay **always visible** when the element is marked as a table (see §5.3)
- Non-table elements render as plain images with no overlay

### 3.4 Element Z-Order

- Elements have a stored z-index that determines render order
- Z-order is controlled in edit mode via a **right-click context menu** on the element:
  - "Bring to Front"
  - "Send to Back"
- Elements can freely overlap; no collision detection

---

## 4. Edit Mode

### 4.1 Activating Edit Mode

- A toggle button in the top-right of the header activates/deactivates edit mode
- When activated: the sidebar slides in from the right
- When deactivated: the sidebar closes; any unsaved changes remain in a pending state (not automatically discarded — the save/discard prompt appears)

### 4.2 Saving Changes

- An explicit **Save** button persists all pending changes to the database
- A **Discard** button or cancelling the mode reverts all unsaved changes
- No auto-save, no undo/redo stack

### 4.3 Sidebar — Default State (No Element Selected)

When no element is selected, the sidebar shows:
- **Image library grid**: thumbnails of all uploaded element images from the shared asset library
- Upload button to add new images to the library (see §6.2)
- Delete button per image (blocked if image is in use on any floor plan — see §6.3)
- Floor plan controls: rename, change background image

### 4.4 Sidebar — Element Selected State

When an element on the canvas is selected:
- Sidebar shifts to show that element's properties
- A **back arrow** or click-outside returns to the default sidebar state
- Properties shown:
  - **Is a table** toggle (checkbox/switch)
  - If toggled on — immediately reveals (no confirmation):
    - Table name (text input)
    - Seat count (number input, 1–99)
    - Status (select: Available / Reserved / Occupied)
  - **Z-order controls** are available here too (or exclusively via right-click)

### 4.5 Placing Elements

- User drags an image thumbnail from the sidebar's library grid onto the canvas
- The element appears at the drop position with a default size
- The same image can be placed multiple times — each instance is independent

### 4.6 Manipulating Placed Elements

When an element is selected in edit mode (click on it):
- **Drag to reposition**: click-and-drag to move
- **Resize handles**: corner and edge handles appear for scaling
- **Rotate handle**: a handle above the element for rotation
  - Default: snaps to 15° increments
  - Hold modifier key (Shift or Alt) for free rotation to any degree
- **Delete**: a delete button visible on selection (or Delete key press)
- **Grid snapping**: elements snap to a grid by default; hold modifier key to move freely pixel-by-pixel

### 4.7 Right-Click Context Menu (Edit Mode)

Right-clicking a placed element in edit mode shows:
- Bring to Front
- Send to Back
- Delete

---

## 5. Tables

### 5.1 Marking as a Table

Any placed element can be designated as a table via the "Is a table" toggle in the sidebar (edit mode). Toggling it on immediately reveals table-specific fields.

### 5.2 Table Fields

| Field | Type | Constraints |
|---|---|---|
| Name | String | Required when marked as table |
| Seat count | Integer | 1–99 |
| Status | Enum | Available, Reserved, Occupied |

### 5.3 Table Visual Indicator (View Mode)

On the canvas in view mode, table elements always show:
- The table name as a text label
- A colored status badge:
  - **Available** → green
  - **Reserved** → yellow/amber
  - **Occupied** → red

The badge/label is an overlay on top of the element image (the image itself is not tinted or bordered).

### 5.4 Clicking a Table (View Mode)

Clicking a table element in view mode opens a **slide-out sheet panel** from the right side of the screen (similar to shadcn/ui Sheet behavior):
- Shows: table name, seat count, current status
- **Status is editable** directly in the sheet (dropdown or segmented control)
- Table name and seat count are **read-only** in view mode (require edit mode to change)
- Changing status saves immediately (no separate save button in the sheet)

### 5.5 Status Summary Strip

A strip in the top bar (right side, near the Edit button) shows aggregate counts for the **active floor plan**:
- `● Available: N`
- `● Reserved: N`
- `● Occupied: N`

Updates instantly when a table status is changed via the sheet.

---

## 6. Image / Asset Management

### 6.1 Shared Asset Library

All uploaded images live in a single, application-wide shared library. Images are available for use on any floor plan.

### 6.2 Uploading Images

- Available inline in the edit-mode sidebar (both for element images and floor plan backgrounds)
- Accepted formats: PNG, JPG, WebP, SVG
- Size limit: soft warning if over 5 MB, but upload proceeds
- Transparency (alpha channel) is preserved and renders correctly on the canvas

### 6.3 Deleting Images

- Images can be deleted from the library via the sidebar
- If an image is currently referenced by any placed element on any floor plan (including soft-deleted plans), deletion is **blocked** with an error message
- If not in use, the image is permanently deleted from storage and the database

### 6.4 Background Images

- Background images follow the same upload constraints as element images
- Stored separately from element images in the database (different model or distinguishing flag), but share the same upload pipeline

---

## 7. Data Model (Conceptual)

### `floor_plans`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | |
| background_image_id | FK → images | nullable |
| deleted_at | timestamp | soft delete |
| created_at / updated_at | timestamps | |

### `floor_plan_elements`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| floor_plan_id | FK → floor_plans | |
| image_id | FK → images | |
| x | decimal | % of canvas width |
| y | decimal | % of canvas height |
| width | decimal | % of canvas width |
| height | decimal | % of canvas height |
| rotation | decimal | degrees (0–359.99) |
| z_index | integer | render order |
| is_table | boolean | default false |
| table_name | string | nullable |
| seat_count | tinyint | nullable, 1–99 |
| status | enum | Available, Reserved, Occupied; nullable |
| deleted_at | timestamp | soft delete |
| created_at / updated_at | timestamps | |

### `images`
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| filename | string | stored filename |
| original_filename | string | display name |
| path | string | storage path |
| mime_type | string | |
| size | integer | bytes |
| created_at / updated_at | timestamps | |

> **Coordinate storage**: Positions and sizes are stored as **percentages** (0.0–100.0) relative to the canvas/background image dimensions. This ensures layouts scale gracefully across different screen sizes and zoom levels.

---

## 8. Interactions & UX Details

### 8.1 Device Support

Fully responsive — works on desktop, tablet, and mobile. Drag, resize, and rotate in edit mode support touch interactions.

### 8.2 Design Style

Clean and minimal, light mode. White/light grey UI, subtle shadows, professional SaaS aesthetic. No dark mode in v1.

### 8.3 Localisation

English only. No i18n infrastructure required.

### 8.4 No Authentication

The application requires no login. All users have full access to all features including edit mode and image management.

---

## 9. Technical Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12, PHP 8.5 |
| Frontend framework | Livewire v3 |
| Styling | Tailwind CSS v4 |
| JS interactions | Alpine.js + Interact.js (drag, resize, rotate) |
| Database | As per existing Laravel config |
| File storage | Laravel filesystem (local or cloud) |
| Testing | Pest v4 |

### Canvas Interaction Library

**Interact.js** handles:
- Draggable elements (with grid snap + free override)
- Resizable elements (with handles)
- Rotatable elements (15° snap + free override via modifier key)

Livewire handles data persistence; Interact.js manages DOM-level interaction and dispatches position/size/rotation updates back to Livewire components via `$wire` calls or Alpine events.

---

## 10. Out of Scope (v1)

- Real-time multi-user sync (WebSockets / broadcasting)
- Authentication / user roles
- Reservation system integration (reserved status is a manual label only)
- Undo / redo history
- Floor plan versioning / restore history
- Dark mode
- Multi-tenancy
