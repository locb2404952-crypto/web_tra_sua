<!-- ========================================== -->
<!-- MODAL TÙY CHỌN MÓN (ĐƯỜNG, ĐÁ, TOPPING) -->
<!-- ========================================== -->
<div id="optionsModal" class="modal">
    <div class="modal-content">
        <button class="close-btn" onclick="dongTuyChon()">&times;</button>
        <h2 id="optionTitle">Tùy Chọn Món</h2>
        
        <div class="form-group">
            <label>Số Lượng Đặt:</label>
            <div class="quantity-counter">
                <button type="button" class="btn-qty" onclick="giamQty()">-</button>
                <input type="number" id="opt_quantity" class="qty-input" value="1" readonly>
                <button type="button" class="btn-qty" onclick="tangQty()">+</button>
            </div>
        </div>

        <div id="drinkOptionsSection">
            <div class="form-group">
                <label>Mức Đường:</label>
                <div class="options-container">
                    <div class="option-tag"><input type="radio" name="opt_sugar" value="100" id="s100" checked><label for="s100" class="option-label">100% Đường</label></div>
                    <div class="option-tag"><input type="radio" name="opt_sugar" value="70" id="s70"><label for="s70" class="option-label">70%</label></div>
                    <div class="option-tag"><input type="radio" name="opt_sugar" value="50" id="s50"><label for="s50" class="option-label">50%</label></div>
                    <div class="option-tag"><input type="radio" name="opt_sugar" value="0" id="s0"><label for="s0" class="option-label">0% Đường</label></div>
                </div>
            </div>
            <div class="form-group">
                <label>Mức Đá:</label>
                <div class="options-container">
                    <div class="option-tag"><input type="radio" name="opt_ice" value="100" id="i100" checked><label for="i100" class="option-label">100% Đá</label></div>
                    <div class="option-tag"><input type="radio" name="opt_ice" value="70" id="i70"><label for="i70" class="option-label">70%</label></div>
                    <div class="option-tag"><input type="radio" name="opt_ice" value="50" id="i50"><label for="i50" class="option-label">50%</label></div>
                    <div class="option-tag"><input type="radio" name="opt_ice" value="0" id="i0"><label for="i0" class="option-label">0% Đá</label></div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label id="toppingLabel">Topping yêu cầu thêm:</label>
            <input type="text" id="opt_topping_note" class="form-control" placeholder="Ví dụ: Thêm trân châu...">
        </div>

        <div class="total-display-box">
            Tạm tính món này: <span class="total-price" id="opt_display_total">0đ</span>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-huy" onclick="dongTuyChon()">Hủy</button>
            <button type="button" class="btn-submit" onclick="xacNhanThemMon()">Thêm Vào Giỏ</button>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL GIỎ HÀNG CHÍNH -->
<!-- ========================================== -->
<div id="cartModal" class="modal">
    <div class="modal-content" style="width: 500px;">
        <button class="close-btn" onclick="dongGioHang()">&times;</button>
        <h2>🛒 Giỏ Hàng Của Bạn</h2>
        <div id="cartItemsList" style="max-height: 250px; overflow-y: auto; margin-bottom: 20px; border-bottom: 2px solid #f1f2f6; padding-bottom: 10px;"></div>

        <form action="" method="POST" onsubmit="return validateCartBeforeSubmit()">
            <input type="hidden" name="cart_data" id="hidden_cart_data">
            <div class="form-group">
                <label>Họ và Tên Khách Hàng <span style="color:red;">*</span></label>
                <input type="text" name="khach_ten" class="form-control" required placeholder="Nhập họ tên nhận hàng" value="<?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : '' ?>">
            </div>
            <div class="form-group">
                <label>Số Điện Thoại <span style="color:red;">*</span></label>
                <input type="text" name="khach_sdt" class="form-control" required placeholder="Nhập số điện thoại liên hệ">
            </div>
            <div class="form-group">
                <label>Địa Chỉ Giao Hàng <span style="color:red;">*</span></label>
                <input type="text" name="khach_diachi" class="form-control" required placeholder="Nhập số nhà, tên đường để shipper giao hàng">
            </div>
            <div class="total-display-box" style="background: #fff3cd; border-color: #ffc107;">
                Tổng tiền toàn bộ giỏ hàng: <span class="total-price" id="cart_global_total" style="color: #d63031;">0đ</span>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-huy" onclick="dongGioHang()">Xem Tiếp</button>
                <button type="submit" name="btn_dat_hang" class="btn-submit">Xác Nhận Đặt Hàng</button>
            </div>
        </form>
    </div>
</div>

<!-- ICON GIỎ HÀNG NỔI -->
<div id="floating-cart" onclick="moGioHangHienTai()" style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background-color: #ff7675; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; cursor: pointer; box-shadow: 0 4px 15px rgba(255, 118, 117, 0.4); z-index: 999; transition: transform 0.2s;">
    <i class="fa-solid fa-cart-shopping"></i>
    <span id="cart-count" style="position: absolute; top: -5px; right: -5px; background: #d63031; color: white; font-size: 12px; font-weight: bold; border-radius: 50%; width: 22px; height: 22px; display: none; align-items: center; justify-content: center;">0</span>
</div>

<!-- ========================================== -->
<!-- LOGIC JAVASCRIPT XỬ LÝ GIỎ HÀNG -->
<!-- ========================================== -->
<script>
let globalCart = []; 
let activeProduct = {}; 

function showToast(message) {
    let toast = document.getElementById('toastNotify');
    if(toast) {
        document.getElementById('toastMessage').innerText = message;
        toast.classList.add('show');
        setTimeout(() => { toast.classList.remove('show'); }, 2500);
    } else {
        alert(message);
    }
}

