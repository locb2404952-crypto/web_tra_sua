<?php
include 'db-connect.php'; // Nhúng file kết nối database của bạn

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $role = 'customer'; // Mặc định tài khoản mới đăng ký là Khách hàng

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($phone)) {
        $error = "Vui lòng nhập đầy đủ tất cả các thông tin!";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu nhập lại không khớp!";
    } else {
        // Kiểm tra xem Email này đã được ai đăng ký chưa
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Địa chỉ email này đã được sử dụng!";
        } else {
            // Chèn tài khoản mới vào bảng users (Lưu mật khẩu thô theo cấu trúc hiện tại của bạn)
            $stmt_insert = $conn->prepare("INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssss", $full_name, $email, $password, $phone, $role);
            
            if ($stmt_insert->execute()) {
                $success = "Đăng ký thành công! <a href='dang-nhap.php'>Đăng nhập ngay</a>";
            } else {
                $error = "Có lỗi xảy ra trong quá trình lưu dữ liệu!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký - Trà Sữa Homie</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f5f5f5; margin: 0; }
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 340px; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        input { width: 100%; padding: 11px; margin: 8px 0; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #218838; }
        .error { color: red; background: #f8d7da; padding: 8px; border-radius: 4px; text-align: center; font-size: 14px; margin-bottom: 10px; }
        .success { color: green; background: #d4edda; padding: 8px; border-radius: 4px; text-align: center; font-size: 14px; margin-bottom: 10px; }
        p { text-align: center; margin-top: 15px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Đăng Ký Tài Khoản</h2>
        
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <?php if($success) echo "<p class='success'>$success</p>"; ?>
        
        <form action="" method="POST">
            <input type="text" name="full_name" placeholder="Họ và tên của bạn" required>
            <input type="email" name="email" placeholder="Địa chỉ Email (Dùng để đăng nhập)" required>
            <input type="text" name="phone" placeholder="Số điện thoại" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
            <button type="submit">Đăng Ký</button>
        </form>
        
        <p>Đại gia đình Homie đã có tài khoản? <a href="dang-nhap.php">Đăng nhập</a></p>
    </div>
</body>
</html>