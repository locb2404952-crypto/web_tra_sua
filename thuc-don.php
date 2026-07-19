<?php
session_start(); 

// 1. Kết nối cơ sở dữ liệu
if (file_exists('includes/db-connect.php')) {
    require_once 'includes/db-connect.php';
} else {
    require_once 'db-connect.php';
}

$thong_bao = ""; 

// 2. Xử lý đặt đơn hàng khi nhận dữ liệu POST từ Form gửi lên
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_dat_hang'])) {
    $ten_khach = mysqli_real_escape_string($conn, $_POST['khach_ten']);
    $sdt = mysqli_real_escape_string($conn, $_POST['khach_sdt']);
    $diachi = mysqli_real_escape_string($conn, $_POST['khach_diachi']);
    $cart_data_json = $_POST['cart_data'];
    $cart_items = json_decode($cart_data_json, true);

    if (empty($ten_khach) || empty($sdt) || empty($cart_items) || empty($diachi)) {
        $thong_bao = "vui_long_nhap_du";
    } else {
        $total_order_amount = 0;
        foreach ($cart_items as $item) {
            $total_order_amount += floatval($item['price']) * intval($item['quantity']);
        }

        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 2;

        $sql_order = "INSERT INTO orders (user_id, total_amount, customer_name, phone, address, status) 
                      VALUES ($user_id, $total_order_amount, '$ten_khach', '$sdt', '$diachi', 'pending')";
        if (mysqli_query($conn, $sql_order)) {
            $order_id = mysqli_insert_id($conn);
            $error_flag = false;

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

// Lấy danh mục sản phẩm từ DB
$sql_categories = "SELECT * FROM categories";
$result_categories = mysqli_query($conn, $sql_categories);

// 3. NHÚNG HEADER CHUNG (Đã chứa sẵn cấu trúc chuẩn HTML/Navbar)
include 'includes/header.php';
?>

<!-- Popup Thông báo Toast nhanh khi thêm món -->
<div id="toastNotify" class="toast-notification">
    <i class="fa-solid fa-circle-check"></i> <span id="toastMessage">Đã thêm món vào giỏ hàng thành công!</span>
</div>

<div class="container" style="display: flex; gap: 30px; align-items: flex-start; margin-top: 30px;">
    
    <!-- Nhúng thanh danh mục (Nếu bạn đã có file danh-muc.php riêng biệt) -->
    <div style="flex: 0 0 260px;">
        <?php 
        if (file_exists('danh-muc.php')) {
            include 'danh-muc.php';
        }
        ?>
    </div>

    <!-- Lưới hiển thị danh sách sản phẩm -->
    <div style="flex: 1;">
        <?php 
        while ($cat = mysqli_fetch_assoc($result_categories)) {
            $cat_id = $cat['category_id'];
            $sql_products = "SELECT * FROM products WHERE category_id = $cat_id";
            $result_products = mysqli_query($conn, $sql_products);
            
            if (mysqli_num_rows($result_products) > 0) {
                echo '<div class="category-section" id="danh-muc-'.$cat_id.'">';
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
                        <div class="product-image"><?= $icon ?></div>
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

<!-- POPUP MODAL: TÙY CHỌN ĐƯỜNG/ĐÁ/TOPPING -->
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

<!-- POPUP MODAL: CHI TIẾT GIỎ HÀNG & FORM MUA -->
<div id="cartModal" class="modal">
    <div class="modal-content" style="width: 500px;">
        <button class="close-btn" onclick="dongGioHang()">&times;</button>
        <h2>🛒 Giỏ Hàng Của Bạn</h2>
        
        <div id="cartItemsList" style="max-height: 250px; overflow-y: auto; margin-bottom: 20px; border-bottom: 2px solid #f1f2f6; padding-bottom: 10px;"></div>

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
                <input type="text" name="khach_diachi" class="form-control" required placeholder="Nhập địa chỉ giao hàng">
            </div>

            <div class="total-display-box" style="background: #fff3cd; border-color: #ffc107;">
                Tổng tiền giỏ hàng: <span class="total-price" id="cart_global_total" style="color: #d63031;">0đ</span>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-huy" onclick="dongGioHang()">Xem Tiếp</button>
                <button type="submit" name="btn_dat_hang" class="btn-submit">Xác Nhận Đặt Hàng</button>
            </div>
        </form>
    </div>
</div>

<!-- NÚT GIỎ HÀNG TRÒN NỔI -->
<div id="floating-cart" onclick="moGioHangHienTai()" style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background-color: #ff7675; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; cursor: pointer; box-shadow: 0 4px 15px rgba(255, 118, 117, 0.4); z-index: 999; transition: transform 0.2s;">
    <i class="fa-solid fa-cart-shopping"></i>
    <span id="cart-count" style="position: absolute; top: -5px; right: -5px; background: #d63031; color: white; font-size: 12px; font-weight: bold; border-radius: 50%; width: 22px; height: 22px; display: none; align-items: center; justify-content: center;">0</span>
</div>

<!-- Thông báo PHP kết quả đặt hàng -->
<?php if ($thong_bao == "thanh_cong"): ?>
    <script>
        alert('🎉 Đặt hàng thành công! Đơn hàng đã được đồng bộ vào hệ thống.');
        sessionStorage.removeItem('homie_cart');
        if (typeof globalCart !== 'undefined') {
            globalCart = [];
            capNhatGiaoDienGioHang();
        }
    </script>
<?php elseif ($thong_bao == "vui_long_nhap_du"): ?>
    <script>alert('⚠️ Vui lòng điền đầy đủ thông tin Tên, Số điện thoại và Địa chỉ để nhận hàng!');</script>
<?php elseif ($thong_bao == "loi_chi_tiet_don" || $thong_bao == "loi_tao_don_hang"): ?>
    <script>alert('❌ Có lỗi hệ thống xảy ra khi lưu trữ đơn hàng. Vui lòng thử lại sau!');</script>
<?php endif; ?>

<!-- NHÚNG FILE SCRIPT GIỎ HÀNG TRƯỚC FOOTER -->
<script src="js/cart.js"></script>

<?php 
// 4. NHÚNG FOOTER CHUNG
include 'includes/footer.php'; 
?>