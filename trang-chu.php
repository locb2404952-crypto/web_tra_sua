
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
                                        <div>
                                            <span class="product-price"><?= number_format($prod['price'], 0, ',', '.') ?>đ</span>
                                            <br>
                                            <span class="sold-count">Đã bán: <?= $prod['total_sold'] ?> ly/đĩa</span>
                                        </div>
                                        <a href="index.php?add_id=<?= $prod['product_id'] ?>" class="btn-buy-now">Đặt Mua</a>
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

<?php include_once 'includes/footer.php'; ?>