-- WorkPulse Pro Database Schema
-- Compatible with MySQL 5.7+ and MariaDB 10.3+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Database: workpulse_pro
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `workpulse_pro`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `workpulse_pro`;

-- --------------------------------------------------------
-- Table: companies
-- --------------------------------------------------------

CREATE TABLE `companies` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_name` VARCHAR(150)     NOT NULL,
  `owner_name`   VARCHAR(100)     NOT NULL,
  `email`        VARCHAR(180)     NOT NULL,
  `password`     VARCHAR(255)     NOT NULL,
  `created_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: workers
-- --------------------------------------------------------

CREATE TABLE `workers` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id`  INT(11) UNSIGNED NOT NULL,
  `name`        VARCHAR(120)     NOT NULL,
  `worker_id`   VARCHAR(50)      NOT NULL,
  `role`        VARCHAR(100)     NOT NULL,
  `hourly_rate` DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_worker_company` (`company_id`, `worker_id`),
  KEY `idx_company_id` (`company_id`),
  CONSTRAINT `fk_workers_company`
    FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: attendance
-- --------------------------------------------------------

CREATE TABLE `attendance` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `company_id`   INT(11) UNSIGNED NOT NULL,
  `worker_id`    INT(11) UNSIGNED NOT NULL,
  `date`         DATE             NOT NULL,
  `hours_worked` DECIMAL(5,2)     NOT NULL DEFAULT 0.00,
  `created_at`   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_attendance_day` (`company_id`, `worker_id`, `date`),
  KEY `idx_att_company`   (`company_id`),
  KEY `idx_att_worker`    (`worker_id`),
  KEY `idx_att_date`      (`date`),
  CONSTRAINT `fk_att_company`
    FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_att_worker`
    FOREIGN KEY (`worker_id`) REFERENCES `workers` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
