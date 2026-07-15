USE `web_tra_sua`;

-- Xóa sản phẩm test (dùng để lập trình viên thử nghiệm) ra khỏi database đang chạy
DELETE FROM `products` WHERE `product_name` = 'Sản phẩm test';