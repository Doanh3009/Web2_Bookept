<?php

include 'config.php';

session_start();
session_unset();
session_destroy();

// Dùng JavaScript để dọn dẹp localStorage rồi mới chuyển về trang login
echo "<script>
        // Xóa trí nhớ của AI chatbot
        localStorage.removeItem('bookept_chat_history');
        
        // Chuyển hướng người dùng về trang đăng nhập
        window.location.href = 'login_customer.php';
      </script>";
exit;

?>