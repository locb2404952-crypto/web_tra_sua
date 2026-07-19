<?php
session_start();
// Gọi file kết nối database
require_once 'includes/db-connect.php';

// Gọi giao diện header dùng chung
include_once 'includes/header.php';

// ========================================================
// THUẬT TOÁN SQL: LẤY 2 MÓN CÓ SỐ LƯỢNG MUA NHIỀU NHẤT CỦA TỪNG DANH MỤC
// ========================================================
$sql_best_seller = "
    SELECT p.product_id, p.product_name, p.price, p.description, p.category_id, p.image_url, c.category_name, 
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
          AND (temp.sub_sold > IFNULL(SUM(oi.quantity), 0) OR (temp.sub_sold = IFNULL(SUM(oi.quantity), 0) AND temp.product_id < p.product_id))
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
<div class="container">
    <div class="hero-section">
        <h2>Chào Mừng Đến Với Homie Tea</h2>
        <p>Không gian ngọt ngào, đồ uống chuẩn vị, nguyên liệu 100% tự nhiên!</p>
        <a href="index.php" class="btn-menu-now"><i class="fa-solid fa-circle-play"></i> Xem Thực Đơn & Đặt Món Ngay</a>
    </div>

    <div class="bestseller-box">
        <h2 class="bestseller-title"><i class="fa-solid fa-crown"></i> Gợi Ý Món Bán Chạy Nhất (Best Seller) <i class="fa-solid fa-crown"></i></h2>
        
        <?php if (empty($best_sellers_by_cat)): ?>
            <p class="empty-bestseller-msg">Hệ thống chưa ghi nhận lượt đặt mua nào. Hãy là người mua đầu tiên nhé!</p>
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
                                    
                                    <div class="product-price-action" style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px; gap: 6px;">
                                        <div>
                                            <span class="product-price"><?= number_format($prod['price'], 0, ',', '.') ?>đ</span>
                                            <br>
                                            <span class="sold-count" style="font-size: 11px; color: #7f8c8d;">Đã bán: <?= $prod['total_sold'] ?> ly/đĩa</span>
                                        </div>
                                        
                                        <div style="display: flex; gap: 6px; align-items: center;">
                                            <!-- Nút Mua màu hồng -->
                                            <button onclick="moTuyChonMon(<?= $prod['product_id'] ?>, '<?= addslashes($prod['product_name']) ?>', <?= $prod['price'] ?>, <?= $prod['category_id'] ?>)"
                                                    style="background: #ff7675; color: white; border: none; padding: 6px 14px; border-radius: 20px; cursor: pointer; font-weight: bold; font-size: 13px;">
                                                Mua
                                            </button>

                                            <!-- Nút Icon Giỏ hàng nhỏ màu vàng/cam -->
                                            <button onclick="themNhanhVaoGioHang(<?= $prod['product_id'] ?>, '<?= addslashes($prod['product_name']) ?>', <?= $prod['price'] ?>, <?= $prod['category_id'] ?>)"
                                                    style="background: #ffeaa7; color: #e17055; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                                                <i class="fa-solid fa-cart-shopping"></i>
                                            </button>
                                        </div>
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

