<section class="footer" style="background-color: #f5f5f5; padding: 5rem 2rem; border-top: 1px solid #ddd;">

   <div class="box-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(25rem, 1fr)); gap: 3rem; max-width: 1200px; margin: 0 auto;">

      <div class="box">
         <h3 style="font-size: 2.2rem; color: var(--black); margin-bottom: 2rem;">Quick links</h3>
         <a href="home.php" style="display: block; font-size: 1.6rem; color: var(--light-color); padding: 1rem 0;"><i class="fas fa-angle-right" style="padding-right: .5rem; color: var(--purple);"></i> Home</a>
         <a href="about.php" style="display: block; font-size: 1.6rem; color: var(--light-color); padding: 1rem 0;"><i class="fas fa-angle-right" style="padding-right: .5rem; color: var(--purple);"></i> About</a>
         <a href="shop.php" style="display: block; font-size: 1.6rem; color: var(--light-color); padding: 1rem 0;"><i class="fas fa-angle-right" style="padding-right: .5rem; color: var(--purple);"></i> Shop</a>
         <a href="contact.php" style="display: block; font-size: 1.6rem; color: var(--light-color); padding: 1rem 0;"><i class="fas fa-angle-right" style="padding-right: .5rem; color: var(--purple);"></i> Contact</a>
      </div>

      <div class="box">
         <h3 style="font-size: 2.2rem; color: var(--black); margin-bottom: 2rem;">Extra links</h3>
         <a href="cart.php" style="display: block; font-size: 1.6rem; color: var(--light-color); padding: 1rem 0;"><i class="fas fa-angle-right" style="padding-right: .5rem; color: var(--purple);"></i> My Cart</a>
         <a href="bill.php" style="display: block; font-size: 1.6rem; color: var(--light-color); padding: 1rem 0;"><i class="fas fa-angle-right" style="padding-right: .5rem; color: var(--purple);"></i> Order History</a>

      </div>

      <div class="box">
         <h3 style="font-size: 2.2rem; color: var(--black); margin-bottom: 2rem;">Contact info</h3>
         <p style="font-size: 1.6rem; color: var(--light-color); padding: 1rem 0;"><i class="fas fa-phone" style="padding-right: .5rem; color: var(--purple);"></i> +123-456-7890 </p>
         <p style="font-size: 1.6rem; color: var(--light-color); padding: 1rem 0;"><i class="fas fa-envelope" style="padding-right: .5rem; color: var(--purple);"></i> support@bookept.com </p>
         <p style="font-size: 1.6rem; color: var(--light-color); padding: 1rem 0;"><i class="fas fa-map-marker-alt" style="padding-right: .5rem; color: var(--purple);"></i> Ho Chi Minh City, Vietnam </p>
      </div>

      <div class="box">
         <h3 style="font-size: 2.2rem; color: var(--black); margin-bottom: 2rem;">Follow us</h3>
         <div style="display: flex; gap: 1.5rem;">
            <a href="https://www.facebook.com/share/18aW8PKrpE/?mibextid=wwXIfr" style="font-size: 2.5rem; color: var(--purple);"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.instagram.com/tapdoanmoclen" style="font-size: 2.5rem; color: var(--purple);"><i class="fab fa-instagram"></i></a>
         </div>
      </div>

   </div>

   <p class="credit" style="text-align: center; margin-top: 3rem; padding-top: 3rem; border-top: 1px solid #ddd; font-size: 2rem; color: var(--light-color);"> 
      &copy; Copyright <?php echo date('Y'); ?> by <span style="color: var(--purple);">Bookist Team</span> | All rights reserved. 
   </p>

</section>
</section>

<div id="chatbot-toggle" style="position: fixed; bottom: 80px; right: 20px; width: 60px; height: 60px; background-color: #8e44ad; color: white; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 30px; cursor: pointer; box-shadow: 0 4px 8px rgba(0,0,0,0.2); z-index: 9999;">
    <i class="fas fa-comments"></i>
    <span id="chatbot-badge" style="position: absolute; top: -5px; right: -5px; background-color: #e74c3c; color: white; font-size: 13px; font-weight: bold; font-family: Arial, sans-serif; width: 22px; height: 22px; border-radius: 50%; display: none; justify-content: center; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); border: 2px solid white;">1</span>
</div>

<div id="chatbot-container" style="display: none; position: fixed; bottom: 150px; right: 20px; width: 350px; height: 450px; background-color: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 9999; flex-direction: column; overflow: hidden; font-family: Arial, sans-serif;">
    <div style="background-color: #8e44ad; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: bold; font-size: 16px;"><i class="fas fa-robot"></i> Bookworm</span>
        <span id="close-chatbot" style="cursor: pointer; font-size: 20px;">&times;</span>
    </div>
    
    <div id="chatbot-messages" style="flex: 1; padding: 15px; overflow-y: auto; background-color: #f9f9f9; display: flex; flex-direction: column; gap: 10px;">
        <div style="background-color: #e0e0e0; color: black; padding: 10px; border-radius: 10px; max-width: 80%; align-self: flex-start; font-size: 14px; line-height: 1.5;">
            Hello! 👋 I'm Bookworm – your reliable bookstore assistant! I'm here to help you with:<br>
            📚 Finding the perfect book for your taste<br>
            🔍 Searching for books by your favorite categories<br>
            💡 Recommending top authors in our store<br><br>
            What kind of book are you looking for today?
        </div>
    </div>
    
    <div style="display: flex; border-top: 1px solid #ddd; padding: 10px; background: white;">
        <input type="text" id="chatbot-input" placeholder="Type a message..." style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 20px; outline: none; font-size: 14px;">
        <button id="chatbot-send" style="background-color: #8e44ad; color: white; border: none; border-radius: 50%; width: 40px; height: 40px; margin-left: 10px; cursor: pointer;">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>
