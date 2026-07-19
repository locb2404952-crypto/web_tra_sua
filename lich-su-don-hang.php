<?php
session_start();

// 1. Kiểm tra xem khách đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    // Nếu chưa đăng nhập, bắt buộc chuyển hướng sang trang đăng nhập
    header("Location: dang-nhap.php");
    exit();
}

// 2. Kết nối cơ sở dữ liệu
if (file_exists('includes/db-connect.php')) {
    require_once 'includes/db-connect.php';
} else {
    require_once 'db-connect.php';
}

$user_id = $_SESSION['user_id'];

// 3. Lấy danh sách đơn hàng của CHÍNH NGƯỜI ĐANG ĐĂNG NHẬP (Sắp xếp đơn mới nhất lên đầu)
$sql_orders = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_id DESC";
$result_orders = mysqli_query($conn, $sql_orders);

// 4. NHÚNG HEADER CHUNG
include 'includes/header.php';
?>

<div class="history-container">
    <h1 class="history-title-main">📋 Lịch Sử Đặt Hàng Của Bạn</h1>

    <?php 
    if (mysqli_num_rows($result_orders) > 0) {
        // Lặp qua từng đơn hàng của khách
        while ($order = mysqli_fetch_assoc($result_orders)) {
            $order_id = $order['order_id'];
            
            // Định dạng trạng thái đơn hàng hiển thị bằng tiếng Việt kèm màu sắc
            $status_text = "Chờ duyệt";
            $status_class = "status-pending";
            
            if ($order['status'] == 'processing') {
                $status_text = "Đang làm nước";
                $status_class = "status-processing";
            } elseif ($order['status'] == 'completed') {
                $status_text = "Đã giao hàng";
                $status_class = "status-completed";
            } elseif ($order['status'] == 'cancelled') {
                $status_text = "Đã hủy đơn";
                $status_class = "status-cancelled";
            }
            ?>
            
            <div class="order-block">
                <!-- Phần đầu của khối đơn hàng: Mã đơn, ngày đặt, trạng thái -->
                <div class="order-header">
                    <div class="order-id-date">
                        Mã đơn: <strong>#<?= $order_id ?></strong> | Ngày đặt: <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                    </div>
                    <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                </div>

                <!-- Phần thân: Lấy chi tiết các món ăn trong đơn hàng này ra -->
                <div class="order-body">
                    <?php 
                    $sql_items = "SELECT oi.*, p.product_name 
                                  FROM order_items oi 
                                  JOIN products p ON oi.product_id = p.product_id 
                                  WHERE oi.order_id = $order_id";
                    $result_items = mysqli_query($conn, $sql_items);
                    
                    while ($item = mysqli_fetch_assoc($result_items)) {
                        ?>
                        <div class="order-item-detail">
                            <div>
                                <span class="item-info-name"><?= htmlspecialchars($item['product_name']) ?></span>
                                <!-- Hiển thị tùy chọn đường đá topping nếu có -->
                                <div class="item-info-options">
                                    <?php 
                                    $options = [];
                                    if ($item['sugar_level'] != 100) $options[] = "Đường: " . $item['sugar_level'] . "%";
                                    if ($item['ice_level'] != 100) $options[] = "Đá: " . $item['ice_level'] . "%";
                                    if (!empty($item['topping_note'])) $options[] = "Ghi chú: " . htmlspecialchars($item['topping_note']);
                                    
                                    echo implode(" | ", $options);
                                    ?>
                                </div>
                            </div>
                            <div class="item-price-qty">
                                <?= $item['quantity'] ?> x <?= number_format($item['price'], 0, ',', '.') ?>đ
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>

                <!-- Phần chân của khối đơn hàng: Địa chỉ nhận và Tổng tiền đơn hàng -->
                <div class="order-footer">
                    <div class="customer-delivery-info">
                        👤 Người nhận: <?= htmlspecialchars($order['customer_name']) ?> - 📞 <?= htmlspecialchars($order['phone']) ?><br>
                        📍 Địa chỉ giao: <?= htmlspecialchars($order['address']) ?>
                    </div>
                    <div class="order-total-display">
                        Tổng tiền: <strong><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</strong>
                    </div>
                </div>
            </div>

            <?php
        }
    } else {
        // Trường hợp tài khoản này chưa từng mua món nào
        ?>
        <div class="no-order-box">
            <i class="fa-solid fa-basket-shopping"></i>
            <p>Bạn chưa có đơn hàng nào được đặt.</p>
            <p style="margin-top: 10px;"><a href="thuc-don.php" style="color: #ff7675; font-weight: bold; text-decoration: none;">👉 Vào xem Thực đơn đặt món ngay thôi!</a></p>
        </div>
        <?php
    }
    ?>
</div>

<?php 
// 5. NHÚNG FOOTER CHUNG
include 'includes/footer.php'; 
?>