<!-- ============================================================ -->
<!-- CỤM NÚT LIÊN HỆ & NÚT GIỎ HÀNG NỔI (ĐÃ TÁCH CSS SANG FILE)     -->
<!-- ============================================================ -->
<div class="sticky-contact-bar collapsed">
    <!-- Nhóm các nút sẽ ẩn đi khi thu gọn -->
    <div class="contact-buttons-wrapper" style="display: none; flex-direction: column; gap: 10px; align-items: center; margin-bottom: 10px;">
        <!-- Nút Zalo -->
        <a href="https://zalo.me/YOUR_PHONE_NUMBER" target="_blank" class="contact-btn zalo-btn">
            <img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg" alt="Zalo">
        </a>
        
        <!-- Nút Gọi Điện -->
        <a href="tel:YOUR_PHONE_NUMBER" class="contact-btn phone-btn">
            <i class="fa-solid fa-phone"></i>
        </a>
        
        <!-- Nút Dấu X để đóng thanh tiện ích -->
        <button type="button" class="contact-btn toggle-btn close-action">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- 2 Nút mặc định luôn lộ ra ngoài (Chuẩn hình trang thực đơn) -->
    <!-- Nút Chat (Cục màu cam nhạt phía trên) -->
    <button type="button" class="contact-btn chat-trigger-btn" style="width: 40px; height: 40px; background: #fff5f0; border-radius: 50%; color: #ff7675; font-size: 18px; display: flex; justify-content: center; align-items: center; border: none; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <i class="fa-regular fa-comment-dots"></i>
    </button>

    <!-- Nút Giỏ Hàng Nổi -->
    <button id="floatingCartButton" class="floating-cart-btn">
        <i class="fa-solid fa-cart-shopping"></i>
        <span id="cart-count" class="cart-badge">0</span>
    </button>
</div>

<!-- ========================================== -->
<!-- KHU VỰC CẤU TRÚC CÁC MODAL HỆ THỐNG -->
<!-- ========================================== -->

<!-- 1. Toast Thông Báo Nhỏ Gọn -->
<div id="toastNotify" class="toast-style" style="position: fixed; top: 20px; right: 20px; background: #2ecc71; color: white; padding: 12px 25px; border-radius: 5px; display: none; z-index: 9999; font-weight: bold;">
    <span id="toastMessage"></span>
</div>

<!-- 2. Modal Tùy Chọn Món -->
<div id="optionsModal" class="modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 9999;">
    <div class="modal-content" style="background: white; padding: 25px; border-radius: 15px; width: 90%; max-width: 450px; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
        <span class="close-btn" onclick="dongTuyChon()" style="position: absolute; top: 15px; right: 20px; font-size: 22px; cursor: pointer; color: #aaa;">&times;</span>
        <h3 id="optionTitle" style="margin-top: 0; color: #ff7675; font-size: 20px; font-weight: bold;">Tên món</h3>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
        
        <p style="margin: 10px 0 5px 0; font-weight: bold; color: #555;">Số lượng Đặt:</p>
        <div style="display: flex; gap: 5px; align-items: center; margin-bottom: 15px;">
            <button type="button" onclick="giamQty()" style="width:32px; height:32px; border:1px solid #ddd; background:#fff; cursor:pointer; border-radius:4px;">-</button>
            <input type="text" id="opt_quantity" value="1" style="width: 45px; height:32px; text-align: center; border:1px solid #ddd; border-radius:4px;" readonly>
            <button type="button" onclick="tangQty()" style="width:32px; height:32px; border:1px solid #ddd; background:#fff; cursor:pointer; border-radius:4px;">+</button>
        </div>

        <div id="drinkOptionsSection">
            <p style="margin: 10px 0 5px 0; font-weight: bold; color: #555;">Mức Đường:</p>
            <div class="sugar-options-group" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 15px;">
                <label class="custom-option"><input type="radio" name="opt_sugar" id="s100" value="100" checked hidden><span>100% Đường</span></label>
                <label class="custom-option"><input type="radio" name="opt_sugar" value="70" hidden><span>70% Đường</span></label>
                <label class="custom-option"><input type="radio" name="opt_sugar" value="50" hidden><span>50% Đường</span></label>
                <label class="custom-option"><input type="radio" name="opt_sugar" value="0" hidden><span>0% Đường</span></label>
            </div>
            
            <p style="margin: 10px 0 5px 0; font-weight: bold; color: #555;">Mức Đá:</p>
            <div class="ice-options-group" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 15px;">
                <label class="custom-option"><input type="radio" name="opt_ice" id="i100" value="100" checked hidden><span>100% Đá</span></label>
                <label class="custom-option"><input type="radio" name="opt_ice" value="70" hidden><span>70% Đá</span></label>
                <label class="custom-option"><input type="radio" name="opt_ice" value="50" hidden><span>50% Đá</span></label>
                <label class="custom-option"><input type="radio" name="opt_ice" value="0" hidden><span>0% Đá</span></label>
            </div>
        </div>

        <p style="margin: 10px 0 5px 0; font-weight: bold; color: #555;"><span id="toppingLabel">Topping yêu cầu thêm:</span></p>
        <input type="text" id="opt_topping_note" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px; box-sizing: border-box;" placeholder="Ví dụ: Thêm trân châu...">

        <div style="background: #fff5f5; border: 1px dashed #ffb8b8; padding: 12px; border-radius: 8px; text-align: center; margin-bottom: 20px; color: #ff7675; font-weight: bold;">
            Tạm tính món này: <span id="opt_display_total">0đ</span>
        </div>

        <div style="display: flex; gap: 10px;">
            <button onclick="dongTuyChon()" style="flex: 1; padding: 10px; background: #f5f6fa; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; color:#7f8c8d;">Hủy</button>
            <button onclick="xacNhanThemMon()" style="flex: 2; padding: 10px; background: #ff7675; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;">Thêm Vào Giỏ</button>
        </div>
    </div>
