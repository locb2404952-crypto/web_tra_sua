USE `web_tra_sua`;

-- Thêm cột created_at cho bảng products để biết món nào được đăng gần đây nhất
-- (Các món cũ có sẵn sẽ nhận giá trị = thời điểm chạy lệnh này, món thêm sau sẽ tự có thời gian đúng)
ALTER TABLE `products`
ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `image_url`;