function themMonVaoMangGioHang(newProduct) {
    let existingIndex = globalCart.findIndex(item => 
        item.id === newProduct.id && item.sugar === newProduct.sugar && item.ice === newProduct.ice && item.topping.trim().toLowerCase() === newProduct.topping.trim().toLowerCase()
    );
    if (existingIndex > -1) { globalCart[existingIndex].quantity += newProduct.quantity; } 
    else { globalCart.push(newProduct); }
}

function themNhanhVaoGioHang(productId, productName, price, categoryId) {
    let itemNew = { id: productId, name: productName, price: parseFloat(price), quantity: 1, sugar: 100, ice: 100, topping: "" };
    themMonVaoMangGioHang(itemNew);
    capNhatGiaoDienGioHang();
    showToast("✨ Đã thêm '" + productName + "' vào giỏ hàng thành công!");
}

function moTuyChonMon(productId, productName, price, categoryId) {
    activeProduct = { id: productId, name: productName, price: parseFloat(price), catId: categoryId };
    document.getElementById('optionTitle').innerText = productName;
    document.getElementById('opt_quantity').value = 1;
    document.getElementById('opt_topping_note').value = "";
    document.getElementById('s100').checked = true;
    document.getElementById('i100').checked = true;
    let drinkSection = document.getElementById('drinkOptionsSection');
    let toppingLabel = document.getElementById('toppingLabel');
    if (categoryId == 1 || categoryId == 4) { drinkSection.style.display = 'none'; toppingLabel.innerText = "Yêu cầu ghi chú cho món ăn:"; } 
    else { drinkSection.style.display = 'block'; toppingLabel.innerText = "Topping yêu cầu thêm:"; }
    tinhTienTuyChonMon();
    document.getElementById('optionsModal').style.display = 'flex';
}

function dongTuyChon() { document.getElementById('optionsModal').style.display = 'none'; }
function tangQty() { let input = document.getElementById('opt_quantity'); input.value = parseInt(input.value) + 1; tinhTienTuyChonMon(); }
function giamQty() { let input = document.getElementById('opt_quantity'); if (parseInt(input.value) > 1) { input.value = parseInt(input.value) - 1; tinhTienTuyChonMon(); } }
function tinhTienTuyChonMon() { let qty = parseInt(document.getElementById('opt_quantity').value); let total = activeProduct.price * qty; document.getElementById('opt_display_total').innerText = total.toLocaleString('vi-VN') + 'đ'; }

function xacNhanThemMon() {
    let qty = parseInt(document.getElementById('opt_quantity').value);
    let toppingNote = document.getElementById('opt_topping_note').value;
    let sugarValue = 100;
    let iceValue = 100;
    if (activeProduct.catId != 1 && activeProduct.catId != 4) {
        let sugarRadio = document.querySelector('input[name="opt_sugar"]:checked');
        let iceRadio = document.querySelector('input[name="opt_ice"]:checked');
        if(sugarRadio) sugarValue = sugarRadio.value;
        if(iceRadio) iceValue = iceRadio.value;
    }
    let itemNew = { id: activeProduct.id, name: activeProduct.name, price: activeProduct.price, quantity: qty, sugar: sugarValue, ice: iceValue, topping: toppingNote };
    themMonVaoMangGioHang(itemNew);
    capNhatGiaoDienGioHang();
    dongTuyChon();
    document.getElementById('cartModal').style.display = 'flex';
}

function capNhatGiaoDienGioHang() {
    let totalItemsCount = globalCart.reduce((sum, item) => sum + item.quantity, 0);
    let countSpan = document.getElementById('cart-count');
    countSpan.innerText = totalItemsCount;
    countSpan.style.display = totalItemsCount > 0 ? 'flex' : 'none';
    let listContainer = document.getElementById('cartItemsList');
    listContainer.innerHTML = "";
    let globalTotal = 0;
    globalCart.forEach((item, index) => {
        let itemTotal = item.price * item.quantity;
        globalTotal += itemTotal;
        let subText = `SL: ${item.quantity} x ${item.price.toLocaleString('vi-VN')}đ`;
        if (item.sugar != 100 || item.ice != 100 || item.topping != "") { subText += ` | Đường: ${item.sugar}%, Đá: ${item.ice}% ${item.topping ? ', Note: ' + item.topping : ''}`; }
        listContainer.innerHTML += `
            <div class="cart-item-row">
                <div class="cart-item-details">
                    <span class="cart-item-name">${item.name}</span>
                    <span class="cart-item-sub">${subText}</span>
                </div>
                <div class="cart-item-price-qty">
                    <span style="font-weight:bold; color:#e17055;">${itemTotal.toLocaleString('vi-VN')}đ</span>
                    <button type="button" class="btn-delete-item" onclick="xoaMonKhoiGio(${index})"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>
        `;
    });
    document.getElementById('cart_global_total').innerText = globalTotal.toLocaleString('vi-VN') + 'đ';
    document.getElementById('hidden_cart_data').value = JSON.stringify(globalCart);
}

function xoaMonKhoiGio(index) { globalCart.splice(index, 1); capNhatGiaoDienGioHang(); }
function moGioHangHienTai() { if (globalCart.length === 0) { alert("🛒 Giỏ hàng bạn đang trống, hãy thêm sản phẩm vào giỏ!"); } else { document.getElementById('cartModal').style.display = 'flex'; } }
function dongGioHang() { document.getElementById('cartModal').style.display = 'none'; }
function validateCartBeforeSubmit() { if (globalCart.length === 0) { alert("⚠️ Giỏ hàng không có sản phẩm nào để đặt!"); return false; } return true; }
window.onclick = function(event) { if (event.target == document.getElementById('optionsModal')) dongTuyChon(); if (event.target == document.getElementById('cartModal')) dongGioHang(); }
</script>