</div>

<!-- 3. Modal Giỏ Hàng Mới Tinh - Đồng Bộ Tỷ Lệ & Giao Diện 100% Giống Hình 2 -->
<div id="cartModal" class="modal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 9999;">
    <div class="modal-content" style="background: white; padding: 25px; border-radius: 15px; width: 90%; max-width: 440px; max-height: 85vh; overflow-y: auto; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.2); font-family: sans-serif;">
        <span class="close-btn" onclick="dongGioHang()" style="position: absolute; top: 15px; right: 20px; font-size: 22px; cursor: pointer; color: #aaa;">&times;</span>
        <h3 style="margin-top: 0; color: #ff7675; font-size: 18px; font-weight: bold; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-basket-shopping"></i> Giỏ Hàng Của Bạn</h3>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
        
        <div id="cartItemsList"></div>
        
        <form action="index.php" method="POST" onsubmit="return validateCartBeforeSubmit()" style="margin-top: 15px;">
            <input type="hidden" id="hidden_cart_data" name="cart_data">

            <div style="margin-bottom: 12px; text-align: left;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #2d3436;">Họ và Tên Khách Hàng <span style="color: red;">*</span></label>
                <input type="text" name="customer_name" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 13px;" placeholder="Nhập họ tên nhận hàng">
            </div>

            <div style="margin-bottom: 12px; text-align: left;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #2d3436;">Số Điện Thoại <span style="color: red;">*</span></label>
                <input type="tel" name="customer_phone" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 13px;" placeholder="Nhập số điện thoại liên hệ">
            </div>

            <div style="margin-bottom: 15px; text-align: left;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: #2d3436;">Địa Chỉ Giao Hàng <span style="color: red;">*</span></label>
                <input type="text" name="customer_address" required style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 13px;" placeholder="Nhập số nhà, tên đường để shipper giao hàng">
            </div>

            <!-- Khung tổng tiền viền đứt nét siêu tinh tế chuẩn đét hình 2 -->
            <div style="background: #fff9db; border: 1px dashed #ffe3e3; padding: 12px 15px; border-radius: 8px; text-align: center; margin-bottom: 18px; color: #d63031; font-weight: bold; font-size: 14px;">
                Tổng tiền toàn bộ giỏ hàng: <span id="cart_global_total" style="font-size: 15px; font-weight: 800;">0đ</span>
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <button type="button" onclick="dongGioHang()" style="flex: 1; padding: 10px; background: #f5f6fa; border: 1px solid #eee; border-radius: 6px; cursor: pointer; font-weight: bold; color:#636e72; font-size: 13px; height: 40px;">Xem Tiếp</button>
                <button type="submit" style="flex: 2; padding: 10px; background: #ff7675; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 13px; height: 40px;">Xác Nhận Đặt Hàng</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- KHU VỰC CSS BỔ TRỢ HÀM HIỂN THỊ -->
