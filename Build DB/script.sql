﻿/* =========================================================
   0) TẠO DATABASE
========================================================= */
CREATE DATABASE IF NOT EXISTS `webproject`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `webproject`;


/* =========================================================
   1) TẠO BẢNG TRƯỚC (CHƯA GẮN PK/FK)
========================================================= */

CREATE TABLE `user` (
  `user_id` INT UNSIGNED NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','editor','author','reader') NOT NULL DEFAULT 'reader',
  `status` ENUM('active','inactive','banned') NOT NULL DEFAULT 'active',
  `full_name` VARCHAR(100) NULL,
  `avatar` VARCHAR(255) NULL,
  `bio` TEXT NULL,
  `phone` VARCHAR(20) NULL,
  `address` VARCHAR(255) NULL,
  `gender` ENUM('male','female','other') NULL,
  `birth_date` DATE NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `category` (
  `category_id` INT UNSIGNED NOT NULL,
  `category_name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `article` (
  `article_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `summary` VARCHAR(500) NULL,
  `content` TEXT NOT NULL,
  `thumbnail` VARCHAR(255) NULL,
  `status` ENUM('draft','pending','published','archived') NOT NULL DEFAULT 'draft',
  `view_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comment` (
  `comment_id` INT UNSIGNED NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('visible','hidden','deleted') NOT NULL DEFAULT 'visible',
  `parent_comment_id` INT UNSIGNED NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `article_id` INT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


/* =========================================================
   2) THÊM KHÓA (PRIMARY KEY, UNIQUE, INDEX)
========================================================= */

ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_user_username` (`username`),
  ADD UNIQUE KEY `uq_user_email` (`email`),
  MODIFY `user_id` INT UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `uq_category_name` (`category_name`),
  MODIFY `category_id` INT UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `article`
  ADD PRIMARY KEY (`article_id`),
  ADD KEY `idx_article_user_id` (`user_id`),
  ADD KEY `idx_article_category_id` (`category_id`),
  ADD KEY `idx_article_status` (`status`),
  ADD KEY `idx_article_created_at` (`created_at`),
  MODIFY `article_id` INT UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `comment`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `idx_comment_parent_comment_id` (`parent_comment_id`),
  ADD KEY `idx_comment_user_id` (`user_id`),
  ADD KEY `idx_comment_article_id` (`article_id`),
  ADD KEY `idx_comment_created_at` (`created_at`),
  MODIFY `comment_id` INT UNSIGNED NOT NULL AUTO_INCREMENT;


/* =========================================================
   3) THÊM KHÓA NGOẠI (FOREIGN KEY + HÀNH VI XÓA)
========================================================= */

ALTER TABLE `article`
  ADD CONSTRAINT `fk_article_user`
    FOREIGN KEY (`user_id`) REFERENCES `user`(`user_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_article_category`
    FOREIGN KEY (`category_id`) REFERENCES `category`(`category_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

ALTER TABLE `comment`
  ADD CONSTRAINT `fk_comment_user`
    FOREIGN KEY (`user_id`) REFERENCES `user`(`user_id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comment_article`
    FOREIGN KEY (`article_id`) REFERENCES `article`(`article_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comment_parent`
    FOREIGN KEY (`parent_comment_id`) REFERENCES `comment`(`comment_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;


/* =========================================================
   4) RÀNG BUỘC BỔ SUNG (CHECK)
   Lưu ý: một số bản MariaDB cũ có thể không enforce CHECK đầy đủ.
========================================================= */

ALTER TABLE `user`
  ADD CONSTRAINT `chk_user_username_not_empty` CHECK (CHAR_LENGTH(TRIM(`username`)) > 0),
  ADD CONSTRAINT `chk_user_email_not_empty` CHECK (CHAR_LENGTH(TRIM(`email`)) > 0);

ALTER TABLE `category` -- đang lỗi
  ADD CONSTRAINT `chk_category_name_not_empty` CHECK (CHAR_LENGTH(TRIM(`category_name`)) > 0);

ALTER TABLE `article`
  ADD CONSTRAINT `chk_article_title_not_empty` CHECK (CHAR_LENGTH(TRIM(`title`)) > 0),
  ADD CONSTRAINT `chk_article_view_count_non_negative` CHECK (`view_count` >= 0),
  ADD CONSTRAINT `chk_article_time_order` CHECK (`updated_at` >= `created_at`);

ALTER TABLE `comment`
  ADD CONSTRAINT `chk_comment_content_not_empty` CHECK (CHAR_LENGTH(TRIM(`content`)) > 0);