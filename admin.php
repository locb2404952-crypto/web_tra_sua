<?php
// 1. Nhúng file kết nối database  
require_once 'db-connect.php';

// 2. XỬ LÝ CHỨC NĂNG: THÊM MÓN MỚI 
if (isset($_POST['add_product'])) {
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $image_url = 'default.png'; 

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "images/"; 
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION);
        $new_file_name = time() . '_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_file_name;

        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            $image_url = $target_file; 
        }
    }

    if (!empty($product_name) && $category_id > 0 && $price > 0) {
        $sql_add = "INSERT INTO products (category_id, product_name, price, description, image_url) 
                    VALUES ($category_id, '$product_name', $price, '$description', '$image_url')";
        mysqli_query($conn, $sql_add);
        header("Location: admin.php");
        exit();
    }
}

// 3. XỬ LÝ CHỨC NĂNG: XÓA MÓN
if (isset($_GET['delete_id'])) {
    $product_id = intval($_GET['delete_id']);
    $sql_delete = "DELETE FROM products WHERE product_id = $product_id";
    mysqli_query($conn, $sql_delete);
    header("Location: admin.php");
    exit();
} 
// 4. XỬ LÝ CHỨC NĂNG: SỬA MÓN (BAO GỒM ĐỔI ẢNH LƯU VÀO IMAGES)
if (isset($_POST['edit_product'])) {
    $product_id = intval($_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    if (!empty($product_name) && $price > 0) {
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            $target_dir = "images/"; 
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $file_extension = pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION);
            $new_file_name = time() . '_' . uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
                $sql_update = "UPDATE products 
                               SET product_name = '$product_name', category_id = $category_id, price = $price, description = '$description', image_url = '$target_file' 
                               WHERE product_id = $product_id";
            }
        } else {
            $sql_update = "UPDATE products 
                           SET product_name = '$product_name', category_id = $category_id, price = $price, description = '$description' 
                           WHERE product_id = $product_id";
        }
        
        mysqli_query($conn, $sql_update);
        header("Location: admin.php");
        exit();
    }
}

// 5. Lấy danh sách sản phẩm hiển thị ra bảng
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.product_id DESC";
$result = mysqli_query($conn, $sql);