<!-- ========================================== -->
<style>
    .custom-option span {
        display: inline-block;
        padding: 6px 12px;
        border: 1px solid #ddd;
        border-radius: 20px;
        cursor: pointer;
        font-size: 13px;
        color: #555;
        transition: all 0.2s;
        background: #fff;
    }
    .custom-option input:checked + span {
        background: #ff7675;
        color: white;
        border-color: #ff7675;
        font-weight: bold;
    }

    #cartItemsList {
        width: 100%;
        margin-bottom: 15px;
        max-height: 240px;
        overflow-y: auto;
    }
    .cart-item, .cart-item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
        text-align: left;
    }
    .cart-item-info {
        display: flex;
        flex-direction: column;
    }
    .cart-item-title {
        font-weight: bold;
        color: #333;
        font-size: 14px;
    }
    .cart-item-details {
        font-size: 12px;
        color: #7f8c8d;
        margin-top: 2px;
    }
    .cart-item-price {
        font-weight: bold;
        color: #ff7675;
    }
    .btn-remove, .delete-item {
        color: #ff7675;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        padding-left: 10px;
    }
    .toast-style.show { display: block !important; animation: fadeIn 0.5s; }
</style>

<!-- NHÚNG FILE JS GIỎ HÀNG HỆ THỐNG -->
<script src="js/cart.js"></script>

<!-- ĐOẠN JS ĐỒNG BỘ ĐẾM SỐ LƯỢNG VÀ NÚT BẤM -->
<!-- ĐOẠN JS ĐỒNG BỘ TRIỆT ĐỂ GIỎ HÀNG VỚI TRANG INDEX -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Gọi hàm hiển thị chuẩn của hệ thống nếu có
        if(typeof capNhatGiaoDienGioHang === "function") {
            capNhatGiaoDienGioHang();
        } else if(typeof renderCart === "function") {
            renderCart();
        } else {
            // Nếu hệ thống không tự chạy, ta tự ép render danh sách trùng khớp 100% với index
            renderGioHangTrangChu();
        }

        // Cập nhật số lượng bong bóng đỏ
        capNhatBadgeGioHang();
