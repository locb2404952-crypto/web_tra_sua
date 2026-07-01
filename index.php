<?php
// kết nối cơ sở dữ liệu vào
require_once 'db-connect.php';

$thong_bao = ""; // Biến dùng để lưu trạng thái thông báo cho khách hàng

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_dat_hang'])) {
    // 1. Lấy dữ liệu từ form gửi lên và ép kiểu để bảo mật sạch sẽ
    $ten_khach = mysqli_real_escape_string($conn, $_POST['khach_ten']);
    $sdt = mysqli_real_escape_string($conn, $_POST['khach_sdt']);
    
    // --- TUẦN 3: Hứng thêm dữ liệu món ăn từ form giỏ hàng của Nhat Huy ---
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;

    // Kiểm tra xem khách có bỏ trống ô nào không hoặc chưa chọn món
    if (empty($ten_khach) || empty($sdt) || $product_id == 0) {
        $thong_bao = "vui_long_nhap_du";
    } else {
        // --- TUẦN 3: Tính toán tổng tiền thật ---
        $total_amount = $price * $quantity;

        // 2. Viết câu lệnh SQL để chèn dữ liệu vào bảng orders
        // Đã thay số 0 thành biến $total_amount tiền thật
        $sql_insert_order = "INSERT INTO orders (user_id, total_amount, status) 
                             VALUES (1, '$total_amount', 'pending')";
        
        // 3. Chạy câu lệnh và kiểm tra kết quả
        if ($conn->query($sql_insert_order) === TRUE) {
            // --- TUẦN 3: Lấy ID của đơn hàng vừa tạo tự động để lưu chi tiết món ---
            $order_id = $conn->insert_id;

            // 4. Viết lệnh INSERT vào bảng order_items (Chi tiết đơn hàng)
            $sql_insert_item = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                                VALUES ('$order_id', '$product_id', '$quantity', '$price')";
            
            // Chạy lệnh lưu chi tiết món
            if ($conn->query($sql_insert_item) === TRUE) {
                $thong_bao = "thanh_cong";
            } else {
                $thong_bao = "loi_luu_chi_tiet";
            }
        } else {
            $thong_bao = "that_bai";
        }
    }
}


// 1. Lấy danh sách các danh mục để làm thanh chọn
$sql_categories = "SELECT * FROM categories";
$result_categories = $conn->query($sql_categories);


// =========================================================================
// PHẦN XỬ LÝ TÌM KIẾM CỦA CỎN (THÀNH VIÊN 3)
// =========================================================================
// Kiểm tra xem người dùng có nhập từ khóa tìm kiếm hay không
$search_keyword = "";
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_keyword = $conn->real_escape_string(trim($_GET['search']));
    
    // Nếu có tìm kiếm: Thêm điều kiện WHERE để lọc theo tên sản phẩm (product_name)
    $sql_products = "SELECT p.*, c.category_name 
                     FROM products p 
                     JOIN categories c ON p.category_id = c.category_id 
                     WHERE p.product_name LIKE '%$search_keyword%'
                     ORDER BY p.category_id ASC, p.product_name ASC";
} else {
    // Nếu KHÔNG tìm kiếm: Lấy toàn bộ sản phẩm như ban đầu
    $sql_products = "SELECT p.*, c.category_name 
                     FROM products p 
                     JOIN categories c ON p.category_id = c.category_id 
                     ORDER BY p.category_id ASC, p.product_name ASC";
}
// =========================================================================


$result_products = $conn->query($sql_products);

