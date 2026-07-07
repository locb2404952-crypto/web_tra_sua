<?php
session_start(); // Kích hoạt session để máy biết đang xóa session nào
session_unset(); // Xóa bỏ tất cả các biến session đã lưu
session_destroy(); // Hủy toàn bộ phiên làm việc của session này

// Sau khi xóa xong, lập tức chuyển hướng quay lại trang chủ index.php
header("Location: index.php");
exit();
?>