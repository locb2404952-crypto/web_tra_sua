<?php
session_start();
require_once 'db-connect.php';

$order_message = "";

// ========================================================
// XỬ LÝ ĐẶT MUA NHANH NGAY TẠI TRANG CHỦ (KHÔNG CẦN CHUYỂN TRANG)
// ========================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['quick_order'])) {
    $product_id   = intval($_POST['qo_product_id']);
    $quantity     = intval($_POST['qo_quantity']);
    $price        = floatval($_POST['qo_price']);
    $sugar_level  = intval($_POST['qo_sugar']);
    $ice_level    = intval($_POST['qo_ice']);
    $topping_note = mysqli_real_escape_string($conn, trim($_POST['qo_topping']));
    $ten_khach    = mysqli_real_escape_string($conn, trim($_POST['qo_name']));
    $sdt          = mysqli_real_escape_string($conn, trim($_POST['qo_phone']));
    $diachi       = mysqli_real_escape_string($conn, trim($_POST['qo_address']));

    if ($product_id <= 0 || $quantity <= 0 || empty($ten_khach) || empty($sdt) || empty($diachi)) {
        $order_message = "vui_long_nhap_du";
    } else {
        $total_order_amount = $price * $quantity;
        $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 2;

        $sql_order = "INSERT INTO orders (user_id, total_amount, customer_name, phone, address, status) 
                      VALUES ($user_id, $total_order_amount, '$ten_khach', '$sdt', '$diachi', 'pending')";

        if (mysqli_query($conn, $sql_order)) {
            $order_id = mysqli_insert_id($conn);
            $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price, sugar_level, ice_level, topping_note) 
                         VALUES ($order_id, $product_id, $quantity, $price, $sugar_level, $ice_level, '$topping_note')";
            $order_message = mysqli_query($conn, $sql_item) ? "thanh_cong" : "loi_chi_tiet_don";
        } else {
            $order_message = "loi_tao_don_hang";
        }
    }
}

// ========================================================
// KHUNG 1: TOP 3 SẢN PHẨM BÁN CHẠY NHẤT (TOÀN HỆ THỐNG)
// ========================================================
$sql_best_seller = "
    SELECT p.product_id, p.product_name, p.price, p.description, p.category_id, p.image_url, c.category_name,
           IFNULL(SUM(oi.quantity), 0) AS total_sold
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    GROUP BY p.product_id
    HAVING total_sold > 0
    ORDER BY total_sold DESC, p.product_id ASC
    LIMIT 3
";
$result_best_seller = mysqli_query($conn, $sql_best_seller);
$best_sellers = [];
$best_seller_ids = [];
if ($result_best_seller) {
    while ($row = mysqli_fetch_assoc($result_best_seller)) {
        $best_sellers[] = $row;
        $best_seller_ids[] = $row['product_id'];
    }
}
$exclude_ids = !empty($best_seller_ids) ? implode(',', $best_seller_ids) : '0';

// ========================================================
// KHUNG 2: SẢN PHẨM KHUYẾN MÃI TRONG TUẦN (DO ADMIN CHỌN Ở TRANG QUẢN LÝ)
// Nếu tuần nào admin không chọn sản phẩm khuyến mãi nào (bảng promotions rỗng/không có dòng active)
// thì mảng $promotions sẽ rỗng và khối này tự động không hiển thị.
// ========================================================
$sql_promo = "
    SELECT pr.promotion_id, pr.discount_percent, p.product_id, p.product_name, p.price, p.description, 
           p.category_id, p.image_url, c.category_name
    FROM promotions pr
    JOIN products p ON pr.product_id = p.product_id
    JOIN categories c ON p.category_id = c.category_id
    WHERE pr.is_active = 1 AND p.product_id NOT IN ($exclude_ids)
    ORDER BY pr.created_at DESC
";
$result_promo = mysqli_query($conn, $sql_promo);
$promotions = [];
$promo_ids = [];
if ($result_promo) {
    while ($row = mysqli_fetch_assoc($result_promo)) {
        $row['discounted_price'] = round($row['price'] * (1 - $row['discount_percent'] / 100));
        $promotions[] = $row;
        $promo_ids[] = $row['product_id'];
    }
}
$exclude_ids_2 = !empty(array_merge($best_seller_ids, $promo_ids)) ? implode(',', array_merge($best_seller_ids, $promo_ids)) : '0';

