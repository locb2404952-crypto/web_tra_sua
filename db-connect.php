<?php
// Thông tin cấu hình kết nối MySQL XAMPP
$host = "localhost";
$username = "root";
$password = "";
$dbname = "web_tra_sua"; // Tên database 5 bảng của bạn

// Tiến hành kết nối
$conn = new mysqli($host, $username, $password, $dbname);

// Kiểm tra xem kết nối có bị lỗi không
if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

// Cấu hình hiển thị tiếng Việt có dấu không bị lỗi font
$conn->set_charset("utf8mb4");


