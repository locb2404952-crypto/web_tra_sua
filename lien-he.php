<?php 
session_start();
// Nhúng file kết nối database
require_once 'includes/db-connect.php'; 

// Nhúng header dùng chung (Trong file này bạn nhớ chèn thẻ link kết nối tới css/style.css nhé)
include_once 'includes/header.php'; 
?>

<!-- Thanh điều hướng phụ của trang Liên Hệ -->
<div class="sub-header" style="background: #ff7675; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; font-family: sans-serif; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h2 style="margin:0; font-size: 24px;">🧋 Trà Sữa Homie</h2>
    <a href="index.php" style="color: white; text-decoration: none; font-weight: bold; background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 4px; transition: 0.3s;">
        <i class="fa-solid fa-house"></i> Quay Về Trang Chủ
    </a>
</div>

<div class="contact-container">
    
    <!-- CỘT BÊN TRÁI: THÔNG TIN CỬA HÀNG -->
    <div class="contact-info">
        <h2 style="color: #d63031;">🏠 TRÀ SỮA HOMIE</h2>
        <p><strong>Địa chỉ:</strong> 123 Đường Ba Tháng Hai, Xuân Khánh, Ninh Kiều, Cần Thơ</p>
        <p><strong>Hotline đặt hàng:</strong> 1900 8386 (08:00 - 22:00)</p>
        <p><strong>Email:</strong> trasuahomie@gmail.com</p>
        <br>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3928.8415184086424!2d105.7684266147423!3d10.029933692830634!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31a0895a51d60719%3A0x9d76b0035f6d53d0!2zVHLGsOG7nW5nIMSQ4bqhaSBo4buNYyBD4bqnbiBUaMah!5e0!3m2!1svi!2s!4v1625000000000!5m2!1svi!2s" width="100%" height="300" style="border:0; border-radius: 8px;" allowfullscreen="" loading="lazy"></iframe>
    </div>

    <!-- CỘT BÊN PHẢI: FORM LIÊN HỆ -->
    <div class="contact-form">
        <h3 style="margin-top: 0; color: #333;">📩 Gửi Góp Ý Cho Homie</h3>
        <form action="" method="POST">
            <div class="form-group">
                <label>Họ và Tên</label>
                <input type="text" class="form-control" required placeholder="Nhập họ tên của bạn">
            </div>
            <div class="form-group">
                <label>Số Điện Thoại</label>
                <input type="text" class="form-control" required placeholder="Nhập số điện thoại">
            </div>
            <div class="form-group">
                <label>Nội Dung Góp Ý</label>
                <textarea class="form-control" rows="5" required placeholder="Nhập ý kiến đóng góp hoặc khiếu nại của bạn..."></textarea>
            </div>
            <button type="button" class="btn-submit" onclick="alert('🎉 Cảm ơn bạn đã gửi góp ý! Homie sẽ liên hệ lại sớm nhất.')">Gửi Ý Kiến</button>
        </form>
    </div>

</div>

<?php 
// Nhúng chân trang dùng chung
include_once 'includes/footer.php'; 
?>