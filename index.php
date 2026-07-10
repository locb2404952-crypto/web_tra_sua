<?php
session_start(); // Kích hoạt Session để lấy thông tin người dùng nếu đã đăng nhập từ dang-nhap.php
require_once 'db-connect.php';

$thong_bao = ""; // Biến lưu trạng thái hiển thị thông báo

// ========================================================
// XỬ LÝ KHI KHÁCH HÀNG BẤM NÚT "XÁC NHẬN ĐẶT HÀNG" (FORM SUBMIT NHIỀU MÓN) Nhat Huy
// ========================================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_dat_hang'])) {
    $ten_khach = mysqli_real_escape_string($conn, $_POST['khach_ten']);
    $sdt = mysqli_real_escape_string($conn, $_POST['khach_sdt']);
    $diachi = mysqli_real_escape_string($conn, $_POST['khach_diachi']);
    // Nhận chuỗi JSON chứa danh sách tất cả các món ăn trong giỏ hàng
    $cart_data_json = $_POST['cart_data'];
    $cart_items = json_decode($cart_data_json, true);

    if (empty($ten_khach) || empty($sdt) || empty($cart_items) ||empty($diachi)) {
        $thong_bao = "vui_long_nhap_du";
    } else {
        // 1. Tính toán tổng tiền thực tế của toàn bộ đơn hàng
        $total_order_amount = 0;
        foreach ($cart_items as $item) {
            $total_order_amount += floatval($item['price']) * intval($item['quantity']);
        }

        // Lấy mã user_id nếu khách đã đăng nhập, nếu chưa mặc định là 2 (Khách vãng lai mẫu)
        $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 2;

        // Bước A: Chèn thông tin tổng quan vào bảng orders
        $sql_order = "INSERT INTO orders (user_id, total_amount, customer_name, phone, address, status) 
                      VALUES ($user_id, $total_order_amount, '$ten_khach', '$sdt', '$diachi', 'pending')";
        if (mysqli_query($conn, $sql_order)) {
            // Lấy ra mã order_id tự động tăng vừa chèn ở trên để làm khóa ngoại
            $order_id = mysqli_insert_id($conn);
            $error_flag = false;

            // Bước B: Chạy vòng lặp chèn từng món ăn trong giỏ hàng vào bảng order_items
            foreach ($cart_items as $item) {
                $product_id = intval($item['id']);
                $quantity = intval($item['quantity']);
                $price = floatval($item['price']);
                $sugar_level = intval($item['sugar']);
                $ice_level = intval($item['ice']);
                $topping_note = mysqli_real_escape_string($conn, $item['topping']);

                $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price, sugar_level, ice_level, topping_note) 
                             VALUES ($order_id, $product_id, $quantity, $price, $sugar_level, $ice_level, '$topping_note')";
                
                if (!mysqli_query($conn, $sql_item)) {
                    $error_flag = true;
                    break;
                }
            }
            
            if (!$error_flag) {
                $thong_bao = "thanh_cong";
            } else {
                $thong_bao = "loi_chi_tiet_don";
            }
        } else {
            $thong_bao = "loi_tao_don_hang";
        }
    }
}

