USE `web_tra_sua`;

-- 1. Tài khoản 
INSERT INTO `users` (`full_name`, `email`, `password`, `phone`, `role`) VALUES 
('Quản Trị Viên', 'admin@trasua.com', '123456', '0999999999', 'admin'),
('Nguyễn Khách Hàng', 'khachhang@gmail.com', '123456', '0912345678', 'customer');


INSERT INTO `categories` (`category_id`, `category_name`) VALUES 
(1, 'Ăn Vặt'),
(2, 'Cà Phê'),
(3, 'Khác'),
(4, 'Mỳ Cay - Lẩu'),
(5, 'Sinh Tố'),
(6, 'Trà Sữa'),
(7, 'Trái Cây Tươi');


INSERT INTO `products` (`category_id`, `product_name`, `price`, `description`) VALUES 
-- Ăn Vặt
(1, 'Combo đồ chiên', 55000.00, 'Gồm cá viên, bò viên, xúc xích, khoai tây chiên'),
(1, 'Bánh tráng trộn', 20000.00, 'Bánh tráng trộn mối tôm, hành phi, trứng cút, khô bò'),
(1, 'Bánh gạo cay', 35000.00, 'Tokbokki chuẩn vị Hàn Quốc sốt cay ngọt'),
(1, 'Cá viên sốt mayonnaise', 25000.00, 'Cá viên chiên giòn rụm kèm sốt béo ngậy'),
(1, 'Mỳ Ý', 45000.00, 'Mỳ Ý sốt bò băm cà chua thơm ngon'),
(1, 'Gà rán', 30000.00, '1 Đùi/Cánh gà rán truyền thống giòn tan'),
(1, 'Gà rán sốt cay', 35000.00, 'Gà rán phủ sốt cay ngọt kiểu Hàn Quốc'),
(1, 'Hotdog', 25000.00, 'Hotdog xúc xích phô mai kéo sợi'),
(1, 'Khoai tây lắc phô mai', 28000.00, 'Khoai tây chiên vàng lắc bột phô mai mặn ngọt'),
(1, 'Kimbap', 30000.00, 'Cơm cuộn Hàn Quốc đầy đủ nhân thịt, trứng, rau củ'),
(1, 'Phô mai que', 25000.00, 'Phô mai que chiên xù giòn béo ngậy'),
(1, 'Xúc xích chiên', 15000.00, 'Xúc xích đức chiên nóng hổi'),

-- Cà Phê
(2, 'Cà phê đen đá', 18000.00, 'Cà phê rang xay nguyên chất đậm vị'),
(2, 'Cà phê muối', 25000.00, 'Cà phê kết hợp lớp kem sữa mặn béo ngậy'),
(2, 'Cà phê sữa', 20000.00, 'Cà phê pha sữa đặc truyền thống'),
(2, 'Cà phê sữa tươi', 22000.00, 'Sữa tươi hòa quyện cùng cốt cà phê thơm nồng'),

-- Khác
(3, 'Sản phẩm test', 1000.00, 'Dành cho lập trình viên chạy thử nghiệm hệ thống'),
(3, 'Yaourt đá', 20000.00, 'Sữa chua đánh đá chanh thanh mát'),
(3, 'Yaourt dâu', 25000.00, 'Sữa chua kết hợp mứt dâu tây ngọt lịm'),
(3, 'Cacao sữa', 25000.00, 'Cacao đậm đà pha sữa nóng hoặc đá'),
(3, 'Cacao Latte', 30000.00, 'Cacao kết hợp sữa tươi đánh bọt mịn'),
(3, 'Cacao kem muối', 35000.00, 'Cacao đá kết hợp lớp màng kem muối mặn béo'),
(3, 'Matcha Latte', 32000.00, 'Trà xanh Nhật Bản nguyên chất cùng sữa tươi'),

-- Mỳ Cay - Lẩu
(4, 'Lẩu kim chi', 120000.00, 'Nồi lẩu kim chi chua cay đầy đủ thịt bò, nấm, đậu hũ'),
(4, 'Lẩu Thái', 120000.00, 'Lẩu Thái hải sản chua cay chuẩn vị Tomyum'),
(4, 'Mỳ cay bò', 45000.00, 'Mỳ cay Hàn Quốc nấu thịt bò tươi, chọn cấp độ từ 0-7'),
(4, 'Mỳ cay hải sản', 45000.00, 'Mỳ cay nấu tôm, mực, cá viên và nấm kim châm'),
(4, 'Mỳ cay thập cẩm', 50000.00, 'Sự kết hợp giữa thịt bò, hải sản và xúc xích'),

