USE `webproject`;

START TRANSACTION;

-- =====================================================
-- 0) Dọn dữ liệu demo cũ (nếu chạy lại script)
-- =====================================================
DELETE c
FROM `comment` c
JOIN `article` a ON a.article_id = c.article_id
WHERE a.title LIKE 'DEMO-%';

DELETE FROM `article`
WHERE title LIKE 'DEMO-%';

-- =====================================================
-- 1) Tạo 6 user (1 admin + 5 user thường)
-- =====================================================
INSERT INTO `user` (`username`, `email`, `password_hash`, `role`, `status`)
VALUES
('admin_web', 'admin_web@example.com', '$2y$10$adminhashdemo', 'admin', 'active'),
('user_an',   'user_an@example.com',   '$2y$10$useranhashdemo', 'reader', 'active'),
('user_binh', 'user_binh@example.com', '$2y$10$userbinhhash',   'author', 'active'),
('user_chi',  'user_chi@example.com',  '$2y$10$userchihashdemo', 'editor', 'active'),
('user_dung', 'user_dung@example.com', '$2y$10$userdunghashdm', 'reader', 'active'),
('user_hoa',  'user_hoa@example.com',  '$2y$10$userhoahashdemo', 'author', 'active')
ON DUPLICATE KEY UPDATE
`role` = VALUES(`role`),
`status` = VALUES(`status`);

-- =====================================================
-- 2) Tạo 5 category, trong đó 1 cha có 2 con
-- =====================================================
INSERT INTO `category` (`category_name`, `description`, `parent_id`)
VALUES
('Tin tuc', 'Danh muc cha', NULL),
('The thao', 'Tin the thao', NULL),
('Giai tri', 'Tin giai tri', NULL)
ON DUPLICATE KEY UPDATE
`description` = VALUES(`description`);

INSERT INTO `category` (`category_name`, `description`, `parent_id`)
SELECT 'Cong nghe', 'Tin cong nghe', c.category_id
FROM `category` c
WHERE c.category_name = 'Tin tuc'
ON DUPLICATE KEY UPDATE
`description` = VALUES(`description`),
`parent_id` = VALUES(`parent_id`);

INSERT INTO `category` (`category_name`, `description`, `parent_id`)
SELECT 'Kinh doanh', 'Tin kinh doanh', c.category_id
FROM `category` c
WHERE c.category_name = 'Tin tuc'
ON DUPLICATE KEY UPDATE
`description` = VALUES(`description`),
`parent_id` = VALUES(`parent_id`);

-- =====================================================
-- 3) Mỗi user KHÔNG phải admin có 3 bài (tổng 15 bài)
--    Mỗi bài gắn đúng 1 category
-- =====================================================
INSERT INTO `article` (`title`, `summary`, `content`, `thumbnail`, `status`, `view_count`, `user_id`, `category_id`) VALUES
('DEMO-An-01', 'Tom tat DEMO-An-01', 'Noi dung DEMO-An-01', 'an01.jpg', 'published', 10,
 (SELECT user_id FROM `user` WHERE username='user_an'),
 (SELECT category_id FROM `category` WHERE category_name='Cong nghe')),

('DEMO-An-02', 'Tom tat DEMO-An-02', 'Noi dung DEMO-An-02', 'an02.jpg', 'published', 12,
 (SELECT user_id FROM `user` WHERE username='user_an'),
 (SELECT category_id FROM `category` WHERE category_name='The thao')),

('DEMO-An-03', 'Tom tat DEMO-An-03', 'Noi dung DEMO-An-03', 'an03.jpg', 'draft', 0,
 (SELECT user_id FROM `user` WHERE username='user_an'),
 (SELECT category_id FROM `category` WHERE category_name='Giai tri')),

('DEMO-Binh-01', 'Tom tat DEMO-Binh-01', 'Noi dung DEMO-Binh-01', 'binh01.jpg', 'published', 15,
 (SELECT user_id FROM `user` WHERE username='user_binh'),
 (SELECT category_id FROM `category` WHERE category_name='Kinh doanh')),

