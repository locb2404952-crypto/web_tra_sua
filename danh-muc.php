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
            echo '<li style="padding: 12px; text-align: center; color: #95a5a6; font-size: 13px;">Chưa có danh mục</li>';
        }
        ?>
    </ul>
</div>

<style>
/* Cấu hình hiệu ứng cuộn toàn trang mượt mà */
html {
    scroll-behavior: smooth;
}

/* Định dạng thanh danh mục gốc */
.homie-category-hover-container {
    position: sticky;        /* Giữ cố định khi cuộn màn hình */
    top: 15px;               /* Cách mép trên cùng của màn hình 15px khi cuộn xuống */
    z-index: 999;            /* Đảm bảo menu luôn nổi lên trên các thẻ sản phẩm khác */
    
    width: 100%;
    max-width: 260px;
    background-color: #fffaf5;
    border: 2px solid #ecd3b9;
    border-radius: 12px;
    padding: 6px;
    box-sizing: border-box;
    box-shadow: 0 4px 15px rgba(236, 211, 185, 0.2);
}

.homie-category-title-btn {
    background-color: #ff7675;
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    font-weight: bold;
    font-size: 15px;
    letter-spacing: 0.5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: background-color 0.2s;
}

.homie-category-title-btn:hover {
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
    padding: 12px 16px;
    color: #2d3436;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    border-bottom: 1px solid #fcf6f0;
    transition: all 0.2s ease;
}

.homie-cat-item:last-child {
    border-bottom: none;
}

.homie-cat-item:hover {
    background-color: #fff0e6;
    color: #ff7675;
    padding-left: 22px;
}

.homie-cat-item .arrow {
    color: #b2bec3;
    font-size: 12px;
    transition: transform 0.2s;
}

.homie-cat-item:hover .arrow {
    color: #ff7675;
    transform: translateX(3px);
}

/* Khi bấm nhảy link danh mục, tiêu đề của danh mục đó không bị che mất bởi thanh menu trượt */
.category-block {
    scroll-margin-top: 80px; 
}
</style>