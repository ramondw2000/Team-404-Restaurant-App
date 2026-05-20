# Roles & Permissions

This view defines what each **role** is allowed to do across the entire app. Edit a role here once, and every user with that role inherits the change instantly.

## How it works

Think of it in three layers:

```
Permission  →  Role  →  User
```

- **Permissions** are specific actions (e.g. *Manage Accounts*, *Take Reservations*).
- **Roles** bundle permissions into a job description (e.g. *Waiter* bundles *Take Reservations*, *Take Orders*, *View Menu*).
- **Users** get their access through the roles assigned to them — not directly.

You set **which permissions a role has** here. You assign **roles to people** on the *Users* tab.

## Layout

- **Left sidebar** — List of every role. Click a role to select it.
- **Right pane** — The editor for the selected role: name, colour, permission checkboxes.

## Selecting a Role

Click any role in the left sidebar. The right pane updates to show that role's permissions. The currently selected row is highlighted blue.

The **Admin** badge marks roles flagged as administrator — these bypass most checks. Handle with care.

## Editing Permissions

1. Select a role on the left.
2. Tick or untick permission checkboxes on the right.
3. Save (or changes save automatically depending on the form layout).

**Changes apply immediately.** A user mid-session may lose access on their next click.

## Creating a New Role

1. Click **+ New Role** at the top of the sidebar.
2. Enter:
   - **Role Name** — short, descriptive (e.g. *Bar Staff*).
   - **Colour** — a visual marker used on user badges.
   - **Permissions** — tick all that apply.
3. Save. The new role appears in the sidebar and is available on the *Users* tab.

## Renaming or Recolouring

- Select the role, edit the form fields, save.
- All users with this role pick up the new name/colour automatically.

## Deleting a Role

- Delete is only allowed if **no users** currently have the role.
- First reassign or remove the role from each affected user on the *Users* tab.
- Then return here to delete.

## The Administrator Flag

Roles flagged as administrator (shown with the purple **Admin** badge) **bypass per-permission checks** in many code paths. Treat them like a master key.

- Limit administrator roles to a small, trusted group.
- Never grant the administrator flag to a role intended for general staff.

## Safety Rules

| Don't | Reason |
| --- | --- |
| Remove a permission from your own role mid-session | You might lose access to fix the mistake |
| Delete the administrator role | You may lock everyone out |
| Grant *Manage Accounts* widely | That permission lets the holder grant *any* permission to anyone |
| Create roles ad-hoc per user | Roles are reusable — make them job-shaped, not person-shaped |

## Tips

- **Name roles by job, not person.** "Bar Staff" is reusable; "Marco's Role" is not.
- **Audit annually.** Job titles change — make sure each role still matches a real job.
- **Use colour deliberately.** Red for high-privilege roles, green for limited roles, etc. — makes the *Users* tab easier to scan.
- **Document new permissions.** If you add a permission to the codebase, tick it onto the right role here on the same PR.
