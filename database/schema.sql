-- =====================================================================
--  Aesthetica — Clinic Management System
--  Schema for aesthetic / injectable clinics (botox, fillers, threads)
--  MySQL 8 / MariaDB 10.4+   ·   InnoDB   ·   utf8mb4
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
--  ACCESS CONTROL
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
  id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug        VARCHAR(40)  NOT NULL,
  name        VARCHAR(80)  NOT NULL,
  description VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS permissions;
CREATE TABLE permissions (
  id    SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug  VARCHAR(60) NOT NULL,
  area  VARCHAR(40) NOT NULL,
  label VARCHAR(120) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permissions_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS role_permissions;
CREATE TABLE role_permissions (
  role_id       TINYINT UNSIGNED NOT NULL,
  permission_id SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_id       TINYINT UNSIGNED NOT NULL,
  full_name     VARCHAR(120) NOT NULL,
  email         VARCHAR(160) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  phone         VARCHAR(40)  NULL,
  avatar_initials VARCHAR(4) NULL,
  is_active     TINYINT(1)   NOT NULL DEFAULT 1,
  last_login_at DATETIME     NULL,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY ix_users_role (role_id),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  PRACTITIONERS  (a user who performs treatments)
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS practitioners;
CREATE TABLE practitioners (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id         INT UNSIGNED NOT NULL,
  title           VARCHAR(30)  NOT NULL DEFAULT 'Dr.',
  speciality      VARCHAR(120) NULL,
  license_no      VARCHAR(60)  NULL,
  commission_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  calendar_colour VARCHAR(7)   NOT NULL DEFAULT '#1F6F52',
  bio             TEXT         NULL,
  is_active       TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_practitioner_user (user_id),
  CONSTRAINT fk_prac_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS practitioner_schedules;
CREATE TABLE practitioner_schedules (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  practitioner_id INT UNSIGNED NOT NULL,
  weekday         TINYINT UNSIGNED NOT NULL COMMENT '0=Sunday … 6=Saturday',
  start_time      TIME NOT NULL,
  end_time        TIME NOT NULL,
  PRIMARY KEY (id),
  KEY ix_sched_prac (practitioner_id, weekday),
  CONSTRAINT fk_sched_prac FOREIGN KEY (practitioner_id) REFERENCES practitioners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS time_off;
CREATE TABLE time_off (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  practitioner_id INT UNSIGNED NOT NULL,
  starts_at       DATETIME NOT NULL,
  ends_at         DATETIME NOT NULL,
  reason          VARCHAR(160) NULL,
  PRIMARY KEY (id),
  KEY ix_timeoff_prac (practitioner_id, starts_at),
  CONSTRAINT fk_timeoff_prac FOREIGN KEY (practitioner_id) REFERENCES practitioners(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  PATIENTS
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS patients;
CREATE TABLE patients (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  code         VARCHAR(20)  NOT NULL COMMENT 'human reference, e.g. PT-00142',
  full_name    VARCHAR(140) NOT NULL,
  date_of_birth DATE        NULL,
  gender       ENUM('female','male','other') NULL,
  phone        VARCHAR(40)  NOT NULL,
  email        VARCHAR(160) NULL,
  address      VARCHAR(255) NULL,
  source       ENUM('instagram','referral','walk_in','google','facebook','other') NOT NULL DEFAULT 'other',
  notes        TEXT NULL,
  created_by   INT UNSIGNED NULL,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_patients_code (code),
  KEY ix_patients_name (full_name),
  KEY ix_patients_phone (phone),
  CONSTRAINT fk_patients_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS medical_histories;
CREATE TABLE medical_histories (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id        INT UNSIGNED NOT NULL,
  allergies         TEXT NULL,
  medications       TEXT NULL,
  conditions        TEXT NULL,
  is_pregnant       TINYINT(1) NOT NULL DEFAULT 0,
  is_breastfeeding  TINYINT(1) NOT NULL DEFAULT 0,
  on_blood_thinners TINYINT(1) NOT NULL DEFAULT 0,
  has_autoimmune    TINYINT(1) NOT NULL DEFAULT 0,
  had_prior_reaction TINYINT(1) NOT NULL DEFAULT 0,
  prior_treatments  TEXT NULL,
  smoker            TINYINT(1) NOT NULL DEFAULT 0,
  reviewed_by       INT UNSIGNED NULL,
  reviewed_at       DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_history_patient (patient_id),
  CONSTRAINT fk_history_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_history_user FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  STOCK  (declared before treatments — treatments reference products)
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS suppliers;
CREATE TABLE suppliers (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name         VARCHAR(140) NOT NULL,
  contact_name VARCHAR(120) NULL,
  phone        VARCHAR(40)  NULL,
  email        VARCHAR(160) NULL,
  notes        VARCHAR(255) NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS products;
CREATE TABLE products (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(140) NOT NULL,
  brand         VARCHAR(100) NULL,
  type          ENUM('toxin','filler','thread','skinbooster','consumable') NOT NULL,
  unit          ENUM('unit','ml','piece') NOT NULL DEFAULT 'unit',
  unit_cost     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  reorder_level DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'alert when remaining falls below',
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY ix_products_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS batches;
CREATE TABLE batches (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id    INT UNSIGNED NOT NULL,
  supplier_id   INT UNSIGNED NULL,
  lot_number    VARCHAR(60) NOT NULL,
  expiry_date   DATE NOT NULL,
  qty_received  DECIMAL(10,2) NOT NULL,
  qty_remaining DECIMAL(10,2) NOT NULL,
  received_at   DATE NOT NULL,
  PRIMARY KEY (id),
  KEY ix_batches_product (product_id, expiry_date),
  KEY ix_batches_lot (lot_number),
  CONSTRAINT fk_batches_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  CONSTRAINT fk_batches_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS stock_movements;
CREATE TABLE stock_movements (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  batch_id       INT UNSIGNED NOT NULL,
  type           ENUM('in','out','waste','adjustment') NOT NULL,
  quantity       DECIMAL(10,2) NOT NULL,
  reference_type VARCHAR(40) NULL COMMENT 'e.g. treatment_record',
  reference_id   INT UNSIGNED NULL,
  note           VARCHAR(255) NULL,
  user_id        INT UNSIGNED NULL,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_moves_batch (batch_id, created_at),
  CONSTRAINT fk_moves_batch FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
  CONSTRAINT fk_moves_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  TREATMENT CATALOGUE
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS aftercare_templates;
CREATE TABLE aftercare_templates (
  id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title   VARCHAR(140) NOT NULL,
  body    TEXT NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS treatments;
CREATE TABLE treatments (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name                VARCHAR(140) NOT NULL,
  category            ENUM('toxin','filler','thread','skinbooster','consultation','other') NOT NULL,
  duration_min        SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  price               DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  price_type          ENUM('fixed','per_unit','per_ml') NOT NULL DEFAULT 'fixed',
  requires_consent    TINYINT(1) NOT NULL DEFAULT 1,
  followup_days       SMALLINT UNSIGNED NULL COMMENT 'review due after N days',
  recall_days         SMALLINT UNSIGNED NULL COMMENT 'recall patient after N days',
  aftercare_id        INT UNSIGNED NULL,
  description         TEXT NULL,
  is_active           TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY ix_treatments_cat (category),
  CONSTRAINT fk_treat_aftercare FOREIGN KEY (aftercare_id) REFERENCES aftercare_templates(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS treatment_products;
CREATE TABLE treatment_products (
  treatment_id INT UNSIGNED NOT NULL,
  product_id   INT UNSIGNED NOT NULL,
  default_qty  DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (treatment_id, product_id),
  CONSTRAINT fk_tp_treatment FOREIGN KEY (treatment_id) REFERENCES treatments(id) ON DELETE CASCADE,
  CONSTRAINT fk_tp_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  APPOINTMENTS
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS appointments;
CREATE TABLE appointments (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id      INT UNSIGNED NOT NULL,
  practitioner_id INT UNSIGNED NOT NULL,
  treatment_id    INT UNSIGNED NOT NULL,
  starts_at       DATETIME NOT NULL,
  ends_at         DATETIME NOT NULL,
  status          ENUM('booked','confirmed','arrived','in_room','completed','no_show','cancelled')
                  NOT NULL DEFAULT 'booked',
  deposit         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  notes           VARCHAR(500) NULL,
  created_by      INT UNSIGNED NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_appt_day (starts_at),
  KEY ix_appt_prac (practitioner_id, starts_at),
  KEY ix_appt_patient (patient_id),
  CONSTRAINT fk_appt_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_appt_prac FOREIGN KEY (practitioner_id) REFERENCES practitioners(id),
  CONSTRAINT fk_appt_treatment FOREIGN KEY (treatment_id) REFERENCES treatments(id),
  CONSTRAINT fk_appt_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS consultations;
CREATE TABLE consultations (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  appointment_id INT UNSIGNED NOT NULL,
  assessment     TEXT NULL,
  plan           TEXT NULL,
  quote_total    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_consult_appt (appointment_id),
  CONSTRAINT fk_consult_appt FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  CONSENT
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS consents;
CREATE TABLE consents (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id     INT UNSIGNED NOT NULL,
  appointment_id INT UNSIGNED NULL,
  treatment_id   INT UNSIGNED NOT NULL,
  version        VARCHAR(20) NOT NULL DEFAULT '1.0',
  body_snapshot  TEXT NOT NULL COMMENT 'exact wording the patient agreed to',
  signed_name    VARCHAR(140) NULL,
  signed_at      DATETIME NULL,
  signature_data MEDIUMTEXT NULL COMMENT 'drawn signature, data URL',
  photo_consent  TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'separate permission to use images',
  witnessed_by   INT UNSIGNED NULL,
  ip_address     VARCHAR(45) NULL,
  PRIMARY KEY (id),
  KEY ix_consent_patient (patient_id),
  KEY ix_consent_appt (appointment_id),
  CONSTRAINT fk_consent_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_consent_appt FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
  CONSTRAINT fk_consent_treatment FOREIGN KEY (treatment_id) REFERENCES treatments(id),
  CONSTRAINT fk_consent_user FOREIGN KEY (witnessed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  CLINICAL RECORDS   (the heart of the system)
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS treatment_records;
CREATE TABLE treatment_records (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  appointment_id  INT UNSIGNED NULL,
  patient_id      INT UNSIGNED NOT NULL,
  practitioner_id INT UNSIGNED NOT NULL,
  treatment_id    INT UNSIGNED NOT NULL,
  performed_at    DATETIME NOT NULL,
  technique       VARCHAR(160) NULL COMMENT 'needle / cannula, dilution',
  notes           TEXT NULL,
  total_units     DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_ml        DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_rec_patient (patient_id, performed_at),
  KEY ix_rec_prac (practitioner_id, performed_at),
  CONSTRAINT fk_rec_appt FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
  CONSTRAINT fk_rec_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_rec_prac FOREIGN KEY (practitioner_id) REFERENCES practitioners(id),
  CONSTRAINT fk_rec_treatment FOREIGN KEY (treatment_id) REFERENCES treatments(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- One row per injection site. This is what makes the system clinical
-- rather than a booking calendar: product + batch + site + dose.
DROP TABLE IF EXISTS treatment_record_items;
CREATE TABLE treatment_record_items (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  treatment_record_id INT UNSIGNED NOT NULL,
  product_id          INT UNSIGNED NOT NULL,
  batch_id            INT UNSIGNED NOT NULL,
  injection_site      VARCHAR(80) NOT NULL COMMENT 'glabella, left lip, jawline…',
  quantity            DECIMAL(10,2) NOT NULL,
  unit                ENUM('unit','ml','piece') NOT NULL,
  PRIMARY KEY (id),
  KEY ix_item_record (treatment_record_id),
  KEY ix_item_batch (batch_id),
  CONSTRAINT fk_item_record FOREIGN KEY (treatment_record_id) REFERENCES treatment_records(id) ON DELETE CASCADE,
  CONSTRAINT fk_item_product FOREIGN KEY (product_id) REFERENCES products(id),
  CONSTRAINT fk_item_batch FOREIGN KEY (batch_id) REFERENCES batches(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS patient_photos;
CREATE TABLE patient_photos (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id          INT UNSIGNED NOT NULL,
  treatment_record_id INT UNSIGNED NULL,
  type                ENUM('before','after','progress') NOT NULL,
  file_path           VARCHAR(255) NOT NULL,
  taken_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  marketing_consent   TINYINT(1) NOT NULL DEFAULT 0,
  uploaded_by         INT UNSIGNED NULL,
  PRIMARY KEY (id),
  KEY ix_photo_patient (patient_id),
  CONSTRAINT fk_photo_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_photo_record FOREIGN KEY (treatment_record_id) REFERENCES treatment_records(id) ON DELETE SET NULL,
  CONSTRAINT fk_photo_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS follow_ups;
CREATE TABLE follow_ups (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id          INT UNSIGNED NOT NULL,
  treatment_record_id INT UNSIGNED NULL,
  type                ENUM('review','top_up','recall','session') NOT NULL,
  due_date            DATE NOT NULL,
  status              ENUM('pending','done','dismissed') NOT NULL DEFAULT 'pending',
  notes               VARCHAR(255) NULL,
  completed_at        DATETIME NULL,
  PRIMARY KEY (id),
  KEY ix_follow_due (due_date, status),
  KEY ix_follow_patient (patient_id),
  CONSTRAINT fk_follow_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_follow_record FOREIGN KEY (treatment_record_id) REFERENCES treatment_records(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS adverse_events;
CREATE TABLE adverse_events (
  id                  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  patient_id          INT UNSIGNED NOT NULL,
  treatment_record_id INT UNSIGNED NULL,
  type                VARCHAR(100) NOT NULL COMMENT 'bruising, swelling, nodule, vascular occlusion…',
  severity            ENUM('mild','moderate','severe') NOT NULL DEFAULT 'mild',
  description         TEXT NULL,
  action_taken        TEXT NULL,
  resolved            TINYINT(1) NOT NULL DEFAULT 0,
  reported_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  reported_by         INT UNSIGNED NULL,
  PRIMARY KEY (id),
  KEY ix_ae_patient (patient_id),
  CONSTRAINT fk_ae_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_ae_record FOREIGN KEY (treatment_record_id) REFERENCES treatment_records(id) ON DELETE SET NULL,
  CONSTRAINT fk_ae_user FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  BILLING
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS invoices;
CREATE TABLE invoices (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  number         VARCHAR(20) NOT NULL,
  patient_id     INT UNSIGNED NOT NULL,
  appointment_id INT UNSIGNED NULL,
  issued_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  subtotal       DECIMAL(10,2) NOT NULL DEFAULT 0,
  discount       DECIMAL(10,2) NOT NULL DEFAULT 0,
  total          DECIMAL(10,2) NOT NULL DEFAULT 0,
  amount_paid    DECIMAL(10,2) NOT NULL DEFAULT 0,
  status         ENUM('unpaid','partial','paid','void') NOT NULL DEFAULT 'unpaid',
  created_by     INT UNSIGNED NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_invoice_number (number),
  KEY ix_invoice_patient (patient_id),
  KEY ix_invoice_issued (issued_at),
  CONSTRAINT fk_inv_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_inv_appt FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
  CONSTRAINT fk_inv_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS invoice_items;
CREATE TABLE invoice_items (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  invoice_id   INT UNSIGNED NOT NULL,
  description  VARCHAR(200) NOT NULL,
  quantity     DECIMAL(10,2) NOT NULL DEFAULT 1,
  unit_price   DECIMAL(10,2) NOT NULL DEFAULT 0,
  line_total   DECIMAL(10,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY ix_ii_invoice (invoice_id),
  CONSTRAINT fk_ii_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS payments;
CREATE TABLE payments (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  invoice_id  INT UNSIGNED NOT NULL,
  method      ENUM('cash','card','transfer','other') NOT NULL DEFAULT 'cash',
  amount      DECIMAL(10,2) NOT NULL,
  paid_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  received_by INT UNSIGNED NULL,
  reference   VARCHAR(100) NULL,
  PRIMARY KEY (id),
  KEY ix_pay_invoice (invoice_id),
  KEY ix_pay_date (paid_at),
  CONSTRAINT fk_pay_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
  CONSTRAINT fk_pay_user FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  SYSTEM
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS settings;
CREATE TABLE settings (
  `key`   VARCHAR(60) NOT NULL,
  `value` TEXT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS audit_log;
CREATE TABLE audit_log (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     INT UNSIGNED NULL,
  action      VARCHAR(60) NOT NULL,
  entity      VARCHAR(60) NOT NULL,
  entity_id   INT UNSIGNED NULL,
  detail      VARCHAR(500) NULL,
  ip_address  VARCHAR(45) NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY ix_audit_user (user_id, created_at),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
