<?php
// 1. Nhúng file kết nối database  
require_once 'db-connect.php';

// ==========================================
// [NHIỆM VỤ CỦA CỎN] - XỬ LÝ ĐỔI TRẠNG THÁI ĐƠN HÀNG
// ==========================================
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $sql_update_status = "UPDATE orders SET status = '$new_status' WHERE order_id = $order_id";
    mysqli_query($conn, $sql_update_status);
    header("Location: admin.php");
    exit();
}

// 2. XỬ LÝ CHỨC NĂNG: THÊM MÓN MỚI 
if (isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $image_url = 'default.png'; 

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "images/"; 
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_name = time() . '_' . basename($_FILES["product_image"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $image_url = $file_name;
        }
    }

    $sql_add = "INSERT INTO products (product_name, category_id, price, description, image_url) 
                VALUES ('$product_name', $category_id, $price, '$description', '$image_url')";
    mysqli_query($conn, $sql_add);
    header("Location: admin.php");
    exit();
}

// 3. XỬ LÝ CHỨC NĂNG: SỬA CHỈNH SỬA MÓN ĂN
if (isset($_POST['edit_product'])) {
    $product_id = intval($_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $sql_update = "UPDATE products 
                   SET product_name = '$product_name', category_id = $category_id, price = $price, description = '$description' 
                   WHERE product_id = $product_id";
    mysqli_query($conn, $sql_update);
    header("Location: admin.php");
    exit();
}

// 4. XỬ LÝ CHỨC NĂNG: XÓA SẢN PHẨM KHỎI THỰC ĐƠN
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql_delete = "DELETE FROM products WHERE product_id = $delete_id";
    mysqli_query($conn, $sql_delete);
    header("Location: admin.php");
    exit();
}

