// KHỞI TẠO GIỎ HÀNG TỪ SESSIONSTORAGE
let globalCart = JSON.parse(sessionStorage.getItem("homie_cart")) || [];
let activeProduct = {};

document.addEventListener("DOMContentLoaded", function () {
  capNhatGiaoDienGioHang();
});

function showToast(message) {
  let toast = document.getElementById("toastNotify");
  if (toast) {
    document.getElementById("toastMessage").innerText = message;
    toast.classList.add("show");
    setTimeout(() => {
      toast.classList.remove("show");
    }, 2500);
  }
}

function themMonVaoMangGioHang(newProduct) {
  let existingIndex = globalCart.findIndex(
    (item) =>
      item.id === newProduct.id &&
      item.sugar === newProduct.sugar &&
      item.ice === newProduct.ice &&
      item.topping.trim().toLowerCase() ===
        newProduct.topping.trim().toLowerCase(),
  );

  if (existingIndex > -1) {
    globalCart[existingIndex].quantity += newProduct.quantity;
  } else {
    globalCart.push(newProduct);
  }
  sessionStorage.setItem("homie_cart", JSON.stringify(globalCart));
}

function themNhanhVaoGioHang(productId, productName, price, categoryId) {
  let itemNew = {
    id: productId,
    name: productName,
    price: parseFloat(price),
    quantity: 1,
    sugar: 100,
    ice: 100,
    topping: "",
  };
  themMonVaoMangGioHang(itemNew);
  capNhatGiaoDienGioHang();
  showToast("✨ Đã thêm nhanh '" + productName + "' vào giỏ hàng!");
}

function moTuyChonMon(productId, productName, price, categoryId) {
  activeProduct = {
    id: productId,
    name: productName,
    price: parseFloat(price),
    catId: categoryId,
  };
  document.getElementById("optionTitle").innerText = productName;
  document.getElementById("opt_quantity").value = 1;
  document.getElementById("opt_topping_note").value = "";
  document.getElementById("s100").checked = true;
  document.getElementById("i100").checked = true;

  let drinkSection = document.getElementById("drinkOptionsSection");
  let toppingLabel = document.getElementById("toppingLabel");
  let toppingInput = document.getElementById("opt_topping_note");

  if (categoryId == 1 || categoryId == 4) {
    drinkSection.style.display = "none";
    toppingLabel.innerText = "Yêu cầu ghi chú cho món ăn:";
    toppingInput.placeholder = "Ví dụ: Làm cay nhiều, không bỏ hành...";
  } else {
    drinkSection.style.display = "block";
    toppingLabel.innerText = "Topping yêu cầu thêm:";
    toppingInput.placeholder = "Ví dụ: Thêm trân châu hoàng kim...";
  }
  tinhTienTuyChonMon();
  document.getElementById("optionsModal").style.display = "flex";
}

function dongTuyChon() {
  document.getElementById("optionsModal").style.display = "none";
}
function tangQty() {
  document.getElementById("opt_quantity").value =
    parseInt(document.getElementById("opt_quantity").value) + 1;
  tinhTienTuyChonMon();
}
function giamQty() {
  if (parseInt(document.getElementById("opt_quantity").value) > 1) {
    document.getElementById("opt_quantity").value =
      parseInt(document.getElementById("opt_quantity").value) - 1;
    tinhTienTuyChonMon();
  }
}
function tinhTienTuyChonMon() {
  document.getElementById("opt_display_total").innerText =
    (
      activeProduct.price *
      parseInt(document.getElementById("opt_quantity").value)
    ).toLocaleString("vi-VN") + "đ";
}

function xacNhanThemMon() {
  let qty = parseInt(document.getElementById("opt_quantity").value);
  let sugarValue = document.querySelector(
    'input[name="opt_sugar"]:checked',
  ).value;
  let iceValue = document.querySelector('input[name="opt_ice"]:checked').value;
  let toppingNote = document.getElementById("opt_topping_note").value;

  if (activeProduct.catId == 1 || activeProduct.catId == 4) {
    sugarValue = 100;
    iceValue = 100;
  }
  let itemNew = {
    id: activeProduct.id,
    name: activeProduct.name,
    price: activeProduct.price,
    quantity: qty,
    sugar: sugarValue,
    ice: iceValue,
    topping: toppingNote,
  };

  themMonVaoMangGioHang(itemNew);
  capNhatGiaoDienGioHang();
  dongTuyChon();
  document.getElementById("cartModal").style.display = "flex";
}

function capNhatGiaoDienGioHang() {
  let countSpan = document.getElementById("cart-count");
  if (!countSpan) return;

  let totalItemsCount = globalCart.reduce(
    (sum, item) => sum + item.quantity,
    0,
  );
  countSpan.innerText = totalItemsCount;
  countSpan.style.display = totalItemsCount > 0 ? "flex" : "none";

  let listContainer = document.getElementById("cartItemsList");
  if (!listContainer) return;

  listContainer.innerHTML = "";
  let globalTotal = 0;

  globalCart.forEach((item, index) => {
    let itemTotal = item.price * item.quantity;
    globalTotal += itemTotal;
    let subText = `SL: ${item.quantity} x ${item.price.toLocaleString("vi-VN")}đ`;
    if (item.sugar != 100 || item.ice != 100 || item.topping != "") {
      subText += ` | Đường: ${item.sugar}%, Đá: ${item.ice}% ${item.topping ? ", Ghi chú: " + item.topping : ""}`;
    }

    listContainer.innerHTML += `
            <div class="cart-item-row">
                <div class="cart-item-details">
                    <span class="cart-item-name">${item.name}</span>
                    <span class="cart-item-sub">${subText}</span>
                </div>
                <div class="cart-item-price-qty">
                    <span style="font-weight:bold; color:#e17055;">${itemTotal.toLocaleString("vi-VN")}đ</span>
                    <button type="button" class="btn-delete-item" onclick="xoaMonKhoiGio(${index})"><i class="fa-solid fa-trash-can"></i></button>
                </div>
            </div>
        `;
  });

  document.getElementById("cart_global_total").innerText =
    globalTotal.toLocaleString("vi-VN") + "đ";
  document.getElementById("hidden_cart_data").value =
    JSON.stringify(globalCart);
}

function xoaMonKhoiGio(index) {
  globalCart.splice(index, 1);
  sessionStorage.setItem("homie_cart", JSON.stringify(globalCart));
  capNhatGiaoDienGioHang();
}

function moGioHangHienTai() {
  if (globalCart.length === 0) {
    alert("🛒 Giỏ hàng của bạn đang trống!");
  } else {
    document.getElementById("cartModal").style.display = "flex";
  }
}
function dongGioHang() {
  document.getElementById("cartModal").style.display = "none";
}
function validateCartBeforeSubmit() {
  if (globalCart.length === 0) {
    alert("⚠️ Giỏ hàng trống, không thể đặt!");
    return false;
  }
  return true;
}

window.onclick = function (event) {
  if (event.target == document.getElementById("optionsModal")) dongTuyChon();
  if (event.target == document.getElementById("cartModal")) dongGioHang();
};
