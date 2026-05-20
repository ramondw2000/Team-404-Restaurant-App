# Account Form

This side sheet creates a new staff account or edits an existing one. Every staff member who needs to log in has a row created here.

## Full Name

The person's real name. Shown on the user list, on permission audits, and (depending on theme) next to anything they create.

- Use the same name they use in the workplace (e.g. *Sofia Ricci*).
- Avoid usernames like *sofia42* — they're harder to recognise on audits.

## Email Address

This is **the login identifier**. The user signs in with their email + password.

- Must be unique across the system.
- Use the official work email when possible.
- Personal email is fine for short-term staff, but be aware they keep access until you remove it.

## Password

- **Adding a user** — Required. Minimum 8 characters. Share the password with the user via a secure channel (not chat history).
- **Editing a user** — Leave blank to keep their existing password. Fill in only to reset.

A password reset takes effect on the user's next login attempt; existing sessions are not killed.

## Roles

Tick every role the user should have. Roles bundle permissions — see the *Roles & Permissions* tab for what each role grants.

- **At least one role is required.** A user with no role can log in but do nothing.
- Multiple roles are allowed and combine additively.
- The checked-on description (when present) tells you what the role grants in plain English.

## Saving

- **Save** — Persists changes and closes the sheet.
- The user list refreshes immediately so the new/edited account is visible.
- Validation errors appear inline on the offending field.

## Deleting (edit mode only)

- A separate **Delete** button shows for existing accounts.
- Deletion is **permanent** and asks for confirmation.
- Prefer removing roles to fully deleting if there is any chance the person returns.

## Practical Workflow

### New hire
1. Open the form via **Add Account**.
2. Type their name + work email.
3. Generate a strong password and tick the role(s) for their job.
4. Save, then share credentials in person or via password manager.
5. Confirm they can log in.

### Promoting someone
1. Open their existing row → **Edit**.
2. Tick the additional role.
3. Save. Permissions take effect on next page load for them.

### Off-boarding
1. Edit the account.
2. **Uncheck every role** and save. Login still works, but they can do nothing.
3. After verification or grace period, return to delete the account.

## Tips

- **Don't share login credentials across staff.** Each person needs their own account so audit logs are meaningful.
- **Rotate passwords on suspicion of leak,** not on a schedule — frequent rotation invites sticky-note passwords.
- **Test a new account before handing it over.** Log in as them, confirm the menus they need are visible.
