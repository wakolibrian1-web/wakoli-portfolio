-- ============================================================
-- setup_db.sql
-- Run this once in phpMyAdmin (or the MySQL CLI) to create
-- the database and table used by the portfolio contact form.
-- ============================================================

-- 1. Create the database
CREATE DATABASE IF NOT EXISTS wakoli_portfolio
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- 2. Switch to it
USE wakoli_portfolio;

-- 3. Create the contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    name         VARCHAR(254)     NOT NULL,
    email        VARCHAR(254)     NOT NULL,
    subject      VARCHAR(100)     NOT NULL,
    message      TEXT             NOT NULL,
    submitted_at DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_read      TINYINT(1)       NOT NULL DEFAULT 0,   -- 0 = unread, 1 = read
    PRIMARY KEY (id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