// Gom nhóm sản phẩm theo danh mục để dễ hiển thị
$menu = [];
if ($result_products && $result_products->num_rows > 0) {
    while ($row = $result_products->fetch_assoc()) {
        $menu[$row['category_name']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trà Sữa & Đồ Ăn Vặt Homie</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f9f9f9; color: #333; padding: 20px; }
        header { text-align: center; padding: 30px 0; background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: #fff; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        header h1 { font-size: 2.5rem; margin-bottom: 10px; text-shadow: 1px 1px 3px rgba(0,0,0,0.2); }
        .container { max-width: 1200px; margin: 0 auto; }
        .category-section { margin-bottom: 40px; }
        .category-title { font-size: 1.8rem; color: #d85a7f; border-left: 5px solid #d85a7f; padding-left: 10px; margin-bottom: 20px; text-transform: uppercase; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
        .product-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #eee; transition: transform 0.2s; display: flex; flex-direction: column; justify-content: space-between; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 6px 12px rgba(0,0,0,0.1); }
        .product-name { font-size: 1.2rem; font-weight: bold; margin-bottom: 8px; color: #222; }
        .product-desc { font-size: 0.9rem; color: #777; margin-bottom: 15px; min-height: 40px; }
        .product-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
        .product-price { font-size: 1.2rem; color: #e67e22; font-weight: bold; }
        .btn-order { background-color: #ff7675; color: white; border: none; padding: 8px 15px; border-radius: 20px; cursor: pointer; font-weight: bold; transition: background 0.2s; }
        .btn-order:hover { background-color: #d63031; }
        /* === PHẦN CSS Ô TÌM KIẾM CỦA q.con === */
        .search-container { text-align: center; margin-bottom: 30px; }
        .search-input { padding: 10px 15px; width: 300px; border: 2px solid #ff7675; border-radius: 20px; outline: none; font-size: 1rem; }
        .btn-search { background-color: #ff7675; color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-weight: bold; font-size: 1rem; margin-left: 10px; transition: background 0.2s; }
        .btn-search:hover { background-color: #d63031; }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>QUÁN TRÀ SỮA & ĐỒ ĂN VẶT HOMIE</h1>
        <p>Menu siêu ngon - Càng ăn càng ghiền!</p>
    </header>
    
    <div class="search-container">
        <form action="index.php" method="GET">
            <input type="text" class="search-input" placeholder="Nhập tên trà sữa, đồ ăn..." name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit" class="btn-search">Tìm kiếm</button>
            <?php if(!empty($search_keyword)): ?>
                <a href="index.php" style="display: block; margin-top: 10px; color: #ff7675; text-decoration: none; font-size: 0.9rem;">[Xóa bộ lọc / Hiển thị tất cả]</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (!empty($menu)): ?>
        <?php foreach ($menu as $category_name => $products): ?>
            <div class="category-section">
                <h2 class="category-title"><?php echo htmlspecialchars($category_name); ?></h2>
                <div class="product-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div>
                                <div class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></div>
                                <div class="product-desc"><?php echo htmlspecialchars($product['description'] ? $product['description'] : 'Món ăn thức uống thơm ngon được làm trong ngày.'); ?></div>
                            </div>
                            <div class="product-footer">
                                <span class="product-price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                                <button class="btn-order" onclick="moForm(<?php echo $product['product_id']; ?>, <?php echo $product['price']; ?>)">Đặt món</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; font-size: 1.2rem; color: #999;">Không tìm thấy món ăn nào phù hợp với từ khóa của bạn.</p>
    <?php endif; ?>
</div>

<div id="orderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 9999;">
    <div style="background: #fff; padding: 24px; border-radius: 12px; width: 90%; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; margin-bottom: 15px; color: #333;">Thông Tin Đặt Hàng</h3>

        <form id="formDatHang" action="" method="POST">
            <!-- Tạo ra các ô lưu trữ dữ liệu tạm thời (Mã món ăn, Giá tiền, Số lượng) nằm ẩn bên trong Form đặt hàng. Khi khách hàng bấm xác nhận, các dữ liệu ẩn này sẽ được đóng gói chung với Họ tên, SĐT để gửi lên cho Backend (PHP) xử lý tính tiền thật -->
            <input type="hidden" id="modalProductId" name="product_id" value="0">
            <input type="hidden" id="modalPrice" name="price" value="0.0">
            <input type="hidden" id="modalQuantity" name="quantity" value="1">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: bold; font-size: 0.9rem;">Họ và tên:</label>
                <input type="text" id="khachHangTen" name="khach_ten" placeholder="Nhập họ và tên của bạn" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" required>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: bold; font-size: 0.9rem;">Số điện thoại:</label>
                <input type="text" id="khachHangSdt" name="khach_sdt" placeholder="Nhập số điện thoại" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;" required>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="dongForm()" style="padding: 8px 16px; background: #eee; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Hủy</button>
                <button type="submit" name="btn_dat_hang" style="padding: 8px 16px; background: #ff7675; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Xác nhận đặt</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Khi khách bấm nút Đặt món, hàm này sẽ lấy Mã món và Giá tiền từ nút bấm đó, tự động điền (gán giá trị) vào các ô nhập liệu ẩn bên trong Form rồi mới mở cái khung nhập thông tin lên cho khách.
function moForm(productId, price) {
    if(productId && price) {
        // Tự động điền ID và Giá bốc được từ nút bấm vào 2 ô ẩn trong Form
        document.getElementById('modalProductId').value = productId;
        document.getElementById('modalPrice').value = price;
    }
    // Mở khung điền thông tin đặt hàng lên
    document.getElementById('orderModal').style.display = 'flex';
}
   // dongForm(): Tìm đến hộp thoại bằng ID và đổi display về lại none để giấu nó đi
function dongForm() {
    document.getElementById('orderModal').style.display = 'none';
}


// Bắn thông báo dựa trên kết quả PHP trả về sau khi load lại trang
<?php if ($thong_bao == "thanh_cong"): ?>
    alert('Đặt hàng thành công! Đơn hàng của bạn đã được ghi nhận vào hệ thống.');
<?php elseif ($thong_bao == "vui_long_nhap_du"): ?>
    alert('Vui lòng điền đầy đủ Họ tên và Số điện thoại!');
    moForm(); // Hiện lại form cho khách nhập lại
<?php elseif ($thong_bao == "that_bai"): ?>
    alert('Có lỗi xảy ra, không thể lưu đơn hàng. Vui lòng thử lại!');
<?php endif; ?>
</script>

</body>
</html>