('DEMO-Binh-02', 'Tom tat DEMO-Binh-02', 'Noi dung DEMO-Binh-02', 'binh02.jpg', 'pending', 2,
 (SELECT user_id FROM `user` WHERE username='user_binh'),
 (SELECT category_id FROM `category` WHERE category_name='Cong nghe')),

('DEMO-Binh-03', 'Tom tat DEMO-Binh-03', 'Noi dung DEMO-Binh-03', 'binh03.jpg', 'published', 4,
 (SELECT user_id FROM `user` WHERE username='user_binh'),
 (SELECT category_id FROM `category` WHERE category_name='The thao')),

('DEMO-Chi-01', 'Tom tat DEMO-Chi-01', 'Noi dung DEMO-Chi-01', 'chi01.jpg', 'published', 11,
 (SELECT user_id FROM `user` WHERE username='user_chi'),
 (SELECT category_id FROM `category` WHERE category_name='Giai tri')),

('DEMO-Chi-02', 'Tom tat DEMO-Chi-02', 'Noi dung DEMO-Chi-02', 'chi02.jpg', 'published', 7,
 (SELECT user_id FROM `user` WHERE username='user_chi'),
 (SELECT category_id FROM `category` WHERE category_name='Kinh doanh')),

('DEMO-Chi-03', 'Tom tat DEMO-Chi-03', 'Noi dung DEMO-Chi-03', 'chi03.jpg', 'draft', 0,
 (SELECT user_id FROM `user` WHERE username='user_chi'),
 (SELECT category_id FROM `category` WHERE category_name='Cong nghe')),

('DEMO-Dung-01', 'Tom tat DEMO-Dung-01', 'Noi dung DEMO-Dung-01', 'dung01.jpg', 'published', 20,
 (SELECT user_id FROM `user` WHERE username='user_dung'),
 (SELECT category_id FROM `category` WHERE category_name='The thao')),

('DEMO-Dung-02', 'Tom tat DEMO-Dung-02', 'Noi dung DEMO-Dung-02', 'dung02.jpg', 'pending', 3,
 (SELECT user_id FROM `user` WHERE username='user_dung'),
 (SELECT category_id FROM `category` WHERE category_name='Giai tri')),

('DEMO-Dung-03', 'Tom tat DEMO-Dung-03', 'Noi dung DEMO-Dung-03', 'dung03.jpg', 'published', 8,
 (SELECT user_id FROM `user` WHERE username='user_dung'),
 (SELECT category_id FROM `category` WHERE category_name='Kinh doanh')),

('DEMO-Hoa-01', 'Tom tat DEMO-Hoa-01', 'Noi dung DEMO-Hoa-01', 'hoa01.jpg', 'published', 13,
 (SELECT user_id FROM `user` WHERE username='user_hoa'),
 (SELECT category_id FROM `category` WHERE category_name='Cong nghe')),

('DEMO-Hoa-02', 'Tom tat DEMO-Hoa-02', 'Noi dung DEMO-Hoa-02', 'hoa02.jpg', 'published', 9,
 (SELECT user_id FROM `user` WHERE username='user_hoa'),
 (SELECT category_id FROM `category` WHERE category_name='The thao')),

('DEMO-Hoa-03', 'Tom tat DEMO-Hoa-03', 'Noi dung DEMO-Hoa-03', 'hoa03.jpg', 'draft', 1,
 (SELECT user_id FROM `user` WHERE username='user_hoa'),
 (SELECT category_id FROM `category` WHERE category_name='Giai tri'));

