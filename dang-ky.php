<?php
require_once 'includes/db-connect.php'; // Nhúng file kết nối database của bạn

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
            // Chèn tài khoản mới vào bảng users (Lưu mật khẩu thô theo cấu trúc cũ của bạn)
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

// 1. NHÚNG HEADER VÀO ĐẦU TRANG (Thay thế cho các thẻ <!DOCTYPE html>, <head> cũ để tránh trùng lặp)
include 'includes/header.php';
?>

<!-- Bọc form vào class container của hệ thống để căn giữa sạch đẹp giữa Header và Footer -->
<div class="container" style="max-width: 500px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin: 40px auto;">
    <h2 style="text-align: center; color: #ff7675; margin-bottom: 25px;">Đăng Ký Tài Khoản</h2>
    
    <?php if($error) echo "<p class='error' style='color: red; background: #ffe0e0; padding: 10px; border-radius: 5px;'>$error</p>"; ?>
    <?php if($success) echo "<p class='success' style='color: green; background: #e1f7ec; padding: 10px; border-radius: 5px;'>$success</p>"; ?>
    
    <!-- Sử dụng class form-control để các ô nhập liệu tự động ăn theo file style.css tổng -->
    <form action="" method="POST">
        <div class="form-group" style="margin-bottom: 15px;">
            <label>Họ và tên của bạn</label>
            <input type="text" name="full_name" class="form-control" placeholder="Họ và tên của bạn" required>
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label>Địa chỉ Email (Dùng để đăng nhập)</label>
            <input type="email" name="email" class="form-control" placeholder="Địa chỉ Email (Dùng để đăng nhập)" required>
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label>Số điện thoại</label>
            <input type="text" name="phone" class="form-control" placeholder="Số điện thoại" required>
        </div>
        <div class="form-group" style="margin-bottom: 15px;">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
        </div>
        <div class="form-group" style="margin-bottom: 20px;">
            <label>Nhập lại mật khẩu</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required>
        </div>
        
        <button type="submit" class="btn-select" style="width: 100%; padding: 12px; font-size: 16px;">Đăng Ký</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px; font-size: 14px;">
        Đại gia đình Homie đã có tài khoản? <a href="dang-nhap.php" style="color: #ff7675; font-weight: bold; text-decoration: none;">Đăng nhập</a>
    </p>
</div>

<?php 
// 2. NHÚNG FOOTER VÀO CUỐI TRANG
include 'includes/footer.php'; 
?>