// Lấy danh sách danh mục phục vụ thẻ select option trong Form
$categories = mysqli_query($conn, "SELECT * FROM categories");
$cat_list = [];
while($row = mysqli_fetch_assoc($categories)) {
    $cat_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Trị - Trà Sữa Homie</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 20px; background: #f4f6f9; color: #333; }
        .admin-container { max-width: 1200px; margin: 0 auto; }
        h1, h2 { color: #ff4d6d; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        .btn { padding: 8px 14px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-add { background: #2ecc71; color: white; margin-bottom: 15px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #ff4d6d; color: white; }
        
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-pending { background: #ffeaa7; color: #d63031; }
        .badge-delivered { background: #badc58; color: #6ab04c; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 999; }
        .modal-content { background: white; padding: 25px; border-radius: 8px; width: 450px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-control { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    </style>
</head>
<body>

<div class="admin-container">
    <h1>Dashboard Quản Trị - Trà Sữa Homie</h1>
    <p>Chào mừng Admin! Quản lý thông tin đơn hàng đặt mua của khách và cập nhật thực đơn.</p>

    <div class="card">
        <h2>📦 Danh Sách Đơn Hàng Cần Xử Lý</h2>
        <table>
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th>Chi Tiết Món Đặt & Topping</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Câu lệnh SQL liên kết nâng cao lấy thông tin Số lượng, Đường, Đá, Topping từ bảng order_items ra
                $sql_orders = "SELECT o.*, u.full_name as user_name, oi.quantity, oi.sugar_level, oi.ice_level, oi.topping_note, p.product_name 
                               FROM orders o
                               LEFT JOIN users u ON o.user_id = u.user_id
                               LEFT JOIN order_items oi ON o.order_id = oi.order_id
                               LEFT JOIN products p ON oi.product_id = p.product_id
                               ORDER BY o.order_id DESC";
                $result_orders = mysqli_query($conn, $sql_orders);

                if (mysqli_num_rows($result_orders) > 0) {
                    while ($order = mysqli_fetch_assoc($result_orders)) {
                        ?>
                        <tr>
                            <td>#<?= $order['order_id'] ?></td>
                            <td><strong><?= htmlspecialchars($order['user_name']) ?></strong></td>
                            <td>
                                <?php if($order['product_name']): ?>
                                    <div style="background: #fff5f5; padding: 8px; border-radius: 4px; border-left: 3px solid #ff4d6d; font-size: 13px;">
                                        • <strong><?= htmlspecialchars($order['product_name']) ?></strong><br>
                                        • Số lượng: <span style="color:red; font-weight:bold;">x<?= $order['quantity'] ?></span><br>
                                        <?php if($order['sugar_level'] > 0 || $order['ice_level'] > 0): ?>
                                            • Tùy chọn: Đường (<?= $order['sugar_level'] ?>%) | Đá (<?= $order['ice_level'] ?>%)<br>
                                        <?php endif; ?>
                                        • Topping/Ghi chú: <span style="color:#0984e3; font-weight:bold;"><em><?= !empty($order['topping_note']) ? htmlspecialchars($order['topping_note']) : 'Không có' ?></em></span>
                                    </div>
                                <?php else: ?>
                                    <span style="color:#aaa;">Món ăn trống hoặc đã bị xóa</span>
                                <?php endif; ?>
                            </td>
                            <td><strong style="color: #e17055;"><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</strong></td>
                            <td>
                                <span class="badge badge-<?= $order['status'] == 'pending' ? 'pending' : 'delivered' ?>">
                                    <?= $order['status'] == 'pending' ? 'Chờ duyệt' : 'Đã giao' ?>
                                </span>
                            </td>
                            <td>
                                <form action="" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <select name="status" class="form-control" style="width:120px; display:inline-block; padding:4px;">
                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                                        <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Đã giao</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn" style="background:#ff4d6d; color:white; padding:4px 10px;">Lưu</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>Chưa có đơn hàng nào trong hệ thống database.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>🛠️ Quản Lý Thực Đơn Món Ăn</h2>
        <button class="btn btn-add" onclick="openModal('addProductModal')">+ Thêm Món Mới Vào Thực Đơn</button>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Món Ăn</th>
                    <th>Danh Mục</th>
                    <th>Giá Bán</th>
                    <th>Mô Tả</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_prods = "SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_id DESC";
                $result_prods = mysqli_query($conn, $sql_prods);
                while($prod = mysqli_fetch_assoc($result_prods)) {
                    ?>
                    <tr>
                        <td><?= $prod['product_id'] ?></td>
                        <td><strong><?= htmlspecialchars($prod['product_name']) ?></strong></td>
                        <td><?= htmlspecialchars($prod['category_name']) ?></td>
                        <td><?= number_format($prod['price'], 0, ',', '.') ?>đ</td>
                        <td><?= htmlspecialchars($prod['description']) ?></td>
                        <td>
                            <button class="btn" style="background:#0984e3; color:white; padding:4px 8px;" onclick="openEditModal(<?= $prod['product_id'] ?>, '<?= htmlspecialchars($prod['product_name']) ?>', <?= $prod['category_id'] ?>, <?= $prod['price'] ?>, '<?= htmlspecialchars($prod['description']) ?>')">Sửa</button>
                            <button class="btn" style="background:#d63031; color:white; padding:4px 8px;" onclick="confirmDelete(<?= $prod['product_id'] ?>, '<?= htmlspecialchars($prod['product_name']) ?>')">Xóa</button>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addProductModal" class="modal">
    <div class="modal-content">
        <h2>Thêm Món Mới</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Tên món ăn / thức uống</label>
                <input type="text" name="product_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Danh mục</label>
                <select name="category_id" class="form-control">
                    <?php foreach($cat_list as $c) { echo "<option value='{$c['category_id']}'>{$c['category_name']}</option>"; } ?>
                </select>
            </div>
            <div class="form-group">
                <label>Giá bán (VNĐ)</label>
                <input type="number" name="price" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Hình ảnh sản phẩm</label>
                <input type="file" name="product_image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>Mô tả chi tiết</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div style="text-align: right; margin-top: 15px;">
                <button type="button" class="btn" style="background:#ccc;" onclick="closeModal('addProductModal')">Hủy bỏ</button>
                <button type="submit" name="add_product" class="btn" style="background: #2ecc71; color:white;">Lưu Lại</button>
            </div>
        </form>
    </div>
</div>

<div id="editProductModal" class="modal">
    <div class="modal-content">
        <h2>Chỉnh Sửa Món Ăn</h2>
        <form action="" method="POST">
            <input type="hidden" name="product_id" id="edit_product_id">
            <div class="form-group">
                <label>Tên món ăn</label>
                <input type="text" name="product_name" id="edit_product_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Danh mục</label>
                <select name="category_id" id="edit_category_id" class="form-control">
                    <?php foreach($cat_list as $c) { echo "<option value='{$c['category_id']}'>{$c['category_name']}</option>"; } ?>
                </select>
            </div>
            <div class="form-group">
                <label>Giá bán</label>
                <input type="number" name="price" id="edit_price" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Mô tả</label>
                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
            </div>
            <div style="text-align: right; margin-top: 15px;">
                <button type="button" class="btn" style="background:#ccc;" onclick="closeModal('editProductModal')">Hủy bỏ</button>
                <button type="submit" name="edit_product" class="btn" style="background: #ff4d6d; color:white;">Cập Nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(modalId) { document.getElementById(modalId).style.display = 'flex'; }
    function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }

    function openEditModal(id, name, category_id, price, description) {
        document.getElementById('edit_product_id').value = id;
        document.getElementById('edit_product_name').value = name;
        document.getElementById('edit_category_id').value = category_id;
        document.getElementById('edit_price').value = price;
        document.getElementById('edit_description').value = description;
        document.getElementById('editProductModal').style.display = 'flex';
    }

    function confirmDelete(id, name) {
        if (confirm("Bạn có chắc chắn muốn xóa món \"" + name + "\"?")) {
            window.location.href = "admin.php?delete_id=" + id;
        }
    }

    window.onclick = function(event) {
        let addModal = document.getElementById('addProductModal');
        let editModal = document.getElementById('editProductModal');
        if (event.target == addModal) addModal.style.display = 'none';
        if (event.target == editModal) editModal.style.display = 'none';
    }
</script>
</body>
</html>
