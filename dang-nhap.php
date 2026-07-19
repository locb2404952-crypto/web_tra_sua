<?php
// Kiểm tra và bật Session (nếu chưa có) để tránh xung đột với header
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
            
            // KIỂM TRA MẬT KHẨU THÔ (Giữ nguyên theo logic cũ của bạn)
            if ($input_password == $user['password']) {
                
                // ĐĂNG NHẬP THÀNH CÔNG: Lưu thông tin vào Session
                $_SESSION['user_id'] = $user['user_id'];      
                $_SESSION['username'] = $user['full_name'];   
                $_SESSION['role'] = $user['role'];           
                
                // Điều hướng thông minh dựa vào quyền hạn của tài khoản
                if ($_SESSION['role'] === 'admin') {
                    header("Location: admin.php"); 
                } else {
                    header("Location: index.php"); 
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

// 1. NHÚNG HEADER VÀO ĐẦU TRANG
include 'includes/header.php';
?>

<!-- Bọc form vào cấu trúc container chuẩn để giao diện cân đối, mượt mà giữa Header và Footer -->
<div class="container" style="max-width: 450px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin: 40px auto;">
    <h2 style="text-align: center; color: #ff7675; margin-bottom: 25px;">Đăng Nhập</h2>
    
    <?php if($error) echo "<p class='error' style='color: red; background: #ffe0e0; padding: 10px; border-radius: 5px; margin-bottom: 20px;'>$error</p>"; ?>
    
    <!-- Áp dụng class form-control để input đồng bộ theo style.css tổng -->
    <form action="" method="POST">
        <div class="form-group" style="margin-bottom: 15px;">
            <label>Địa chỉ Email</label>
            <input type="email" name="email" class="form-control" placeholder="Nhập địa chỉ Email (VD: admin@trasua.com)" required>
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" placeholder="Nhập Mật khẩu" required>
        </div>
        
        <button type="submit" class="btn-select" style="width: 100%; padding: 12px; font-size: 16px;">Đăng Nhập</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px; font-size: 14px;">
        Chưa có tài khoản Homie? <a href="dang-ky.php" style="color: #ff7675; font-weight: bold; text-decoration: none;">Đăng ký ngay</a>
    </p>
</div>

<?php 
// 2. NHÚNG FOOTER VÀO CUỐI TRANG
include 'includes/footer.php'; 
?>