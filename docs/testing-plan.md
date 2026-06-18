# Office Service Management System Testing Plan

Use this matrix for regression testing and user acceptance testing. “Automated pass” means a corresponding Laravel feature test currently verifies the core behavior; device/camera and visual checks remain manual.

| ID | Test scenario | Test steps | Expected result | Actual result | Status |
| --- | --- | --- | --- | --- | --- |
| AUTH-001 | Valid login | Enter an active seeded account and correct password | Login succeeds and role dashboard opens | Covered by `AuthenticationTest` | Pass |
| AUTH-002 | Invalid login | Enter valid email and wrong password | Generic authentication error; no login | Covered by `AuthenticationTest` | Pass |
| AUTH-003 | Login throttling | Submit five invalid attempts, then retry | Further attempts are temporarily blocked | Execute manually/feature expansion | Pending |
| AUTH-004 | Inactive account | Deactivate user and attempt login | Login is rejected | Execute manually | Pending |
| AUTH-005 | Logout | Login and submit Logout | Session invalidates and public home opens | Covered by `AuthenticationTest` | Pass |
| RBAC-001 | Admin dashboard | Login as Admin and open `/admin/dashboard` | Full dashboard renders | Covered by `RoleBasedAccessTest` | Pass |
| RBAC-002 | Management dashboard | Login as DS/ADS/AO | Office-wide dashboard renders | Covered by `RoleBasedAccessTest` | Pass |
| RBAC-003 | Reception dashboard | Login as Reception Staff | Reception dashboard renders | Covered by `RoleBasedAccessTest` | Pass |
| RBAC-004 | Branch Head dashboard | Login as Branch Head | Assigned-branch dashboard renders | Covered by `RoleBasedAccessTest` | Pass |
| RBAC-005 | Branch Staff dashboard | Login as Branch Staff | Assigned-work dashboard renders | Covered by `RoleBasedAccessTest` | Pass |
| RBAC-006 | Admin-only user management | Open `/admin/users` as Management user | 403 response | Covered by `RoleBasedAccessTest` | Pass |
| BRANCH-001 | Other branch page | Branch Staff opens another branch URL | 403 response | Covered by `RoleBasedAccessTest` | Pass |
| BRANCH-002 | Report query tampering | Branch Head submits another `branch_id` | Results remain pinned to assigned branch | Covered by `ReportModuleTest` | Pass |
| PEOPLE-001 | Person registration | Reception submits valid person data | Person and unique ID are created | Manual workflow run | Pending |
| PEOPLE-002 | Registration validation | Submit without both NIC and passport | Validation error; no person created | Manual workflow run | Pending |
| QR-001 | QR/barcode generation | Register a person | QR and barcode SVG files and values exist | Verified in live browser | Pass |
| QR-002 | QR profile display | Open person profile | QR and barcode render successfully | Verified in live browser | Pass |
| QR-003 | Valid scan | Scan a registered code | Authorized profile opens | Requires camera/device test | Pending |
| QR-004 | Invalid scan | Scan an unknown code | Friendly error and no profile data | Requires camera/device test | Pending |
| PROFILE-001 | Person profile | Open profile as authorized user | Applications, documents, appointments, notes, codes, and timeline display | Verified in live browser | Pass |
| APP-001 | Application creation | Reception creates application | Unique number, branch, creator, and initial history save | Manual workflow run | Pending |
| APP-002 | Branch assignment | Select service/branch | Application is assigned to selected valid branch | Manual workflow run | Pending |
| APP-003 | Officer assignment | Branch Head selects Branch Staff | Only staff in application branch can be assigned | Manual workflow run | Pending |
| APP-004 | Status update | Change status with remarks | Status changes and immutable timeline entry is added | Verified existing workflow | Pass |
| APP-005 | Application filters | Search by number/name/NIC and filter branch/status | Matching scoped rows display | Manual regression | Pending |
| DOC-001 | Valid upload | Upload PDF/JPG/PNG/DOC/DOCX within size limit | Private file and metadata save | Manual regression | Pending |
| DOC-002 | Invalid upload | Upload executable or oversized file | Validation rejects upload | Manual regression | Pending |
| DOC-003 | Secure download | Other-branch user requests document URL | 403 response | Policy review/manual regression | Pending |
| DOC-004 | Visibility | Upload internal/branch/public documents | Access follows selected visibility and role | Manual regression | Pending |
| APPT-001 | Book appointment | Submit valid person, date, and time | Appointment appears on profile/dashboard | Manual regression | Pending |
| APPT-002 | Reschedule/cancel | Update schedule then cancel | New schedule/status persists | Manual regression | Pending |
| NOTIF-001 | Assignment notification | Assign an application | Assigned officer receives notification | Manual regression | Pending |
| NOTIF-002 | Notification overlay | Open navbar bell | Dropdown stays visible above dashboard content | Verified in live browser | Pass |
| NOTIF-003 | Mark all read | Click “Mark all read” | Badge count becomes zero | Manual regression | Pending |
| REPORT-001 | Admin reports | Open `/reports` as Admin | All report types, charts, and branches appear | Covered by `ReportModuleTest` | Pass |
| REPORT-002 | Management reports | Open `/reports` as DS/ADS/AO | All reports appear | Covered by `ReportModuleTest` | Pass |
| REPORT-003 | Branch reports | Open reports as Branch Head/Staff | Only permitted reports and branch data appear | Covered by `ReportModuleTest` | Pass |
| REPORT-004 | Reception reports | Open reports as Reception | Only people/application/daily/monthly reports appear | Covered by `ReportModuleTest` | Pass |
| REPORT-005 | Combined filters | Apply date, branch, status, officer | Cards, charts, table, exports match filters | Covered by `ReportModuleTest` | Pass |
| REPORT-006 | PDF export | Download selected report PDF | Valid filtered PDF downloads | Manual file inspection | Pending |
| REPORT-007 | Excel export | Download selected report Excel | Valid `.xlsx` downloads | Manual file inspection | Pending |
| REPORT-008 | CSV export | Download selected report CSV | UTF-8 CSV downloads | Covered by `ReportModuleTest` | Pass |
| SEARCH-001 | Global search | Enter person/application text | Safe suggestions appear and open correct record | Manual regression | Pending |
| PUBLIC-001 | Application lookup | Search by application number | Limited result displays | Covered by `PublicWebsiteTest` | Pass |
| PUBLIC-002 | Person/NIC/QR lookup | Search each supported identifier | Matching limited applications display | Manual regression | Pending |
| PUBLIC-003 | Blank lookup | Submit empty public query | No recent applications are exposed | Covered by `PublicWebsiteTest` | Pass |
| PUBLIC-004 | Officer details | Open public result | Officer name, designation, contact, and branch display | Covered by `PublicWebsiteTest` | Pass |
| PUBLIC-005 | Privacy separation | Inspect public result HTML | No applicant contact/address/private notes/files | Covered by `PublicWebsiteTest` | Pass |
| PUBLIC-006 | Invalid lookup | Search unknown reference | Friendly no-result message displays | Manual regression | Pending |
| UI-001 | Mobile layout | Test 360px, 768px, and desktop widths | Navigation, cards, tables, and forms remain usable | Manual device test | Pending |
| UI-002 | Print report | Print reports page | Navigation/filters hide and report content prints clearly | Manual print preview | Pending |
| SEC-001 | CSRF | POST without token | Laravel returns 419 | Manual/security automation | Pending |
| SEC-002 | SQL injection input | Search with `' OR 1=1 --` | No unrelated records or SQL error | Manual/security automation | Pending |
| SEC-003 | Stored/reflected XSS | Save/search `<script>` payload | Payload is escaped and does not execute | Manual/security automation | Pending |
| SEC-004 | Security headers | Inspect authenticated/public response | CSP, frame, MIME, referrer, permissions headers exist | Manual header inspection | Pending |
| SEC-005 | Audit logs | Create/update/upload/login | Correct actor/action/IP/timestamp recorded | Manual regression | Pending |
| SEC-006 | Backup | Run `php artisan office:backup` | Non-public timestamped SQL dump is created | Environment-dependent | Pending |

## Manual Execution Record

| Test Case ID | Tester | Date | Browser / Device | Actual result | Status | Notes / Defect ID |
| --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  | Pass / Fail / Blocked |  |
|  |  |  |  |  | Pass / Fail / Blocked |  |
|  |  |  |  |  | Pass / Fail / Blocked |  |

## Recommended Test Commands

```bash
php artisan test
php artisan test --filter=ReportModuleTest
php artisan test --filter=PublicWebsiteTest
vendor/bin/pint --test
```

## Release Exit Criteria

- All automated tests pass.
- No critical/high security defect remains open.
- Every role completes its dashboard and access-control smoke test.
- Branch-isolation tests pass with URL and query tampering.
- PDF, Excel, and CSV exports open successfully.
- Public results contain no applicant-private fields.
- QR scanning is verified on at least one Android and one iOS device over HTTPS.
