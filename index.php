<?php
session_start();
// Gọi file kết nối database
require_once 'includes/db-connect.php'; 

// Gọi giao diện header dùng chung
include_once 'includes/header.php';

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

    if (empty($ten_khach) || empty($sdt) || empty($cart_items) || empty($diachi)) {
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

<div class="homie-main-layout">
    <div class="homie-left-sidebar">
        <?php include_once 'danh-muc.php'; ?>
    </div>

    <div class="homie-right-content">
        <?php 
        if (isset($result_categories)) {
            mysqli_data_seek($result_categories, 0);
        }
        
        while ($cat = mysqli_fetch_assoc($result_categories)) {
            $cat_id = $cat['category_id'];
            $sql_products = "SELECT * FROM products WHERE category_id = $cat_id";
            $result_products = mysqli_query($conn, $sql_products);
            
            if (mysqli_num_rows($result_products) > 0) {
                echo '<div class="category-section" id="danh-muc-' . $cat_id . '">';
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
</div>

<!-- THÔNG BÁO TRẠNG THÁI ĐẶT HÀNG -->
<?php if ($thong_bao == "thanh_cong"): ?>
    <script>alert('🎉 Đặt hàng thành công!'); globalCart = []; capNhatGiaoDienGioHang();</script>
<?php elseif ($thong_bao == "vui_long_nhap_du"): ?>
    <script>alert('⚠️ Vui lòng điền đầy đủ thông tin Tên và Số điện thoại để nhận hàng!');</script>
<?php elseif ($thong_bao == "loi_chi_tiet_don" || $thong_bao == "loi_tao_don_hang"): ?>
    <script>alert('❌ Có lỗi hệ thống xảy ra khi lưu trữ đơn hàng. Vui lòng thử lại sau!');</script>
<?php endif; ?>

<!-- NHÚNG CÁC THÀNH PHẦN PHỤ TRỢ TỪ THƯ MỤC INCLUDES -->
<?php include_once 'includes/lien-he-noi.php'; ?>
<?php include_once 'includes/giohang-trangchu.php'; ?>
<?php include_once 'includes/footer.php'; ?>