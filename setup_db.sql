-- ============================================================
-- setup_db.sql
-- Run this once in phpMyAdmin (or the MySQL CLI).
-- Safe to re-run — uses IF NOT EXISTS and INSERT IGNORE.
-- ============================================================

-- 1. Create the database
CREATE DATABASE IF NOT EXISTS wakoli_portfolio
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- 2. Switch to it
USE wakoli_portfolio;

-- 3. Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name         VARCHAR(254)  NOT NULL,
    email        VARCHAR(254)  NOT NULL,
    subject      VARCHAR(100)  NOT NULL,
    message      TEXT          NOT NULL,
    submitted_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_read      TINYINT(1)    NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Site settings table (all uploadable assets live here)
CREATE TABLE IF NOT EXISTS site_settings (
    setting_key   VARCHAR(100) NOT NULL,
    setting_value TEXT         NOT NULL,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Default values for every asset (INSERT IGNORE = skip if already set)
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
  -- Hero section
  ('portfolio_photo',      'images/wakoli-picc.jpeg'),

  -- About section
  ('about_photo',          'images/WAA.jpg'),

  -- Skills section (4 cards)
  ('skill_img_frontend',   'images/twwwo.jpg'),
  ('skill_img_javascript', 'images/js.jpg'),
  ('skill_img_design',     'images/ui and ux.jpg'),
  ('skill_img_portfolio',  'images/ONE.jpg'),

  -- Projects section (3 cards)
  ('project_img_1',        'images/focus (1).jpg'),
  ('project_img_2',        'images/web.png'),
  ('project_img_3',        'images/dashbord.jpg'),

  -- CV / resume file
  ('cv_file',              'cv/Wenyaa-Brian-Wakoli-CV.pdf');
