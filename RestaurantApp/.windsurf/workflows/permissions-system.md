# Permissions System Specification

## Overview

Managers can create and manage roles, assign permissions to those roles, and assign roles to users. The system is built on top of the already-installed Spatie Permission package.

---

## Roles

### General Rules
- Roles are fully dynamic — managers can create roles with any custom name and color.
- Each role has a **name** and a **color** (for display purposes).
- There is no role hierarchy. When a user has multiple roles, permissions are resolved as a **union** — any permission granted by any role applies.
- Users can be assigned **multiple roles** simultaneously.

### The Management Role
- The **Management** role is the only permanently protected role.
- It **cannot be deleted** or have its Administrator toggle removed.
- All other default roles can be renamed or deleted by managers.

### Deleting Roles
- When a role is deleted, it is **cascade-removed** from all users currently assigned to it.

---

## Permissions

### Structure
Permissions follow a **full CRUD model** per resource: `view`, `create`, `edit`, `delete`.

### Core Resources
The following resources each have their own set of view/create/edit/delete permissions:

- Dashboard
- Account Management
- Dishes
- Kitchen Orders
- Orders
- Restaurant Configuration

> Kitchen Orders and Orders are treated as **separate resources**.

### Administrator Toggle
- Each role has an **Administrator toggle**.
- When enabled, the role **bypasses all permission checks** and implicitly grants every permission.
- The Administrator toggle on the **Management role is permanently enabled** and cannot be disabled.

---

## UI

### Location
Role and permission management lives in **Account Management** as a dedicated tab, alongside existing tabs.

### Role Permissions Editor
- Permissions are displayed as **grouped toggles**, grouped by resource.
- Each resource group shows its four CRUD toggles (view, create, edit, delete).
- The Administrator toggle sits prominently at the top of the role editor.

---

## Safety & Self-Lockout Prevention

The following actions are **blocked** to prevent managers from locking themselves out:

1. **Removing the Administrator toggle from the Management role** — this is permanently locked on.
2. **Removing themselves from the Management role** — a manager cannot unassign the Management role from their own account.

---

## Permission Enforcement

Permission changes take effect **immediately** on the next request — no re-login required.

---

## Seeded Defaults

On first deployment, the system is seeded with the following 6 default roles matching the current application structure:

| Role             | Administrator | Notes                        |
|------------------|---------------|------------------------------|
| Management       | Yes           | Protected, cannot be deleted |
| Server           | No            | —                            |
| Chef             | No            | —                            |
| Receptionist     | No            | —                            |
| Bar Staff        | No            | —                            |
| Maintenance Crew | No            | —                            |

Each seeded role is pre-assigned appropriate permissions for the resources they currently have access to in the application.

---

## Out of Scope (for now)

- Audit logging of permission/role changes
- Role hierarchy or permission precedence beyond union
