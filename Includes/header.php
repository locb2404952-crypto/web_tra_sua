<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trà Sữa Homie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #ff7675;   /* Màu hồng cam san hô Homie */
            --secondary-color: #fab1a0; 
            --dark-color: #2d3436;      
            --light-color: #f9f9f9;     
        }
        html { scroll-behavior: smooth; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: var(--light-color); color: var(--dark-color); }
        header { background-color: var(--primary-color); color: white; padding: 30px 20px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); position: relative; }
        header h1 { margin: 0; font-size: 32px; letter-spacing: 1px; }
        header p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.9; }
        .auth-buttons { position: absolute; top: 30px; right: 30px; }
        .auth-buttons a { color: white; text-decoration: none; margin-left: 15px; font-weight: bold; font-size: 14px; background-color: rgba(255, 255, 255, 0.2); padding: 6px 14px; border-radius: 20px; transition: all 0.3s ease; }
        .auth-buttons a:hover { background-color: white; color: var(--primary-color); }
        .auth-buttons span { color: white; font-weight: bold; margin-right: 10px; }
        
        /* Gom toàn bộ CSS của cả Trang Chủ lẫn Thực Đơn vào đây để không bị mất giao diện */
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .hero-section { background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('https://img.freepik.com/free-photo/delicious-bubble-tea-table_23-2150764127.jpg') no-repeat center center/cover; height: 350px; border-radius: 15px; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; text-align: center; padding: 20px; box-shadow: 0 6px 20px rgba(0,0,0,0.15); margin-bottom: 40px; }
        .hero-section h2 { font-size: 42px; margin: 0 0 15px 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.6); }
        .hero-section p { font-size: 20px; margin: 0 0 30px 0; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); }
        .btn-menu-now { background-color: #fff; color: var(--primary-color); padding: 14px 35px; border-radius: 30px; font-size: 18px; font-weight: bold; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: all 0.3s ease; }
        .btn-menu-now:hover { background-color: var(--primary-color); color: white; transform: scale(1.05); }
        .bestseller-title { font-size: 26px; color: #d63031; text-align: center; font-weight: bold; text-transform: uppercase; margin-bottom: 30px; position: relative; }
        .bestseller-title i { color: #f1c40f; margin: 0 8px; }
        .bestseller-box { background: #fff5f5; border: 2px dashed #ff7675; border-radius: 15px; padding: 25px; margin-bottom: 45px; }
        .bs-cat-group { margin-bottom: 25px; }
        .bs-cat-name { font-size: 16px; font-weight: bold; color: #e17055; margin-bottom: 12px; border-left: 4px solid #ff7675; padding-left: 10px; }
        
        .homie-main-layout { display: flex; gap: 30px; align-items: flex-start; max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        .homie-left-sidebar { width: 260px; flex-shrink: 0; position: sticky; top: 25px; z-index: 999; }
        .homie-right-content { flex-grow: 1; }
        .category-section { margin-bottom: 5px; scroll-margin-top: 25px; }
        .category-title { font-size: 24px; color: #d63031; border-bottom: 3px solid var(--primary-color); padding-bottom: 6px; margin-bottom: 25px; display: inline-block; text-transform: uppercase; letter-spacing: 0.5px; }
        .product-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; }
        .product-card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease; display: flex; flex-direction: column; border: 1px solid #f1f2f6; position: relative; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(255, 118, 117, 0.15); }
        .badge-hot { position: absolute; top: 10px; left: 10px; background: #e74c3c; color: white; padding: 4px 10px; font-size: 11px; font-weight: bold; border-radius: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 10; }
        .product-image { width: 100%; height: 160px; background-color: #ffeaa7; display: flex; align-items: center; justify-content: center; font-size: 55px; user-select: none; overflow: hidden; }
        .product-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease; }
        .product-card:hover .product-image img { transform: scale(1.05); }
        .product-info { padding: 20px; display: flex; flex-direction: column; flex-grow: 1; }
        .product-name { font-size: 19px; font-weight: bold; margin: 0 0 8px 0; color: #2d3436; }
        .product-desc { font-size: 14px; color: #636e72; margin: 0 0 20px 0; line-height: 1.4; flex-grow: 1; }
        .product-price-action { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
        .product-price { font-size: 19px; font-weight: bold; color: #e17055; }
        .sold-count { font-size: 12px; color: #7f8c8d; font-style: italic; background: #f1f2f6; padding: 2px 8px; border-radius: 10px; }
        .action-buttons-group { display: flex; gap: 6px; }
        .btn-select { background-color: var(--primary-color); color: white; border: none; padding: 9px 20px; border-radius: 25px; font-weight: bold; cursor: pointer; box-shadow: 0 3px 8px rgba(255, 118, 117, 0.3); transition: background 0.2s ease; }
        .btn-select:hover { background-color: #ff5252; }
        .btn-buy-now { background-color: var(--primary-color); color: white; border: none; padding: 7px 15px; border-radius: 20px; font-weight: bold; text-decoration: none; font-size: 13px; cursor: pointer; }
        .btn-buy-now:hover { background-color: #ff5252; }
        .btn-mini-cart { background-color: #ffeaa7; color: #d63031; border: none; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; cursor: pointer; box-shadow: 0 3px 8px rgba(0,0,0,0.05); transition: all 0.2s ease; }
        .btn-mini-cart:hover { background-color: var(--primary-color); color: white; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.55); justify-content: center; align-items: center; z-index: 9999; }
        .modal-content { background-color: white; padding: 25px; border-radius: 14px; width: 440px; max-width: 90%; box-shadow: 0 5px 25px rgba(0,0,0,0.25); position: relative; animation: formFadeIn 0.3s ease; }
        @keyframes formFadeIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .close-btn { position: absolute; top: 15px; right: 20px; font-size: 28px; color: #b2bec3; border: none; background: none; cursor: pointer; }
        .close-btn:hover { color: #2d3436; }
        .modal-content h2 { margin: 0 0 20px 0; font-size: 22px; color: var(--primary-color); border-bottom: 2px solid #f1f2f6; padding-bottom: 12px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        .form-control:focus { border-color: var(--primary-color); outline: none; }
        .options-container { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
        .option-tag { position: relative; }
        .option-tag input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
        .option-label { display: inline-block; padding: 6px 12px; background: #f1f2f6; border: 1px solid #ddd; border-radius: 20px; font-size: 13px; cursor: pointer; }
        .option-tag input[type="radio"]:checked + .option-label { background: var(--primary-color); color: white; border-color: var(--primary-color); font-weight: bold; }
        .quantity-counter { display: flex; align-items: center; gap: 12px; margin-top: 4px; }
        .btn-qty { background: #f1f2f6; border: 1px solid #ccc; width: 36px; height: 36px; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; display: flex; align-items: center; justify-content: center; }
        .qty-input { width: 55px; height: 36px; text-align: center; font-size: 16px; font-weight: bold; border: 1px solid #ddd; border-radius: 6px; }
        .total-display-box { background: #fff5f5; border: 1px dashed var(--primary-color); padding: 12px; border-radius: 6px; text-align: center; margin: 18px 0; }
        .total-price { font-size: 22px; font-weight: bold; color: #e17055; }
        .form-actions { display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #f1f2f6; padding-top: 15px; }
        .btn-submit { background-color: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-huy { background-color: #eee; color: #333; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .feature-card { background: white; padding: 30px; border-radius: 12px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f1f2f6; }
        .feature-card i { font-size: 45px; color: var(--primary-color); margin-bottom: 15px; }
        .feature-card h3 { margin: 10px 0; font-size: 20px; }
        .feature-card p { color: #636e72; font-size: 14px; line-height: 1.6; }
        
        .cart-item-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee; font-size: 14px; }
        .cart-item-details { display: flex; flex-direction: column; gap: 2px; }
        .cart-item-name { font-weight: bold; color: #2d3436; }
        .cart-item-sub { font-size: 12px; color: #7f8c8d; }
        .cart-item-price-qty { display: flex; align-items: center; gap: 15px; }
        .btn-delete-item { color: #e74c3c; background: none; border: none; cursor: pointer; font-size: 14px; }
        .toast-notification { position: fixed; top: 25px; left: 50%; transform: translateX(-50%) translateY(-40px); background-color: #2ecc71; color: white; font-weight: 600; font-size: 15px; padding: 12px 24px; border-radius: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.15); z-index: 10000; opacity: 0; visibility: hidden; transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; align-items: center; gap: 8px; }
        .toast-notification.show { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); }
        @media (max-width: 1200px) { .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 992px) { .homie-main-layout { flex-direction: column; } .homie-left-sidebar { width: 100%; position: static; } .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    </style>
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
        <a href="trang-chu.php" style="color: white; margin-right: 20px; text-decoration: none; font-weight: bold;"><i class="fa-solid fa-house"></i> Trang Chủ</a>
        <a href="index.php" style="color: white; margin-right: 20px; text-decoration: none; font-weight: bold;"><i class="fa-solid fa-utensils"></i> Thực Đơn</a>
        <a href="lien-he.php" style="color: white; text-decoration: none; font-weight: bold;"><i class="fa-solid fa-envelope"></i> Liên Hệ</a> 
    </div>
</header>