<div id="contact-sidebar-widget" style="position: fixed; right: 30px; bottom: 105px; z-index: 9999; display: flex; flex-direction: column; align-items: center; gap: 10px;">
    
    <div id="contact-buttons-box" style="background-color: #fbc79a; padding: 12px 8px; border-radius: 12px; display: flex; flex-direction: column; gap: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); transition: all 0.3s ease;">
        <a href="https://zalo.me/0773167336" target="_blank" style="width: 40px; height: 40px; border-radius: 50%; background-color: #fff; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <img src="https://upload.wikimedia.org/wikipedia/commons/9/91/Icon_of_Zalo.svg" alt="Zalo" style="width: 38px; height: 38px; border-radius: 50%;">
        </a>
        
        <a href="https://m.me/your_page_id" target="_blank" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135px, #0066ff, #00c6ff, #0072ff); display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <i class="fa-brands fa-facebook-messenger" style="color: white; font-size: 24px;"></i>
        </a>
        
        <a href="tel:0773167336" style="width: 40px; height: 40px; border-radius: 50%; background-color: #4cd137; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 2px 5px rgba(0,0,0,0.1); animation: homie-vibrate 1.5s infinite ease-in-out;">
            <i class="fa-solid fa-phone" style="color: white; font-size: 20px;"></i>
        </a>
    </div>

    <button onclick="toggleContactWidget()" id="btn-toggle-contact" style="width: 40px; height: 40px; border-radius: 50%; background-color: #ff7675; border: none; color: white; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(255, 118, 117, 0.3); transition: all 0.2s;">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>

<style>
@keyframes homie-vibrate {
    0% { transform: scale(1); }
    15% { transform: scale(1.1) rotate(-5deg); }
    30% { transform: scale(1.1) rotate(5deg); }
    45% { transform: scale(1.1) rotate(-5deg); }
    60% { transform: scale(1.1) rotate(5deg); }
    75% { transform: scale(1); }
    100% { transform: scale(1); }
}
</style>

<script>
function toggleContactWidget() {
    let box = document.getElementById('contact-buttons-box');
    let btn = document.getElementById('btn-toggle-contact');
    
    if (box.style.display === 'none') {
        box.style.display = 'flex';
        btn.style.backgroundColor = '#ff7675';
        btn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
    } else {
        box.style.display = 'none';
        btn.style.backgroundColor = '#fbc79a';
        btn.innerHTML = '<i class="fa-solid fa-comment-dots"></i>';
    }
}
</script>