<style>
/* Hiệu ứng sóng lượn cho dấu 3 chấm */
@keyframes typing-wave {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-4px); }
}
.typing-dot {
    display: inline-block;
    animation: typing-wave 1.3s infinite ease-in-out;
    font-weight: bold;
    font-size: 16px;
    color: #8e44ad; /* Màu tím cho đồng bộ */
}
/* Độ trễ để tạo thành làn sóng */
.typing-dot:nth-child(1) { animation-delay: 0s; }
.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('chatbot-toggle');
    const chatContainer = document.getElementById('chatbot-container');
    const closeBtn = document.getElementById('close-chatbot');
    const sendBtn = document.getElementById('chatbot-send');
    const inputField = document.getElementById('chatbot-input');
    const messagesArea = document.getElementById('chatbot-messages');
    const badge = document.getElementById('chatbot-badge'); // MỚI THÊM: Khai báo badge

    // MỚI THÊM: Kiểm tra xem phiên đăng nhập này khách đã bấm xem AI chưa
    // Nếu chưa bấm, cho hiển thị số 1 lên
    if (!sessionStorage.getItem('bookept_ai_read')) {
        badge.style.display = 'flex';
    }

    // ==========================================
    // 1. KHÔI PHỤC LỊCH SỬ CHAT KHI LOAD TRANG
    // ==========================================
    const savedChat = localStorage.getItem('bookept_chat_history');
    if (savedChat) {
        messagesArea.innerHTML = savedChat;
        messagesArea.scrollTop = messagesArea.scrollHeight; // Cuộn xuống cuối
    }

    // ==========================================
    // 2. HÀM LƯU TRỮ LỊCH SỬ CHAT
    // ==========================================
    function saveChatHistory() {
        localStorage.setItem('bookept_chat_history', messagesArea.innerHTML);
    }

    // Mở/Đóng chat
    toggleBtn.addEventListener('click', () => {
        chatContainer.style.display = chatContainer.style.display === 'none' || chatContainer.style.display === '' ? 'flex' : 'none';
        
        // MỚI THÊM: Khi khách bấm vào icon, ẩn số 1 đi và lưu vào bộ nhớ tạm
        if (badge.style.display !== 'none') {
            badge.style.display = 'none';
            sessionStorage.setItem('bookept_ai_read', 'true');
        }
    });

    closeBtn.addEventListener('click', () => { chatContainer.style.display = 'none'; });

    // Hàm thêm tin nhắn vào khung chat
    function appendMessage(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.style.padding = '10px';
        msgDiv.style.borderRadius = '10px';
        msgDiv.style.maxWidth = '80%';
        msgDiv.style.fontSize = '14px';
        msgDiv.style.marginBottom = '10px';
        msgDiv.style.lineHeight = '1.5';
        
        // Render text có xuống dòng
        msgDiv.innerHTML = text.replace(/\n/g, '<br>');

        if (sender === 'user') {
            msgDiv.style.backgroundColor = '#8e44ad';
            msgDiv.style.color = 'white';
            msgDiv.style.alignSelf = 'flex-end';
        } else {
            msgDiv.style.backgroundColor = '#e0e0e0';
            msgDiv.style.color = 'black';
            msgDiv.style.alignSelf = 'flex-start';
        }

        messagesArea.appendChild(msgDiv);
        messagesArea.scrollTop = messagesArea.scrollHeight; // Cuộn xuống cuối
        
        // LƯU LẠI LỊCH SỬ NGAY SAU KHI THÊM TIN NHẮN
        saveChatHistory();
    }

    // Xử lý gửi tin nhắn
    function sendMessage() {
        const text = inputField.value.trim();
        if (text === '') return;

        // In tin nhắn của User ra màn hình
        appendMessage(text, 'user');
        inputField.value = '';

        // Hiển thị trạng thái "đang gõ..." với hiệu ứng sóng
        const typingMsg = document.createElement('div');
        typingMsg.id = 'typing-indicator';
        typingMsg.style.fontSize = '13px';
        typingMsg.style.color = '#888';
        typingMsg.style.fontStyle = 'italic';
        typingMsg.style.marginBottom = '10px';
        // Lắp các dấu chấm vào thẻ span để chạy animation
        typingMsg.innerHTML = 'Bookworm is typing <span class="typing-dot">.</span><span class="typing-dot">.</span><span class="typing-dot">.</span>';
        
        messagesArea.appendChild(typingMsg);
        messagesArea.scrollTop = messagesArea.scrollHeight; // Tự động cuộn xuống để nhìn thấy AI đang gõ

        // Gửi dữ liệu lên file PHP bằng AJAX
        fetch('chatbot_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(text)
        })
        .then(response => response.json())
        .then(data => {
            const indicator = document.getElementById('typing-indicator');
            if(indicator) indicator.remove();
            
            if(data.reply) {
                appendMessage(data.reply, 'bot');
            } else {
                appendMessage('Sorry, the AI system is experiencing connection issues.', 'bot');
            }
        })
        .catch(error => {
            console.error("Error:", error);
            const indicator = document.getElementById('typing-indicator');
            if(indicator) indicator.remove();
            appendMessage('Network error, please try again later!', 'bot');
        });
    }

    sendBtn.addEventListener('click', sendMessage);
    inputField.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });
});
</script>