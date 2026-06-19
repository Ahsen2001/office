# Office Service Management System

A Laravel-based service workflow platform for Divisional Secretariats and public offices. It connects citizen registration, QR/barcode identity, service applications, branch processing, appointments, documents, notifications, public status tracking, and management analytics in one secure system.

## Highlights

- Unique person IDs with generated QR codes and Code 128 barcodes
- Service application intake, assignment, status workflow, receipts, and timelines
- Branch-isolated dashboards for Branch Heads and Branch Staff
- Secure private document storage, preview, download, and visibility controls
- Appointment scheduling and applicant service history
- Public privacy-safe application tracking by application number, person ID, NIC/passport, or QR scan
- Operational reports with date, branch, status, and officer filters
- PDF, Excel, and CSV report exports
- Chart.js dashboards, responsive Bootstrap 5 UI, notifications, search, and audit logs

## User Roles

| Role | Main responsibilities |
| --- | --- |
| Admin | Full system, users, branches, services, settings, audit logs, and reports |
| DS / ADS / AO | Office-wide monitoring, applications, branches, and reports |
| Reception Staff | Register people, issue QR/barcodes, create applications, upload intake documents |
| Branch Head | Manage only the assigned branch, assign staff, monitor performance, update applications |
| Branch Staff | Process assigned-branch applications, scan codes, update progress, request documents |
| Public / Applicant | Check limited application status without logging in |

## Technology Stack

- PHP 8.2+
- Laravel 12
- MySQL
- Blade and Bootstrap 5
- Chart.js
- Laravel Breeze authentication
- `endroid/qr-code`
- `picqer/php-barcode-generator`
- `barryvdh/laravel-dompdf`
- `rap2hpoutre/fast-excel`

## Installation

```bash
git clone <repository-url>
cd office-service-management
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Create the MySQL database:

```sql
CREATE DATABASE Office_Service
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Configure `.env`:

```env
APP_NAME="Office Service"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Office_Service
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

Finish setup:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

## Default Development Accounts

All seeded development accounts use the password `password`. Change or remove them before deployment.

| Role | Email |
| --- | --- |
| Admin | `admin@office.test` |
| Reception Staff | `staff@office.test` |
| Branch Head | `branchhead@office.test` |
| Branch Staff | `officer@office.test` |
| DS / ADS / AO | `manager@office.test` |

### Additional Branch Test Accounts

All accounts below use the password `password`.

| Branch | Branch Head | Branch Staff |
| --- | --- | --- |
| Administration | `adminhead@office.test` | `adminofficer@office.test` |
| Accounts | `accountshead@office.test` | `accountsofficer@office.test` |
| Land | `landhead@office.test` | `landofficer@office.test` |
| Social Services | `socialhead@office.test` | `socialofficer@office.test` |
| Samurdhi | `samurdhihead@office.test` | `samurdhiofficer@office.test` |
| Pension | `pensionhead@office.test` | `pensionofficer@office.test` |
| Registration | `registrationhead@office.test` | `registrationofficer@office.test` |
| Development | `developmenthead@office.test` | `developmentofficer@office.test` |
| GN Coordination | `gnhead@office.test` | `gnofficer@office.test` |

Development credentials are displayed on the login page only when `APP_ENV=local`.

## QR and Barcode Workflow

1. Reception Staff registers a person.
2. The system generates a person code, QR SVG, and barcode SVG.
3. Staff can display, print, or download the identity codes.
4. Authorized staff open **QR Scanner** and allow camera access.
5. A valid scan resolves to the permitted person profile.
6. Public users may scan the QR from the public status page to locate applications without seeing private profile data.

Camera scanning requires HTTPS in production.

## Public Status Checking

The public `/status-check` page accepts:

- Application number
- Person ID
- NIC/passport number
- QR code scan

It shows only application number, service, branch, status, dates, missing document names, upcoming appointment, and in-charge officer details. It does not expose applicant contact details, address, uploaded files, internal notes, audit logs, or private remarks.

## Branch-Based Access Control

Branch Heads and Branch Staff are linked to `users.branch_id`. Branch isolation is enforced through:

- Route middleware
- `ServiceApplication::visibleTo()`
- Controller query scopes
- Assignment validation
- Document authorization policies
- Forced report filters
- Person-profile and appointment checks

Changing a branch ID in a URL or report query does not widen a branch user's access.

## Reports and Analytics

Available reports include people registration, applications, pending/completed/rejected work, branch performance, Branch Head and Branch Staff performance, officer workload, delayed applications, appointments, and daily/monthly summaries.

Reports support:

- Date range, branch, status, and officer filters
- Chart.js visualizations and summary cards
- PDF, Excel, and UTF-8 CSV exports
- Print-friendly dashboard output
- Role-specific report availability and branch scoping

## Security Features

- Laravel password hashing and active-account validation
- Login throttling by normalized email and IP
- CSRF protection and session regeneration
- Eloquent parameter binding against SQL injection
- Escaped Blade output and safe DOM rendering
- Role- and branch-based middleware
- Document policies and controller-streamed private files
- Extension, MIME type, and file-size validation
- Content Security Policy and defensive HTTP headers
- Public/private data separation and no-store public lookup responses
- Audit logging for sensitive operations
- MySQL backup command: `php artisan office:backup`

See [docs/security-review.md](docs/security-review.md) for deployment guidance.

## Testing

```bash
php artisan test
vendor/bin/pint --test
```

The manual and automated QA matrix is in [docs/testing-plan.md](docs/testing-plan.md).

## Screenshots

Add release screenshots under `docs/screenshots/` using these suggested names:

| Screen | Suggested file |
| --- | --- |
| Public home | `public-home.png` |
| Public status check | `public-status.png` |
| Admin dashboard | `admin-dashboard.png` |
| Branch dashboard | `branch-dashboard.png` |
| Person profile with QR/barcode | `person-profile.png` |
| Application details and timeline | `application-details.png` |
| Reports and analytics | `reports.png` |

## Project Structure

```text
app/
  Http/Controllers/
    Admin/
    Manager/
    Staff/
  Http/Middleware/
  Models/
  Policies/
  Services/
database/
  migrations/
  seeders/
docs/
resources/views/
  admin/
  dashboards/
  manager/reports/
  public/
  staff/
routes/
  auth.php
  console.php
  web.php
tests/
  Feature/
  Unit/
```

## Production Notes

- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Enable HTTPS and `SESSION_SECURE_COOKIE=true`.
- Rotate all seeded credentials.
- Run queues and scheduled backups under a process manager.
- Store encrypted backup copies outside the web server.
- Keep private documents on the local/private disk.

## Future Enhancements

- Email/SMS applicant notifications
- Configurable workflow steps per service
- Digital signatures and approval certificates
- Multilingual Sinhala/Tamil/English UI
- Two-factor authentication
- Scheduled encrypted off-site backups
- API and mobile application support
- Accessibility conformance audit

## Author

Developed by **AHSEN** — Full Stack Developer.

## License

This project is intended for organizational/public-office deployment. Add the appropriate license before public distribution.
