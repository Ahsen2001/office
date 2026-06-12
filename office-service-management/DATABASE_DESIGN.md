# Office_Service Database Design

This database design supports a Laravel and MySQL based Office Service Management System with QR/Barcode tracking.

Database name: `Office_Service`

## Main Relationships

- One `department` has many `users`.
- One `department` has many `services`.
- One `person` has many `service_applications`.
- One `service_application` belongs to one `person`, one `service`, one `department`, and one assigned officer from `users`.
- One `service_application` has many `application_status_histories`.
- One `service_application` has many `application_documents`, `payments`, `appointments`, `application_notes`, and `notifications`.
- Users can have multiple roles through `role_user`.

## Tables

### roles

| Column | Type | Key | Description |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK | Role ID |
| name | VARCHAR(80) | UNIQUE | Role name |
| slug | VARCHAR(80) | UNIQUE | Machine-readable role |
| description | TEXT |  | Role description |
| is_active | BOOLEAN |  | Active flag |
| created_at, updated_at | TIMESTAMP |  | Laravel timestamps |

Default roles: Admin, Staff, Department Officer, Manager.

### departments

| Column | Type | Key | Description |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK | Department ID |
| code | VARCHAR(30) | UNIQUE | Department code |
| name | VARCHAR(150) |  | Department name |
| phone | VARCHAR(30) |  | Contact phone |
| email | VARCHAR(150) |  | Contact email |
| description | TEXT |  | Department description |
| is_active | BOOLEAN |  | Active flag |
| created_at, updated_at | TIMESTAMP |  | Laravel timestamps |

### users

| Column | Type | Key | Description |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK | User ID |
| department_id | BIGINT UNSIGNED | FK departments.id | Nullable user department |
| name | VARCHAR(150) |  | User name |
| email | VARCHAR(150) | UNIQUE | Login email |
| phone | VARCHAR(30) |  | Phone |
| email_verified_at | TIMESTAMP |  | Verification timestamp |
| password | VARCHAR(255) |  | Hashed password |
| is_active | BOOLEAN |  | Active flag |
| remember_token | VARCHAR(100) |  | Laravel remember token |
| created_at, updated_at, deleted_at | TIMESTAMP |  | Laravel timestamps and soft delete |

### role_user

| Column | Type | Key | Description |
| --- | --- | --- | --- |
| role_id | BIGINT UNSIGNED | PK, FK roles.id | Role |
| user_id | BIGINT UNSIGNED | PK, FK users.id | User |
| created_at, updated_at | TIMESTAMP |  | Laravel timestamps |

### people

| Column | Type | Key | Description |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK | Person row ID |
| person_code | VARCHAR(40) | UNIQUE | Public unique person ID |
| qr_code_value | VARCHAR(120) | UNIQUE | QR payload/value |
| barcode_value | VARCHAR(120) | UNIQUE | Barcode payload/value |
| qr_code_path | VARCHAR(255) |  | Stored QR image path |
| barcode_path | VARCHAR(255) |  | Stored barcode image path |
| first_name, last_name, full_name | VARCHAR |  | Person names |
| gender | ENUM |  | male, female, other, not_specified |
| date_of_birth | DATE |  | Date of birth |
| national_id | VARCHAR(80) | UNIQUE | National ID |
| passport_no | VARCHAR(80) | UNIQUE | Passport number |
| phone | VARCHAR(30) |  | Phone |
| email | VARCHAR(150) |  | Email |
| address_line_1, address_line_2 | VARCHAR(180) |  | Address |
| city, state, postal_code, country | VARCHAR |  | Location |
| photo_path | VARCHAR(255) |  | Photo path |
| registered_by | BIGINT UNSIGNED | FK users.id | Staff user |
| registered_at | TIMESTAMP |  | Registration date |
| is_active | BOOLEAN |  | Active flag |
| created_at, updated_at, deleted_at | TIMESTAMP |  | Laravel timestamps and soft delete |

### services

