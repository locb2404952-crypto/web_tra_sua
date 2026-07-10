<?php
session_start();
require_once 'db-connect.php';

// ========================================================
// THUẬT TOÁN SQL: LẤY 2 MÓN CÓ SỐ LƯỢNG MUA NHIỀU NHẤT CỦA TỪNG DANH MỤC
// ========================================================
$sql_best_seller = "
    SELECT p.product_id, p.product_name, p.price, p.description, p.category_id, c.category_name, 
           IFNULL(SUM(oi.quantity), 0) as total_sold
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    GROUP BY p.product_id
    HAVING (
        SELECT COUNT(*) 
        FROM (
            SELECT p2.category_id, p2.product_id, IFNULL(SUM(oi2.quantity), 0) as sub_sold
            FROM products p2
            LEFT JOIN order_items oi2 ON p2.product_id = oi2.product_id
            GROUP BY p2.product_id
        ) as temp
        WHERE temp.category_id = p.category_id 
          AND (temp.sub_sold > total_sold OR (temp.sub_sold = total_sold AND temp.product_id < p.product_id))
    ) < 2
    ORDER BY p.category_id ASC, total_sold DESC, p.product_id ASC
";

$result_best_seller = mysqli_query($conn, $sql_best_seller);

// Nhóm các sản phẩm best seller theo danh mục để dễ hiển thị giao diện
$best_sellers_by_cat = [];
if ($result_best_seller) {
    while ($row = mysqli_fetch_assoc($result_best_seller)) {
        $best_sellers_by_cat[$row['category_name']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trà Sữa Homie - Trang Chủ & Best Seller</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff7675;
            --secondary-color: #fab1a0;
            --dark-color: #2d3436;
            --light-color: #f9f9f9;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0; padding: 0;
            background-color: var(--light-color); color: var(--dark-color);
        }

        header {
            background-color: var(--primary-color); color: white;
            padding: 30px 20px; text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); position: relative;
        }

        header h1 { margin: 0; font-size: 32px; letter-spacing: 1px; }
        header p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.9; }

        .auth-buttons { position: absolute; top: 30px; right: 30px; }
        .auth-buttons a {
            color: white; text-decoration: none; margin-left: 15px;
            font-weight: bold; font-size: 14px; background-color: rgba(255, 255, 255, 0.2);
            padding: 6px 14px; border-radius: 20px; transition: all 0.3s ease;
        }
        .auth-buttons a:hover { background-color: white; color: var(--primary-color); }
        .auth-buttons span { color: white; font-weight: bold; margin-right: 10px; }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://img.freepik.com/free-photo/delicious-bubble-tea-table_23-2150764127.jpg') no-repeat center center/cover;
            height: 350px; border-radius: 15px; display: flex; flex-direction: column;
            justify-content: center; align-items: center; color: white; text-align: center;
            padding: 20px; box-shadow: 0 6px 20px rgba(0,0,0,0.15); margin-bottom: 40px;
        }
        .hero-section h2 { font-size: 42px; margin: 0 0 15px 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.6); }
        .hero-section p { font-size: 20px; margin: 0 0 30px 0; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); }
        
        .btn-menu-now {
            background-color: #fff; color: var(--primary-color); padding: 14px 35px;
            border-radius: 30px; font-size: 18px; font-weight: bold; text-decoration: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.3s ease;
        }
        .btn-menu-now:hover { background-color: var(--primary-color); color: white; transform: scale(1.05); }

        /* Khối Best Seller */
        .bestseller-title {
            font-size: 26px; color: #d63031; text-align: center; font-weight: bold;
            text-transform: uppercase; margin-bottom: 30px; position: relative;
        }
        .bestseller-title i { color: #f1c40f; margin: 0 8px; }

        .bestseller-box {
            background: #fff5f5; border: 2px dashed #ff7675; border-radius: 15px;
            padding: 25px; margin-bottom: 45px;
        }

        .bs-cat-group { margin-bottom: 25px; }
        .bs-cat-name { font-size: 16px; font-weight: bold; color: #e17055; margin-bottom: 12px; border-left: 4px solid #ff7675; padding-left: 10px; }
        
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .product-card {
            background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            overflow: hidden; display: flex; flex-direction: column; border: 1px solid #f1f2f6; position: relative;
        }
        .badge-hot {
            position: absolute; top: 10px; left: 10px; background: #e74c3c; color: white;
            padding: 4px 10px; font-size: 11px; font-weight: bold; border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 10;
        }
        .product-image {
            width: 100%; height: 130px; background-color: #ffeaa7;
            display: flex; align-items: center; justify-content: center; font-size: 45px; user-select: none;
        }
        .product-info { padding: 15px; display: flex; flex-direction: column; flex-grow: 1; }
        .product-name { font-size: 17px; font-weight: bold; margin: 0 0 5px 0; color: #2d3436; }
        .product-desc { font-size: 13px; color: #636e72; margin: 0 0 15px 0; line-height: 1.4; flex-grow: 1; }
        .product-price-action { display: flex; justify-content: space-between; align-items: center; }
        .product-price { font-size: 17px; font-weight: bold; color: #e17055; }
        .sold-count { font-size: 12px; color: #7f8c8d; font-style: italic; background: #f1f2f6; padding: 2px 8px; border-radius: 10px; }

        .btn-buy-now {
            background-color: var(--primary-color); color: white; border: none;
            padding: 7px 15px; border-radius: 20px; font-weight: bold; text-decoration: none; font-size: 13px;
        }
        .btn-buy-now:hover { background-color: #ff5252; }

        /* Khối tính năng nổi bật */
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .feature-card {
            background: white; padding: 30px; border-radius: 12px; text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f1f2f6;
        }
        .feature-card i { font-size: 45px; color: var(--primary-color); margin-bottom: 15px; }
        .feature-card h3 { margin: 10px 0; font-size: 20px; }
        .feature-card p { color: #636e72; font-size: 14px; line-height: 1.6; }

        footer { text-align: center; padding: 30px; color: #7f8c8d; font-size: 14px; margin-top: 50px; border-top: 1px solid #e1e2e6; }
    </style>
</head>
<body>

<header>
    <h1>🧋 Trà Sữa Homie 🧋</h1>
    <p>Thơm ngon từng giọt - Đậm vị yêu thương</p>
    <div class="main-menu" style="margin-top: 15px; margin-bottom: 5px;">
        <a href="trang-chu.php" style="color: white; margin-right: 20px; text-decoration: none; font-weight: bold;"><i class="fa-solid fa-house"></i> Trang Chủ</a>
        <a href="index.php" style="color: white; margin-right: 20px; text-decoration: none; font-weight: bold;"><i class="fa-solid fa-utensils"></i> Thực Đơn</a>
        <a href="lien-he.php" style="color: white; text-decoration: none; font-weight: bold;"><i class="fa-solid fa-envelope"></i> Liên Hệ</a>
    </div>
    
    <div class="auth-buttons">
        <?php if(isset($_SESSION['username'])): ?>
            <span><i class="fa-solid fa-user"></i> Xin chào, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <a href="dang-xuat.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng Xuất</a>
        <?php else: ?>
            <a href="dang-nhap.php"><i class="fa-solid fa-user-lock"></i> Đăng Nhập</a>
            <a href="dang-ky.php"><i class="fa-solid fa-user-plus"></i> Đăng Ký</a>
        <?php endif; ?>
    </div>
</header>

<div class="container">
    <div class="hero-section">
        <h2>Chào Mừng Đến Với Homie Tea</h2>
        <p>Không gian ngọt ngào, đồ uống chuẩn vị, nguyên liệu 100% tự nhiên!</p>
        <a href="index.php" class="btn-menu-now"><i class="fa-solid fa-circle-play"></i> Xem Thực Đơn & Đặt Món Ngay</a>
    </div>

    <div class="bestseller-box">
        <h2 class="bestseller-title"><i class="fa-solid fa-crown"></i> Gợi Ý Món Bán Chạy Nhất (Best Seller) <i class="fa-solid fa-crown"></i></h2>
        
        <?php if (empty($best_sellers_by_cat)): ?>
            <p style="text-align: center; color: #7f8c8d; font-style: italic;">Hệ thống chưa ghi nhận lượt đặt mua nào. Hãy là người mua đầu tiên nhé!</p>
        <?php else: ?>
            <?php foreach ($best_sellers_by_cat as $cat_name => $products): ?>
                <div class="bs-cat-group">
                    <div class="bs-cat-name">Danh mục: <?= htmlspecialchars($cat_name) ?></div>
                    <div class="product-grid">
                        <?php foreach ($products as $prod): 
                            $icon = "🧋";
                            if ($prod['category_id'] == 1) $icon = "🍟";       
                            if ($prod['category_id'] == 2) $icon = "☕";       
                            if ($prod['category_id'] == 4) $icon = "🍜";       
                            if ($prod['category_id'] == 5) $icon = "🥑";       
                            if ($prod['category_id'] == 7) $icon = "🍓";       
                        ?>
                            <div class="product-card">
                                <span class="badge-hot"><i class="fa-solid fa-fire"></i> Bán Chạy</span>
                                <div class="product-image"><?= $icon ?></div>
                                <div class="product-info">
                                    <h3 class="product-name"><?= htmlspecialchars($prod['product_name']) ?></h3>
                                    <p class="product-desc"><?= htmlspecialchars($prod['description']) ?></p>
                                    <div class="product-price-action">
                                        <div>
                                            <span class="product-price"><?= number_format($prod['price'], 0, ',', '.') ?>đ</span>
                                            <br>
                                            <span class="sold-count">Đã bán: <?= $prod['total_sold'] ?> ly/đĩa</span>
                                        </div>
                                        <a href="index.php" class="btn-buy-now">Đặt Mua</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <i class="fa-solid fa-leaf"></i>
            <h3>Nguyên Liệu Sạch</h3>
            <p>Trà và topping của chúng tôi được chọn lọc kỹ càng từ nông trại, chế biến và sử dụng trong ngày bảo đảm độ tươi ngon tuyệt đối.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-truck-fast"></i>
            <h3>Giao Hàng Siêu Tốc</h3>
            <p>Đội ngũ shipper của Homie luôn túc trực để giao đến tay bạn những ly trà sữa mát lạnh, thơm ngon trong thời gian nhanh nhất.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-heart"></i>
            <h3>Phục Vụ Tận Tâm</h3>
            <p>Với tiêu chí đặt trải nghiệm khách hàng lên hàng đầu, mỗi sản phẩm trao đi là một niềm gửi gắm yêu thương từ Homie.</p>
        </div>
    </div>
</div>

<footer>
    <p>© 2026 Trà Sữa Homie - All Rights Reserved. Thiết kế bởi nhóm dự án web_tra_sua.</p>
</footer>
<?php include 'footer.php'; ?>
</body>
</html>
