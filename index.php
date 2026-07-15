<?php
// File gốc của website (index.php) - khi truy cập localhost/web_tra_sua
// hệ thống sẽ ưu tiên nạp file này trước, nên ta chuyển hướng thẳng
// sang Trang Chủ (trang-chu.php) để khách vào đúng trang chủ ngay từ đầu.
header("Location: trang-chu.php");
exit();
 