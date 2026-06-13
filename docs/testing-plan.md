# Office Service Management System Testing Plan

Use this table for manual QA, UAT, and as a blueprint for Laravel feature tests. Fill **Actual Result** and **Status** during each run.

| Test Case ID | Test Scenario | Test Steps | Expected Result | Actual Result | Status |
| --- | --- | --- | --- | --- | --- |
| AUTH-001 | Login with valid active user | Open `/login`, enter valid email/password, submit | User is authenticated and redirected to role dashboard |  | Pending |
| AUTH-002 | Login throttling | Submit invalid credentials 6 times for same email/IP | Login is blocked with throttle message |  | Pending |
| AUTH-003 | Logout | Login, click Logout | Session is invalidated and user returns to public page |  | Pending |
| RBAC-001 | Admin access | Login as Admin, open `/admin/users` | Page loads and user management actions are visible |  | Pending |
| RBAC-002 | Staff access restriction | Login as Staff, open `/admin/users` | Request is denied with 403 |  | Pending |
| RBAC-003 | Officer department restriction | Login as Department Officer, open an application from another department | Request is denied with 403 |  | Pending |
| PEOPLE-001 | Person registration | Login as Staff, open person create page, enter required person data, submit | Person is saved, unique person ID is generated, profile page opens |  | Pending |
| PEOPLE-002 | Person validation | Submit person form without NIC/passport | Validation error is shown |  | Pending |
| QR-001 | QR code generation | Register a new person | QR code and barcode values/files are created |  | Pending |
| QR-002 | QR scan redirect | Open scanner, scan valid person QR | Browser redirects to person profile |  | Pending |
| QR-003 | Invalid QR scan | Scan random/unknown QR value | Error message is shown, no private data is exposed |  | Pending |
| PROFILE-001 | Person profile view | Open a person profile as Staff | Photo, person ID, QR, applications, payments, documents, notes, appointments, and timeline are shown |  | Pending |
| APP-001 | Application creation | Create application for registered person with service, department, officer, status | Application number is generated and details page opens |  | Pending |
| APP-002 | Application status update | Update application status with remarks | Application status changes and history timeline entry is created |  | Pending |
| APP-003 | Application search/filter | Filter applications by status, department, and search text | Result list matches filters and pagination keeps query string |  | Pending |
| OFFICER-001 | Officer assigned applications | Login as officer, open officer applications page | Only applications in officer department are listed |  | Pending |
| OFFICER-002 | Officer status workflow | Officer changes assigned application to Processing/Completed/Rejected | Allowed status changes save, remarks and audit logs are created |  | Pending |
| DOC-001 | Document upload | Upload PDF/JPG/PNG/DOC/DOCX under allowed size | Document is saved and visible on profile/application |  | Pending |
| DOC-002 | Invalid document upload | Upload disallowed file type or oversized file | Validation error is shown and file is not stored |  | Pending |
| DOC-003 | Secure document download | Try document download as unauthorized role/user | Request is denied with 403 |  | Pending |
| PAY-001 | Payment entry | Add payment to application with method/status/amount | Receipt number is generated and payment appears in history |  | Pending |
| PAY-002 | Payment receipt PDF | Open payment PDF export | PDF downloads with correct receipt data |  | Pending |
| APPT-001 | Appointment booking | Book appointment for person/application | Appointment is saved and appears in calendar/profile/dashboard |  | Pending |
| APPT-002 | Appointment reschedule/cancel | Edit appointment date/time, then cancel | Status and new schedule are saved correctly |  | Pending |
| NOTIF-001 | Notification creation | Create/assign/update application | Assigned officer and managers receive notification where applicable |  | Pending |
| NOTIF-002 | Mark notifications read | Open dropdown/list and mark all read | Unread count becomes zero |  | Pending |
| REPORT-001 | Reports filter | Open reports, filter by date range/department/status | Summary cards, tables, and charts reflect filters |  | Pending |
| REPORT-002 | Reports export | Export report to PDF/Excel/CSV | File downloads and matches filtered results |  | Pending |
| SEARCH-001 | Global search | Type person/application text in navbar search | Suggestions appear and selected result opens correct record |  | Pending |
| SEARCH-002 | Advanced search | Use `/search` with person ID, NIC, phone, application number, dates | Matching people/applications are shown |  | Pending |
| PUBLIC-001 | Public status check | Search public page by application number/person ID/NIC/QR | Limited application status is shown without private person details |  | Pending |
| PUBLIC-002 | Public invalid lookup | Search unknown data | Friendly no-result message is shown |  | Pending |
| SEC-001 | CSRF protection | Submit protected POST without CSRF token | Request is rejected |  | Pending |
| SEC-002 | XSS protection | Enter `<script>alert(1)</script>` in searchable text field | Script is displayed as text or sanitized; it does not execute |  | Pending |
| SEC-003 | SQL injection resistance | Search with `' OR 1=1 --` | Query executes safely and does not expose unrelated records |  | Pending |
| SEC-004 | Audit logs | Perform login, create person, upload document, update status, add payment | Audit log entries contain user, action, module, IP, user agent, and timestamp |  | Pending |
| SEC-005 | Backup command | Run `php artisan office:backup` | Timestamped SQL backup is created in `storage/app/backups` |  | Pending |

## Manual Testing Table Template

| Test Case ID | Tester | Date | Browser/Device | Actual Result | Status | Notes |
| --- | --- | --- | --- | --- | --- | --- |
| AUTH-001 |  |  |  |  | Pass/Fail |  |
| PEOPLE-001 |  |  |  |  | Pass/Fail |  |
| APP-001 |  |  |  |  | Pass/Fail |  |
| PUBLIC-001 |  |  |  |  | Pass/Fail |  |

## Security Regression Checklist

- Confirm every state-changing form includes `@csrf`.
- Confirm admin, staff, officer, and manager routes return 403 for unauthorized roles.
- Confirm department officers cannot view or update another department's applications.
- Confirm uploads accept only configured file types and size limits.
- Confirm private documents are streamed through controllers, not direct public links.
- Confirm login throttle works after repeated failed attempts.
- Confirm session cookie settings are production-ready: encrypted, HTTP-only, SameSite, and secure over HTTPS.
- Confirm audit logs are written for authentication, people, applications, documents, payments, appointments, users, departments, services, and settings.
- Confirm backups are stored outside the public web root and protected by server permissions.
