CREATE DATABASE IF NOT EXISTS `Office_Service`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `Office_Service`;

CREATE TABLE `roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(80) NOT NULL,
  `description` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `departments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(30) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `email` VARCHAR(150) NULL,
  `description` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` BIGINT UNSIGNED NULL,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_department_id_foreign` (`department_id`),
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_user` (
  `role_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`role_id`, `user_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `people` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `person_code` VARCHAR(40) NOT NULL,
  `qr_code_value` VARCHAR(120) NOT NULL,
  `barcode_value` VARCHAR(120) NULL,
  `qr_code_path` VARCHAR(255) NULL,
  `barcode_path` VARCHAR(255) NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `full_name` VARCHAR(210) NOT NULL,
  `gender` ENUM('male','female','other','not_specified') NOT NULL DEFAULT 'not_specified',
  `date_of_birth` DATE NULL,
  `national_id` VARCHAR(80) NULL,
  `passport_no` VARCHAR(80) NULL,
  `phone` VARCHAR(30) NULL,
  `email` VARCHAR(150) NULL,
  `address_line_1` VARCHAR(180) NULL,
  `address_line_2` VARCHAR(180) NULL,
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(100) NULL,
  `postal_code` VARCHAR(20) NULL,
  `country` VARCHAR(100) NOT NULL DEFAULT 'Sri Lanka',
  `photo_path` VARCHAR(255) NULL,
  `registered_by` BIGINT UNSIGNED NULL,
  `registered_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `people_person_code_unique` (`person_code`),
  UNIQUE KEY `people_qr_code_value_unique` (`qr_code_value`),
  UNIQUE KEY `people_barcode_value_unique` (`barcode_value`),
  UNIQUE KEY `people_national_id_unique` (`national_id`),
  UNIQUE KEY `people_passport_no_unique` (`passport_no`),
  KEY `people_registered_by_foreign` (`registered_by`),
  KEY `people_full_name_phone_index` (`full_name`, `phone`),
  KEY `people_registered_at_index` (`registered_at`),
  CONSTRAINT `people_registered_by_foreign` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `services` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_id` BIGINT UNSIGNED NOT NULL,
  `code` VARCHAR(40) NOT NULL,
  `name` VARCHAR(180) NOT NULL,
  `description` TEXT NULL,
  `fee_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `estimated_days` SMALLINT UNSIGNED NULL,
  `requires_appointment` TINYINT(1) NOT NULL DEFAULT 0,
  `requires_payment` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `services_code_unique` (`code`),
  KEY `services_department_id_is_active_index` (`department_id`, `is_active`),
  CONSTRAINT `services_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `application_statuses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(80) NOT NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_terminal` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_statuses_code_unique` (`code`),
  UNIQUE KEY `application_statuses_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `service_applications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_no` VARCHAR(50) NOT NULL,
  `person_id` BIGINT UNSIGNED NOT NULL,
  `service_id` BIGINT UNSIGNED NOT NULL,
  `department_id` BIGINT UNSIGNED NOT NULL,
  `assigned_officer_id` BIGINT UNSIGNED NULL,
  `status_id` BIGINT UNSIGNED NOT NULL,
  `submitted_by` BIGINT UNSIGNED NULL,
  `priority` ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `subject` TEXT NULL,
  `description` LONGTEXT NULL,
  `total_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `due_date` DATE NULL,
  `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `rejected_at` TIMESTAMP NULL DEFAULT NULL,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  `cancelled_at` TIMESTAMP NULL DEFAULT NULL,
  `rejection_reason` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_applications_application_no_unique` (`application_no`),
  KEY `service_applications_person_id_status_id_index` (`person_id`, `status_id`),
  KEY `service_applications_department_id_status_id_index` (`department_id`, `status_id`),
  KEY `service_applications_assigned_officer_id_status_id_index` (`assigned_officer_id`, `status_id`),
  KEY `service_applications_service_id_foreign` (`service_id`),
  KEY `service_applications_status_id_foreign` (`status_id`),
  KEY `service_applications_submitted_by_foreign` (`submitted_by`),
  KEY `service_applications_submitted_at_index` (`submitted_at`),
  CONSTRAINT `service_applications_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `service_applications_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `service_applications_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `service_applications_assigned_officer_id_foreign` FOREIGN KEY (`assigned_officer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `service_applications_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `application_statuses` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `service_applications_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `application_status_histories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_id` BIGINT UNSIGNED NOT NULL,
  `from_status_id` BIGINT UNSIGNED NULL,
  `to_status_id` BIGINT UNSIGNED NOT NULL,
  `changed_by` BIGINT UNSIGNED NULL,
  `remarks` TEXT NULL,
  `changed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_status_histories_application_id_changed_at_index` (`application_id`, `changed_at`),
  KEY `application_status_histories_from_status_id_foreign` (`from_status_id`),
  KEY `application_status_histories_to_status_id_foreign` (`to_status_id`),
  KEY `application_status_histories_changed_by_foreign` (`changed_by`),
  CONSTRAINT `application_status_histories_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `service_applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_status_histories_from_status_id_foreign` FOREIGN KEY (`from_status_id`) REFERENCES `application_statuses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_status_histories_to_status_id_foreign` FOREIGN KEY (`to_status_id`) REFERENCES `application_statuses` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `application_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `document_types` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `is_required` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document_types_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `application_documents` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_id` BIGINT UNSIGNED NOT NULL,
  `person_id` BIGINT UNSIGNED NOT NULL,
  `document_type_id` BIGINT UNSIGNED NOT NULL,
  `uploaded_by` BIGINT UNSIGNED NULL,
  `verified_by` BIGINT UNSIGNED NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(120) NULL,
  `file_size` BIGINT UNSIGNED NULL,
  `status` ENUM('uploaded','verified','rejected') NOT NULL DEFAULT 'uploaded',
  `verification_remarks` TEXT NULL,
  `verified_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_documents_application_id_status_index` (`application_id`, `status`),
  KEY `application_documents_person_id_document_type_id_index` (`person_id`, `document_type_id`),
  KEY `application_documents_document_type_id_foreign` (`document_type_id`),
  KEY `application_documents_uploaded_by_foreign` (`uploaded_by`),
  KEY `application_documents_verified_by_foreign` (`verified_by`),
  CONSTRAINT `application_documents_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `service_applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_documents_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `application_documents_document_type_id_foreign` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `application_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_documents_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payment_methods` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_methods_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `receipt_no` VARCHAR(50) NOT NULL,
  `application_id` BIGINT UNSIGNED NOT NULL,
  `person_id` BIGINT UNSIGNED NOT NULL,
  `payment_method_id` BIGINT UNSIGNED NOT NULL,
  `received_by` BIGINT UNSIGNED NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending','paid','failed','refunded','cancelled') NOT NULL DEFAULT 'pending',
  `transaction_reference` VARCHAR(120) NULL,
  `remarks` TEXT NULL,
  `paid_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_receipt_no_unique` (`receipt_no`),
  KEY `payments_application_id_status_index` (`application_id`, `status`),
  KEY `payments_person_id_paid_at_index` (`person_id`, `paid_at`),
  KEY `payments_payment_method_id_foreign` (`payment_method_id`),
  KEY `payments_received_by_foreign` (`received_by`),
  CONSTRAINT `payments_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `service_applications` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `payments_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `payments_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `appointments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `appointment_no` VARCHAR(50) NOT NULL,
  `application_id` BIGINT UNSIGNED NULL,
  `person_id` BIGINT UNSIGNED NOT NULL,
  `department_id` BIGINT UNSIGNED NOT NULL,
  `officer_id` BIGINT UNSIGNED NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `appointment_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NULL,
  `status` ENUM('scheduled','rescheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled',
  `purpose` TEXT NULL,
  `remarks` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appointments_appointment_no_unique` (`appointment_no`),
  KEY `appointments_department_id_appointment_date_index` (`department_id`, `appointment_date`),
  KEY `appointments_officer_id_appointment_date_index` (`officer_id`, `appointment_date`),
  KEY `appointments_person_id_appointment_date_index` (`person_id`, `appointment_date`),
  KEY `appointments_application_id_foreign` (`application_id`),
  KEY `appointments_created_by_foreign` (`created_by`),
  CONSTRAINT `appointments_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `service_applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `appointments_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `appointments_officer_id_foreign` FOREIGN KEY (`officer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `application_notes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `application_id` BIGINT UNSIGNED NOT NULL,
  `person_id` BIGINT UNSIGNED NULL,
  `created_by` BIGINT UNSIGNED NULL,
  `visibility` ENUM('internal','manager','public') NOT NULL DEFAULT 'internal',
  `note` TEXT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_notes_application_id_visibility_index` (`application_id`, `visibility`),
  KEY `application_notes_person_id_foreign` (`person_id`),
  KEY `application_notes_created_by_foreign` (`created_by`),
  CONSTRAINT `application_notes_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `service_applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_notes_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_notes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL,
  `person_id` BIGINT UNSIGNED NULL,
  `application_id` BIGINT UNSIGNED NULL,
  `channel` ENUM('system','email','sms') NOT NULL DEFAULT 'system',
  `title` VARCHAR(180) NOT NULL,
  `message` TEXT NOT NULL,
  `data` JSON NULL,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `sent_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_read_at_index` (`user_id`, `read_at`),
  KEY `notifications_person_id_read_at_index` (`person_id`, `read_at`),
  KEY `notifications_application_id_foreign` (`application_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notifications_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `service_applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(100) NOT NULL,
  `auditable_type` VARCHAR(150) NULL,
  `auditable_id` BIGINT UNSIGNED NULL,
  `old_values` JSON NULL,
  `new_values` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(500) NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_module_action_index` (`module`, `action`),
  KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`, `auditable_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` VARCHAR(80) NOT NULL DEFAULT 'general',
  `key` VARCHAR(120) NOT NULL,
  `value` LONGTEXT NULL,
  `type` ENUM('string','integer','boolean','decimal','json') NOT NULL DEFAULT 'string',
  `description` TEXT NULL,
  `is_public` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`),
  KEY `system_settings_group_index` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

