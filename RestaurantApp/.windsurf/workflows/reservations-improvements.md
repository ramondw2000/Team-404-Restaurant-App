---
description: Reservations UI — full reactive overhaul spec
---

# Reservations UI Improvement Spec

## Overview

Convert the current static Blade + Alpine reservations page into a fully reactive Livewire component with multi-session support, live polling, contextual status actions, smart table selection, and modern UX patterns. This is a **full-scope implementation** — all items below are in scope.

---

## 1. Architecture

### 1.1 Livewire Component

Replace `reservations.blade.php` + its Alpine `x-data` root with a single **full-page Livewire component**:

- **Class:** `App\Livewire\Reservations`
- **View:** `resources/views/livewire/reservations.blade.php`
- The component owns: selected date, active session tab, search query, active status filter, selected reservation ID (for sheet), and the reservations collection.
- All status transitions, create, and edit actions become **`wire:click` methods** on the component — no full-page reloads.
- The existing `reservations.blade.php` becomes a thin wrapper that mounts the Livewire component.

### 1.2 Session Constants

Session time ranges are **hardcoded as a constant array** directly in the Livewire component class:

```php
const SESSIONS = [
    'breakfast' => ['label' => 'Breakfast', 'start' => '07:00', 'end' => '10:30'],
    'lunch'     => ['label' => 'Lunch',     'start' => '12:00', 'end' => '15:00'],
    'dinner'    => ['label' => 'Dinner',    'start' => '17:00', 'end' => '23:00'],
];
```

### 1.3 Live Polling

- Use `#[Poll('5s')]` (Livewire v4 attribute) on the component to re-render every **5 seconds**.
- On each poll cycle, call `ReservationService::autoMarkLateReservations()` — this auto-advances overdue `scheduled` reservations to `late` without needing a cron job.

### 1.4 Blade Sub-components

- `reservation-card`, `status-badge`, `create-sheet`, `edit-sheet`, and `detail-sheet` remain as **Blade components** (not nested Livewire).
- The parent Livewire component handles all `wire:click` actions; cards are pure display.

---

## 2. Page Header Redesign

Replace the current static `<x-ui.page-header title="Reservations">` with:

- **Primary headline:** Selected date displayed in long human-readable format — e.g. `Tuesday, 15 April 2025`.
- **Subtitle/secondary line:** Total reservation count for the selected date — e.g. `14 reservations tonight`.
- **Actions slot:** Calendar popover (see §3) + "New Reservation" button (unchanged).

---

## 3. Date Navigation — Calendar Popover

Replace the current `<input type="date" onchange="this.form.submit()">` with an **Alpine.js calendar popover**:

- A calendar icon button in the header actions opens a month-view popover via Alpine `x-show`.
- Clicking a date in the popover calls a Livewire method `setDate($date)` which updates the component's `$selectedDate` property and re-renders the list — **no page reload**.
- The currently selected date is highlighted in the calendar with the Molveno blue brand colour.
- The popover closes on date selection or outside click (`@click.outside`).

---

## 4. Multi-Session Tabs

Below the page header, render a **`<x-ui.tab-group>`** with three tabs: Breakfast, Lunch, Dinner.

- Each tab shows the session label + reservation count for that session on the selected date as a badge.
- Switching tabs is a `wire:click` call that updates `$activeSession` on the component.
- The reservation list below re-renders filtered to the active session's time range.
- Default active tab on load: the session closest to `now()` (e.g. if current time is 18:30, default to Dinner).

---

## 5. Reservation List

### 5.1 Search + Status Filter Bar

Above the list, render:

1. **`<x-ui.search-input>`** bound with `wire:model.live.debounce.300ms="search"` — filters the list by guest name in real-time.
2. **Status filter pills** (using `<x-ui.tab-group>` or equivalent pill row): All | Scheduled | Arrived | Late | Departed | Cancelled | No Show.
   - Each pill shows a count of reservations in that status for the current session + date.
   - Active filter stored in `$statusFilter` Livewire property. Default: `"all"`.

### 5.2 Time Slot Grouping

The filtered collection is grouped by 30-minute time slots (existing behaviour) and rendered with the current time-slot header design (blue pill with `H:i`).

### 5.3 Reservation Card

`reservation-card.blade.php` — keep minimal, with the following additions:

- **Room number indicator:** if `room_number` is set, show a small hotel icon + room number to the right of the table number.
- Card click fires `wire:click="selectReservation({{ $reservation->id }})"` on the parent component, which sets `$selectedReservationId` and dispatches an Alpine event to open the detail sheet.

---

## 6. Detail Sheet

When a reservation card is clicked, open **`<x-ui.sheet name="detail-reservation">`** (slide-in panel, right side).

### 6.1 Sheet Contents

- **Guest avatar + name + datetime** (existing design, keep).
- **Details section:** party size, table, status badge, room number (if set), phone (if set), email (if set).
- **Internal notes card** (blue-tinted, shown only if notes exist — keep).
- ~~Payment/deposit section~~ — **removed entirely**.
- **Metadata:** Created at (keep).

### 6.2 Status Stepper

Replace the current button grid with a **horizontal 3-step stepper**:

```
● Scheduled  →  ● Arrived  →  ● Departed
```

