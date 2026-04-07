<?php

include 'config.php';

session_start();
session_unset();
session_destroy();

// Dùng JavaScript để dọn dẹp localStorage và sessionStorage rồi mới chuyển về trang login
echo "<script>
        // Xóa trí nhớ của AI chatbot (localStorage)
        localStorage.removeItem('bookept_chat_history');
        
        // MỚI THÊM: Xóa luôn cờ đã xem tin nhắn để lần sau đăng nhập hiện lại số 1 (sessionStorage)
        sessionStorage.removeItem('bookept_ai_read');
        
        // Chuyển hướng người dùng về trang đăng nhập
        window.location.href = 'login_customer.php';
      </script>";
exit;

?>