| Column | Type | Key | Description |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK | Service ID |
| department_id | BIGINT UNSIGNED | FK departments.id | Owning department |
| code | VARCHAR(40) | UNIQUE | Service code |
| name | VARCHAR(180) |  | Service name |
| description | TEXT |  | Description |
| fee_amount | DECIMAL(12,2) |  | Service fee |
| estimated_days | SMALLINT UNSIGNED |  | Expected duration |
| requires_appointment | BOOLEAN |  | Appointment required |
| requires_payment | BOOLEAN |  | Payment required |
| is_active | BOOLEAN |  | Active flag |
| created_at, updated_at | TIMESTAMP |  | Laravel timestamps |

### application_statuses

| Column | Type | Key | Description |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK | Status ID |
| code | VARCHAR(50) | UNIQUE | submitted, pending, under_review, processing, waiting_for_documents, approved, rejected, completed, cancelled |
| name | VARCHAR(80) | UNIQUE | Display name |
| sort_order | SMALLINT UNSIGNED |  | Workflow order |
| is_terminal | BOOLEAN |  | Final status flag |
| is_active | BOOLEAN |  | Active flag |
| created_at, updated_at | TIMESTAMP |  | Laravel timestamps |

### service_applications

| Column | Type | Key | Description |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK | Application ID |
| application_no | VARCHAR(50) | UNIQUE | Public application number |
| person_id | BIGINT UNSIGNED | FK people.id | Applicant |
| service_id | BIGINT UNSIGNED | FK services.id | Requested service |
| department_id | BIGINT UNSIGNED | FK departments.id | Processing department |
| assigned_officer_id | BIGINT UNSIGNED | FK users.id | Assigned officer |
| status_id | BIGINT UNSIGNED | FK application_statuses.id | Current status |
| submitted_by | BIGINT UNSIGNED | FK users.id | Staff who submitted |
| priority | ENUM |  | low, normal, high, urgent |
| subject | TEXT |  | Short subject |
| description | LONGTEXT |  | Application details |
| total_fee | DECIMAL(12,2) |  | Fee due |
| due_date | DATE |  | Due date |
| submitted_at | TIMESTAMP |  | Submission timestamp |
| approved_at, rejected_at, completed_at, cancelled_at | TIMESTAMP |  | Lifecycle timestamps |
| rejection_reason | TEXT |  | Required when rejected |
| created_at, updated_at, deleted_at | TIMESTAMP |  | Laravel timestamps and soft delete |

### application_status_histories

Tracks every status change for an application.

| Column | Type | Key | Description |
| --- | --- | --- | --- |
| id | BIGINT UNSIGNED | PK | History ID |
| application_id | BIGINT UNSIGNED | FK service_applications.id | Application |
| from_status_id | BIGINT UNSIGNED | FK application_statuses.id | Previous status |
| to_status_id | BIGINT UNSIGNED | FK application_statuses.id | New status |
| changed_by | BIGINT UNSIGNED | FK users.id | User who changed status |
| remarks | TEXT |  | Change remarks |
| changed_at | TIMESTAMP |  | Change timestamp |
| created_at, updated_at | TIMESTAMP |  | Laravel timestamps |

### document_types

Stores document categories such as NIC, birth certificate, proof of address, and application form.

### application_documents

Stores uploaded files connected to a person and application, with upload and verification information.

### payment_methods

Stores payment methods such as cash, card, bank transfer, and online payment.

### payments

Stores receipt number, amount, method, payment status, paid date, and receiving user.

### appointments

Stores appointment number, application/person/department/officer, appointment date, time, status, purpose, and remarks.

### application_notes

Stores internal, manager-only, or public notes against applications.

### notifications

Stores system, email, or SMS notifications for users, people, and applications.

### audit_logs

Stores sensitive system events including module, action, changed values, IP address, and user agent.

### system_settings

Stores configurable key-value settings such as code prefixes, office name, and session timeout.

## Laravel Usage

Set the database name in `.env`:

```env
DB_DATABASE=Office_Service
```

Run migrations:

```bash
php artisan migrate
```

Run sample seed data:

```bash
php artisan db:seed --class=OfficeServiceSeeder
```

Sample login users created by the seeder all use password `password`:

| Role | Email |
| --- | --- |
| Admin | admin@office.test |
| Staff | staff@office.test |
| Department Officer | officer@office.test |
| Manager | manager@office.test |