// ========================================================
// KHUNG 3: SẢN PHẨM MỚI ĐƯỢC THÊM VÀO GẦN ĐÂY NHẤT
// Mỗi khi admin thêm món mới ở trang quản lý, món đó sẽ tự động xuất hiện ở đây.
// ========================================================
$sql_new = "
    SELECT p.product_id, p.product_name, p.price, p.description, p.category_id, p.image_url, c.category_name
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id NOT IN ($exclude_ids_2)
    ORDER BY p.product_id DESC
    LIMIT 8
";
$result_new = mysqli_query($conn, $sql_new);
$new_products = [];
if ($result_new) {
    while ($row = mysqli_fetch_assoc($result_new)) {
        $new_products[] = $row;
    }
}

// Hàm chọn icon emoji hiển thị khi sản phẩm chưa có ảnh
function homie_icon($category_id) {
    $icon = "🧋";
    if ($category_id == 1) $icon = "🍟";
    if ($category_id == 2) $icon = "☕";
    if ($category_id == 4) $icon = "🍜";
    if ($category_id == 5) $icon = "🥑";
    if ($category_id == 7) $icon = "🍓";
    return $icon;
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

        /* Tiêu đề chung cho từng khung sản phẩm */
        .section-block-title {
            font-size: 26px; text-align: center; font-weight: bold;
            text-transform: uppercase; margin-bottom: 30px; position: relative;
        }
        .section-block-title i { margin: 0 8px; }

        .bestseller-box {
            background: #fff5f5; border: 2px dashed #ff7675; border-radius: 15px;
            padding: 25px; margin-bottom: 45px;
        }
        .bestseller-box .section-block-title { color: #d63031; }
        .bestseller-box .section-block-title i { color: #f1c40f; }

        .promo-box {
            background: #fff9f0; border: 2px dashed #e17055; border-radius: 15px;
            padding: 25px; margin-bottom: 45px;
        }
        .promo-box .section-block-title { color: #e17055; }
        .promo-box .section-block-title i { color: #e67e22; }

        .new-box {
            background: #f0f8ff; border: 2px dashed #0984e3; border-radius: 15px;
            padding: 25px; margin-bottom: 45px;
        }
        .new-box .section-block-title { color: #0984e3; }
        .new-box .section-block-title i { color: #00cec9; }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
        .product-card {
            background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            overflow: hidden; display: flex; flex-direction: column; border: 1px solid #f1f2f6; position: relative;
        }
        .badge-corner {
            position: absolute; top: 10px; left: 10px; color: white;
            padding: 4px 10px; font-size: 11px; font-weight: bold; border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 10;
        }
        .badge-hot { background: #e74c3c; }
        .badge-promo { background: #e17055; }
        .badge-new { background: #0984e3; }
        
        .product-image {
            width: 100%; height: 180px; background-color: #ffeaa7;
            display: flex; align-items: center; justify-content: center; font-size: 55px; user-select: none;
            overflow: hidden;
        }
        .product-image img {
            width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;
        }
        .product-card:hover .product-image img { transform: scale(1.05); }

        .product-info { padding: 15px; display: flex; flex-direction: column; flex-grow: 1; }
        .product-name { font-size: 17px; font-weight: bold; margin: 0 0 5px 0; color: #2d3436; }
        .product-desc { font-size: 13px; color: #636e72; margin: 0 0 15px 0; line-height: 1.4; flex-grow: 1; }
        .product-price-action { display: flex; justify-content: space-between; align-items: center; }
        .product-price { font-size: 17px; font-weight: bold; color: #e17055; }
        .original-price { text-decoration: line-through; color: #b2bec3; font-size: 13px; margin-right: 6px; }
        .sold-count { font-size: 12px; color: #7f8c8d; font-style: italic; background: #f1f2f6; padding: 2px 8px; border-radius: 10px; }

        .btn-buy-now {
            background-color: var(--primary-color); color: white; border: none;
            padding: 7px 15px; border-radius: 20px; font-weight: bold; text-decoration: none; font-size: 13px;
            cursor: pointer;
        }
        .btn-buy-now:hover { background-color: #ff5252; }
        .empty-block-msg { text-align: center; color: #7f8c8d; font-style: italic; padding: 10px; }

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

        /* ============================
           MODAL ĐẶT MUA NHANH (KHÔNG CHUYỂN TRANG)
           ============================ */
        .qo-modal {
            display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(26, 32, 44, 0.45); backdrop-filter: blur(4px);
            justify-content: center; align-items: center; overflow-y: auto; padding: 20px;
        }
        .qo-modal-content {
            background: #fff; padding: 30px; border-radius: 18px; width: 100%; max-width: 440px;
            animation: qoPopUp 0.2s ease-out; max-height: 90vh; overflow-y: auto;
        }
        @keyframes qoPopUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .qo-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        .qo-modal-header h3 { margin: 0; color: #d63031; font-size: 20px; }
        .qo-close { cursor: pointer; font-size: 26px; color: #b2bec3; line-height: 1; }
        .qo-close:hover { color: #d63031; }
        .qo-modal-content label { display: block; font-weight: 600; font-size: 14px; margin: 12px 0 6px 0; color: #2d3436; }
        .qo-modal-content input[type="text"], .qo-modal-content input[type="tel"] {
            width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px;
            outline: none; font-size: 14px; box-sizing: border-box;
        }
        .qo-radio-group { display: flex; flex-wrap: wrap; gap: 10px; font-size: 13px; font-weight: normal; }
        .qo-radio-group label { display: flex; align-items: center; gap: 4px; margin: 0; font-weight: normal; }
        .qo-qty-control { display: flex; align-items: center; gap: 10px; }
        .qo-qty-control button {
            width: 34px; height: 34px; border-radius: 50%; border: 1px solid var(--primary-color);
            background: white; color: var(--primary-color); font-size: 18px; font-weight: bold; cursor: pointer;
        }
        .qo-qty-control input { width: 60px; text-align: center; padding: 8px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .qo-total { margin-top: 18px; font-size: 16px; font-weight: bold; text-align: right; color: #d63031; }
        .qo-submit-btn {
            width: 100%; margin-top: 15px; background-color: var(--primary-color); color: white;
            border: none; padding: 13px; border-radius: 10px; font-size: 16px; font-weight: bold; cursor: pointer;
        }
        .qo-submit-btn:hover { background-color: #ff5252; }
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

    <!-- ================= KHUNG 1: BEST SELLER (TOP 3 BÁN CHẠY NHẤT) ================= -->
    <div class="bestseller-box">
        <h2 class="section-block-title"><i class="fa-solid fa-crown"></i> Top 3 Món Bán Chạy Nhất <i class="fa-solid fa-crown"></i></h2>

        <?php if (empty($best_sellers)): ?>
            <p class="empty-block-msg">Hệ thống chưa ghi nhận lượt đặt mua nào. Hãy là người mua đầu tiên nhé!</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($best_sellers as $prod): ?>
                    <div class="product-card">
                        <span class="badge-corner badge-hot"><i class="fa-solid fa-fire"></i> Bán Chạy</span>
                        <div class="product-image">
                            <?php if (!empty($prod['image_url']) && $prod['image_url'] != 'default.png' && file_exists($prod['image_url'])): ?>
                                <img src="<?= htmlspecialchars($prod['image_url']) ?>" alt="<?= htmlspecialchars($prod['product_name']) ?>">
                            <?php else: ?>
                                <?= homie_icon($prod['category_id']) ?>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= htmlspecialchars($prod['product_name']) ?></h3>
                            <p class="product-desc"><?= htmlspecialchars($prod['description']) ?></p>
                            <div class="product-price-action">
                                <div>
                                    <span class="product-price"><?= number_format($prod['price'], 0, ',', '.') ?>đ</span>
                                    <br>
                                    <span class="sold-count">Đã bán: <?= $prod['total_sold'] ?> ly/đĩa</span>
                                </div>
                                <button type="button" class="btn-buy-now" onclick="moQuickOrder(<?= $prod['product_id'] ?>, '<?= addslashes($prod['product_name']) ?>', <?= $prod['price'] ?>, <?= $prod['category_id'] ?>)">Đặt Mua</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ================= KHUNG 2: SẢN PHẨM KHUYẾN MÃI TRONG TUẦN ================= -->
    <?php if (!empty($promotions)): ?>
    <div class="promo-box">
        <h2 class="section-block-title"><i class="fa-solid fa-tags"></i> Ưu Đãi Tuần Này <i class="fa-solid fa-tags"></i></h2>
        <div class="product-grid">
            <?php foreach ($promotions as $prod): ?>
                <div class="product-card">
                    <span class="badge-corner badge-promo"><i class="fa-solid fa-percent"></i> Giảm <?= $prod['discount_percent'] ?>%</span>
                    <div class="product-image">
                        <?php if (!empty($prod['image_url']) && $prod['image_url'] != 'default.png' && file_exists($prod['image_url'])): ?>
                            <img src="<?= htmlspecialchars($prod['image_url']) ?>" alt="<?= htmlspecialchars($prod['product_name']) ?>">
                        <?php else: ?>
                            <?= homie_icon($prod['category_id']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($prod['product_name']) ?></h3>
                        <p class="product-desc"><?= htmlspecialchars($prod['description']) ?></p>
                        <div class="product-price-action">
                            <div>
                                <span class="original-price"><?= number_format($prod['price'], 0, ',', '.') ?>đ</span>
                                <br>
                                <span class="product-price"><?= number_format($prod['discounted_price'], 0, ',', '.') ?>đ</span>
                            </div>
                            <button type="button" class="btn-buy-now" onclick="moQuickOrder(<?= $prod['product_id'] ?>, '<?= addslashes($prod['product_name']) ?>', <?= $prod['discounted_price'] ?>, <?= $prod['category_id'] ?>)">Đặt Mua</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ================= KHUNG 3: SẢN PHẨM MỚI ================= -->
    <?php if (!empty($new_products)): ?>
    <div class="new-box">
        <h2 class="section-block-title"><i class="fa-solid fa-sparkles"></i> Món Mới Ra Mắt <i class="fa-solid fa-sparkles"></i></h2>
        <div class="product-grid">
            <?php foreach ($new_products as $prod): ?>
                <div class="product-card">
                    <span class="badge-corner badge-new"><i class="fa-solid fa-star"></i> Mới</span>
                    <div class="product-image">
                        <?php if (!empty($prod['image_url']) && $prod['image_url'] != 'default.png' && file_exists($prod['image_url'])): ?>
                            <img src="<?= htmlspecialchars($prod['image_url']) ?>" alt="<?= htmlspecialchars($prod['product_name']) ?>">
                        <?php else: ?>
                            <?= homie_icon($prod['category_id']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($prod['product_name']) ?></h3>
                        <p class="product-desc"><?= htmlspecialchars($prod['description']) ?></p>
                        <div class="product-price-action">
                            <span class="product-price"><?= number_format($prod['price'], 0, ',', '.') ?>đ</span>
                            <button type="button" class="btn-buy-now" onclick="moQuickOrder(<?= $prod['product_id'] ?>, '<?= addslashes($prod['product_name']) ?>', <?= $prod['price'] ?>, <?= $prod['category_id'] ?>)">Đặt Mua</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

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

<!-- ================= MODAL ĐẶT MUA NHANH (DÙNG CHUNG CHO CẢ 3 KHUNG) ================= -->
<div id="quickOrderModal" class="qo-modal">
    <div class="qo-modal-content">
        <div class="qo-modal-header">
            <h3 id="qoProductName">Tên món</h3>
            <span class="qo-close" onclick="dongQuickOrder()">&times;</span>
        </div>
        <form action="" method="POST" id="quickOrderForm">
            <input type="hidden" name="quick_order" value="1">
            <input type="hidden" name="qo_product_id" id="qo_product_id">
            <input type="hidden" name="qo_price" id="qo_price">

            <div id="qoDrinkOptions">
                <label>Mức đường:</label>
                <div class="qo-radio-group">
                    <label><input type="radio" name="qo_sugar" value="0"> 0%</label>
                    <label><input type="radio" name="qo_sugar" value="30"> 30%</label>
                    <label><input type="radio" name="qo_sugar" value="50"> 50%</label>
                    <label><input type="radio" name="qo_sugar" value="70"> 70%</label>
                    <label><input type="radio" name="qo_sugar" value="100" id="qoSugar100" checked> 100%</label>
                </div>
                <label>Mức đá:</label>
                <div class="qo-radio-group">
                    <label><input type="radio" name="qo_ice" value="0"> 0%</label>
                    <label><input type="radio" name="qo_ice" value="30"> 30%</label>
                    <label><input type="radio" name="qo_ice" value="50"> 50%</label>
                    <label><input type="radio" name="qo_ice" value="70"> 70%</label>
                    <label><input type="radio" name="qo_ice" value="100" id="qoIce100" checked> 100%</label>
                </div>
            </div>

            <label id="qoToppingLabel">Topping yêu cầu thêm:</label>
            <input type="text" name="qo_topping" id="qo_topping_note" placeholder="Ví dụ: thêm trân châu, thạch...">

            <label>Số lượng:</label>
            <div class="qo-qty-control">
                <button type="button" onclick="qoGiamQty()">-</button>
                <input type="number" name="qo_quantity" id="qo_quantity" value="1" min="1" readonly>
                <button type="button" onclick="qoTangQty()">+</button>
            </div>

            <label>Họ và tên người nhận:</label>
            <input type="text" name="qo_name" required placeholder="Nhập họ tên của bạn">

            <label>Số điện thoại:</label>
            <input type="text" name="qo_phone" required placeholder="Nhập số điện thoại liên hệ">

            <label>Địa chỉ giao hàng:</label>
            <input type="text" name="qo_address" required placeholder="Nhập địa chỉ nhận hàng">

            <div class="qo-total">Tổng tiền: <span id="qoTotalDisplay">0đ</span></div>

            <button type="submit" class="qo-submit-btn">✅ Xác Nhận Đặt Hàng</button>
        </form>
    </div>
</div>

<script>
let qoActiveProduct = {};

function moQuickOrder(id, name, price, categoryId) {
    qoActiveProduct = { id: id, price: parseFloat(price) };

    document.getElementById('qoProductName').innerText = name;
    document.getElementById('qo_product_id').value = id;
    document.getElementById('qo_price').value = price;
    document.getElementById('qo_quantity').value = 1;
    document.getElementById('qo_topping_note').value = '';
    document.getElementById('qoSugar100').checked = true;
    document.getElementById('qoIce100').checked = true;

    let drinkOptions = document.getElementById('qoDrinkOptions');
    let toppingLabel = document.getElementById('qoToppingLabel');
    let toppingInput = document.getElementById('qo_topping_note');

    // Danh mục 1 (Ăn Vặt) và 4 (Mỳ Cay - Lẩu) là món ăn, không cần chọn đường/đá
    if (categoryId == 1 || categoryId == 4) {
        drinkOptions.style.display = 'none';
        toppingLabel.innerText = 'Ghi chú cho món ăn:';
        toppingInput.placeholder = 'Ví dụ: làm cay nhiều, không hành...';
    } else {
        drinkOptions.style.display = 'block';
        toppingLabel.innerText = 'Topping yêu cầu thêm:';
        toppingInput.placeholder = 'Ví dụ: thêm trân châu, thạch...';
    }

    qoTinhTien();
    document.getElementById('quickOrderModal').style.display = 'flex';
}

function dongQuickOrder() {
    document.getElementById('quickOrderModal').style.display = 'none';
}

function qoTangQty() {
    let input = document.getElementById('qo_quantity');
    input.value = parseInt(input.value) + 1;
    qoTinhTien();
}
function qoGiamQty() {
    let input = document.getElementById('qo_quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        qoTinhTien();
    }
}
function qoTinhTien() {
    let qty = parseInt(document.getElementById('qo_quantity').value);
    let total = qoActiveProduct.price * qty;
    document.getElementById('qoTotalDisplay').innerText = total.toLocaleString('vi-VN') + 'đ';
}

window.addEventListener('click', function (event) {
    let modal = document.getElementById('quickOrderModal');
    if (event.target == modal) dongQuickOrder();
});
</script>

<?php if ($order_message == "thanh_cong"): ?>
    <script>alert('🎉 Đặt hàng thành công! Homie sẽ liên hệ xác nhận với bạn sớm nhất.');</script>
<?php elseif ($order_message == "vui_long_nhap_du"): ?>
    <script>alert('⚠️ Vui lòng điền đầy đủ thông tin (Họ tên, SĐT, Địa chỉ) để Homie giao hàng nhé!');</script>
<?php elseif ($order_message == "loi_chi_tiet_don" || $order_message == "loi_tao_don_hang"): ?>
    <script>alert('❌ Có lỗi hệ thống xảy ra khi lưu đơn hàng. Vui lòng thử lại sau!');</script>
<?php endif; ?>

<footer>
    <p>© 2026 Trà Sữa Homie - All Rights Reserved. Thiết kế bởi nhóm dự án web_tra_sua.</p>
</footer>
<?php include 'footer.php'; ?>
</body>
</html>
<!-- okoko -->