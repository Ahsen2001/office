# Security Review and Hardening Guide

## Implemented Controls

| Area | Implementation |
| --- | --- |
| Passwords | `User` uses Laravel's `hashed` cast; authentication verifies hashes with `Hash::check`. |
| Login protection | `LoginRequest` limits attempts to five per normalized email/IP and rejects inactive users. |
| CSRF | All web state changes use Laravel web middleware and Blade `@csrf`. |
| SQL injection | Controllers use Eloquent/query-builder bindings and validated filter values. |
| XSS | Blade output is escaped; dynamic search suggestions use `textContent`; CSP restricts executable sources. |
| Sessions | Login regenerates session IDs; logout invalidates sessions and CSRF tokens; cookies are HTTP-only and SameSite. |
| RBAC | Route groups and role middleware protect Admin, Management, Reception, Branch Head, and Branch Staff features. |
| Branch isolation | Middleware, model scopes, policies, controller filters, and forced report filters enforce `branch_id`. |
| Documents | Private files use the local disk and are streamed only after policy authorization. |
| Uploads | Documents validate extension, MIME type, and size; profile photos validate as images. |
| Public privacy | Status lookups use exact matching, reject blank searches, select narrow relationships, hide applicant data, and return `no-store`. |
| HTTP headers | `SecurityHeaders` adds CSP, frame denial, MIME sniffing protection, referrer policy, permissions policy, and HSTS over HTTPS. |
| Audit | `AuditLogger` records sensitive changes with actor, action, model, IP, user agent, and old/new values. |
| Backups | `php artisan office:backup` writes timestamped MySQL dumps outside the public directory. |

## Important Code Locations

- Authentication: `app/Http/Requests/Auth/LoginRequest.php`
- Security headers: `app/Http/Middleware/SecurityHeaders.php`
- Branch middleware: `app/Http/Middleware/BranchAccessMiddleware.php`
- Report scoping: `app/Http/Controllers/Manager/ReportController.php`
- Public status separation: `app/Http/Controllers/PublicApplicationStatusController.php`
- Document policy: `app/Policies/ApplicationDocumentPolicy.php`
- Secure file streaming: `app/Http/Controllers/Staff/DocumentController.php`
- Audit helper: `app/Support/AuditLogger.php`
- Backup command: `routes/console.php`

## Correct Patterns

### Role-Protected Routes

Place routes in `routes/web.php`:

```php
Route::middleware(['auth', 'active', 'role:admin,management'])
    ->prefix('reports')
    ->group(function () {
        Route::get('/', [ReportController::class, 'index']);
    });
```

### Branch-Scoped Queries

```php
$applications = ServiceApplication::query()
    ->visibleTo($request->user())
    ->with(['branch', 'status'])
    ->paginate();
```

Never trust a submitted branch ID for branch users. Replace it with the authenticated user's branch before querying.

### Validated Input

```php
$data = $request->validate([
    'status_id' => ['required', 'exists:application_statuses,id'],
    'remarks' => ['nullable', 'string', 'max:5000'],
]);
```

### Safe Blade Output

```blade
{{ $application->remarks }}
```

Avoid `{!! !!}` unless content has been independently sanitized.

### Secure Downloads

```php
Gate::authorize('view', $document);
return response()->streamDownload($callback, $document->file_name);
```

Private documents must not be exposed with direct `/storage/...` URLs.

## Production Checklist

- Set `APP_ENV=production`, `APP_DEBUG=false`, and a unique `APP_KEY`.
- Serve the application only through HTTPS.
- Set `SESSION_SECURE_COOKIE=true` and retain `SESSION_HTTP_ONLY=true`.
- Rotate seeded passwords and database credentials.
- Restrict database accounts to required privileges.
- Keep `.env`, `storage/app/private`, and `storage/app/backups` outside public access.
- Run `php artisan config:cache`, `route:cache`, and `view:cache` after deployment.
- Schedule backups and copy them to encrypted off-site storage.
- Monitor failed logins, authorization failures, audit logs, and backup results.
- Periodically run `composer audit`, `npm audit`, and the full test suite.
- Review CSP sources when adding third-party scripts.

## Residual Risks

- The current CSP permits inline scripts/styles because existing Blade pages contain inline Chart.js and scanner code. Moving these scripts into compiled assets would allow removal of `'unsafe-inline'`.
- Public NIC/passport lookup is explicitly required. Deployments with stricter privacy rules should require an application number plus a second identifier.
- The backup command passes database credentials to `mysqldump`; production deployments should prefer a protected MySQL option file or managed backup service.
