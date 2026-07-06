<?php
session_start();
require_once 'db-connect.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trà Sữa Homie - Trang Chủ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff7675;
            --secondary-color: #fab1a0;
            --dark-color: #2d3436;
            --light-color: #f9f9f9;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0; padding: 0;
            background-color: var(--light-color); color: var(--dark-color);
        }
        header {
            background-color: var(--primary-color); color: white;
            padding: 30px 20px; text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); position: relative;
        }
        header h1 { margin: 0; font-size: 32px; letter-spacing: 1px; }
        header p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.9; }

        .auth-buttons { position: absolute; top: 30px; right: 30px; }
        .auth-buttons a {
            color: white; text-decoration: none; margin-left: 15px;
            font-weight: bold; font-size: 14px; background-color: rgba(255, 255, 255, 0.2);
            padding: 6px 14px; border-radius: 20px; transition: all 0.3s ease;
        }
        .auth-buttons a:hover { background-color: white; color: var(--primary-color); }
        .auth-buttons span { color: white; font-weight: bold; margin-right: 10px; }

        /* HERO BANNER SECTION */
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('https://img.freepik.com/free-photo/delicious-bubble-tea-table_23-2150767554.jpg') no-repeat center center/cover;
            height: 450px; display: flex; flex-direction: column; justify-content: center; align-items: center;
            color: white; text-align: center; padding: 0 20px;
        }
        .hero-section h2 { font-size: 42px; margin-bottom: 10px; text-shadow: 2px 2px 8px rgba(0,0,0,0.5); }
        .hero-section p { font-size: 18px; margin-bottom: 30px; max-width: 60px0px; text-shadow: 1px 1px 5px rgba(0,0,0,0.5); }
        
        .btn-cta {
            background-color: var(--primary-color); color: white; text-decoration: none;
            padding: 15px 35px; font-size: 18px; font-weight: bold; border-radius: 30px;
            box-shadow: 0 5px 15px rgba(255, 118, 117, 0.4); transition: all 0.3s ease;
        }
        .btn-cta:hover { background-color: #ff5252; transform: scale(1.05); }

        /* GIỚI THIỆU CỬA HÀNG */
        .intro-container { max-width: 900px; margin: 60px auto; text-align: center; padding: 0 20px; }
        .intro-container h3 { font-size: 28px; color: #d63031; margin-bottom: 20px; }
        .intro-container p { font-size: 16px; color: #636e72; line-height: 1.6; }
        
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-top: 40px; }
        .feature-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .feature-card i { font-size: 40px; color: var(--primary-color); margin-bottom: 15px; }
        .feature-card h4 { margin: 10px 0; font-size: 18px; }
        
        footer { background: #2d3436; color: #b2bec3; text-align: center; padding: 20px; font-size: 14px; margin-top: 60px; }
    </style>
</head>
<body>

<header>
    <h1>🧋 Trà Sữa Homie 🧋</h1>
    <p>Thơm ngon từng giọt - Đậm vị yêu thương</p>
    
    <div class="auth-buttons">
        <?php if(isset($_SESSION['username'])): ?>
            <span><i class="fa-solid fa-user"></i> Xin chào, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <a href="dang-xuat.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng Xuất</a>
        <?php else: ?>
            <a href="dang-nhap.php"><i class="fa-solid fa-user-lock"></i> Đăng Nhập</a>
            <a href="dang-ky.php"><i class="fa-solid fa-user-plus"></i> Đăng Ký</a>
        <?php endif; ?>
    </div>
</header>

<section class="hero-section">
    <h2>Chào Mừng Đến Với Homie Tea</h2>
    <p>Không gian lý tưởng mang đến cho bạn những ly trà sữa chuẩn vị, thơm béo kết hợp cùng menu đồ ăn vặt, mỳ cay siêu cuốn.</p>
    <a href="thuc-don.php" class="btn-cta"><i class="fa-solid fa-utensils"></i> Xem Thực Đơn & Đặt Món Ngay</a>
</section>

<div class="intro-container">
    <h3>Tại sao bạn nên chọn Trà Sữa Homie?</h3>
    <p>Chúng tôi tự hào đem đến trải nghiệm ẩm thực tuyệt vời với nguồn nguyên liệu sạch, có nguồn gốc rõ ràng và quy trình chế biến đảm bảo an toàn vệ sinh thực phẩm.</p>
    
    <div class="features">
        <div class="feature-card">
            <i class="fa-solid fa-leaf"></i>
            <h4>Nguyên Liệu Sạch</h4>
            <p>Trà và sữa được chọn lọc kỹ càng, topping làm mới mỗi ngày.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-truck-fast"></i>
            <h4>Giao Hàng Nhanh</h4>
            <p>Đảm bảo thức uống của bạn luôn mát lạnh khi ship tới tay.</p>
        </div>
        <div class="feature-card">
            <i class="fa-solid fa-tags"></i>
            <h4>Giá Cả Hợp Lý</h4>
            <p>Menu đa dạng, phù hợp túi tiền của học sinh, sinh viên.</p>
        </div>
    </div>
</div>

<footer>
    <p>© 2026 Trà Sữa Homie - Hệ Thống Đặt Món Trực Tuyến Thông Minh.</p>
</footer>

</body>
</html>
