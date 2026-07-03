<?php
session_start(); 
require_once 'db-connect.php';

$thong_bao = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_dat_hang'])) {
    // 1. Nhận các thông tin cơ bản từ Form gửi lên (Đúng tên biến bạn Huy yêu cầu)
    $ten_khach = mysqli_real_escape_string($conn, $_POST['khach_ten']);
    $sdt = mysqli_real_escape_string($conn, $_POST['khach_sdt']);
    $product_id = intval($_POST['product_id']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    
    // Thu thập thêm tùy chọn mức đường, đá, topping theo cấu trúc bảng order_items
    $sugar_level = isset($_POST['sugar_level']) ? intval($_POST['sugar_level']) : 100;
    $ice_level = isset($_POST['ice_level']) ? intval($_POST['ice_level']) : 100;
    $topping_note = isset($_POST['topping_note']) ? mysqli_real_escape_string($conn, $_POST['topping_note']) : '';

    // Kiểm tra tính hợp lệ
    if (empty($ten_khach) || empty($sdt) || $product_id == 0 || $quantity <= 0) {
        $thong_bao = "vui_long_nhap_du";
    } else {
        // Tính toán tổng tiền thật
        $total_amount = $price * $quantity;

        // Lấy mã user_id nếu khách đã đăng nhập, nếu chưa mặc định là 2 (Tài khoản khách vãng lai mẫu)
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 2;

        // Bước A: Chèn thông tin vào bảng orders (Lưu tổng tiền thật vào cột total_amount)
        $sql_order = "INSERT INTO orders (user_id, total_amount, status) VALUES ($user_id, $total_amount, 'pending')";
        
        if (mysqli_query($conn, $sql_order)) {
            // Lấy ra mã order_id tự động tăng vừa chèn ở trên để làm khóa ngoại cho bảng order_items
            $order_id = mysqli_insert_id($conn);

            // Bước B: Chèn chi tiết vào bảng order_items (Lưu đầy đủ số lượng, giá, đường, đá, topping)
            $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price, sugar_level, ice_level, topping_note) 
                         VALUES ($order_id, $product_id, $quantity, $price, $sugar_level, $ice_level, '$topping_note')";
            
            if (mysqli_query($conn, $sql_item)) {
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
        /* Thiết lập các biến màu sắc chủ đạo của quán */
        :root {
            --primary-color: #ff7675;   /* Màu hồng cam san hô ngọt ngào */
            --secondary-color: #fab1a0; /* Màu cam nhạt phối hợp */
            --dark-color: #2d3436;      /* Màu chữ tối */
            --light-color: #f9f9f9;     /* Màu nền sáng */
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-color);
            color: var(--dark-color);
        }

        header {
            background-color: var(--primary-color);
            color: white;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            position: relative;
        }

        header h1 { margin: 0; font-size: 32px; letter-spacing: 1px; }
        header p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.9; }

        /* Khu vực hiển thị nút Đăng nhập / Đăng ký góc phải màn hình */
        .auth-buttons {
            position: absolute;
            top: 30px;
            right: 30px;
        }
        .auth-buttons a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-weight: bold;
            font-size: 14px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        .auth-buttons a:hover {
            background-color: white;
            color: var(--primary-color);
        }
        .auth-buttons span {
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* Khối phân chia theo từng Danh Mục món ăn */
        .category-section {
            margin-bottom: 5px;
        }

        .category-title {
            font-size: 24px;
            color: #d63031;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 6px;
            margin-bottom: 25px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Bố cục Grid hiển thị danh sách sản phẩm tự động co giãn */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            border: 1px solid #f1f2f6;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(255, 118, 117, 0.15);
        }

        /* Ảnh đại diện tạm thời bằng biểu tượng Icon */
        .product-image {
            width: 100%;
            height: 160px;
            background-color: #ffeaa7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 55px;
            user-select: none;
        }

        .product-info {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .product-name {
            font-size: 19px;
            font-weight: bold;
            margin: 0 0 8px 0;
            color: #2d3436;
        }

        .product-desc {
            font-size: 14px;
            color: #636e72;
            margin: 0 0 20px 0;
            line-height: 1.4;
            flex-grow: 1; /* Đẩy cụm giá và nút bấm xuống luôn đều nhau ở đáy */
        }

        .product-price-action {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .product-price {
            font-size: 19px;
            font-weight: bold;
            color: #e17055;
        }

        .btn-select {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 9px 18px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 3px 8px rgba(255, 118, 117, 0.3);
            transition: background 0.2s ease;
        }

        .btn-select:hover {
            background-color: #ff5252;
        }

        /* ======================================================== */
        /* CSS GIAO DIỆN GIỎ HÀNG (CART MODAL) CỦA HUY BỰ */
        /* ======================================================== */
        .modal {
            display: none; /* Mặc định ẩn, bật flex khi gọi hàm mở */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.55);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-content {
            background-color: white;
            padding: 25px;
            border-radius: 14px;
            width: 440px;
            max-width: 90%;
            box-shadow: 0 5px 25px rgba(0,0,0,0.25);
            position: relative;
            animation: formFadeIn 0.3s ease;
        }

        @keyframes formFadeIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            color: #b2bec3;
            cursor: pointer;
            border: none;
            background: none;
        }
        .close-btn:hover { color: #2d3436; }

        .modal-content h2 {
            margin: 0 0 20px 0;
            font-size: 22px;
            color: var(--primary-color);
            border-bottom: 2px solid #f1f2f6;
            padding-bottom: 12px;
            padding-right: 25px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 14px;
            color: #2d3436;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
        }

        /* CSS phần chọn Đường Đá Topping và Tăng giảm Số lượng */
        .options-container {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 4px;
        }
        .option-tag {
            position: relative;
        }
        .option-tag input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .option-label {
            display: inline-block;
            padding: 6px 12px;
            background: #f1f2f6;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 13px;
            cursor: pointer;
            user-select: none;
        }
        .option-tag input[type="radio"]:checked + .option-label {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            font-weight: bold;
        }

        .quantity-counter {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 4px;
        }
        .btn-qty {
            background: #f1f2f6;
            border: 1px solid #ccc;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-qty:hover { background: #e4e7eb; }
        
        .qty-input {
            width: 55px;
            height: 36px;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .total-display-box {
            background: #fff5f5;
            border: 1px dashed var(--primary-color);
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            margin: 18px 0;
            font-size: 15px;
        }
        .total-price {
            font-size: 22px;
            font-weight: bold;
            color: #e17055;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-top: 1px solid #f1f2f6;
            padding-top: 15px;
        }
        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-submit:hover { background-color: #ff5252; }
        .btn-huy {
            background-color: #eee;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-huy:hover { background-color: #ddd; }
    </style>
</head>
<body>

<header>
    <h1>🧋 Trà Sữa Homie 🧋</h1>
    <p>Thơm ngon từng giọt - Đậm vị yêu thương</p>
    
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
    <?php 
    // Vòng lặp duyệt qua từng danh mục sản phẩm trong database
    while ($cat = mysqli_fetch_assoc($result_categories)) {
        $cat_id = $cat['category_id'];
        
        // Truy vấn tất cả sản phẩm thuộc danh mục hiện tại
        $sql_products = "SELECT * FROM products WHERE category_id = $cat_id";
        $result_products = mysqli_query($conn, $sql_products);
        
        if (mysqli_num_rows($result_products) > 0) {
            echo '<div class="category-section">';
            echo '<h2 class="category-title">' . htmlspecialchars($cat['category_name']) . '</h2>';
            echo '<div class="product-grid">';
            
            while ($prod = mysqli_fetch_assoc($result_products)) {
                // Đổi icon hiển thị đại diện tùy thuộc vào danh mục món
                $icon = "🧋"; 
                if ($cat_id == 1) $icon = "🍟";       // Ăn vặt
                if ($cat_id == 2) $icon = "☕";       // Cà phê
                if ($cat_id == 4) $icon = "🍜";       // Mỳ cay
                if ($cat_id == 5) $icon = "🥑";       // Sinh tố
                if ($cat_id == 7) $icon = "🍓";       // Trái cây tươi
                ?>
                <div class="product-card">
                    <div class="product-image"><?= $icon ?></div>
                    <div class="product-info">
                        <h3 class="product-name"><?= htmlspecialchars($prod['product_name']) ?></h3>
                        <p class="product-desc"><?= htmlspecialchars($prod['description']) ?></p>
                        <div class="product-price-action">
                            <span class="product-price"><?= number_format($prod['price'], 0, ',', '.') ?>đ</span>
                            <button class="btn-select" onclick="moGioHang(<?= $prod['product_id'] ?>, '<?= htmlspecialchars($prod['product_name']) ?>', <?= $prod['price'] ?>, <?= $cat_id ?>)">Chọn mua</button>
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

<div id="cartModal" class="modal">
    <div class="modal-content">
        <button class="close-btn" onclick="dongGioHang()">&times;</button>
        <h2 id="cartTitle">Tùy Chọn Đặt Món</h2>
        
        <form action="" method="POST">
            <input type="hidden" name="product_id" id="cart_product_id">
            <input type="hidden" name="price" id="cart_price">

            <div class="form-group">
                <label>Họ và Tên Khách Hàng <span style="color:red;">*</span></label>
                <input type="text" name="khach_ten" class="form-control" required placeholder="Nhập họ tên nhận hàng" value="<?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '' ?>">
            </div>
            <div class="form-group">
                <label>Số Điện Thoại <span style="color:red;">*</span></label>
                <input type="text" name="khach_sdt" class="form-control" required placeholder="Nhập số điện thoại liên hệ">
            </div>

            <div id="drinkOptionsSection">
                <div class="form-group">
                    <label>Mức Đường:</label>
                    <div class="options-container">
                        <div class="option-tag"><input type="radio" name="sugar_level" value="100" id="s100" checked><label for="s100" class="option-label">100% Đường</label></div>
                        <div class="option-tag"><input type="radio" name="sugar_level" value="70" id="s70"><label for="s70" class="option-label">70%</label></div>
                        <div class="option-tag"><input type="radio" name="sugar_level" value="50" id="s50"><label for="s50" class="option-label">50%</label></div>
                        <div class="option-tag"><input type="radio" name="sugar_level" value="0" id="s0"><label for="s0" class="option-label">0% Đường</label></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Mức Đá:</label>
                    <div class="options-container">
                        <div class="option-tag"><input type="radio" name="ice_level" value="100" id="i100" checked><label for="i100" class="option-label">100% Đá</label></div>
                        <div class="option-tag"><input type="radio" name="ice_level" value="70" id="i70"><label for="i70" class="option-label">70%</label></div>
                        <div class="option-tag"><input type="radio" name="ice_level" value="50" id="i50"><label for="i50" class="option-label">50%</label></div>
                        <div class="option-tag"><input type="radio" name="ice_level" value="0" id="i0"><label for="i0" class="option-label">0% Đá</label></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label id="toppingLabel">Topping yêu cầu thêm:</label>
                <input type="text" name="topping_note" id="cart_topping_note" class="form-control" placeholder="Ví dụ: Thêm trân châu hoàng kim, thạch trái cây...">
            </div>

            <div class="form-group">
                <label>Số Lượng Đặt Mua:</label>
                <div class="quantity-counter">
                    <button type="button" class="btn-qty" onclick="giamSoLuong()">-</button>
                    <input type="number" name="quantity" id="cart_quantity" class="qty-input" value="1" min="1" readonly>
                    <button type="button" class="btn-qty" onclick="tangSoLuong()">+</button>
                </div>
            </div>

            <div class="total-display-box">
                Tổng tiền tạm tính: <span class="total-price" id="display_total_amount">0đ</span>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-huy" onclick="dongGioHang()">Hủy Bỏ</button>
                <button type="submit" name="btn_dat_hang" class="btn-submit">Xác Nhận Đặt Hàng</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentProductPrice = 0; // Biến toàn cục lưu giá gốc món ăn đang chọn

function moGioHang(productId, productName, price, categoryId) {
    currentProductPrice = parseFloat(price);
    
    // Đổ dữ liệu ID, Giá và Tên món ăn vào các ô nhập liệu tương ứng trong giỏ hàng
    document.getElementById('cart_product_id').value = productId;
    document.getElementById('cart_price').value = price;
    document.getElementById('cartTitle').innerText = productName;
    
    // Reset số lượng đặt mua về 1 mặc định mỗi lần bật hộp thoại mới lên
    document.getElementById('cart_quantity').value = 1;
    
    let drinkOptions = document.getElementById('drinkOptionsSection');
    let toppingLabel = document.getElementById('toppingLabel');
    let toppingInput = document.getElementById('cart_topping_note');

    // Nâng cấp khôn khéo: Tự động giấu chọn Đường/Đá nếu là đồ ăn vặt (ID: 1) hoặc Mỳ Cay (ID: 4)
    if (categoryId == 1 || categoryId == 4) {
        drinkOptions.style.display = 'none';
        toppingLabel.innerText = "Yêu cầu ghi chú cho món ăn:";
        toppingInput.placeholder = "Ví dụ: Lấy đũa, làm cay nhiều, không bỏ hành tây...";
    } else {
        drinkOptions.style.display = 'block';
        toppingLabel.innerText = "Topping yêu cầu thêm:";
        toppingInput.placeholder = "Ví dụ: Thêm trân châu hoàng kim, thạch trái cây...";
    }

    // Tính toán tổng tiền khởi điểm cho số lượng bằng 1
    capNhatTongTien();
    
    // Hiển thị khung giỏ hàng modal lên màn hình
    document.getElementById('cartModal').style.display = 'flex';
}

function dongGioHang() {
    document.getElementById('cartModal').style.display = 'none';
}

function tangSoLuong() {
    let qtyInput = document.getElementById('cart_quantity');
    let currentQty = parseInt(qtyInput.value);
    qtyInput.value = currentQty + 1;
    capNhatTongTien();
}

function giamSoLuong() {
    let qtyInput = document.getElementById('cart_quantity');
    let currentQty = parseInt(qtyInput.value);
    if (currentQty > 1) {
        qtyInput.value = currentQty - 1;
        capNhatTongTien();
    }
}

function capNhatTongTien() {
    let qty = parseInt(document.getElementById('cart_quantity').value);
    let total = currentProductPrice * qty;
    // Đổ số tiền đã định dạng Việt Nam Đồng ra khung hiển thị tổng tiền
    document.getElementById('display_total_amount').innerText = total.toLocaleString('vi-VN') + 'đ';
}

// Đóng hộp thoại nếu khách click trượt ra vùng trống bên ngoài
window.onclick = function(event) {
    if (event.target == document.getElementById('cartModal')) {
        dongGioHang();
    }
}
</script>

<?php if ($thong_bao == "thanh_cong"): ?>
    <script>alert('🎉 Đặt hàng thành công! Đơn hàng kèm giá tiền thật đã được đồng bộ vào hệ thống database.');</script>
<?php elseif ($thong_bao == "vui_long_nhap_du"): ?>
    <script>alert('⚠️ Vui lòng điền đầy đủ thông tin Tên và Số điện thoại để nhận hàng!');</script>
<?php elseif ($thong_bao == "loi_chi_tiet_don" || $thong_bao == "loi_tao_don_hang"): ?>
    <script>alert('❌ Có lỗi hệ thống xảy ra khi lưu trữ đơn hàng. Vui lòng thử lại sau!');</script>
<?php endif; ?>

</body>
</html>
