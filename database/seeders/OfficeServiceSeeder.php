<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OfficeServiceSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::transaction(function () use ($now) {
            $roles = [
                ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Full system access.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Reception Staff', 'slug' => 'reception', 'description' => 'Registers people and creates applications.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Branch Head', 'slug' => 'branch_head', 'description' => 'Manages an assigned branch.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Branch Staff', 'slug' => 'branch_staff', 'description' => 'Processes assigned branch applications.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Divisional Secretary / ADS / AO', 'slug' => 'management', 'description' => 'Monitors all branches and reports.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ];

            foreach ($roles as $role) {
                DB::table('roles')->updateOrInsert(['slug' => $role['slug']], $role);
            }

            $adminRoleId = DB::table('roles')->where('slug', 'admin')->value('id');
            $staffRoleId = DB::table('roles')->where('slug', 'reception')->value('id');
            $branchHeadRoleId = DB::table('roles')->where('slug', 'branch_head')->value('id');
            $officerRoleId = DB::table('roles')->where('slug', 'branch_staff')->value('id');
            $managerRoleId = DB::table('roles')->where('slug', 'management')->value('id');

            $generalDepartmentId = DB::table('departments')->insertGetId([
                'code' => 'GEN',
                'name' => 'General Services Department',
                'phone' => '+94-11-000-1000',
                'email' => 'general@example.office',
                'description' => 'Handles general office services.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $recordsDepartmentId = DB::table('departments')->insertGetId([
                'code' => 'REC',
                'name' => 'Records Department',
                'phone' => '+94-11-000-2000',
                'email' => 'records@example.office',
                'description' => 'Handles certificates, letters, and record verification.',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $generalBranchId = DB::table('branches')->insertGetId([
                'code' => 'ADMIN', 'name' => 'Administration Branch', 'phone' => '+94-11-000-1000',
                'email' => 'administration@example.office', 'location' => 'Ground Floor',
                'description' => 'General administration and reception services.', 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);

            $recordsBranchId = DB::table('branches')->insertGetId([
                'code' => 'REC', 'name' => 'Registration Branch', 'phone' => '+94-11-000-2000',
                'email' => 'registration@example.office', 'location' => 'First Floor',
                'description' => 'Certificates, registrations, and record verification.', 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);

            foreach ([['LAND', 'Land Branch'], ['SOCIAL', 'Social Services Branch'], ['SAMURDHI', 'Samurdhi Branch'], ['PENSION', 'Pension Branch'], ['ACCOUNTS', 'Accounts Branch'], ['DEV', 'Development Branch'], ['GN', 'Grama Niladhari Coordination Branch']] as [$code, $name]) {
                DB::table('branches')->insert(['code' => $code, 'name' => $name, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
            }

            $adminUserId = DB::table('users')->insertGetId([
                'department_id' => null,
                'branch_id' => null,
                'name' => 'System Administrator',
                'email' => 'admin@office.test',
                'phone' => '+94770000001',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $staffUserId = DB::table('users')->insertGetId([
                'department_id' => $generalDepartmentId,
                'branch_id' => $generalBranchId,
                'name' => 'Front Desk Staff',
                'email' => 'staff@office.test',
                'phone' => '+94770000002',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $officerUserId = DB::table('users')->insertGetId([
                'department_id' => $recordsDepartmentId,
                'branch_id' => $recordsBranchId,
                'name' => 'Records Officer',
                'email' => 'officer@office.test',
                'phone' => '+94770000003',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $branchHeadUserId = DB::table('users')->insertGetId([
                'department_id' => $recordsDepartmentId,
                'branch_id' => $recordsBranchId,
                'name' => 'Registration Branch Head',
                'email' => 'branchhead@office.test',
                'phone' => '+94770000005',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $managerUserId = DB::table('users')->insertGetId([
                'department_id' => null,
                'branch_id' => null,
                'name' => 'Office Manager',
                'email' => 'manager@office.test',
                'phone' => '+94770000004',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('role_user')->insert([
                ['role_id' => $adminRoleId, 'user_id' => $adminUserId, 'created_at' => $now, 'updated_at' => $now],
                ['role_id' => $staffRoleId, 'user_id' => $staffUserId, 'created_at' => $now, 'updated_at' => $now],
                ['role_id' => $officerRoleId, 'user_id' => $officerUserId, 'created_at' => $now, 'updated_at' => $now],
                ['role_id' => $branchHeadRoleId, 'user_id' => $branchHeadUserId, 'created_at' => $now, 'updated_at' => $now],
                ['role_id' => $managerRoleId, 'user_id' => $managerUserId, 'created_at' => $now, 'updated_at' => $now],
            ]);

            DB::table('branches')->where('id', $recordsBranchId)->update(['branch_head_user_id' => $branchHeadUserId]);

            $certificateServiceId = DB::table('services')->insertGetId([
                'department_id' => $recordsDepartmentId,
                'branch_id' => $recordsBranchId,
                'code' => 'CERT-ISSUE',
                'name' => 'Certificate Issuing Service',
                'description' => 'Issue official certificates after document verification.',
                'estimated_days' => 5,
                'requires_appointment' => true,
                'fee_amount' => 1500.00,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('application_statuses')->insert([
                ['code' => 'submitted', 'name' => 'Submitted', 'sort_order' => 10, 'is_terminal' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'pending', 'name' => 'Pending', 'sort_order' => 20, 'is_terminal' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'under_review', 'name' => 'Under Review', 'sort_order' => 30, 'is_terminal' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'processing', 'name' => 'Processing', 'sort_order' => 40, 'is_terminal' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'waiting_for_documents', 'name' => 'Waiting for Documents', 'sort_order' => 50, 'is_terminal' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'approved', 'name' => 'Approved', 'sort_order' => 60, 'is_terminal' => false, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'rejected', 'name' => 'Rejected', 'sort_order' => 70, 'is_terminal' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'completed', 'name' => 'Completed', 'sort_order' => 80, 'is_terminal' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
                ['code' => 'cancelled', 'name' => 'Cancelled', 'sort_order' => 90, 'is_terminal' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ]);

            $submittedStatusId = DB::table('application_statuses')->where('code', 'submitted')->value('id');

            $personId = DB::table('people')->insertGetId([
                'person_code' => 'PER-2026-000001',
                'qr_code_value' => 'OFFICE:PERSON:PER-2026-000001',
                'barcode_value' => 'PER2026000001',
                'first_name' => 'Ahamed',
                'last_name' => 'Nazeer',
                'full_name' => 'Ahamed Nazeer',
                'gender' => 'male',
                'date_of_birth' => '1995-04-12',
                'national_id' => '951234567V',
                'passport_no' => null,
                'phone' => '+94771234567',
                'email' => 'ahamed@example.test',
                'address_line_1' => 'No. 25, Main Street',
                'address_line_2' => null,
                'city' => 'Colombo',
                'state' => 'Western',
                'postal_code' => '00100',
                'country' => 'Sri Lanka',
                'registered_by' => $staffUserId,
                'registered_at' => $now,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $applicationId = DB::table('service_applications')->insertGetId([
                'application_no' => 'APP-2026-000001',
                'person_id' => $personId,
                'service_id' => $certificateServiceId,
                'department_id' => $recordsDepartmentId,
                'branch_id' => $recordsBranchId,
                'assigned_officer_id' => $officerUserId,
                'status_id' => $submittedStatusId,
                'submitted_by' => $staffUserId,
                'priority' => 'normal',
                'subject' => 'Certificate request',
                'description' => 'Applicant requested official certificate issuing service.',
                'due_date' => $now->copy()->addDays(5)->toDateString(),
                'submitted_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('application_status_histories')->insert([
                'application_id' => $applicationId,
                'department_id' => $recordsDepartmentId,
                'branch_id' => $recordsBranchId,
                'from_status_id' => null,
                'to_status_id' => $submittedStatusId,
                'changed_by' => $staffUserId,
                'remarks' => 'Application submitted at front desk.',
                'changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $identityDocTypeId = DB::table('document_types')->insertGetId([
                'code' => 'NIC',
                'name' => 'National Identity Card',
                'description' => 'Copy of national identity card.',
                'is_required' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('application_documents')->insert([
                'application_id' => $applicationId,
                'person_id' => $personId,
                'document_type_id' => $identityDocTypeId,
                'uploaded_by' => $staffUserId,
                'verified_by' => null,
                'file_name' => 'sample-nic.pdf',
                'file_path' => 'documents/APP-2026-000001/sample-nic.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 250000,
                'status' => 'uploaded',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('appointments')->insert([
                'appointment_no' => 'APT-2026-000001',
                'application_id' => $applicationId,
                'person_id' => $personId,
                'department_id' => $recordsDepartmentId,
                'branch_id' => $recordsBranchId,
                'officer_id' => $officerUserId,
                'created_by' => $staffUserId,
                'appointment_date' => $now->copy()->addDay()->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '10:30:00',
                'status' => 'scheduled',
                'purpose' => 'Document verification appointment.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('application_notes')->insert([
                'application_id' => $applicationId,
                'person_id' => $personId,
                'created_by' => $staffUserId,
                'visibility' => 'internal',
                'note' => 'Applicant was informed to bring original identity document.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('notifications')->insert([
                'user_id' => $officerUserId,
                'person_id' => null,
                'application_id' => $applicationId,
                'channel' => 'system',
                'title' => 'New application assigned',
                'message' => 'APP-2026-000001 has been assigned for review.',
                'sent_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('audit_logs')->insert([
                'user_id' => $staffUserId,
                'action' => 'created',
                'module' => 'service_applications',
                'auditable_type' => 'service_application',
                'auditable_id' => $applicationId,
                'old_values' => null,
                'new_values' => json_encode(['application_no' => 'APP-2026-000001', 'status' => 'Submitted']),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('system_settings')->insert([
                ['group' => 'identity', 'key' => 'person_code_prefix', 'value' => 'PER', 'type' => 'string', 'description' => 'Prefix for generated person IDs.', 'is_public' => false, 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'identity', 'key' => 'application_no_prefix', 'value' => 'APP', 'type' => 'string', 'description' => 'Prefix for generated application numbers.', 'is_public' => false, 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'office', 'key' => 'office_name', 'value' => 'Office Service Management System', 'type' => 'string', 'description' => 'Display name of the office.', 'is_public' => true, 'created_at' => $now, 'updated_at' => $now],
                ['group' => 'security', 'key' => 'session_timeout_minutes', 'value' => '30', 'type' => 'integer', 'description' => 'Default idle session timeout.', 'is_public' => false, 'created_at' => $now, 'updated_at' => $now],
            ]);
        });
    }
}