// Kích hoạt tính năng đóng/mở thanh tiện ích
        const chatTrigger = document.querySelector('.chat-trigger-btn');
        const closeBtn = document.querySelector('.close-action');
        const wrapper = document.querySelector('.contact-buttons-wrapper');

        if (chatTrigger && closeBtn && wrapper) {
            // Click nút Chat -> Mở rộng thanh, hiện Zalo, Phone, X và ẩn chính nó
            chatTrigger.addEventListener('click', function() {
                wrapper.style.display = 'flex';
                chatTrigger.style.display = 'none';
            });

            // Click dấu X -> Thu gọn thanh, ẩn Zalo, Phone, X và hiện lại nút Chat
            closeBtn.addEventListener('click', function() {
                wrapper.style.display = 'none';
                chatTrigger.style.display = 'flex';
            });
        }
        // Lắng nghe sự kiện click nút giỏ hàng nổi để mở Modal
        const btnCart = document.getElementById('floatingCartButton');
        if (btnCart) {
            btnCart.addEventListener('click', function(e) {
                e.preventDefault();
                if (typeof moGioHang === "function") { moGioHang(); } 
                else {
                    const modal = document.getElementById('cartModal');
                    if (modal) modal.style.display = 'flex';
                }
                // Mỗi lần mở lên phải ép render lại cho mới nhất
                if(typeof capNhatGiaoDienGioHang === "function") { capNhatGiaoDienGioHang(); }
                else { renderGioHangTrangChu(); }
            });
        }
    });

    // Hàm tự trị giúp ruột giỏ hàng trùng khớp cấu trúc với trang index
    function renderGioHangTrangChu() {
        const listContainer = document.getElementById('cartItemsList');
        const totalContainer = document.getElementById('cart_global_total');
        const hiddenInput = document.getElementById('hidden_cart_data');
        
        if (!listContainer) return;

        let gioHang = JSON.parse(localStorage.getItem('cart')) || [];
        listContainer.innerHTML = '';
        let tongTien = 0;

        if (gioHang.length === 0) {
            listContainer.innerHTML = '<p style="text-align:center; color:#7f8c8d; font-size:13px; margin:20px 0;">Giỏ hàng trống</p>';
            if(totalContainer) totalContainer.innerText = '0đ';
            if(hiddenInput) hiddenInput.value = '';
            return;
        }

        gioHang.forEach((item, index) => {
            let thanhTien = item.price * item.quantity;
            tongTien += thanhTien;

            // Tạo chuỗi tùy chọn (Đường, Đá, Topping) nếu có
            let optionsText = '';
            if(item.sugar || item.ice || item.topping) {
                let opts = [];
                if(item.sugar) opts.push(`Đường: ${item.sugar}%`);
                if(item.ice) opts.push(`Đá: ${item.ice}%`);
                if(item.topping) opts.push(`Topping: ${item.topping}`);
                optionsText = opts.join(' | ');
            }

            // Render chuẩn cấu trúc hàng (Row) giống hệt trang index của bạn
            const itemRow = document.createElement('div');
            itemRow.className = 'cart-item-row';
            itemRow.style.display = 'flex';
            itemRow.style.justifyContent = 'space-between';
            itemRow.style.alignItems = 'center';
            itemRow.style.padding = '10px 0';
            itemRow.style.borderBottom = '1px solid #eee';

            itemRow.innerHTML = `
                <div class="cart-item-info" style="text-align:left;">
                    <span class="cart-item-title" style="font-weight:bold; font-size:13px; color:#333;">${item.name}</span>
                    <span style="font-size:11px; color:#7f8c8d; display:block; margin-top:2px;">SL: ${item.quantity} x ${Number(item.price).toLocaleString('vi-VN')}đ</span>
                    ${optionsText ? `<span class="cart-item-details" style="font-size:11px; color:#e17055; display:block; margin-top:2px;">${optionsText}</span>` : ''}
                </div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="cart-item-price" style="font-weight:bold; color:#ff7675; font-size:13px;">${thanhTien.toLocaleString('vi-VN')}đ</span>
                    <button type="button" onclick="xoaMonTrangChu(${index})" class="delete-item" style="color:#ff7675; background:none; border:none; cursor:pointer; font-size:15px;"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            `;
            listContainer.appendChild(itemRow);
        });

        if(totalContainer) totalContainer.innerText = tongTien.toLocaleString('vi-VN') + 'đ';
        if(hiddenInput) hiddenInput.value = JSON.stringify(gioHang);
    }

    // Hàm xóa món ngay trên trang chủ và tự đồng bộ
    function xoaMonTrangChu(index) {
        let gioHang = JSON.parse(localStorage.getItem('cart')) || [];
        gioHang.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(gioHang));
        
        // Render lại giao diện và bong bóng số lượng
        renderGioHangTrangChu();
        capNhatBadgeGioHang();
        
        // Nếu trang của bạn có hàm dùng chung thì gọi để đồng bộ toàn cục
        if(typeof capNhatGiaoDienGioHang === "function") capNhatGiaoDienGioHang();
    }

    function capNhatBadgeGioHang() {
        const badge = document.getElementById('cart-count');
        if (badge) {
            let gioHang = JSON.parse(localStorage.getItem('cart')) || [];
            let tongSoLuong = gioHang.reduce((total, item) => total + parseInt(item.quantity || 1), 0);
            badge.innerText = tongSoLuong;
        }
    }
</script>

<?php include_once 'includes/footer.php'; ?>