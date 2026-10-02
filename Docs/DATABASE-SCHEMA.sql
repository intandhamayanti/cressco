-- ============================================================
-- CRESSCO - DATABASE SCHEMA
-- ============================================================
-- Product  : Cressco
-- Scope    : Bimbel Core MVP
-- Database : MySQL 8.0+
-- Framework: Laravel
--
-- Source documents:
--   PRD.md
--   USER-FLOWS.md
--   ERD.md
--   BUSINESS-RULES.md
--   PERMISSION-MATRIX.md
--
-- IMPORTANT:
-- This file is a database BLUEPRINT/reference.
-- Laravel migrations remain the implementation source of truth.
--
-- Authorization is NOT implemented only through this schema.
-- Laravel Policies/Gates + scoped queries must enforce:
--   Role + Tenant + Branch/Teaching Scope + Resource Rules.
--
-- UUID strategy:
--   CHAR(36) is used for readability and Laravel compatibility.
--   Application code should generate UUIDs.
--
-- MySQL:
--   Requires MySQL 8.0+.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ============================================================
-- 0. DATABASE CONVENTIONS
-- ============================================================
--
-- Tenant boundary:
--   tenants
--     └── branches
--     └── users
--     └── operational resources
--
-- Branch boundary:
--   branch-scoped operational resources carry both:
--     tenant_id
--     branch_id
--
-- Owner:
--   full tenant scope
--
-- Admin:
--   explicit branch access through branch_user
--
-- Tutor:
--   teaching scope derived from tutor_assignments
--
-- Super Admin:
--   tenant_id may be NULL and is platform-scoped.
--
-- Soft deletion:
--   Historical/financial/attendance records are not hard-deleted
--   by business rules. Status/deactivation is preferred.
--
-- ============================================================


-- ============================================================
-- 1. TENANTS
-- ============================================================

