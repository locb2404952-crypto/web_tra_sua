<?php
session_start(); // Kích hoạt Session để lưu trạng thái đăng nhập
require_once 'includes/db-connect.php'; 

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nhận dữ liệu từ form nhập vào
    $input_email = trim($_POST['email']); 
    $input_password = $_POST['password'];

    if (empty($input_email) || empty($input_password)) {
        $error = "Vui lòng nhập đầy đủ email và mật khẩu!";
    } else {
        // Lấy thêm cột 'role' từ database để phân quyền
        $stmt = $conn->prepare("SELECT user_id, email, password, full_name, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $input_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // KIỂM TRA MẬT KHẨU: 
            if ($input_password == $user['password']) {
                
                // ĐĂNG NHẬP THÀNH CÔNG: Lưu thông tin vào Session
                $_SESSION['user_id'] = $user['user_id'];      // Mã user_id thật gửi cho Huy kết nối đơn hàng
                $_SESSION['username'] = $user['full_name'];   // Lưu tên hiển thị 
                $_SESSION['role'] = $user['role'];           // Quyết định quyền truy cập trang Admin
                
                // Điều hướng thông minh dựa vào quyền hạn của tài khoản
                if ($_SESSION['role'] === 'admin') {
                    header("Location: admin.php"); // Nếu là admin thì bay thẳng vào trang quản lý
                } else {
                    header("Location: index.php"); // Nếu là khách thường thì về trang chủ đặt trà sữa
                }
                exit();
            } else {
                $error = "Mật khẩu không chính xác!";
            }
        } else {
            $error = "Tài khoản email này không tồn tại!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập - Trà Sữa Homie</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f5f5f5; margin: 0; }
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 320px; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #0056b3; }
        .error { color: red; background: #f8d7da; padding: 8px; border-radius: 4px; text-align: center; font-size: 14px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Đăng Nhập</h2>
        
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        
        <form action="" method="POST">
            <input type="email" name="email" placeholder="Nhập địa chỉ Email (VD: admin@trasua.com)" required>
            <input type="password" name="password" placeholder="Nhập Mật khẩu" required>
            <button type="submit">Đăng Nhập</button>
        </form>
    </div>
</body>
</html>