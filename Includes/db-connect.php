<?php
$servername = "localhost";
$username = "root";     // Mặc định của XAMPP và Laragon
$password = "";         // Mặc định của XAMPP để trống (nếu dùng Laragon cũng để trống)
$dbname = "web_tra_sua"; // Tên database mà cả nhóm thống nhất đặt giống nhau

// Tạo kết nối đến MySQL
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Kiểm tra kết nối xem có thành công không
if (!$conn) {
    die("Kết nối database thất bại: " . mysqli_connect_error());
}

// Cấu hình tiếng Việt để khi xuất dữ liệu ra web không bị lỗi font (???)
mysqli_set_charset($conn, "utf8mb4");
?>
