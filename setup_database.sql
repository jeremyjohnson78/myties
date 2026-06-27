-- ============================================================
--  MyTies Database Setup
--  Run this in cPanel > phpMyAdmin > SQL tab
-- ============================================================

-- 1. TIES TABLE
CREATE TABLE IF NOT EXISTS `ties` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `color`        VARCHAR(50)  NOT NULL,
  `color2`       VARCHAR(50)  DEFAULT '',
  `pattern`      VARCHAR(50)  NOT NULL,
  `size`         VARCHAR(20)  DEFAULT '',
  `occasion`     VARCHAR(50)  DEFAULT '',
  `condition_`   VARCHAR(20)  DEFAULT '',
  `notes`        TEXT         DEFAULT '',
  `photo`        VARCHAR(255) DEFAULT '',
  `loan_status`  ENUM('in','out') NOT NULL DEFAULT 'in',
  `date_added`   DATE         DEFAULT NULL,
  `created_at`   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. LOANS TABLE (history of all borrows)
CREATE TABLE IF NOT EXISTS `loans` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tie_id`          INT UNSIGNED NOT NULL,
  `borrower_name`   VARCHAR(100) NOT NULL,
  `borrower_phone`  VARCHAR(30)  DEFAULT '',
  `date_borrowed`   DATE         DEFAULT NULL,
  `date_returned`   DATE         DEFAULT NULL,
  `status`          ENUM('active','returned') NOT NULL DEFAULT 'active',
  `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tie_id`) REFERENCES `ties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. BORROW REQUESTS TABLE (from public gallery)
CREATE TABLE IF NOT EXISTS `requests` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `tie_id`         INT UNSIGNED NOT NULL,
  `requester_name` VARCHAR(100) NOT NULL,
  `requester_phone` VARCHAR(30) NOT NULL,
  `note`           TEXT         DEFAULT '',
  `status`         ENUM('pending','approved','declined') NOT NULL DEFAULT 'pending',
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`tie_id`) REFERENCES `ties`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. SESSION TABLE (simple owner login)
CREATE TABLE IF NOT EXISTS `sessions` (
  `token`      VARCHAR(64) PRIMARY KEY,
  `expires_at` DATETIME    NOT NULL,
  `created_at` TIMESTAMP   DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  IMPORT YOUR 49 TIES
-- ============================================================

INSERT INTO `ties` (`color`,`color2`,`pattern`,`size`,`occasion`,`condition_`,`notes`,`loan_status`,`date_added`) VALUES
('Black','','Novelty','Medium','Casual','Good','','in','2026-04-23'),
('Black','Yellow','Novelty','Medium','Casual','Good','','in','2026-04-23'),
('Black','','Novelty','Broad','Casual','Good','','in','2026-04-24'),
('Gold','Black','Geometric','Medium','Formal','Good','','out','2026-04-24'),
('Yellow','Gold','Dotted','Medium','Formal','Excellent','','in','2026-04-24'),
('Gold','Black','Geometric','Medium','Business','Fair','','in','2026-04-24'),
('White','','Dotted','Medium','Business','Good','','in','2026-04-24'),
('White','','Solid','Medium','Casual','Fair','','in','2026-04-24'),
('Gray','White','Striped','Medium','Business','Good','','in','2026-04-24'),
('White','','Solid','Medium','Business','Fair','','in','2026-04-24'),
('Gray','Beige','Geometric','Medium','Casual','Good','','in','2026-04-24'),
('Gray','','Striped','Medium','Business','Good','','in','2026-04-24'),
('Gray','','Floral','Slim','Wedding','Excellent','','in','2026-04-24'),
('Brown','','Striped','Medium','Formal','Excellent','','in','2026-04-24'),
('Red','Maroon','Dual Tone','Medium','Casual','Fair','','in','2026-04-24'),
('Red','Silver','Dotted','Medium','Formal','Excellent','','in','2026-04-24'),
('Red','Maroon','Floral','Medium','Wedding','Fair','','in','2026-04-24'),
('Red','','Solid','Medium','Business','Fair','','in','2026-04-24'),
('Red','','Dual Tone','Medium','Formal','Good','','in','2026-04-24'),
('Red','Gold','Dotted','Medium','Formal','Good','','out','2026-04-24'),
('Red','Silver','Dotted','Medium','Formal','Good','','in','2026-04-24'),
('Brown','','Solid','Slim','Business','Excellent','','in','2026-04-24'),
('Brown','Red','Floral','Medium','Wedding','Good','','in','2026-04-24'),
('Brown','','Solid','Slim','Business','Excellent','','in','2026-04-24'),
('Multicolor','Multicolor','Geometric','Medium','Casual','Fair','','in','2026-04-24'),
('Blue','Multicolor','Geometric','Medium','Casual','Good','','in','2026-04-24'),
('Blue','','Solid','Medium','Formal','Excellent','','in','2026-04-24'),
('Blue','Silver','Striped','Medium','Business','Good','','in','2026-04-24'),
('Silver','Blue','Floral','Medium','Wedding','Fair','','in','2026-04-24'),
('Blue','','Plaid','Medium','Formal','Excellent','','in','2026-04-24'),
('Blue','Green','Striped','Medium','Business','Excellent','','in','2026-04-24'),
('Green','Dual Tone','Solid','Medium','Formal','Good','','in','2026-04-24'),
('Green','Gold','Dotted','Medium','Business','Excellent','','in','2026-04-24'),
('Green','','Solid','Medium','Business','Good','','in','2026-04-24'),
('Green','','Solid','Medium','Business','Excellent','','in','2026-04-24'),
('Blue','','Solid','Medium','Formal','Excellent','','in','2026-04-24'),
('Blue','Silver','Striped','Medium','Business','Good','','in','2026-04-24'),
('Blue','Silver','Dotted','Medium','Business','Good','','in','2026-04-24'),
('Blue','Silver','Striped','Medium','Casual','Good','','in','2026-04-24'),
('Blue','Silver','Striped','Medium','Business','Good','','in','2026-04-24'),
('Blue','Orange','Floral','Medium','Wedding','Good','','in','2026-04-24'),
('Black','Gold','Plaid','Medium','Business','Good','','in','2026-04-24'),
('Black','Gold','Geometric','Medium','Business','Good','','in','2026-04-24'),
('Purple','Silver','Dotted','Medium','Business','Good','','in','2026-04-24'),
('Purple','Silver','Dual Tone','Medium','Business','Good','','in','2026-04-24'),
('Purple','Silver','Striped','Medium','Business','Good','','in','2026-04-24'),
('Multicolor','Blue Pink Red','Striped','Medium','Business','Good','','in','2026-04-24'),
('Gray','','Solid','Medium','Business','Good','','in','2026-04-25'),
('Gray','Black','Geometric','Medium','Casual','Good','','in','2026-04-29');

-- Active loans for Wikson (tie #4 = Gold/Black/Geometric/Formal) and Julius Ho (tie #20 = Red/Gold/Dotted)
INSERT INTO `loans` (`tie_id`,`borrower_name`,`borrower_phone`,`date_borrowed`,`status`) VALUES
(4, 'Wikson', '0162509454', '2026-05-09', 'active'),
(20, 'Julius Ho', '', '2026-04-25', 'active');
