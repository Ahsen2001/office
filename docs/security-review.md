# Security Review and Implemented Hardening

This project uses Laravel's built-in protections as the foundation: Blade escaping, CSRF middleware, validated requests, Eloquent/query builder parameter binding, hashed passwords, session regeneration, and login throttling.

## Implemented Code Locations

| Security Area | Location | What to Check |
| --- | --- | --- |
| Password hashing | `app/Models/User.php`, `app/Http/Controllers/Admin/UserController.php` | User model casts `password` as `hashed`; admin user creation/update also uses `Hash::make`. |
| Login throttling | `app/Http/Requests/Auth/LoginRequest.php` | Failed attempts are rate-limited by email and IP. |
| Secure session handling | `app/Http/Controllers/Auth/AuthenticatedSessionController.php`, `.env.example` | Session regenerates on login, invalidates on logout, and production defaults recommend encrypted database sessions. |
| CSRF protection | Blade forms and Laravel web middleware | Forms use `@csrf`; Laravel validates tokens on POST/PUT/PATCH/DELETE. |
| SQL injection prevention | Controllers and models | Queries use Eloquent/query builder, not concatenated raw SQL. |
| XSS protection | Blade views and `resources/views/admin/partials/navbar.blade.php` | Blade escapes output; AJAX search suggestions are rendered with `textContent` instead of unsafe HTML interpolation. |
| Role-based access | `routes/web.php`, `app/Http/Middleware/*Middleware.php` | Admin, staff, officer, and manager routes are grouped by middleware. |
| Department restriction | `app/Http/Controllers/DepartmentOfficer/ApplicationProcessingController.php` | Officers can only view/update applications from their assigned department. |
| Authorization policies | `app/Policies/ApplicationDocumentPolicy.php`, `app/Providers/AppServiceProvider.php` | Document view/delete permissions are centralized in a Laravel policy. |
| Secure document download | `app/Http/Controllers/Staff/DocumentController.php` | Preview/download/delete methods authorize access through the policy before streaming files. |
| File upload validation | `app/Http/Controllers/Staff/DocumentController.php`, `app/Http/Controllers/Staff/PersonController.php` | Documents validate extension, MIME type, and size; profile photos validate as images. |
| Security headers | `app/Http/Middleware/SecurityHeaders.php`, `bootstrap/app.php` | Adds nosniff, frame denial, referrer policy, permissions policy, and HSTS over HTTPS. |
| Audit logs | `app/Support/AuditLogger.php` and controllers | Important actions are recorded with user, module, IP, user agent, old/new values, and timestamps. |
| Backup system | `routes/console.php` | `php artisan office:backup` creates timestamped MySQL dumps under `storage/app/backups`. |

## Correct Code Patterns

### Protected Routes

Place role-protected routes in `routes/web.php`:

```php
Route::middleware(['auth', 'active', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class)->except(['show']);
});

Route::middleware(['auth', 'active', 'role:admin,department_officer'])->prefix('officer')->name('officer.')->group(function () {
    Route::get('/applications/{application}', [ApplicationProcessingController::class, 'show']);
});
```

### Validated Input

Place validation in controllers or Form Request classes:

```php
$data = $request->validate([
    'email' => ['required', 'email', 'max:150'],
    'amount' => ['required', 'numeric', 'min:0'],
]);
```

### Safe Output

Use Blade escaped output:

```blade
{{ $person->full_name }}
```

Only use `{!! !!}` for trusted, sanitized HTML.

### Secure Downloads

Documents should be stored on the `local` disk and streamed through a controller after authorization:

```php
$this->authorizeDocumentAccess($document, false);
return response()->streamDownload($callback, $document->file_name);
```

### Audit Logging

Place audit calls immediately after the database change succeeds:

```php
AuditLogger::log('update', 'applications', "Updated application {$application->application_no}.", $application, $oldValues, $newValues, $request);
```

## Production Checklist

- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Set `SESSION_DRIVER=database`, `SESSION_ENCRYPT=true`, `SESSION_HTTP_ONLY=true`, and `SESSION_SECURE_COOKIE=true` when using HTTPS.
- Protect `storage/app/backups` at the server level and copy backups to encrypted off-site storage.
- Run `php artisan storage:link` only for public assets such as profile photos and QR images, not private documents.
- Use HTTPS for camera access, QR scanning, login, and downloads.
- Rotate credentials and keep `.env` out of version control.
