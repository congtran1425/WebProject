ALTER TABLE `comment`
  ADD COLUMN `parent_comment_id` INT UNSIGNED NULL AFTER `status`,
  ADD KEY `idx_comment_parent_comment_id` (`parent_comment_id`),
  ADD CONSTRAINT `fk_comment_parent`
    FOREIGN KEY (`parent_comment_id`) REFERENCES `comment`(`comment_id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
