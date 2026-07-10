<?php
// Kiểm tra xem file kết nối database có tồn tại không để tránh lỗi crash trang
if (file_exists('db-connect.php')) {
    include_once 'db-connect.php';
}

// Kiểm tra biến kết nối $conn từ file db-connect.php có hoạt động không
$has_database = false;
if (isset($conn) && $conn instanceof mysqli) {
    $sql_categories = "SELECT * FROM categories ORDER BY category_id ASC";
    $result_categories = mysqli_query($conn, $sql_categories);
    if ($result_categories) {
        $has_database = true;
    }
}
?>

<div class="homie-category-hover-container">
    
    <div class="homie-category-title-btn">
        <span>☰</span> DANH MỤC MÓN
    </div>
    
    <ul class="homie-category-dropdown-list">
        <?php 
        if ($has_database && mysqli_num_rows($result_categories) > 0) {
            while ($row = mysqli_fetch_assoc($result_categories)) {
                $cat_id = $row['category_id'];
                $cat_name = $row['category_name'];
                ?>
                <li>
                    <a href="#danh-muc-<?php echo $cat_id; ?>" class="homie-cat-item">
                        <span><?php echo htmlspecialchars($cat_name); ?></span>
                        <span class="arrow">&gt;</span>
                    </a>
                </li>
                <?php
            }
        } else {
            $menu_tinh = [
                '1' => 'Ăn vặt',
                '2' => 'Cà phê',
                '3' => 'Khác',
                '4' => 'Mỳ cay – Lẩu',
                '5' => 'Sinh tố',
                '6' => 'Trà sữa',
                '7' => 'Trà trái cây tươi'
            ];
            
            foreach ($menu_tinh as $key => $value) {
                ?>
                <li>
                    <a href="#danh-muc-<?php echo $key; ?>" class="homie-cat-item">
                        <span><?php echo $value; ?></span>
                        <span class="arrow">&gt;</span>
                    </a>
                </li>
                <?php
            }
        }
        ?>
    </ul>
</div>

<style>
/* ==========================================
   TỰ ĐỘNG ĐỔI MÀU NỀN TOÀN BỘ TRANG WEB
   ========================================== */
body {
    background-color: #fcf1e3 !important; /* Màu da người nhạt / Be trà sữa ngọt ngào ấm áp */
}

/* ==========================================
   CSS CHO KHỐI DANH MỤC HOVER
   ========================================== */
/* Khung viền bao quanh Danh Mục được đổi sang màu da đậm hơn tí để nổi khối rõ ràng */
.homie-category-hover-container {
    position: relative;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    width: 100%;
    box-sizing: border-box;
    border: 2px solid #ecd3b9; 
    background-color: #fffcf7;  
    padding: 6px;
    border-radius: 16px;
}

/* Nút tiêu đề Danh mục chính màu cam gốc của nhóm */
.homie-category-title-btn {
    background-color: #ff7066;
    color: #ffffff;
    padding: 14px 18px;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 14px;
    letter-spacing: 0.8px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(255, 112, 102, 0.15);
    transition: all 0.3s ease;
}

/* Khi rê chuột vào khối container */
.homie-category-hover-container:hover .homie-category-title-btn {
    background-color: #ff5252;
}

/* Danh sách menu - Xổ xuống đè mượt mà lên sản phẩm */
.homie-category-dropdown-list {
    list-style: none;
    padding: 0;
    margin: 0;
    background-color: #ffffff;
    border: 1px solid #ecd3b9;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(236, 211, 185, 0.3);
    overflow: hidden;
    position: absolute;
    width: calc(100% - 12px);
    left: 6px;
    top: calc(100% + 4px);
    z-index: 99999;
    max-height: 0;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.homie-category-hover-container:hover .homie-category-dropdown-list {
    max-height: 450px;
    opacity: 1;
    visibility: visible;
}

/* Định dạng các mục nhỏ bên trong */
.homie-cat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 13px 18px;
    text-decoration: none;
    color: #444444;
    font-weight: 500;
    font-size: 14px;
    border-bottom: 1px solid #fff3e8;
    transition: all 0.2s ease;
}

.homie-category-dropdown-list li:last-child .homie-cat-item {
    border-bottom: none;
}

.homie-cat-item .arrow {
    color: #ff7066;
    font-weight: bold;
    font-size: 11px;
}

/* Hiệu ứng lướt chuột qua từng danh mục nhỏ */
.homie-cat-item:hover {
    background-color: #fff3e8 !important; /* Đổi sang tông nền trà sữa nhạt khi hover */
    color: #ff7066 !important;
    padding-left: 23px !important;
}
</style>