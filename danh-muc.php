<?php
if (file_exists('includes/db-connect.php')) {
    include_once 'includes/db-connect.php';
} elseif (file_exists('db-connect.php')) { 
    include_once 'db-connect.php';
}

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