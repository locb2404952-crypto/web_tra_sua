<?php
// 1. Nhúng file kết nối database
require_once 'db-connect.php';

// 2. XỬ LÝ KHI NGƯỜI DÙNG BẤM LƯU THÊM MÓN MỚI
if (isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    if (!empty($product_name) && $price > 0 && $category_id > 0) {
        $sql_insert = "INSERT INTO products (category_id, product_name, price, description) 
                       VALUES ($category_id, '$product_name', $price, '$description')";
        if ($conn->query($sql_insert)) {
            header("Location: admin.php");
            exit();
        }
    }
}

// 3. XỬ LÝ KHI NGƯỜI DÙNG CẬP NHẬT (SỬA) MÓN ĂN
if (isset($_POST['edit_product'])) {
    $product_id = intval($_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    if ($product_id > 0 && !empty($product_name) && $price > 0 && $category_id > 0) {
        $sql_update = "UPDATE products 
                       SET category_id = $category_id, product_name = '$product_name', price = $price, description = '$description' 
                       WHERE product_id = $product_id";
        if ($conn->query($sql_update)) {
            header("Location: admin.php");
            exit();
        }
    }
}

// 4. Lấy danh sách danh mục để bỏ vào ô chọn (Select box)
$sql_cate = "SELECT * FROM categories";
$result_cate = $conn->query($sql_cate);
$categories = [];
if ($result_cate && $result_cate->num_rows > 0) {
    while($row_c = $result_cate->fetch_assoc()) {
        $categories[] = $row_c;
    }
}

// 5. Lấy toàn bộ sản phẩm và tên danh mục hiển thị lên bảng
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.product_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Lý - Quán Trà Sữa & Đồ Ăn Vặt Homie</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background-color: #fff5f7; color: #333; padding: 20px; }
        .admin-container { max-width: 1000px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .header-admin { text-align: center; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 2px solid #ffe1e9; }
        .header-admin h1 { color: #ff6b8b; font-size: 28px; text-transform: uppercase; }
        .product-table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
        .product-table th { background-color: #ff8da1; color: white; font-weight: 600; padding: 12px 15px; text-align: left; }
        .product-table td { padding: 12px 15px; border-bottom: 1px solid #ffe1e9; vertical-align: middle; }
        .product-table tbody tr:hover { background-color: #fff9fa; }
        .product-price { color: #ff4d6d; font-weight: bold; }
        .btn { padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-block; transition: all 0.2s; }
        .btn-edit { background-color: #ffb3c1; color: #fff; margin-right: 5px; }
        .btn-edit:hover { background-color: #ff8da1; }
        .btn-delete { background-color: #ff4d6d; color: #fff; }
        .btn-delete:hover { background-color: #c9184a; }
        .btn-add { background-color: #ff6b8b; color: white; padding: 10px 15px; margin-bottom: 15px; float: right; }
        .btn-add:hover { background-color: #ff4d6d; }
        .clearfix::after { content: ""; clear: both; display: table; }

        /* Cửa sổ Form ẩn bật lên (Modal Popup) */
        .modal { display: none; position: fixed; z-index: 100; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); justify-content: center; align-items: center; }
        .modal-content { background-color: #fff; padding: 25px; border-radius: 12px; width: 450px; box-shadow: 0 5px 20px rgba(0,0,0,0.2); animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-title { color: #ff6b8b; margin-bottom: 20px; font-size: 20px; border-bottom: 2px solid #fff5f7; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px; color: #555; }
        .form-control { width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; outline: none; font-size: 14px; }
        .form-control:focus { border-color: #ff8da1; }
        .modal-buttons { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .btn-close { background-color: #eee; color: #333; }
        .btn-save { background-color: #ff6b8b; color: white; }
    </style>
</head>
<body>

<div class="admin-container">
    <div class="header-admin">
        <h1>Hệ Thống Quản Lý Cửa Hàng</h1>
    </div>

    <div class="clearfix">
        <button class="btn btn-add" onclick="openAddModal()">+ Thêm Món Mới</button>
    </div>

    <table class="product-table">
        <thead>
            <tr>
                <th style="width: 8%;">STT</th>
                <th style="width: 40%;">Tên món ăn / Đồ uống</th>
                <th style="width: 20%;">Danh mục</th>
                <th style="width: 17%;">Giá bán</th>
                <th style="width: 15%; text-align: center;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result && $result->num_rows > 0) {
                $stt = 1;
                while($row = $result->fetch_assoc()) {
                    ?>
                    <tr>
                        <td><?php echo $stt++; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['product_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Chưa phân loại'); ?></td>
                        <td class="product-price"><?php echo number_format($row['price'], 0, ',', '.'); ?>đ</td>
                        <td style="text-align: center;">
                            <button class="btn btn-edit" onclick="openEditModal(
                                '<?php echo $row['product_id']; ?>',
                                '<?php echo addslashes($row['product_name']); ?>',
                                '<?php echo $row['category_id']; ?>',
                                '<?php echo $row['price']; ?>',
                                '<?php echo addslashes($row['description']); ?>'
                            )">Sửa</button>
                            <button class="btn btn-delete">Xóa</button>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>Không có món ăn nào trong database.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<div id="addProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-title">Thêm Sản Phẩm Mới</div>
        <form action="admin.php" method="POST">
            <div class="form-group">
                <label>Tên món ăn / thức uống:</label>
                <input type="text" name="product_name" class="form-control" placeholder="Ví dụ: Trà sữa Matcha" required>
            </div>
            <div class="form-group">
                <label>Danh mục mặt hàng:</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php foreach ($categories as $cate): ?>
                        <option value="<?php echo $cate['category_id']; ?>"><?php echo htmlspecialchars($cate['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Giá bán (VNĐ):</label>
                <input type="number" name="price" class="form-control" placeholder="Ví dụ: 35000" required>
            </div>
            <div class="form-group">
                <label>Mô tả ngắn:</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Vị béo ngậy ngon tuyệt..."></textarea>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn btn-close" onclick="closeModal('addProductModal')">Hủy bỏ</button>
                <button type="submit" name="add_product" class="btn btn-save">Lưu Lại</button>
            </div>
        </form>
    </div>
</div>

<div id="editProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-title">Chỉnh Sửa Sản Phẩm</div>
        <form action="admin.php" method="POST">
            <input type="hidden" name="product_id" id="edit_product_id">
            
            <div class="form-group">
                <label>Tên món ăn / thức uống:</label>
                <input type="text" name="product_name" id="edit_product_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Danh mục mặt hàng:</label>
                <select name="category_id" id="edit_category_id" class="form-control" required>
                    <?php foreach ($categories as $cate): ?>
                        <option value="<?php echo $cate['category_id']; ?>"><?php echo htmlspecialchars($cate['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Giá bán (VNĐ):</label>
                <input type="number" name="price" id="edit_price" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Mô tả ngắn:</label>
                <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn btn-close" onclick="closeModal('editProductModal')">Hủy bỏ</button>
                <button type="submit" name="edit_product" class="btn btn-save">Cập Nhật</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Hàm mở form Thêm
    function openAddModal() {
        document.getElementById('addProductModal').style.display = 'flex';
    }
    
    // Hàm mở form Sửa và tự động đổ dữ liệu cũ của dòng đó vào các ô input
    function openEditModal(id, name, category_id, price, description) {
        document.getElementById('edit_product_id').value = id;
        document.getElementById('edit_product_name').value = name;
        document.getElementById('edit_category_id').value = category_id;
        document.getElementById('edit_price').value = price;
        document.getElementById('edit_description').value = description;
        document.getElementById('editProductModal').style.display = 'flex';
    }

    // Hàm đóng form chung
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Đóng khi click ra ngoài vùng trắng của pop-up
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
</script>

</body>
</html>
