# Users

The Users tab is the master list of every staff member who can log into the system. Each row is one person; each person has one or more **roles**, and those roles determine what they can do.

## How permissions reach a user

You don't give permissions directly to a person. Instead you assign them a **role**, and the role defines what they can do. If someone has two roles, they get everything both roles allow combined.

```
Permission  →  Role  →  User
```

The permissions for each role are set on the *Roles & Permissions* tab.

## Filtering the List

### Role filter tabs
The pills above the table let you narrow by role.

- **All** — Every account
- *Role name* (e.g. *Manager*, *Waiter*) — Only users with that role
- The number on each tab is the count for that role

Clicking a role tab reveals exactly who can do what category of job — handy for "who can take reservations tonight?" style questions.

## Reading the Table

| Column | Notes |
| --- | --- |
| **User** | Full name + avatar (initials if no photo). |
| **Email** | Used as the login identifier. Must be unique. |
| **Role** | Coloured badges. Multiple if the user has multiple roles. |
| **Since** | When the account was created. |
| **Actions** | Edit / Delete buttons. |

## Adding a User

1. Click **Add Account** in the page header.
2. Fill in the form. See the help icon inside that sheet for field-by-field guidance.
3. **Pick at least one role.** Without a role the user can log in but do nothing.

## Editing a User

- Click **Edit** in the actions column. The same account sheet opens, pre-filled.
- Change name, email, password, or roles, then save.
- Leave the password blank to keep the existing password.

## Removing a User

- Click **Delete** in the actions column.
- A confirmation prompt appears — deletion is permanent.
- If a user leaves the company, prefer removing roles (revokes access) before deletion. That preserves the historical record.

## Common Tasks

| I want to... | Where to do it |
| --- | --- |
| Onboard new staff | **Add Account**, then assign role |
| Give someone extra permissions | Edit the **role**, not the user (Roles tab) |
| Temporarily disable access | Remove the user's roles (don't delete) |
| Reset a password | Edit the user, set a new password, share securely |
| Audit who has admin | Click the **Admin** role tab |

## Tips

- **Use real names.** "John D." is easier than "user47" when something goes wrong.
- **Keep one human as an admin at all times.** Don't strip your own admin role mid-session.
- **Audit periodically.** Filter by the most powerful roles and check the list still matches reality.
- **Hand-off etiquette:** when staff leave, remove roles first (cuts access), delete only later.
