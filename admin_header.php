<?php
// Truy vấn lấy thông tin admin từ database
// (Giả định $conn và $admin_id đã được khai báo ở trang chính trước khi include file này)
if (isset($admin_id)) {
    $select_admin = mysqli_query($conn, "SELECT * FROM `users` WHERE id = '$admin_id'") or die('query failed');
    $fetch_admin = mysqli_fetch_assoc($select_admin);
}
?>

<style>
    /* CSS cho header */
    .header {
        display: flex;
        justify-content: space-between; /* Đẩy menu qua trái, user qua phải */
        align-items: center;
        padding: 10px 20px;
        background-color: #fff;
        position: relative;
        z-index: 1000;
    }

    .header-right {
        position: relative;
    }

    #user-btn {
        font-size: 24px;
        cursor: pointer;
        color: #333;
        padding: 5px;
    }

    #user-btn:hover {
        color: #c0392b;
    }

    /* CSS cho popup profile */
    .header-right .profile {
        position: absolute;
        top: 120%;
        right: 0;
        background-color: #fff;
        border-radius: .5rem;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.1);
        border: .1rem solid #ccc;
        padding: 1.5rem;
        width: 25rem;
        display: none; /* Ẩn mặc định */
    }

    .header-right .profile.active {
        display: block;
    }

    .header-right .profile p {
        font-size: 16px;
        color: #666;
        margin-bottom: 15px;
    }

    .header-right .profile p span {
        color: #8e44ad;
    }

    .header-right .profile p i {
        margin-right: 5px;
        color: #333;
    }

    .header-right .profile .btn,
    .header-right .profile .delete-btn {
        display: block;
        width: 100%;
        text-align: center;
        background-color: #c0392b;
        color: #fff;
        padding: 10px;
        margin-top: 10px;
        border-radius: .5rem;
        font-size: 16px;
        text-decoration: none;
        box-sizing: border-box;
    }
    
</style>

<header class="header">
    <button class="menu-icon-btn">
        <div class="menu-icon">
            <i class="fa fa-bars"></i>
        </div>
    </button>

    <div class="header-right">
        <div id="user-btn" class="fa fa-user"></div>
        
        <div class="profile">
            <?php if(isset($fetch_admin) && $fetch_admin) { ?>
                <p><i class="fa fa-user"></i> user : <span><?php echo $fetch_admin['name']; ?></span></p>
                <p><i class="fa fa-envelope"></i> email : <span><?php echo $fetch_admin['email']; ?></span></p>
            <?php } else { ?>
                <p>Không tìm thấy thông tin.</p>
            <?php } ?>
            <a href="admin_update_profile.php" class="btn"><i class="fa fa-sign-in"></i> Edit Information</a>
            <a href="logout_admin.php" class="delete-btn"><i class="fa fa-sign-in"></i> Logout</a>
        </div>
    </div>
</header>

<script>
    // JS Xử lý đóng mở popup
    document.addEventListener('DOMContentLoaded', () => {
        let profile = document.querySelector('.header-right .profile');
        let userBtn = document.querySelector('#user-btn');

        if(userBtn && profile) {
            userBtn.onclick = () => {
                profile.classList.toggle('active');
            }

            // Tự động đóng popup khi scroll chuột
            window.onscroll = () => {
                profile.classList.remove('active');
            }
        }
    });
</script>