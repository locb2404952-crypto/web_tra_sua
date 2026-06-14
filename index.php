<?php
// kết nối cơ sở dữ liệu vào
require_once 'db-connect.php';

// 1. Lấy danh sách các danh mục để làm thanh chọn
$sql_categories = "SELECT * FROM categories";
$result_categories = $conn->query($sql_categories);

// 2. Lấy toàn bộ sản phẩm kèm theo tên danh mục của nó
$sql_products = "SELECT p.*, c.category_name 
                 FROM products p 
                 JOIN categories c ON p.category_id = c.category_id 
                 ORDER BY p.category_id ASC, p.product_name ASC";
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
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>QUÁN TRÀ SỮA & ĐỒ ĂN VẶT HOMIE</h1>
        <p>Menu siêu ngon - Càng ăn càng ghiền!</p>
    </header>

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
                                <button class="btn-order" onclick="moForm()">Đặt món</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; font-size: 1.2rem; color: #999;">Hiện tại chưa có món ăn nào trong hệ thống.</p>
    <?php endif; ?>
</div>

// none ở đây khi mở wed lên nó sẽ ẩn đi cho tới khi họ bấm nút đặt món
<div id="orderModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 9999;">
    <div style="background: #fff; padding: 24px; border-radius: 12px; width: 90%; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; margin-bottom: 15px; color: #333;">Thông Tin Đặt Hàng</h3>

        //khi họ đièn xong và bấm nút xác nhận đặt code sẽ chặn wed kco tải lại và bắn ra thông báo đặt đc ch rồi sẽ dongForm()
        <form id="formDatHang" onsubmit="event.preventDefault(); alert('Tuần 1: Chạy thử thành công! Đã bắt được dữ liệu đặt hàng.'); dongForm();">
            <div style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 6px; font-weight: bold; font-size: 0.9rem;">Họ và tên:</label>
                <input type="text" id="khachHangTen" placeholder="Nhập họ và tên của bạn" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 6px; font-weight: bold; font-size: 0.9rem;">Số điện thoại:</label>
                <input type="text" id="khachHangSdt" placeholder="Nhập số điện thoại" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box;">
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="dongForm()" style="padding: 8px 16px; background: #eee; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Hủy</button>
                <button type="submit" style="padding: 8px 16px; background: #ff7675; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">Xác nhận đặt</button>
            </div>
        </form>
    </div>
</div>

<script>
    // moForm(): Tìm đến cái hộp thoại bằng ID và đổi display thành flex để nó hiện lên màn hình
function moForm() {
    document.getElementById('orderModal').style.display = 'flex';
}
   // dongForm(): Tìm đến hộp thoại bằng ID và đổi display về lại none để giấu nó đi
function dongForm() {
    document.getElementById('orderModal').style.display = 'none';
}
</script>
</body>
</html>