CREATE TABLE tenants (
    id CHAR(36) NOT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    status ENUM('active', 'suspended', 'inactive') NOT NULL DEFAULT 'active',
    logo VARCHAR(500) NULL,
    description TEXT NULL,
    address TEXT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(255) NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_tenants_slug (slug),
    KEY idx_tenants_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 2. BRANCHES
-- ============================================================

CREATE TABLE branches (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NULL,
    address TEXT NULL,
    phone VARCHAR(50) NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_branches_tenant_id (id, tenant_id),
    UNIQUE KEY uq_branches_tenant_code (tenant_id, code),
    KEY idx_branches_tenant_status (tenant_id, status),

    CONSTRAINT fk_branches_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 3. USERS
-- ============================================================
-- One human = one account/email.
-- Role is stored in DB, never inferred from email.
--
-- super_admin:
--   tenant_id = NULL
--
-- owner/admin/tutor:
--   tenant_id = owning tenant
--
-- Branch access is NOT stored directly here.
-- Admin branch access is represented by branch_user.
-- Tutor teaching scope is derived from tutor_assignments.
-- ============================================================

CREATE TABLE users (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('owner', 'admin', 'tutor', 'super_admin') NOT NULL,
    status ENUM('active', 'inactive', 'invited') NOT NULL DEFAULT 'invited',

    remember_token VARCHAR(100) NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_id_tenant (id, tenant_id),
    KEY idx_users_tenant_role (tenant_id, role),
    KEY idx_users_tenant_status (tenant_id, status),

    CONSTRAINT fk_users_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 4. BRANCH USER ACCESS
-- ============================================================
-- Many-to-many:
--
-- Admin/User
--    ↕
-- branch_user
--    ↕
-- Branch
--
-- Owner does not need explicit branch_user rows for access;
-- Owner has tenant-wide scope.
--
-- Tutor branch access is derived through class assignment,
-- not generic branch_user access.
-- ============================================================

CREATE TABLE branch_user (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,
    user_id CHAR(36) NOT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_branch_user_user_branch (user_id, branch_id),
    UNIQUE KEY uq_branch_user_id_tenant (id, tenant_id),
    KEY idx_branch_user_tenant_branch (tenant_id, branch_id),
    KEY idx_branch_user_tenant_user (tenant_id, user_id),

    CONSTRAINT fk_branch_user_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_branch_user_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_branch_user_user_tenant
        FOREIGN KEY (user_id, tenant_id)
        REFERENCES users (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 5. STUDENTS
-- ============================================================

CREATE TABLE students (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,

    name VARCHAR(150) NOT NULL,
    date_of_birth DATE NULL,
    gender VARCHAR(30) NULL,
    phone VARCHAR(50) NULL,
    address TEXT NULL,
    parent_name VARCHAR(150) NULL,
    parent_phone VARCHAR(50) NULL,
    notes TEXT NULL,
    joined_at DATE NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_students_id_tenant (id, tenant_id),
    KEY idx_students_tenant_branch_status (tenant_id, branch_id, status),
    KEY idx_students_parent_phone (tenant_id, parent_phone),
    KEY idx_students_name (tenant_id, name),

    CONSTRAINT fk_students_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_students_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 6. CLASSES
-- ============================================================

CREATE TABLE classes (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,

    name VARCHAR(150) NOT NULL,
    subject VARCHAR(150) NULL,
    level VARCHAR(100) NULL,
    capacity INT UNSIGNED NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_classes_id_tenant (id, tenant_id),
    KEY idx_classes_tenant_branch_status (tenant_id, branch_id, status),
    KEY idx_classes_tenant_name (tenant_id, name),

    CONSTRAINT fk_classes_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_classes_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_classes_capacity
        CHECK (capacity IS NULL OR capacity > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 7. ENROLLMENTS
-- ============================================================
-- Student ↔ Class is many-to-many through enrollment.
--
-- A student may have multiple active enrollments.
-- Historical enrollment is preserved.
-- ============================================================

CREATE TABLE enrollments (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,

    student_id CHAR(36) NOT NULL,
    class_id CHAR(36) NOT NULL,

    started_at DATE NOT NULL,
    ended_at DATE NULL,
    status ENUM('active', 'completed', 'withdrawn') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_enrollments_id_tenant (id, tenant_id),
    KEY idx_enrollments_tenant_branch_status (tenant_id, branch_id, status),
    KEY idx_enrollments_student_status (tenant_id, student_id, status),
    KEY idx_enrollments_class_status (tenant_id, class_id, status),

    CONSTRAINT fk_enrollments_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_enrollments_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_enrollments_student_tenant
        FOREIGN KEY (student_id, tenant_id)
        REFERENCES students (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_enrollments_class_tenant
        FOREIGN KEY (class_id, tenant_id)
        REFERENCES classes (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_enrollments_dates
        CHECK (ended_at IS NULL OR ended_at >= started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 8. TUTOR ASSIGNMENTS
-- ============================================================
-- Tutor ↔ Class is many-to-many.
-- A tutor can teach across multiple branches.
-- ============================================================

CREATE TABLE tutor_assignments (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,

    tutor_id CHAR(36) NOT NULL,
    class_id CHAR(36) NOT NULL,

    started_at DATE NULL,
    ended_at DATE NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_tutor_assignments_id_tenant (id, tenant_id),
    KEY idx_tutor_assignments_tenant_branch_status (tenant_id, branch_id, status),
    KEY idx_tutor_assignments_tutor_status (tenant_id, tutor_id, status),
    KEY idx_tutor_assignments_class_status (tenant_id, class_id, status),

    CONSTRAINT fk_tutor_assignments_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tutor_assignments_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tutor_assignments_tutor_tenant
        FOREIGN KEY (tutor_id, tenant_id)
        REFERENCES users (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tutor_assignments_class_tenant
        FOREIGN KEY (class_id, tenant_id)
        REFERENCES classes (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_tutor_assignments_dates
        CHECK (ended_at IS NULL OR started_at IS NULL OR ended_at >= started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 9. RECURRING SCHEDULES
-- ============================================================
-- Schedule is a recurring rule.
-- It is NOT a concrete teaching event.
--
-- Example:
--   Monday 19:00-21:00
--
-- Concrete dates are represented by teaching_sessions.
-- ============================================================

CREATE TABLE schedules (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,

    class_id CHAR(36) NOT NULL,
    scheduled_tutor_id CHAR(36) NOT NULL,

    day_of_week TINYINT UNSIGNED NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(150) NULL,

    starts_on DATE NOT NULL,
    ends_on DATE NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_schedules_id_tenant (id, tenant_id),
    KEY idx_schedules_tenant_branch_status (tenant_id, branch_id, status),
    KEY idx_schedules_class_status (tenant_id, class_id, status),
    KEY idx_schedules_tutor_status (tenant_id, scheduled_tutor_id, status),

    CONSTRAINT fk_schedules_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_schedules_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_schedules_class_tenant
        FOREIGN KEY (class_id, tenant_id)
        REFERENCES classes (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_schedules_tutor_tenant
        FOREIGN KEY (scheduled_tutor_id, tenant_id)
        REFERENCES users (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_schedules_day_of_week
        CHECK (day_of_week BETWEEN 0 AND 6),

    CONSTRAINT chk_schedules_time
        CHECK (end_time > start_time),

    CONSTRAINT chk_schedules_dates
        CHECK (ends_on IS NULL OR ends_on >= starts_on)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 10. TEACHING SESSIONS
-- ============================================================
-- Concrete teaching event.
--
-- scheduled_tutor_id:
--   tutor originally assigned by recurring schedule
--
-- actual_tutor_id:
--   tutor who actually taught the session
--
-- Normal:
--   scheduled_tutor_id = actual_tutor_id
--
-- Replacement:
--   scheduled_tutor_id != actual_tutor_id
--
-- One-off schedule changes are stored here.
-- No schedule_exceptions table in MVP.
-- ============================================================

CREATE TABLE teaching_sessions (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,

    schedule_id CHAR(36) NULL,
    class_id CHAR(36) NOT NULL,

    scheduled_tutor_id CHAR(36) NOT NULL,
    actual_tutor_id CHAR(36) NULL,

    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(150) NULL,

    status ENUM('scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',

    material TEXT NULL,
    notes TEXT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_teaching_sessions_id_tenant (id, tenant_id),
    UNIQUE KEY uq_teaching_sessions_schedule_date (schedule_id, session_date),
    KEY idx_teaching_sessions_tenant_branch_date (tenant_id, branch_id, session_date),
    KEY idx_teaching_sessions_class_date (tenant_id, class_id, session_date),
    KEY idx_teaching_sessions_scheduled_tutor_date (tenant_id, scheduled_tutor_id, session_date),
    KEY idx_teaching_sessions_actual_tutor_date (tenant_id, actual_tutor_id, session_date),
    KEY idx_teaching_sessions_status_date (tenant_id, status, session_date),

    CONSTRAINT fk_teaching_sessions_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_teaching_sessions_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_teaching_sessions_schedule_tenant
        FOREIGN KEY (schedule_id, tenant_id)
        REFERENCES schedules (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_teaching_sessions_class_tenant
        FOREIGN KEY (class_id, tenant_id)
        REFERENCES classes (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_teaching_sessions_scheduled_tutor_tenant
        FOREIGN KEY (scheduled_tutor_id, tenant_id)
        REFERENCES users (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_teaching_sessions_actual_tutor_tenant
        FOREIGN KEY (actual_tutor_id, tenant_id)
        REFERENCES users (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_teaching_sessions_time
        CHECK (end_time > start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 11. STUDENT ATTENDANCES
-- ============================================================
-- One attendance per student per teaching session.
-- Status:
--   hadir
--   izin
--   sakit
--   alpa
-- ============================================================

CREATE TABLE student_attendances (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,

    teaching_session_id CHAR(36) NOT NULL,
    student_id CHAR(36) NOT NULL,

    status ENUM('hadir', 'izin', 'sakit', 'alpa') NOT NULL,
    note TEXT NULL,

    recorded_at DATETIME NOT NULL,
    recorded_by CHAR(36) NOT NULL,

    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_student_attendance_session_student (teaching_session_id, student_id),
    UNIQUE KEY uq_student_attendance_id_tenant (id, tenant_id),
    KEY idx_student_attendance_tenant_branch (tenant_id, branch_id),
    KEY idx_student_attendance_session (tenant_id, teaching_session_id),
    KEY idx_student_attendance_student (tenant_id, student_id),

    CONSTRAINT fk_student_attendance_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_student_attendance_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_student_attendance_session_tenant
        FOREIGN KEY (teaching_session_id, tenant_id)
        REFERENCES teaching_sessions (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_student_attendance_student_tenant
        FOREIGN KEY (student_id, tenant_id)
        REFERENCES students (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_student_attendance_recorded_by
        FOREIGN KEY (recorded_by)
        REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 12. TUTOR ATTENDANCES
-- ============================================================
-- One tutor attendance record per teaching session.
-- Created/updated as a consequence of successful student
-- attendance submission.
--
-- No GPS, selfie, check-in, or check-out in MVP.
-- ============================================================

CREATE TABLE tutor_attendances (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,

    teaching_session_id CHAR(36) NOT NULL,
    tutor_id CHAR(36) NOT NULL,

    status ENUM('present', 'absent') NOT NULL DEFAULT 'present',
    recorded_at DATETIME NOT NULL,
    source VARCHAR(100) NOT NULL DEFAULT 'student_attendance_submission',

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_tutor_attendance_session (teaching_session_id),
    UNIQUE KEY uq_tutor_attendance_id_tenant (id, tenant_id),
    KEY idx_tutor_attendance_tenant_branch (tenant_id, branch_id),
    KEY idx_tutor_attendance_tutor (tenant_id, tutor_id),

    CONSTRAINT fk_tutor_attendance_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tutor_attendance_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tutor_attendance_session_tenant
        FOREIGN KEY (teaching_session_id, tenant_id)
        REFERENCES teaching_sessions (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tutor_attendance_tutor_tenant
        FOREIGN KEY (tutor_id, tenant_id)
        REFERENCES users (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 13. ASSESSMENTS
-- ============================================================

CREATE TABLE assessments (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,

    class_id CHAR(36) NOT NULL,
    name VARCHAR(200) NOT NULL,
    type ENUM('tugas', 'quiz', 'ujian') NOT NULL,
    material TEXT NULL,
    assessment_date DATE NOT NULL,
    max_score DECIMAL(8,2) NOT NULL,
    notes TEXT NULL,

    created_by CHAR(36) NOT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_assessments_id_tenant (id, tenant_id),
    KEY idx_assessments_tenant_branch_date (tenant_id, branch_id, assessment_date),
    KEY idx_assessments_class_date (tenant_id, class_id, assessment_date),

    CONSTRAINT fk_assessments_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_assessments_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_assessments_class_tenant
        FOREIGN KEY (class_id, tenant_id)
        REFERENCES classes (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_assessments_created_by
        FOREIGN KEY (created_by)
        REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_assessments_max_score
        CHECK (max_score > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 14. ASSESSMENT RESULTS
-- ============================================================

CREATE TABLE assessment_results (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,

    assessment_id CHAR(36) NOT NULL,
    student_id CHAR(36) NOT NULL,

    score DECIMAL(8,2) NOT NULL,
    notes TEXT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_assessment_results_assessment_student (assessment_id, student_id),
    UNIQUE KEY uq_assessment_results_id_tenant (id, tenant_id),
    KEY idx_assessment_results_tenant_branch (tenant_id, branch_id),
    KEY idx_assessment_results_assessment (tenant_id, assessment_id),
    KEY idx_assessment_results_student (tenant_id, student_id),

    CONSTRAINT fk_assessment_results_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_assessment_results_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_assessment_results_assessment_tenant
        FOREIGN KEY (assessment_id, tenant_id)
        REFERENCES assessments (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_assessment_results_student_tenant
        FOREIGN KEY (student_id, tenant_id)
        REFERENCES students (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_assessment_results_score
        CHECK (score >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 15. PAYMENTS
-- ============================================================
-- MVP:
--   manual payment recording
--   optional verification status
--   no payment gateway
--
-- enrollment_id is nullable because the ERD allows payment
-- records that are not tied to a single enrollment.
-- For parent reminder logic, enrollment linkage should be used
-- whenever the payment is class-specific.
-- ============================================================

CREATE TABLE payments (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,

    student_id CHAR(36) NOT NULL,
    enrollment_id CHAR(36) NULL,

    period VARCHAR(30) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    due_date DATE NOT NULL,
    paid_at DATETIME NULL,

    status ENUM(
        'belum_bayar',
        'menunggu_verifikasi',
        'lunas',
        'terlambat'
    ) NOT NULL DEFAULT 'belum_bayar',

    notes TEXT NULL,
    recorded_by CHAR(36) NOT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_payments_id_tenant (id, tenant_id),
    KEY idx_payments_tenant_branch_status (tenant_id, branch_id, status),
    KEY idx_payments_student_period (tenant_id, student_id, period),
    KEY idx_payments_enrollment_period (tenant_id, enrollment_id, period),
    KEY idx_payments_due_date (tenant_id, due_date),
    KEY idx_payments_paid_at (tenant_id, paid_at),

    CONSTRAINT fk_payments_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_payments_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_payments_student_tenant
        FOREIGN KEY (student_id, tenant_id)
        REFERENCES students (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_payments_enrollment_tenant
        FOREIGN KEY (enrollment_id, tenant_id)
        REFERENCES enrollments (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_payments_recorded_by
        FOREIGN KEY (recorded_by)
        REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_payments_amount
        CHECK (amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 16. HONOR SCHEMES
-- ============================================================
-- Methods:
--   per_session
--   per_student
--   revenue_share
--   fixed_monthly
--
-- A tenant has a default scheme.
-- Tutor-specific overrides are represented by honor_assignments.
-- ============================================================

CREATE TABLE honor_schemes (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,

    name VARCHAR(150) NOT NULL,
    method ENUM(
        'per_session',
        'per_student',
        'revenue_share',
        'fixed_monthly'
    ) NOT NULL,

    rate DECIMAL(15,2) NULL,
    percentage DECIMAL(5,2) NULL,
    fixed_amount DECIMAL(15,2) NULL,

    effective_from DATE NOT NULL,
    effective_until DATE NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',

    created_by CHAR(36) NOT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_honor_schemes_id_tenant (id, tenant_id),
    KEY idx_honor_schemes_tenant_status (tenant_id, status),
    KEY idx_honor_schemes_effective (tenant_id, effective_from, effective_until),

    CONSTRAINT fk_honor_schemes_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_honor_schemes_created_by
        FOREIGN KEY (created_by)
        REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_honor_schemes_dates
        CHECK (effective_until IS NULL OR effective_until >= effective_from),

    CONSTRAINT chk_honor_schemes_percentage
        CHECK (percentage IS NULL OR (percentage >= 0 AND percentage <= 100)),

    CONSTRAINT chk_honor_schemes_rate
        CHECK (rate IS NULL OR rate >= 0),

    CONSTRAINT chk_honor_schemes_fixed_amount
        CHECK (fixed_amount IS NULL OR fixed_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 17. HONOR ASSIGNMENTS
-- ============================================================
-- assignment_type:
--   default
--   tutor_override
--
-- default:
--   tutor_id should be NULL
--
-- tutor_override:
--   tutor_id is required
--
-- Tenant default + tutor override provides the MVP hybrid model.
-- ============================================================

CREATE TABLE honor_assignments (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,

    tutor_id CHAR(36) NULL,
    honor_scheme_id CHAR(36) NOT NULL,

    assignment_type ENUM('default', 'tutor_override') NOT NULL,

    effective_from DATE NOT NULL,
    effective_until DATE NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_honor_assignments_id_tenant (id, tenant_id),
    KEY idx_honor_assignments_tenant_type (tenant_id, assignment_type),
    KEY idx_honor_assignments_tutor (tenant_id, tutor_id),
    KEY idx_honor_assignments_scheme (tenant_id, honor_scheme_id),
    KEY idx_honor_assignments_effective (tenant_id, effective_from, effective_until),

    CONSTRAINT fk_honor_assignments_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_honor_assignments_tutor_tenant
        FOREIGN KEY (tutor_id, tenant_id)
        REFERENCES users (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_honor_assignments_scheme_tenant
        FOREIGN KEY (honor_scheme_id, tenant_id)
        REFERENCES honor_schemes (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_honor_assignments_dates
        CHECK (effective_until IS NULL OR effective_until >= effective_from),

    CONSTRAINT chk_honor_assignments_type_tutor
        CHECK (
            (assignment_type = 'default' AND tutor_id IS NULL)
            OR
            (assignment_type = 'tutor_override' AND tutor_id IS NOT NULL)
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 18. HONOR CALCULATIONS
-- ============================================================
-- Calculation is generated operationally by Admin.
-- Owner has oversight.
--
-- branch_id:
--   nullable because some schemes/calculations may be tenant-level.
--
-- method:
--   snapshot of the scheme method at calculation time.
--
-- adjustment_amount:
--   manual adjustment; reason is required by business rules.
-- ============================================================

CREATE TABLE honor_calculations (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NULL,

    tutor_id CHAR(36) NOT NULL,
    honor_scheme_id CHAR(36) NOT NULL,

    period_start DATE NOT NULL,
    period_end DATE NOT NULL,

    method ENUM(
        'per_session',
        'per_student',
        'revenue_share',
        'fixed_monthly'
    ) NOT NULL,

    base_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    adjustment_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    final_amount DECIMAL(15,2) NOT NULL DEFAULT 0,

    status ENUM('draft', 'final', 'paid') NOT NULL DEFAULT 'draft',

    adjustment_reason TEXT NULL,

    finalized_at DATETIME NULL,
    paid_at DATETIME NULL,

    calculated_by CHAR(36) NOT NULL,
    finalized_by CHAR(36) NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_honor_calculations_id_tenant (id, tenant_id),
    KEY idx_honor_calculations_tenant_branch_period
        (tenant_id, branch_id, period_start, period_end),
    KEY idx_honor_calculations_tutor_period
        (tenant_id, tutor_id, period_start, period_end),
    KEY idx_honor_calculations_status
        (tenant_id, status),

    CONSTRAINT fk_honor_calculations_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_honor_calculations_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_honor_calculations_tutor_tenant
        FOREIGN KEY (tutor_id, tenant_id)
        REFERENCES users (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_honor_calculations_scheme_tenant
        FOREIGN KEY (honor_scheme_id, tenant_id)
        REFERENCES honor_schemes (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_honor_calculations_calculated_by
        FOREIGN KEY (calculated_by)
        REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_honor_calculations_finalized_by
        FOREIGN KEY (finalized_by)
        REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_honor_calculations_period
        CHECK (period_end >= period_start),

    CONSTRAINT chk_honor_calculations_amounts
        CHECK (
            base_amount >= 0
            AND final_amount >= 0
        ),

    CONSTRAINT chk_honor_calculations_adjustment_reason
        CHECK (
            adjustment_amount = 0
            OR adjustment_reason IS NOT NULL
        )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 19. TUTOR REPLACEMENTS
-- ============================================================
-- Replacement is an operational change to one concrete session.
-- No formal request/approval workflow in MVP.
--
-- scheduled_tutor_id:
--   original scheduled tutor
--
-- previous_actual_tutor_id:
--   actual tutor before this replacement record
--
-- replacement_tutor_id:
--   new actual tutor
-- ============================================================

CREATE TABLE tutor_replacements (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,
    branch_id CHAR(36) NOT NULL,

    teaching_session_id CHAR(36) NOT NULL,
    scheduled_tutor_id CHAR(36) NOT NULL,
    previous_actual_tutor_id CHAR(36) NULL,
    replacement_tutor_id CHAR(36) NOT NULL,

    reason TEXT NOT NULL,
    changed_by CHAR(36) NOT NULL,
    changed_at DATETIME NOT NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_tutor_replacements_id_tenant (id, tenant_id),
    KEY idx_tutor_replacements_session (tenant_id, teaching_session_id),
    KEY idx_tutor_replacements_branch (tenant_id, branch_id),
    KEY idx_tutor_replacements_changed_at (tenant_id, changed_at),

    CONSTRAINT fk_tutor_replacements_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tutor_replacements_branch_tenant
        FOREIGN KEY (branch_id, tenant_id)
        REFERENCES branches (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tutor_replacements_session_tenant
        FOREIGN KEY (teaching_session_id, tenant_id)
        REFERENCES teaching_sessions (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tutor_replacements_scheduled_tutor
        FOREIGN KEY (scheduled_tutor_id, tenant_id)
        REFERENCES users (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tutor_replacements_previous_actual_tutor
        FOREIGN KEY (previous_actual_tutor_id, tenant_id)
        REFERENCES users (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tutor_replacements_replacement_tutor
        FOREIGN KEY (replacement_tutor_id, tenant_id)
        REFERENCES users (id, tenant_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_tutor_replacements_changed_by
        FOREIGN KEY (changed_by)
        REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 20. TENANT SETTINGS
-- ============================================================
-- Generic tenant-level configuration.
--
-- Examples:
--   payment_due_day = 5
--   default_honor_scheme_id = <UUID>
--   etc.
--
-- Value is JSON to support typed settings without requiring a
-- new table for every simple configuration.
-- ============================================================

CREATE TABLE tenant_settings (
    id CHAR(36) NOT NULL,
    tenant_id CHAR(36) NOT NULL,

    `key` VARCHAR(100) NOT NULL,
    `value` JSON NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_tenant_settings_tenant_key (tenant_id, `key`),
    UNIQUE KEY uq_tenant_settings_id_tenant (id, tenant_id),

    CONSTRAINT fk_tenant_settings_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 21. AUDIT LOGS
-- ============================================================
-- Audit is required for important operational/financial changes.
--
-- tenant_id may be NULL for platform-level actions.
-- actor_user_id may be NULL for system-generated actions.
--
-- Support Mode Phase 2 can use metadata:
-- {
--   "support_mode": true,
--   "target_tenant_id": "...",
--   ...
-- }
-- ============================================================

CREATE TABLE audit_logs (
    id CHAR(36) NOT NULL,

    tenant_id CHAR(36) NULL,
    actor_user_id CHAR(36) NULL,

    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(150) NOT NULL,
    entity_id CHAR(36) NULL,

    old_values JSON NULL,
    new_values JSON NULL,
    metadata JSON NULL,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_audit_logs_tenant_created (tenant_id, created_at),
    KEY idx_audit_logs_actor_created (actor_user_id, created_at),
    KEY idx_audit_logs_entity (entity_type, entity_id),
    KEY idx_audit_logs_action (action),

    CONSTRAINT fk_audit_logs_tenant
        FOREIGN KEY (tenant_id)
        REFERENCES tenants (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL,

    CONSTRAINT fk_audit_logs_actor
        FOREIGN KEY (actor_user_id)
        REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 22. LARAVEL AUTH SUPPORT TABLES
-- ============================================================
-- These are framework-support tables rather than Cressco domain
-- entities. They can be created through Laravel's standard
-- authentication migrations.
--
-- Included here as a reference because Cressco uses Laravel Breeze.
-- ============================================================

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,

    PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL,
    user_id CHAR(36) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,

    PRIMARY KEY (id),
    KEY idx_sessions_user_id (user_id),
    KEY idx_sessions_last_activity (last_activity),

    CONSTRAINT fk_sessions_user
        FOREIGN KEY (user_id)
        REFERENCES users (id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 23. OPTIONAL FUTURE / P1-P2 TABLES
-- ============================================================
--
-- NOT CREATED IN MVP:
--
-- schedule_exceptions
--   Only needed if session overrides become too complex.
--
-- honor_calculation_items
--   Recommended when line-item auditability is required.
--
-- notification_logs
--   Future notification system.
--
-- whatsapp_messages
--   Future WhatsApp API integration.
--
-- subscriptions
--   Super Admin / SaaS subscription management.
--
-- custom_permissions
--   Future granular RBAC.
--
-- Do not add these tables to MVP migrations until their
-- requirements are approved.
-- ============================================================


-- ============================================================
-- 24. DATA INTEGRITY NOTES
-- ============================================================
--
-- 1. Cross-tenant relationships:
--    Composite foreign keys use (resource_id, tenant_id) where
--    practical to prevent accidental cross-tenant references.
--
-- 2. Branch consistency:
--    branch_id + tenant_id composite foreign keys prevent a
--    resource from referencing a branch belonging to another
--    tenant.
--
-- 3. Tutor type:
--    The schema stores user.role, but database CHECK constraints
--    do not fully guarantee that referenced users have role=tutor.
--    Laravel Policies/Services must enforce role semantics.
--
-- 4. Owner/Admin branch scope:
--    branch_user represents explicit branch access.
--    Owner access is derived from role + tenant.
--
-- 5. Tutor teaching scope:
--    Tutor access is derived from tutor_assignments and the
--    relevant class/session relationships.
--
-- 6. Historical records:
--    Prefer status changes/deactivation over hard deletion.
--
-- 7. Schedule generation:
--    Session generation should be idempotent.
--    uq_teaching_sessions_schedule_date supports this for
--    sessions generated from recurring schedules.
--
-- 8. Replacement:
--    Changing actual_tutor_id must not change the recurring
--    schedule's scheduled_tutor_id.
--
-- 9. Honor:
--    Honor calculation stores a snapshot of method and amount.
--    Later changes to honor policy must not rewrite historical
--    calculations.
--
-- 10. Payment:
--     No payment gateway dependency exists in MVP.
--
-- 11. Parent reminder:
--     Reminder text is generated from operational data.
--     No WhatsApp API table is required for MVP.
--
-- 12. Tenant subdomain:
--     Tenant slug is stored here.
--     Actual wildcard DNS and hostname routing are application /
--     infrastructure concerns, not database records.
--
-- ============================================================


-- ============================================================
-- 25. SETTINGS RECOMMENDATIONS
-- ============================================================
--
-- Recommended tenant_settings keys:
--
-- payment_due_day
-- default_honor_scheme_id
-- timezone
-- currency
--
-- Example:
--
-- INSERT INTO tenant_settings (
--     id, tenant_id, `key`, `value`
-- ) VALUES (
--     UUID(),
--     '<tenant-uuid>',
--     'payment_due_day',
--     JSON_OBJECT('day', 5)
-- );
--
-- ============================================================


-- ============================================================
-- 26. MVP ENTITY SUMMARY
-- ============================================================
--
-- tenants
-- branches
-- users
-- branch_user
-- students
-- classes
-- enrollments
-- tutor_assignments
-- schedules
-- teaching_sessions
-- student_attendances
-- tutor_attendances
-- assessments
-- assessment_results
-- payments
-- honor_schemes
-- honor_assignments
-- honor_calculations
-- tutor_replacements
-- tenant_settings
-- audit_logs
--
-- Laravel auth:
-- password_reset_tokens
-- sessions
--
-- ============================================================
-- END OF DATABASE SCHEMA
-- ============================================================
