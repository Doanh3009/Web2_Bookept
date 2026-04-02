
<?php

if (!isset($user_id)) {
  header('location:login_customer.php');
  exit();
}

if (isset($message)) {
   foreach ($message as $message) {
      echo '
      <div class="message">
         <span>' . $message . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>
<style>
   /* CSS cho Dropdown Menu Category */
   .navbar .dropdown {
      display: inline-block;
      position: relative;
   }
   
   .navbar .dropdown-content {
      display: none;
      position: absolute;
      background-color: #fff;
      min-width: 160px;
      box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.1);
      z-index: 1000;
      border-radius: 5px;
      top: 100%;
      left: 0;
      padding: 10px 0;
   }

   /* Hiển thị bảng dropdown khi rà chuột vào */
   .navbar .dropdown:hover .dropdown-content {
      display: block;
   }

   /* Style cho từng dòng thể loại bên trong dropdown */
   .navbar .dropdown-content a {
      display: block !important;
      padding: 10px 20px !important;
      font-size: 1.4rem !important;
      color: var(--black) !important;
      text-transform: capitalize;
      margin: 0 !important;
   }

   .navbar .dropdown-content a:hover {
      background-color: #f1f1f1 !important;
      color: var(--purple) !important;
      padding-left: 25px !important; /* Hiệu ứng thụt lề nhẹ khi hover */
      transition: .2s linear;
   }
</style>
<div class="header">
   <div class="flex">
      <a href="home.php" class="logo"><img src="public/icon/logo.png" alt="logo">Bookept</a>

      <nav class="navbar">
         <a href="home.php"><img src="public/header/home_icon.svg" alt="home_icon">home</a>
         <a href="about.php"><img src="public/header/about_icon.svg" alt="about_icon">about</a>
         <div class="dropdown">
            <a href="search_page.php" style="cursor: pointer;">
               <img src="public/header/options.png" alt="category_icon" style="height: 17px; padding-right: 5px;">category <i class="fas fa-caret-down"></i>
            </a>
            <div class="dropdown-content">
               <?php
               // Tự động truy vấn danh sách thể loại từ Database
               $nav_category_query = mysqli_query($conn, "SELECT CateName FROM category");
               if (mysqli_num_rows($nav_category_query) > 0) {
                  while ($nav_cat = mysqli_fetch_assoc($nav_category_query)) {
                     // Gắn link chuyển thẳng đến trang search_page.php cùng tên Thể loại
                     echo '<a href="search_page.php?category_name=' . urlencode($nav_cat['CateName']) . '">' . $nav_cat['CateName'] . '</a>';
                  }
               }
               ?>
            </div>
         </div>
         <a href="shop.php"><img src="public/header/store.png" style="height: 20px; padding-right: 5px; alt="shop_icon">shop</a>
         <a href="bill.php"><img src="public/header/contact_icon.svg" alt="contact_icon">bill</a>
         <a href="orders.php"><img src="public/header/order_icon.svg" alt="order_icon">orders</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <a href="search_page.php" class="fas fa-search"></a>
         <div id="user-btn" class="fas fa-user"></div>
         <?php
         $select_cart_number = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
         $cart_rows_number = mysqli_num_rows($select_cart_number);
         ?>
         <a href="cart.php"> <i class="fas fa-shopping-cart"></i> <span>(<?php echo $cart_rows_number; ?>)</span> </a>
      </div>

      <div class="user-box">
         <p><img src="./public/header/account/user.svg" alt="user_icon">user : <span><?php echo $_SESSION['user_name']; ?></span></p>
         <p><img src="./public/header/account/email.svg" alt="email.svg">email : <span><?php echo $_SESSION['user_email']; ?></span></p>
         <a href="edit_customer.php" class="delete-btn"><img src="./public/header/logout.svg" alt="logout_icon">Edit Information</a>
         <br>
         <a href="logout.php" class="delete-btn"><img src="./public/header/logout.svg" alt="logout_icon">logout</a>
      </div>
   </div>
</div>