-- Sinh Tố
(5, 'Sinh tố bơ', 35000.00, 'Bơ sáp đắc lắc xay sữa đặc béo ngậy'),
(5, 'Sinh tố dâu', 35000.00, 'Dâu tây Đà Lạt tươi ngon xay nhuyễn'),
(5, 'Sinh tố mãng cầu', 32000.00, 'Mãng cầu gai chua ngọt thanh mát'),
(5, 'Sinh tố sầu riêng', 45000.00, 'Hương vị sầu riêng đậm đà thơm nức mũi'),
(5, 'Sinh tố kiwi', 38000.00, 'Kiwi xanh nhập khẩu giàu vitamin C'),
(5, 'Sinh tố việt quất', 40000.00, 'Quả việt quất chua ngọt thơm dịu'),
(5, 'Sinh tố xoài', 32000.00, 'Xoài chín vàng ngọt lịm tự nhiên'),
(5, 'Sinh tố socola', 35000.00, 'Socola xay cùng đá và sữa béo ngậy'),

-- Trà Sữa
(6, 'Trà sữa khoai môn', 35000.00, 'Trà sữa thơm mùi khoai môn bùi béo'),
(6, 'Trà sữa Macchiato', 38000.00, 'Trà sữa truyền thống phủ lớp kem sữa dày mịn'),
(6, 'Trà sữa matcha', 35000.00, 'Trà sữa vị trà xanh đậm đà thanh mát'),
(6, 'Trà sữa truyền thống', 30000.00, 'Hương vị hồng trà sữa đậm đà quen thuộc'),
(6, 'Trà sữa Macchiato sô cô la', 40000.00, 'Sự kết hợp giữa vị đắng nhẹ sô cô la và kem béo'),
(6, 'Trà sữa trân châu đường đen', 40000.00, 'Sữa tươi/Trà sữa kết hợp trân châu nấu đường đen quanh ly'),
(6, 'Trà sữa sô cô la', 35000.00, 'Trà sữa vị sô cô la đậm đà'),
(6, 'Trà sữa trân châu trắng', 35000.00, 'Trà sữa truyền thống đi kèm trân châu trắng giòn sần sật'),

-- Trái Cây Tươi
(7, 'Hồng trà', 20000.00, 'Hồng trà thuần túy thanh mát ngọt dịu'),
(7, 'Trà đào', 30000.00, 'Trà đào thơm nức kèm lát đào ngâm giòn ngọt'),
(7, 'Trà dâu', 30000.00, 'Trà dâu tươi mát kèm dâu tây xắt nhỏ'),
(7, 'Trà dưa lưới', 32000.00, 'Hương vị dưa lưới thanh nhẹ giải nhiệt tốt'),
(7, 'Trà tắc', 15000.00, 'Trà tắc quốc dân chua ngọt giải khát nhanh'),
(7, 'Trà trái cây nhiệt đới', 40000.00, 'Tổng hợp các loại trái cây tươi như cam, dưa hấu, chanh leo'),
(7, 'Trà vải', 32000.00, 'Trà thanh nhẹ kết hợp những quả vải ngâm mọng nước'),
(7, 'Trà táo', 30000.00, 'Trà vị táo xanh chua nhẹ kích thích vị giác'),
(7, 'Trà dâu tằm', 32000.00, 'Trà dâu tằm màu tím đẹp mắt ngọt thanh'),
(7, 'Trà hoa atiso đỏ', 30000.00, 'Trà hoa bụp giấm (Atiso đỏ) chua thanh, mát gan'),
(7, 'Trà ổi', 32000.00, 'Trà ổi hồng thơm lừng hot trend');


INSERT INTO `orders` (`user_id`, `total_amount`, `status`) VALUES 
(2, 65000.00, 'pending');

INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `sugar_level`, `ice_level`, `topping_note`, `price`) VALUES 
(1, 31, 1, 50, 70, 'Thêm Trân châu đường đen', 35000.00),
(1, 34, 1, 100, 100, NULL, 30000.00);