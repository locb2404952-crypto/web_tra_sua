<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trà Sữa Homie</title>
    <!-- Thư viện icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Nhúng file CSS tổng tập trung bằng đường dẫn tuyệt đối ổn định -->
    <link rel="stylesheet" href="/web_tra_sua/css/style.css">
</head>
<body>

<div id="toastNotify" class="toast-notification">
    <i class="fa-solid fa-circle-check"></i> <span id="toastMessage">Đã thêm món vào giỏ hàng thành công!</span>
</div>

<header>
    <div class="auth-buttons"> 
        <?php if(isset($_SESSION['username'])): ?>
            <span><i class="fa-solid fa-user"></i> Xin chào, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <a href="dang-xuat.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng Xuất</a>
        <?php else: ?>
            <a href="dang-nhap.php"><i class="fa-solid fa-user-lock"></i> Đăng Nhập</a>
            <a href="dang-ky.php"><i class="fa-solid fa-user-plus"></i> Đăng Ký</a>
        <?php endif; ?>
    </div>
    <h1>🧋 Trà Sữa Homie 🧋</h1>  <p>Thơm ngon từng giọt - Đậm vị yêu thương</p>
    
    <div class="main-menu" style="margin-top: 15px; margin-bottom: 5px;">
        <a href="trang-chu.php"><i class="fa-solid fa-house"></i> Trang Chủ</a>
        <a href="index.php"><i class="fa-solid fa-utensils"></i> Thực Đơn</a>
        <a href="lich-su-don-hang.php"><i class="fa-solid fa-clipboard-list"></i> Lịch Sử Đơn Hàng</a>
        <a href="lien-he.php"><i class="fa-solid fa-envelope"></i> Liên Hệ</a> 
    </div>
</header>