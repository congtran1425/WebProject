USE `webproject`;

INSERT INTO `user` (
  `user_id`,
  `username`,
  `email`,
  `password`,
  `role`,
  `status`,
  `full_name`,
  `bio`,
  `phone`,
  `address`,
  `gender`,
  `birth_date`
) VALUES (
  1,
  'admin',
  'admin@example.com',
  '$2y$10$yUTlTVNPNYGYGglaA.vb.enKcHR87Qa9O3cLlk8nVbubz7cW5EcR2',
  'admin',
  'active',
  'Quản trị viên',
  'Tài khoản mẫu để đăng bài, bình luận và cập nhật trang cá nhân.',
  '0900000001',
  'TP. Hồ Chí Minh',
  'other',
  '2000-01-01'
) ON DUPLICATE KEY UPDATE
  `email` = VALUES(`email`),
  `password` = VALUES(`password`),
  `role` = VALUES(`role`),
  `status` = VALUES(`status`),
  `full_name` = VALUES(`full_name`),
  `bio` = VALUES(`bio`),
  `phone` = VALUES(`phone`),
  `address` = VALUES(`address`),
  `gender` = VALUES(`gender`),
  `birth_date` = VALUES(`birth_date`);

INSERT INTO `category` (`category_id`, `category_name`, `description`) VALUES
  (1, 'Thời sự', 'Tin tức thời sự trong ngày'),
  (2, 'Công nghệ', 'Xu hướng công nghệ và đời sống số'),
  (3, 'Thể thao', 'Tin thể thao trong nước và quốc tế'),
  (4, 'Giải trí', 'Văn hóa, phim ảnh và âm nhạc')
ON DUPLICATE KEY UPDATE
  `description` = VALUES(`description`);

INSERT INTO `article` (
  `article_id`,
  `title`,
  `summary`,
  `content`,
  `thumbnail`,
  `status`,
  `view_count`,
  `user_id`,
  `category_id`
) VALUES (
  1,
  'Bài viết mẫu cho hệ thống',
  'Bài viết này được thêm để bạn kiểm tra trang chủ, trang chi tiết và trang cá nhân.',
  '<p>Đây là bài viết mẫu được tạo sẵn trong database để dự án có dữ liệu hiển thị ngay sau khi import.</p><p>Bạn có thể sửa, xóa hoặc tạo thêm bài viết mới từ giao diện quản trị.</p>',
  NULL,
  'published',
  25,
  1,
  1
) ON DUPLICATE KEY UPDATE
  `summary` = VALUES(`summary`),
  `content` = VALUES(`content`),
  `status` = VALUES(`status`),
  `view_count` = VALUES(`view_count`),
  `user_id` = VALUES(`user_id`),
  `category_id` = VALUES(`category_id`);

INSERT INTO `comment` (
  `comment_id`,
  `content`,
  `status`,
  `parent_comment_id`,
  `user_id`,
  `article_id`
) VALUES
  (
    1,
    'Đây là bình luận mẫu để bạn kiểm tra khu vực thảo luận.',
    'visible',
    NULL,
    1,
    1
  ),
  (
    2,
    'Đây là phản hồi mẫu cho bình luận phía trên.',
    'visible',
    1,
    1,
    1
  )
ON DUPLICATE KEY UPDATE
  `content` = VALUES(`content`),
  `status` = VALUES(`status`),
  `parent_comment_id` = VALUES(`parent_comment_id`),
  `user_id` = VALUES(`user_id`),
  `article_id` = VALUES(`article_id`);