// 2. LẤY DANH SÁCH DANH MỤC SẢN PHẨM RA GIAO DIỆN
$sql_categories = "SELECT * FROM categories";
$result_categories = mysqli_query($conn, $sql_categories);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trà Sữa Homie - Thực Đơn Gọi Món</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff7675;   /* Màu hồng cam san hô ngọt ngào */
            --secondary-color: #fab1a0; /* Màu cam nhạt phối hợp */
            --dark-color: #2d3436;      /* Màu chữ tối */
            --light-color: #f9f9f9;     /* Màu nền sáng */
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
        .category-section { margin-bottom: 5px; }
        .category-title {
            font-size: 24px; color: #d63031; border-bottom: 3px solid var(--primary-color);
            padding-bottom: 6px; margin-bottom: 25px; display: inline-block;
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }

        .product-card {
            background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex; flex-direction: column; border: 1px solid #f1f2f6;
        }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(255, 118, 117, 0.15); }

        .product-image {
            width: 100%; height: 160px; background-color: #ffeaa7;
            display: flex; align-items: center; justify-content: center; font-size: 55px; user-select: none;
            overflow: hidden;
        }
        .product-image img {
            width: 100%; height: 100%; object-fit: cover;
        }

        .product-info { padding: 20px; display: flex; flex-direction: column; flex-grow: 1; }
        .product-name { font-size: 19px; font-weight: bold; margin: 0 0 8px 0; color: #2d3436; }
        .product-desc { font-size: 14px; color: #636e72; margin: 0 0 20px 0; line-height: 1.4; flex-grow: 1; }
        .product-price-action { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
        .product-price { font-size: 19px; font-weight: bold; color: #e17055; }

        .action-buttons-group { display: flex; gap: 6px; }
        
        .btn-select {
            background-color: var(--primary-color); color: white; border: none;
            padding: 9px 20px; border-radius: 25px; font-weight: bold; cursor: pointer;
            box-shadow: 0 3px 8px rgba(255, 118, 117, 0.3); transition: background 0.2s ease;
        }
        .btn-select:hover { background-color: #ff5252; }

        .btn-mini-cart {
            background-color: #ffeaa7; color: #d63031; border: none;
            width: 38px; height: 38px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; font-size: 16px; cursor: pointer;
            box-shadow: 0 3px 8px rgba(0,0,0,0.05); transition: all 0.2s ease;
        }
        .btn-mini-cart:hover { background-color: var(--primary-color); color: white; }

        /* MODAL OPTIONS CHO TỪNG MÓN */
        .modal {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.55); justify-content: center; align-items: center; z-index: 9999;
        }

        .modal-content {
            background-color: white; padding: 25px; border-radius: 14px;
            width: 440px; max-width: 90%; box-shadow: 0 5px 25px rgba(0,0,0,0.25);
            position: relative; animation: formFadeIn 0.3s ease;
        }

        @keyframes formFadeIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close-btn { position: absolute; top: 15px; right: 20px; font-size: 28px; color: #b2bec3; border: none; background: none; cursor: pointer; }
        .close-btn:hover { color: #2d3436; }

        .modal-content h2 { margin: 0 0 20px 0; font-size: 22px; color: var(--primary-color); border-bottom: 2px solid #f1f2f6; padding-bottom: 12px; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        .form-control:focus { border-color: var(--primary-color); outline: none; }

        .options-container { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
        .option-tag { position: relative; }
        .option-tag input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
        .option-label { display: inline-block; padding: 6px 12px; background: #f1f2f6; border: 1px solid #ddd; border-radius: 20px; font-size: 13px; cursor: pointer; }
        .option-tag input[type="radio"]:checked + .option-label { background: var(--primary-color); color: white; border-color: var(--primary-color); font-weight: bold; }

        .quantity-counter { display: flex; align-items: center; gap: 12px; margin-top: 4px; }
        .btn-qty { background: #f1f2f6; border: 1px solid #ccc; width: 36px; height: 36px; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; display: flex; align-items: center; justify-content: center; }
        .qty-input { width: 55px; height: 36px; text-align: center; font-size: 16px; font-weight: bold; border: 1px solid #ddd; border-radius: 6px; }

        .total-display-box { background: #fff5f5; border: 1px dashed var(--primary-color); padding: 12px; border-radius: 6px; text-align: center; margin: 18px 0; }
        .total-price { font-size: 22px; font-weight: bold; color: #e17055; }

        .form-actions { display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #f1f2f6; padding-top: 15px; }
        .btn-submit { background-color: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-huy { background-color: #eee; color: #333; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }

        /* GIAO DIỆN HIỂN THỊ DANH SÁCH MÓN ĂN TRONG GIỎ HÀNG TỔNG */
        .cart-item-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee; font-size: 14px; }
        .cart-item-details { display: flex; flex-direction: column; gap: 2px; }
        .cart-item-name { font-weight: bold; color: #2d3436; }
        .cart-item-sub { font-size: 12px; color: #7f8c8d; }
        .cart-item-price-qty { display: flex; align-items: center; gap: 15px; }
        .btn-delete-item { color: #e74c3c; background: none; border: none; cursor: pointer; font-size: 14px; }

        /* POPUP THÔNG BÁO THÊM GIỎ HÀNG CHUẨN ĐẸP */
        .toast-notification {
            position: fixed; top: 25px; left: 50%; transform: translateX(-50%) translateY(-40px);
            background-color: #2ecc71; color: white; font-weight: 600; font-size: 15px;
            padding: 12px 24px; border-radius: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            z-index: 10000; opacity: 0; visibility: hidden; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex; align-items: center; gap: 8px;
        }
        .toast-notification.show { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body>

<div id="toastNotify" class="toast-notification">
    <i class="fa-solid fa-circle-check"></i> <span id="toastMessage">Đã thêm món vào giỏ hàng thành công!</span>
</div>

<header>
    <h1>🧋 Trà Sữa Homie 🧋</h1>  <p>Thơm ngon từng giọt - Đậm vị yêu thương</p> 
    <div class="main-menu" style="margin-top: 15px; margin-bottom: 5px;">
        <a href="trang-chu.php" style="color: white; margin-right: 20px; text-decoration: none; font-weight: bold;"><i class="fa-solid fa-house"></i> Trang Chủ</a>
        <a href="index.php" style="color: white; margin-right: 20px; text-decoration: none; font-weight: bold;"><i class="fa-solid fa-utensils"></i> Thực Đơn</a>
        <a href="lien-he.php" style="color: white; text-decoration: none; font-weight: bold;"><i class="fa-solid fa-envelope"></i> Liên Hệ</a> 
    </div>
    
    <div class="auth-buttons"> <?php if(isset($_SESSION['username'])): ?>
            <span><i class="fa-solid fa-user"></i> Xin chào, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <a href="dang-xuat.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng Xuất</a>
        <?php else: ?>
            <a href="dang-nhap.php"><i class="fa-solid fa-user-lock"></i> Đăng Nhập</a>
            <a href="dang-ky.php"><i class="fa-solid fa-user-plus"></i> Đăng Ký</a>
        <?php endif; ?>
    </div>
</header>

<div class="container">
    <?php 
    while ($cat = mysqli_fetch_assoc($result_categories)) {
        $cat_id = $cat['category_id'];
        $sql_products = "SELECT * FROM products WHERE category_id = $cat_id";
        $result_products = mysqli_query($conn, $sql_products);
        
        if (mysqli_num_rows($result_products) > 0) {
            echo '<div class="category-section">';
            echo '<h2 class="category-title">' . htmlspecialchars($cat['category_name']) . '</h2>';
            echo '<div class="product-grid">';
            
            while ($prod = mysqli_fetch_assoc($result_products)) {
                $icon = "🧋"; 
                if ($cat_id == 1) $icon = "🍟";       
                if ($cat_id == 2) $icon = "☕";       
                if ($cat_id == 4) $icon = "🍜";       
                if ($cat_id == 5) $icon = "🥑";       
                if ($cat_id == 7) $icon = "🍓";       
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if(!empty($prod['image_url']) && file_exists($prod['image_url'])): ?>
                            <img src="<?= htmlspecialchars($prod['image_url']) ?>" alt="<?= htmlspecialchars($prod['product_name']) ?>">
                        <?php elseif(!empty($prod['image_url']) && $prod['image_url'] != 'default.png'): ?>
                            <img src="<?= htmlspecialchars($prod['image_url']) ?>" alt="<?= htmlspecialchars($prod['product_name']) ?>">
                        <?php else: ?>
                            <?= $icon ?>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($prod['product_name']) ?></h3>
                        <p class="product-desc"><?= htmlspecialchars($prod['description']) ?></p>
                        <div class="product-price-action">
                            <span class="product-price"><?= number_format($prod['price'], 0, ',', '.') ?>đ</span>
                            <div class="action-buttons-group">
                                <button class="btn-select" onclick="moTuyChonMon(<?= $prod['product_id'] ?>, '<?= htmlspecialchars($prod['product_name']) ?>', <?= $prod['price'] ?>, <?= $cat_id ?>)">Mua</button>
                                <button class="btn-mini-cart" title="Thêm nhanh vào giỏ hàng" onclick="themNhanhVaoGioHang(<?= $prod['product_id'] ?>, '<?= htmlspecialchars($prod['product_name']) ?>', <?= $prod['price'] ?>, <?= $cat_id ?>)">
                                    <i class="fa-solid fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
            echo '</div></div><br><br>';
        }
    }
    ?>
</div>

<div id="optionsModal" class="modal">
    <div class="modal-content">
        <button class="close-btn" onclick="dongTuyChon()">&times;</button>
        <h2 id="optionTitle">Tùy Chọn Món</h2>
        
        <div class="form-group">
            <label>Số Lượng Đặt:</label>
            <div class="quantity-counter">
                <button type="button" class="btn-qty" onclick="giamQty()">-</button>
                <input type="number" id="opt_quantity" class="qty-input" value="1" readonly>
                <button type="button" class="btn-qty" onclick="tangQty()">+</button>
            </div>
        </div>

        <div id="drinkOptionsSection">
            <div class="form-group">
                <label>Mức Đường:</label>
                <div class="options-container">
                    <div class="option-tag"><input type="radio" name="opt_sugar" value="100" id="s100" checked><label for="s100" class="option-label">100% Đường</label></div>
                    <div class="option-tag"><input type="radio" name="opt_sugar" value="70" id="s70"><label for="s70" class="option-label">70%</label></div>
                    <div class="option-tag"><input type="radio" name="opt_sugar" value="50" id="s50"><label for="s50" class="option-label">50%</label></div>
                    <div class="option-tag"><input type="radio" name="opt_sugar" value="0" id="s0"><label for="s0" class="option-label">0% Đường</label></div>
                </div>
            </div>
            <div class="form-group">
                <label>Mức Đá:</label>
                <div class="options-container">
                    <div class="option-tag"><input type="radio" name="opt_ice" value="100" id="i100" checked><label for="i100" class="option-label">100% Đá</label></div>
                    <div class="option-tag"><input type="radio" name="opt_ice" value="70" id="i70"><label for="i70" class="option-label">70%</label></div>
                    <div class="option-tag"><input type="radio" name="opt_ice" value="50" id="i50"><label for="i50" class="option-label">50%</label></div>
                    <div class="option-tag"><input type="radio" name="opt_ice" value="0" id="i0"><label for="i0" class="option-label">0% Đá</label></div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label id="toppingLabel">Topping yêu cầu thêm:</label>
            <input type="text" id="opt_topping_note" class="form-control" placeholder="Ví dụ: Thêm trân châu...">
        </div>

        <div class="total-display-box">
            Tạm tính món này: <span class="total-price" id="opt_display_total">0đ</span>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-huy" onclick="dongTuyChon()">Hủy</button>
            <button type="button" class="btn-submit" onclick="xacNhanThemMon()">Thêm Vào Giỏ</button>
        </div>
    </div>
</div>

<div id="cartModal" class="modal">
    <div class="modal-content" style="width: 500px;">
        <button class="close-btn" onclick="dongGioHang()">&times;</button>
        <h2>🛒 Giỏ Hàng Của Bạn</h2>
        
        <div id="cartItemsList" style="max-height: 250px; overflow-y: auto; margin-bottom: 20px; border-bottom: 2px solid #f1f2f6; padding-bottom: 10px;">
            </div>

        <form action="" method="POST" onsubmit="return validateCartBeforeSubmit()">
            <input type="hidden" name="cart_data" id="hidden_cart_data">

            <div class="form-group">
                <label>Họ và Tên Khách Hàng <span style="color:red;">*</span></label>
                <input type="text" name="khach_ten" class="form-control" required placeholder="Nhập họ tên nhận hàng" value="<?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '' ?>">
            </div>
            <div class="form-group">
                <label>Số Điện Thoại <span style="color:red;">*</span></label>
                <input type="text" name="khach_sdt" class="form-control" required placeholder="Nhập số điện thoại liên hệ">
            </div>

            <div class="form-group">
                <label>Địa Chỉ Giao Hàng <span style="color:red;">*</span></label>
                <input type="text" name="khach_diachi" class="form-control" required placeholder="Nhập số nhà, tên đường để shipper giao hàng">
            </div>

            <div class="total-display-box" style="background: #fff3cd; border-color: #ffc107;">
                Tổng tiền toàn bộ giỏ hàng: <span class="total-price" id="cart_global_total" style="color: #d63031;">0đ</span>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-huy" onclick="dongGioHang()">Xem Tiếp</button>
                <button type="submit" name="btn_dat_hang" class="btn-submit">Xác Nhận Đặt Hàng</button>
            </div>
        </form>
    </div>
</div>

<div id="floating-cart" onclick="moGioHangHienTai()" style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background-color: #ff7675; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; cursor: pointer; box-shadow: 0 4px 15px rgba(255, 118, 117, 0.4); z-index: 999; transition: transform 0.2s;">
    <i class="fa-solid fa-cart-shopping"></i>
    <span id="cart-count" style="position: absolute; top: -5px; right: -5px; background: #d63031; color: white; font-size: 12px; font-weight: bold; border-radius: 50%; width: 22px; height: 22px; display: none; align-items: center; justify-content: center;">0</span>
</div>

<script>
let globalCart = []; // Mảng lưu trữ các món đã chọn trong giỏ hàng
let activeProduct = {}; // Lưu thông tin tạm của món đang chọn cấu hình khi nhấn chữ "Mua"

// HÀM HIỂN THỊ POPUP THÔNG BÁO CHUẨN ĐẸP NHƯ ẢNH CHỤP
function showToast(message) {
    let toast = document.getElementById('toastNotify');
    document.getElementById('toastMessage').innerText = message;
    toast.classList.add('show');
    
    // Tự động giấu sau 2.5 giây
    setTimeout(() => {
        toast.classList.remove('show');
    }, 2500);
}

// HÀM KIỂM TRA GHI NHỚ VÀ SO SÁNH TÙY CHỌN ĐƯỜNG ĐÁ ĐỂ QUYẾT ĐỊNH GỘP HOẶC TÁCH DÒNG
function themMonVaoMangGioHang(newProduct) {
    // So sánh khớp toàn bộ: ID sản phẩm + Mức đường + Mức đá + Ghi chú topping
    let existingIndex = globalCart.findIndex(item => 
        item.id === newProduct.id && 
        item.sugar === newProduct.sugar && 
        item.ice === newProduct.ice && 
        item.topping.trim().toLowerCase() === newProduct.topping.trim().toLowerCase()
    );

    if (existingIndex > -1) {
        // Nếu trùng khít toàn bộ tùy chọn -> Tiến hành cộng dồn số lượng lên dòng đó
        globalCart[existingIndex].quantity += newProduct.quantity;
    } else {
        // Nếu tùy chọn khác nhau -> Lưu thành một dòng riêng biệt trong giỏ hàng lớn
        globalCart.push(newProduct);
    }
}

// HÀM 1: KHI CLICK BIỂU TƯỢNG GIỎ HÀNG NHỎ - CHỈ BẮN POPUP THÔNG BÁO, KHÔNG BUNG MODAL LỚN LÊN
function themNhanhVaoGioHang(productId, productName, price, categoryId) {
    let sugarVal = 100;
    let iceVal = 100;

    let itemNew = {
        id: productId,
        name: productName,
        price: parseFloat(price),
        quantity: 1,
        sugar: sugarVal,
        ice: iceVal,
        topping: ""
    };

    // Đẩy vào hàm xử lý thông minh để ghi nhớ tùy chọn
    themMonVaoMangGioHang(itemNew);
    capNhatGiaoDienGioHang();
    
    // Chỉ bắn thông báo nổi lên màn hình (như ảnh), giữ giao diện sạch sẽ cho khách lướt tiếp
    showToast("✨ Đã thêm '" + productName + "' vào giỏ hàng thành công!");
}

// HÀM 2: KHI CLICK CHỮ "MUA" - BUNG MODAL CẤU HÌNH ĐƯỜNG ĐÁ, SAU ĐÓ BUNG TIẾP GIỎ HÀNG ĐỂ KIỂM TRA ĐƠN
function moTuyChonMon(productId, productName, price, categoryId) {
    activeProduct = { id: productId, name: productName, price: parseFloat(price), catId: categoryId };
    
    document.getElementById('optionTitle').innerText = productName;
    document.getElementById('opt_quantity').value = 1;
    document.getElementById('opt_topping_note').value = "";
    
    document.getElementById('s100').checked = true;
    document.getElementById('i100').checked = true;

    let drinkSection = document.getElementById('drinkOptionsSection');
    let toppingLabel = document.getElementById('toppingLabel');
    let toppingInput = document.getElementById('opt_topping_note');

    if (categoryId == 1 || categoryId == 4) {
        drinkSection.style.display = 'none';
        toppingLabel.innerText = "Yêu cầu ghi chú cho món ăn:";
        toppingInput.placeholder = "Ví dụ: Làm cay nhiều, không bỏ hành tây...";
    } else {
        drinkSection.style.display = 'block';
        toppingLabel.innerText = "Topping yêu cầu thêm:";
        toppingInput.placeholder = "Ví dụ: Thêm trân châu hoàng kim, thạch...";
    }

    tinhTienTuyChonMon();
    document.getElementById('optionsModal').style.display = 'flex';
}

function dongTuyChon() { document.getElementById('optionsModal').style.display = 'none'; }

function tangQty() {
    let input = document.getElementById('opt_quantity');
    input.value = parseInt(input.value) + 1;
    tinhTienTuyChonMon();
}
function giamQty() {
    let input = document.getElementById('opt_quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        tinhTienTuyChonMon();
    }
}
function tinhTienTuyChonMon() {
    let qty = parseInt(document.getElementById('opt_quantity').value);
    let total = activeProduct.price * qty;
    document.getElementById('opt_display_total').innerText = total.toLocaleString('vi-VN') + 'đ';
}

function xacNhanThemMon() {
    let qty = parseInt(document.getElementById('opt_quantity').value);
    let sugarValue = document.querySelector('input[name="opt_sugar"]:checked').value;
    let iceValue = document.querySelector('input[name="opt_ice"]:checked').value;
    let toppingNote = document.getElementById('opt_topping_note').value;

    if (activeProduct.catId == 1 || activeProduct.catId == 4) {
        sugarValue = 100;
        iceValue = 100;
    }

    let itemNew = {
        id: activeProduct.id,
        name: activeProduct.name,
        price: activeProduct.price,
        quantity: qty,
        sugar: sugarValue,
        ice: iceValue,
        topping: toppingNote
    };

    // Chạy qua thuật toán xử lý để tự động nhận diện gộp/tách dòng thông minh
    themMonVaoMangGioHang(itemNew);

    capNhatGiaoDienGioHang();
    dongTuyChon();
    document.getElementById('cartModal').style.display = 'flex'; // Riêng nút Mua này thì bung Modal lớn lên cho khách coi lại toàn bộ giỏ hàng
}

function capNhatGiaoDienGioHang() {
    // Tính tổng số ly thực tế dựa trên thuộc tính quantity để hiển thị lên vòng tròn đỏ dưới góc
    let totalItemsCount = globalCart.reduce((sum, item) => sum + item.quantity, 0);
    let countSpan = document.getElementById('cart-count');
    countSpan.innerText = totalItemsCount;
    countSpan.style.display = totalItemsCount > 0 ? 'flex' : 'none';

    let listContainer = document.getElementById('cartItemsList');
    listContainer.innerHTML = "";
    let globalTotal = 0;

    globalCart.forEach((item, index) => {
        let itemTotal = item.price * item.quantity;
        globalTotal += itemTotal;

        let subText = `SL: ${item.quantity} x ${item.price.toLocaleString('vi-VN')}đ`;
        if (item.sugar != 100 || item.ice != 100 || item.topping != "") {
            subText += ` | Đường: ${item.sugar}%, Đá: ${item.ice}% ${item.topping ? ', Note: ' + item.topping : ''}`;
        }

        listContainer.innerHTML += `
            <div class="cart-item-row">
                <div class="cart-item-details">
                    <span class="cart-item-name">${item.name}</span>
                    <span class="cart-item-sub">${subText}</span>
                </div>
                <div class="cart-item-price-qty">
                    <span style="font-weight:bold; color:#e17055;">${itemTotal.toLocaleString('vi-VN')}đ</span>
                    <button type="button" class="btn-delete-item" onclick="xoaMonKhoiGio(${index})"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>
        `;
    });

    document.getElementById('cart_global_total').innerText = globalTotal.toLocaleString('vi-VN') + 'đ';
    document.getElementById('hidden_cart_data').value = JSON.stringify(globalCart);
}

function xoaMonKhoiGio(index) {
    globalCart.splice(index, 1);
    capNhatGiaoDienGioHang();
}

function moGioHangHienTai() {
    if (globalCart.length === 0) {
        alert("🛒 Giỏ hàng bạn đang trống, hãy thêm sản phẩm vào giỏ!");
    } else {
        document.getElementById('cartModal').style.display = 'flex';
    }
}

function dongGioHang() { document.getElementById('cartModal').style.display = 'none'; }

function validateCartBeforeSubmit() {
    if (globalCart.length === 0) {
        alert("⚠️ Giỏ hàng không có sản phẩm nào để đặt!");
        return false;
    }
    return true;
}

window.onclick = function(event) {
    if (event.target == document.getElementById('optionsModal')) dongTuyChon();
    if (event.target == document.getElementById('cartModal')) dongGioHang();
}
</script>

<?php if ($thong_bao == "thanh_cong"): ?>
    <script>
        alert('🎉 Đặt hàng thành công!');
        globalCart = [];
        capNhatGiaoDienGioHang();
    </script>
<?php elseif ($thong_bao == "vui_long_nhap_du"): ?>
    <script>alert('⚠️ Vui lòng điền đầy đủ thông tin Tên và Số điện thoại để nhận hàng!');</script>
<?php elseif ($thong_bao == "loi_chi_tiet_don" || $thong_bao == "loi_tao_don_hang"): ?>
    <script>alert('❌ Có lỗi hệ thống xảy ra khi lưu trữ đơn hàng. Vui lòng thử lại sau!');</script>
<?php endif; ?>

</body>
</html>
