# Office Service Management System Setup

This project is a Laravel 12 application configured for PHP 8.2.12 on this machine and MySQL database `Office_Service`.

Laravel 13 is the newest documentation line, but it requires PHP 8.3+. This project uses Laravel 12 because it supports PHP 8.2.

## Installation

```bash
composer install
npm install
php artisan key:generate
php artisan storage:link
```

## Database Configuration

The `.env` file is configured for MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=Office_Service
DB_USERNAME=root
DB_PASSWORD=
```

Create the database in MySQL:

```sql
CREATE DATABASE Office_Service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run migrations and seed sample data:

```bash
php artisan migrate
php artisan db:seed
```

## Authentication Setup

Authentication is scaffolded with Laravel Breeze using Blade views.

Installed auth files include:

- `routes/auth.php`
- `app/Http/Controllers/Auth`
- `resources/views/auth`
- `resources/views/profile`
- `resources/views/dashboard.blade.php`

Sample seeded logins use password `password`:

- `admin@office.test`
- `staff@office.test`
- `officer@office.test`
- `manager@office.test`

## Required Packages

Runtime packages:

- `endroid/qr-code` for QR generation
- `picqer/php-barcode-generator` for barcode generation
- `barryvdh/laravel-dompdf` for PDF export
- `rap2hpoutre/fast-excel` for Excel export

Development/auth package:

- `laravel/breeze`

FastExcel is used instead of Laravel Excel because this machine's PHP CLI does not have the `gd` extension enabled, and Laravel Excel's PhpSpreadsheet dependency requires it.

## QR and Barcode Tracking

QR and barcode generation is handled by:

```text
app/Services/CodeGeneratorService.php
```

Generated files are stored as SVG, so the system does not require the PHP GD extension:

```text
storage/app/public/qr-codes
storage/app/public/barcodes
```

Public access is available through:

```text
public/storage
```

## File Upload Configuration

Upload settings are in:

```text
config/office.php
```

Environment values:

```env
FILESYSTEM_DISK=public
OFFICE_UPLOAD_MAX_KB=10240
OFFICE_UPLOAD_MIMES=pdf,jpg,jpeg,png,doc,docx
```

Folders:

```text
storage/app/public/documents
storage/app/public/qr-codes
storage/app/public/barcodes
storage/app/public/exports
```

## Folder Structure

```text
app/
  Http/
    Controllers/
      Admin/
      Auth/
      DepartmentOfficer/
      Manager/
      Staff/
    Middleware/
  Models/
  Services/
config/
  office.php
database/
  migrations/
  seeders/
resources/
  views/
    auth/
    exports/
    profile/
routes/
  auth.php
  web.php
storage/
  app/public/
```

## Route Structure

Main route groups are defined in:

```text
routes/web.php
```

Route prefixes:

- `/admin`
- `/staff`
- `/officer`
- `/manager`

Role middleware examples:

```php
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin');
Route::middleware(['auth', 'verified', 'role:admin,staff'])->prefix('staff');
Route::middleware(['auth', 'verified', 'role:admin,department_officer'])->prefix('officer');
Route::middleware(['auth', 'verified', 'role:admin,manager'])->prefix('manager');
```

## Controller Structure

```text
app/Http/Controllers/Admin/DashboardController.php
app/Http/Controllers/Staff/PersonController.php
app/Http/Controllers/Staff/ServiceApplicationController.php
app/Http/Controllers/Staff/DocumentController.php
app/Http/Controllers/Staff/PaymentController.php
app/Http/Controllers/Staff/AppointmentController.php
app/Http/Controllers/Staff/QrBarcodeController.php
app/Http/Controllers/DepartmentOfficer/ApplicationProcessingController.php
app/Http/Controllers/Manager/ReportController.php
app/Http/Controllers/Manager/ExportController.php
```

## Model Structure

```text
User
Role
Department
Person
Service
ApplicationStatus
ServiceApplication
ApplicationStatusHistory
DocumentType
ApplicationDocument
PaymentMethod
Payment
Appointment
ApplicationNote
OfficeNotification
AuditLog
SystemSetting
```

## Middleware Structure

Role protection is handled by:

```text
app/Http/Middleware/RoleMiddleware.php
```

It is registered as the `role` alias in:

```text
bootstrap/app.php
```

## Development Commands

Run the backend:

```bash
php artisan serve
```

Run frontend assets:

```bash
npm run dev
```

Build frontend assets:

```bash
npm run build
```

List routes:

```bash
php artisan route:list
```
A:\AHSEN\Documents\office\office-service-management\OFFICE_SERVICE_SETUP.md