- Each step is a circle + label. Completed steps are filled Molveno blue, the current step is outlined blue, future steps are grey.
- Steps are **forward-only** — only the *next* step is clickable. Clicking it calls `wire:click="advanceStatus($reservationId)"` on the parent component.
- **Destructive actions** (Late, Cancelled, No Show) rendered as small danger/ghost buttons *below* the stepper, clearly separated. These call `wire:click="setStatus($reservationId, 'late')"` etc.
- The stepper is non-interactive (greyed out) for terminal statuses: Departed, Cancelled, No Show.

### 6.3 Frozen Data While Open

The sheet shows a **snapshot** from when it was opened. The 5s background poll re-renders the list but **does not update the open sheet**. Closing and reopening fetches fresh data.

### 6.4 Edit Button

A full-width "Edit Reservation" button at the bottom of the detail sheet:

- Closes the detail sheet and opens the edit sheet.
- Implemented via Alpine: `@click="$dispatch('close-sheet', 'detail-reservation'); $dispatch('open-sheet', 'edit-reservation')"`.

---

## 7. Edit Sheet

Keep as a **separate `<x-ui.sheet name="edit-reservation">`** opened from the detail sheet's Edit button.

- Pre-fills all fields from the selected reservation.
- On submit, calls a Livewire `updateReservation()` method (replaces the current form POST to `/reservations/{id}`).
- On success: closes the sheet, dispatches a toast event.

---

## 8. Create Sheet

The "New Reservation" button in the header opens `<x-ui.sheet name="create-reservation">`.

### 8.1 Table Picker

Replace the free-text `table_number` input with a **Livewire-reactive `<select>`**:

- Available tables are fetched reactively when **both** `party_size` and `reservation_datetime` are filled in via `wire:model`.
- Calls a new `ReservationService::getAvailableTablesAt(Carbon $dateTime, int $partySize): Collection` method (see §11).
- **Unavailable tables are hidden entirely** — only available tables appear in the dropdown.
- Dropdown option format: `Table 4 — 4 seats`.
- If `party_size` or `reservation_datetime` is not yet set, the dropdown shows placeholder text `"Fill in guests and time first"` and is disabled.
- Selecting a table also populates a hidden `floor_plan_element_id` field, allowing `ReservationService::createForTable()` to be used.

### 8.2 Datetime Validation

- `reservation_datetime` is validated server-side via Livewire rules: **must not be in the past**.
- Inline Livewire validation error shown below the field: `"Reservation time cannot be in the past."`

### 8.3 Form Submission

On submit, the Livewire `createReservation()` method delegates to `ReservationService::createForTable()` if a `floor_plan_element_id` was selected, or plain `Reservation::create()` if no table was chosen.

---

## 9. Optimistic Status UI

For status transitions (stepper clicks and destructive buttons):

- **Immediately update the status badge colour** in Alpine on the client side (optimistic update) before the Livewire round-trip completes.
- On Livewire response, the re-rendered component confirms the actual state.
- On error, the re-render corrects the badge back to the real status automatically.

Implementation pattern — Alpine property `localStatus` on the card, updated on click:

```html
<button wire:click="advanceStatus({{ $id }})"
        @click="localStatus = getNextStatus(localStatus)">
```

---

## 10. Success Feedback

After every successful action (create, edit, status change), dispatch a **`<x-ui.toast>`** notification:

- Toast message is contextual: e.g. `"Maria Santos marked as Arrived"`, `"Reservation created for John Smith"`.
- Auto-dismiss after ~3 seconds (existing toast behaviour).
- Dispatched from Livewire via `$this->dispatch('toast', message: '...')` caught by the `x-ui.toast` Alpine listener.

---

## 11. Files to Create / Modify

| File | Action |
|---|---|
| `app/Livewire/Reservations.php` | **Create** — main Livewire component |
| `resources/views/livewire/reservations.blade.php` | **Create** — Livewire view |
| `resources/views/reservations.blade.php` | **Modify** — replace body with `<livewire:reservations />` |
| `resources/views/components/reservations/reservation-card.blade.php` | **Modify** — add room indicator, wire:click |
| `resources/views/components/reservations/detail-sheet.blade.php` | **Create** — replaces `detail-panel.blade.php`; sheet-based with stepper |
| `resources/views/components/reservations/status-stepper.blade.php` | **Create** — reusable stepper sub-component |
| `resources/views/components/reservations/create-sheet.blade.php` | **Create** — replaces `create-modal.blade.php`; includes table picker |
| `resources/views/components/reservations/edit-sheet.blade.php` | **Modify** — replaces `edit-modal.blade.php`; wire-based submission |
| `resources/views/components/reservations/status-badge.blade.php` | **Modify** — add Alpine `x-bind:class` support for optimistic update |
| `app/Services/ReservationService.php` | **Modify** — add `getAvailableTablesAt(Carbon $dateTime, int $partySize): Collection` |

---

## 12. Testing

- Feature tests for the Livewire component covering: session tab switching, search filtering, status filter, status transitions (`advanceStatus`, `setStatus`), create, and edit.
- Unit tests for the new `ReservationService::getAvailableTablesAt()` method.
- Run with: `php artisan test --compact --filter Reservation`