// 6. Lấy danh sách danh mục để đổ vào thẻ chọn <select>
$sql_cates = "SELECT * FROM categories ORDER BY category_name ASC";
$result_cates = mysqli_query($conn, $sql_cates);
$categories_list = [];
while($cat = mysqli_fetch_assoc($result_cates)) {
    $categories_list[] = $cat;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Quản Lý - Homie Tea & Snack</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body {
            background: linear-gradient(135deg, #fff5f7 0%, #fde2e4 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: #2d3748;
        }
        .admin-container {
            max-width: 1150px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(255, 154, 162, 0.15);
        }
        .header-admin { text-align: center; margin-bottom: 35px; }
        .header-admin h1 {
            background: linear-gradient(90deg, #ff4d6d, #ff758f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 32px;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .header-admin p { color: #718096; font-size: 14px; font-weight: 500; }
        
        .btn-trigger-add {
            background: linear-gradient(90deg, #ff4d6d, #ff758f);
            color: white; padding: 12px 24px; border: none; border-radius: 12px;
            font-size: 15px; font-weight: 700; cursor: pointer; margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(255, 77, 109, 0.3); transition: all 0.2s;
        }
        .btn-trigger-add:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255, 77, 109, 0.4); }

        .table-wrapper { width: 100%; border-radius: 16px; overflow: hidden; border: 1px solid #ffe5ec; }
        .product-table { width: 100%; border-collapse: collapse; background: #fff; text-align: left; }
        .product-table th { background: linear-gradient(90deg, #ff758f 0%, #ff8da1 100%); color: white; font-weight: 600; padding: 18px 24px; font-size: 14px; text-transform: uppercase; }
        .product-table td { padding: 16px 24px; border-bottom: 1px solid #ffe5ec; font-size: 15px; vertical-align: middle; }
        .product-table tbody tr:hover { background-color: #fff8f9; }
        
        .product-info { display: block; } /* Đổi từ flex sang block để tối ưu hiển thị chữ */
        .product-name-txt { font-weight: 600; color: #1a202c; font-size: 16px; }
        .product-desc-txt { font-size: 13px; color: #718096; margin-top: 4px; line-height: 1.4; }
        .stt-num { font-weight: 700; color: #ff758f; text-align: center; }
        .cate-badge { background: #ffe5ec; color: #ff4d6d; padding: 6px 14px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .product-price { color: #ff4d6d; font-weight: 700; font-size: 16px; }
        
        .btn-group { display: flex; gap: 8px; justify-content: center; }
        .btn { padding: 8px 16px; border: none; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s; }
        .btn-edit { background-color: #fff0f3; color: #ff4d6d; border: 1px solid #ffb3c1; }
        .btn-edit:hover { background-color: #ff4d6d; color: white; }
        .btn-delete { background-color: #fff5f5; color: #e53e3e; border: 1px solid #feb2b2; }
        .btn-delete:hover { background-color: #e53e3e; color: white; }

        .modal {
            display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(26, 32, 44, 0.4); backdrop-filter: blur(5px);
            justify-content: center; align-items: center; overflow-y: auto; padding: 20px;
        }
        .modal-content { background-color: #fff; padding: 35px; border-radius: 20px; width: 100%; max-width: 460px; animation: popUp 0.25s ease-out; }
        @keyframes popUp { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .modal-title { color: #ff4d6d; margin-bottom: 25px; font-size: 22px; font-weight: 700; text-align: center; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #4a5568; }
        .form-control { width: 100%; padding: 11px 16px; border: 1px solid #e2e8f0; border-radius: 10px; outline: none; font-size: 14px; transition: all 0.2s; }
        .form-control:focus { border-color: #ff8da1; box-shadow: 0 0 0 3px rgba(255, 141, 161, 0.15); }
        textarea.form-control { resize: none; height: 75px; }
        .modal-buttons { display: flex; justify-content: flex-end; gap: 12px; margin-top: 25px; }
    </style>
</head>
<body>

<div class="admin-container">
    <div class="header-admin">
        <h1>Hệ Thống Quản Lý Cửa Hàng</h1>
        <p>Bảng chỉnh sửa và cập nhật thực đơn quán trà sữa Homie</p>
    </div>

    <button class="btn-trigger-add" onclick="openModal('addProductModal')">
        + Thêm Món Mới Vào Menu
    </button>

    <div class="table-wrapper">
        <table class="product-table">
            <thead>
                <tr>
                    <th style="width: 8%; text-align: center;">STT</th>
                    <th style="width: 44%;">Thông tin món ăn / Đồ uống</th>
                    <th style="width: 18%;">Danh mục</th>
                    <th style="width: 15%;">Giá bán</th>
                    <th style="width: 15%; text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($result && mysqli_num_rows($result) > 0) {
                    $stt = 1;
                    while($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td class="stt-num"><?php echo $stt++; ?></td>
                            <td>
                                <div class="product-info">
                                    <div class="product-name-txt"><?php echo htmlspecialchars($row['product_name']); ?></div>
                                    <div class="product-desc-txt"><?php echo htmlspecialchars($row['description'] ? $row['description'] : 'Chưa có mô tả ngắn.'); ?></div>
                                </div>
                            </td>
                            <td><span class="cate-badge"><?php echo htmlspecialchars($row['category_name'] ? $row['category_name'] : 'Khác'); ?></span></td>
                            <td class="product-price"><?php echo number_format($row['price'], 0, ',', '.'); ?>đ</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-edit" onclick="openEditModal('<?php echo $row['product_id']; ?>', '<?php echo addslashes($row['product_name']); ?>', '<?php echo $row['category_id']; ?>', '<?php echo $row['price']; ?>', '<?php echo addslashes($row['description']); ?>')">Sửa</button>
                                    <button class="btn btn-delete" onclick="confirmDelete('<?php echo $row['product_id']; ?>', '<?php echo addslashes($row['product_name']); ?>')">Xóa</button>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center; padding: 30px; color: #a0aec0;'>Chưa có món ăn nào.</td></tr>";
                }
                mysqli_close($conn);
                ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-title">Thêm Sản Phẩm Mới</div>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Tên món ăn / Thức uống:</label>
                <input type="text" name="product_name" class="form-control" placeholder="Ví dụ: Trà sữa trân châu" required>
            </div>
            <div class="form-group">
                <label>Danh mục phân loại:</label>
                <select name="category_id" class="form-control" required>
                    <option value="">-- Chọn một danh mục --</option>
                    <?php foreach($categories_list as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Giá bán (VNĐ):</label>
                <input type="number" name="price" class="form-control" placeholder="Ví dụ: 35000" required>
            </div>
            <div class="form-group">
                <label>Ảnh sản phẩm (Để lưu trữ ra trang chủ):</label>
                <input type="file" name="product_image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>Mô tả chi tiết:</label>
                <textarea name="description" class="form-control" placeholder="Mô tả hương vị..."></textarea>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn" style="background:#edf2f7; color:#4a5568;" onclick="closeModal('addProductModal')">Hủy bỏ</button>
                <button type="submit" name="add_product" class="btn" style="background: #ff4d6d; color:white;">Lưu Lại</button>
            </div>
        </form>
    </div>
</div>

<div id="editProductModal" class="modal">
    <div class="modal-content">
        <div class="modal-title">Chỉnh Sửa Sản Phẩm</div>
        <form action="admin.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="edit_product_id">
            <div class="form-group">
                <label>Tên món ăn / Thức uống:</label>
                <input type="text" name="product_name" id="edit_product_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Danh mục phân loại:</label>
                <select name="category_id" id="edit_category_id" class="form-control" required>
                    <?php foreach($categories_list as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Giá bán mới (VNĐ):</label>
                <input type="number" name="price" id="edit_price" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Thay đổi ảnh mới (Để trống nếu giữ nguyên ảnh cũ):</label>
                <input type="file" name="product_image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>Mô tả sản phẩm:</label>
                <textarea name="description" id="edit_description" class="form-control"></textarea>
            </div>
            <div class="modal-buttons">
                <button type="button" class="btn" style="background:#edf2f7; color:#4a5568;" onclick="closeModal('editProductModal')">Hủy bỏ</button>
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