-- =====================================================
-- 4) Mỗi bài 2 comment; người comment KHÔNG trùng tác giả bài
-- =====================================================
INSERT INTO `comment` (`content`, `status`, `user_id`, `article_id`) VALUES
('Hay qua DEMO-An-01', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-An-01')),
('Dong gop DEMO-An-01', 'visible', (SELECT user_id FROM `user` WHERE username='user_binh'), (SELECT article_id FROM `article` WHERE title='DEMO-An-01')),

('Hay qua DEMO-An-02', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-An-02')),
('Dong gop DEMO-An-02', 'visible', (SELECT user_id FROM `user` WHERE username='user_binh'), (SELECT article_id FROM `article` WHERE title='DEMO-An-02')),

('Hay qua DEMO-An-03', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-An-03')),
('Dong gop DEMO-An-03', 'visible', (SELECT user_id FROM `user` WHERE username='user_binh'), (SELECT article_id FROM `article` WHERE title='DEMO-An-03')),

('Hay qua DEMO-Binh-01', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-Binh-01')),
('Dong gop DEMO-Binh-01', 'visible', (SELECT user_id FROM `user` WHERE username='user_chi'), (SELECT article_id FROM `article` WHERE title='DEMO-Binh-01')),

('Hay qua DEMO-Binh-02', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-Binh-02')),
('Dong gop DEMO-Binh-02', 'visible', (SELECT user_id FROM `user` WHERE username='user_chi'), (SELECT article_id FROM `article` WHERE title='DEMO-Binh-02')),

('Hay qua DEMO-Binh-03', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-Binh-03')),
('Dong gop DEMO-Binh-03', 'visible', (SELECT user_id FROM `user` WHERE username='user_chi'), (SELECT article_id FROM `article` WHERE title='DEMO-Binh-03')),

('Hay qua DEMO-Chi-01', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-Chi-01')),
('Dong gop DEMO-Chi-01', 'visible', (SELECT user_id FROM `user` WHERE username='user_dung'), (SELECT article_id FROM `article` WHERE title='DEMO-Chi-01')),

('Hay qua DEMO-Chi-02', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-Chi-02')),
('Dong gop DEMO-Chi-02', 'visible', (SELECT user_id FROM `user` WHERE username='user_dung'), (SELECT article_id FROM `article` WHERE title='DEMO-Chi-02')),

('Hay qua DEMO-Chi-03', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-Chi-03')),
('Dong gop DEMO-Chi-03', 'visible', (SELECT user_id FROM `user` WHERE username='user_dung'), (SELECT article_id FROM `article` WHERE title='DEMO-Chi-03')),

('Hay qua DEMO-Dung-01', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-Dung-01')),
('Dong gop DEMO-Dung-01', 'visible', (SELECT user_id FROM `user` WHERE username='user_hoa'), (SELECT article_id FROM `article` WHERE title='DEMO-Dung-01')),

('Hay qua DEMO-Dung-02', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-Dung-02')),
('Dong gop DEMO-Dung-02', 'visible', (SELECT user_id FROM `user` WHERE username='user_hoa'), (SELECT article_id FROM `article` WHERE title='DEMO-Dung-02')),

('Hay qua DEMO-Dung-03', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-Dung-03')),
('Dong gop DEMO-Dung-03', 'visible', (SELECT user_id FROM `user` WHERE username='user_hoa'), (SELECT article_id FROM `article` WHERE title='DEMO-Dung-03')),

('Hay qua DEMO-Hoa-01', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-Hoa-01')),
('Dong gop DEMO-Hoa-01', 'visible', (SELECT user_id FROM `user` WHERE username='user_an'), (SELECT article_id FROM `article` WHERE title='DEMO-Hoa-01')),

('Hay qua DEMO-Hoa-02', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-Hoa-02')),
('Dong gop DEMO-Hoa-02', 'visible', (SELECT user_id FROM `user` WHERE username='user_an'), (SELECT article_id FROM `article` WHERE title='DEMO-Hoa-02')),

('Hay qua DEMO-Hoa-03', 'visible', (SELECT user_id FROM `user` WHERE username='admin_web'), (SELECT article_id FROM `article` WHERE title='DEMO-Hoa-03')),
('Dong gop DEMO-Hoa-03', 'visible', (SELECT user_id FROM `user` WHERE username='user_an'), (SELECT article_id FROM `article` WHERE title='DEMO-Hoa-03'));

COMMIT;
