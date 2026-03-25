# Permissions System — Specific Actions Spec

## Overview

This document supersedes the CRUD-based model in `permissions-system.md`.

Permissions are **named after the specific action a user performs**, not generic CRUD verbs. They are grouped by the page they appear on for seeding and display purposes, but rendered as a **flat list** in the role editor UI.

---

## Core Rules (unchanged)

- Roles are fully dynamic — managers can create roles with any name and color.
- No role hierarchy. Multiple roles resolve as a **union** of permissions.
- The **Management** role is permanently protected: cannot be deleted, renamed, or have its Administrator toggle disabled.
- When a role is deleted, it is cascade-removed from all users.
- The **Administrator toggle** on a role bypasses all permission checks entirely.
- Permission changes take effect **immediately** on the next request — no re-login required.

---

## Dashboard

The Dashboard is accessible to **every logged-in user** with no permission check. No "View Dashboard" permission exists.

---

## Permission List

Permissions are grouped below by page for clarity. In the role editor UI they appear as a **flat list**.

### Dishes

| Permission | Description |
|---|---|
| View Dishes | Access the Dishes page |
| Add Dishes | Create new dishes |
| Edit Dishes | Modify existing dishes |
| Delete Dishes | Remove dishes permanently |

### Kitchen Orders

| Permission | Description |
|---|---|
| View Kitchen Orders | Access the Kitchen Orders page |
| Mark Orders Ready | Mark a kitchen order or individual item as ready |

### Orders

| Permission | Description |
|---|---|
| View Orders | Access the Order Management page |
| Create Order | Start a new customer order |
| Edit Order | Modify items on an existing order |
| Cancel Order | Cancel an active order |
| Process Payment | Mark an order as paid / process checkout |
| Assign Table | Assign or reassign a table to an order |

### Account Management

| Permission | Description |
|---|---|
| View Account Management | Access the Account Management page |
| Create User | Add a new staff account |
| Edit User | Update an existing staff account |
| Delete User | Remove a staff account |
| Manage Roles | Access the Roles & Permissions tab; create, edit, delete roles and assign permissions |

### Table Management

| Permission | Description |
|---|---|
| View Table Management | Access the Table Management page |
| Edit Table Layout | Enter edit mode; place, move, resize, and delete floor plan elements |
| Manage Floor Plans | Create and delete floor plans; upload background images |
| Update Table Status | Change a table's status (Available / Occupied / Reserved) outside of edit mode |

### Statistics

| Permission | Description |
|---|---|
| View Statistics | Access the Statistics page (read-only) |

### Cross-Page

These permissions are not tied to a single page.

| Permission | Applies To | Description |
|---|---|---|
| Export Data | Orders, Statistics | Download CSV exports of orders or statistical data |
| Manage Availability | Dishes, Table Management | Toggle dish availability on the Dishes page; update table status on Table Management |

> **Note:** `Manage Availability` and `Update Table Status` both gate table status changes. A role only needs one of them to change table status. `Manage Availability` is the broader cross-page version; `Update Table Status` is page-scoped.

---

## View-Gate Rule

For every page that has a **"View [Page]"** permission:

- The View permission must be **enabled before any action permissions on that page can be enabled**.
- In the role editor UI, action permission toggles are **disabled and visually dimmed** when the View permission is off.
- Enabling View does **not** automatically enable action permissions.
- Disabling View **automatically disables all action permissions** for that page.

Pages with a View gate: Dishes, Kitchen Orders, Orders, Account Management, Table Management, Statistics.

Cross-page permissions (`Export Data`, `Manage Availability`) have **no View gate** — they are always independently toggleable.

---

## Role Editor UI

### Layout

- The role editor renders all permissions as a **flat, scrollable list**.
- Each permission is a single toggle row: `[toggle] Permission Name — Description`.
- Permissions are visually separated into sections by page label (Dishes, Kitchen Orders, etc.), but this is **presentational only** — there is no nesting, and cross-page permissions appear at the bottom under "General".
- The **Administrator toggle** sits at the top of the editor, above the permission list. When enabled, all permission rows are hidden and replaced with an info message.

### View-Gate Interaction

- The **"View [Page]"** toggle for each section is the **first row** in that section and is visually distinct (slightly bolder or with a separator).
- When "View [Page]" is off, all other toggles in that section render as **disabled** (`opacity-50`, `pointer-events-none`).
- When "View [Page]" is turned off, the component **automatically revokes all action permissions** in that group from the role before saving.

---

## Seeded Default Permissions per Role

| Role | Permissions |
|---|---|
| **Management** | Administrator (bypasses all) |
| **Server** | View Orders, Create Order, Edit Order, Cancel Order, Process Payment, Assign Table · View Table Management |
| **Chef** | View Dishes · View Kitchen Orders, Mark Orders Ready |
| **Receptionist** | View Orders, Create Order, Edit Order, Assign Table · View Kitchen Orders · View Table Management, Update Table Status |
| **Bar Staff** | View Dishes · View Kitchen Orders, Mark Orders Ready |
| **Maintenance Crew** | View Table Management, Edit Table Layout, Manage Floor Plans |

---

## Route Protection

Routes map to the **View** permission of their respective page:

| Route | Required Permission |
|---|---|
| `/dishes` | View Dishes |
| `/kitchenorders` | View Kitchen Orders |
| `/ordermanagement` | View Orders |
| `/accounts` | View Account Management |
| `/tablemanagement` | View Table Management |
| `/statistics` | View Statistics |
| `/dashboard` | *(none — all logged-in users)* |

> Actions within a page (e.g. creating an order) are enforced at the controller/component level, not at the route level.

---

## Navigation Visibility

Each nav link uses `@can('View [Page]')` — consistent with the route protection above. The Dashboard link is shown to all authenticated users unconditionally.

---

## Out of Scope

- Audit logging of permission/role changes
- Role hierarchy or permission precedence beyond union
- Per-user permission overrides (only role-level permissions)
