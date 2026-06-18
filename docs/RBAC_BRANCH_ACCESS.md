# Authentication, Roles, and Branch Access

## Staff roles

| Role slug | Dashboard | Data access |
|---|---|---|
| `admin` | `admin.dashboard` | Full system access |
| `management` | `management.dashboard` | Read access across all branches and reports |
| `reception` | `reception.dashboard` | Person registration, application creation, QR/barcode scanning |
| `branch_head` | `branch-head.dashboard` | Assigned branch, assignment of applications, progress monitoring |
| `branch_staff` | `branch-staff.dashboard` | Assigned branch applications and status processing |

Public applicants do not have accounts. The public status endpoint remains outside the authenticated route groups and exposes only limited application, branch, status, and in-charge officer information.

## Branch restriction

Branch users have a required `users.branch_id`. The following layers enforce isolation:

1. `BranchAccessMiddleware` validates route-bound branches, services, applications, and appointments.
2. `ServiceApplication::scopeVisibleTo()` adds `where branch_id = authenticated_user.branch_id` for branch users.
3. Controllers apply branch filtering to lists, dashboards, appointments, search results, people profiles, services, and reports.
4. Assignment validation requires the selected Branch Staff user to belong to the application's branch.
5. Document policy checks the application's `branch_id`.

Example:

```php
$applications = ServiceApplication::query()
    ->visibleTo($request->user())
    ->with(['person', 'service', 'branch', 'status'])
    ->paginate();
```

## User validation

- Email addresses are unique and normalized to lowercase.
- Passwords use Laravel's password rule defaults and are stored with the model's `hashed` cast.
- Every managed user receives exactly one active staff role.
- `branch_id` is required for `branch_head` and `branch_staff`.
- Public registration routes are disabled; only Admin can create staff accounts.
- Admin cannot delete or deactivate their own account.

## Login security

- Login attempts are limited to five per email/IP throttle key.
- Inactive accounts cannot authenticate.
- Session IDs regenerate after successful login.
- Logout invalidates the session and regenerates the CSRF token.
- Authenticated routes use CSRF protection, active-account checks, role middleware, and branch middleware.
- Demo credentials are rendered only when `APP_ENV=local`.

## Service and branch access

- Admin: create, update, delete, activate, and deactivate branches and services.
- Management: view all branches and services.
- Branch Head / Branch Staff: view only their assigned branch and its services.
- Public: view active services only.
