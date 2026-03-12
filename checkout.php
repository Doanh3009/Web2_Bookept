<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
}

if (isset($_POST['order_btn'])) {

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $number = mysqli_real_escape_string($conn, $_POST['number']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $method = mysqli_real_escape_string($conn, $_POST['method']);
   $house_number = mysqli_real_escape_string($conn, $_POST['house-num']);
   $road = mysqli_real_escape_string($conn, $_POST['road']);
   $ward = mysqli_real_escape_string($conn, $_POST['ward']);
   $district =  mysqli_real_escape_string($conn, $_POST['district']);
   $city = mysqli_real_escape_string($conn, $_POST['city']);

   $address = "flat no. $house_number, $road, $ward, $district, $city, $country";
   $placed_on = date('Y-m-d');

   $cart_total = 0;
   $cart_products[] = '';

   $cart_query = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
   if (mysqli_num_rows($cart_query) > 0) {
      while ($cart_item = mysqli_fetch_assoc($cart_query)) {
         $cart_products[] = $cart_item['name'] . ' (' . $cart_item['quantity'] . ') ';
         $sub_total = ($cart_item['price'] * $cart_item['quantity']);
         $cart_total += $sub_total;
      }
   }

   $total_products = implode(', ', $cart_products);

   $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE name = '$name' AND number = '$number' AND email = '$email' AND method = '$method' AND address = '$address' AND total_products = '$total_products' AND total_price = '$cart_total'") or die('query failed');

   if ($cart_total == 0) {
      $message[] = 'your cart is empty';
   } else {
      if (mysqli_num_rows($order_query) > 0) {
         $message[] = 'order already placed!';
      } else {
         mysqli_query($conn, "INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES('$user_id', '$name', '$number', '$email', '$method', '$address', '$total_products', '$cart_total', '$placed_on')") or die('query failed');
         $message[] = 'order placed successfully!';
         mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Bookept | Checkout</title>
   <meta name="description" content="Knowledge space for nerds. Search online books by subject and add them to your favorite cart">
   <meta name="keywords" content="php, sql, mysql, html, css, javascript, book">
   <link rel="shortcut icon" href="./public/favicon.ico" type="image/x-icon">

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="styles/main.css">
   <link rel="stylesheet" href="./styles/customers/checkout.css">
   <style>
      .form-select {

         font-size: 16px;
         display: inline-block;
         width: 33.33%;
         margin-right: 20px;
         width: 100%;
         /* Đặt chiều rộng là 100% để các select box dài hết chiều rộng của cột */
         max-width: 400px;
         /* Tùy chỉnh chiều rộng tối đa của các select box */
      }
   </style>
</head>

<body>

   <?php include 'header.php'; ?>

   <div class="heading">
      <h3>checkout</h3>
      <p> <a href="home.php">home</a> / checkout </p>
   </div>
   <?php
   $user_id = $_SESSION['user_id'];
   $sql = mysqli_query($conn, "SELECT * FROM  `users` WHERE id=$user_id");
   $check = mysqli_fetch_assoc($sql);
   ?>
   <section class="checkout-container">
      <form action="" method="post">
         <h3><i class="fa-solid fa-folder-open"></i> place your order</h3>
         <div class="flex">
            <div class="inputBox">
               <span><i class="fa-solid fa-signature"></i> your name :</span>
               <input type="text" name="name" value="<?php echo $check['name']; ?>">
            </div>
            <div class="inputBox">
               <span><i class="fa-solid fa-hashtag"></i> your number :</span>
               <input type="text" name="number" value="<?php echo $check['phone_number']; ?>">
            </div>
            <div class="inputBox">
               <span><i class="fa-solid fa-at"></i> your email :</span>
               <input type="email" name="email" value="<?php echo $check['email']; ?>">
            </div>
            <div class="inputBox">
               <span><i class="fa-solid fa-money-check-dollar"></i> payment method :</span>
               <select name="method">
                  <option value="cash on delivery">cash on delivery</option>
                  <option value="credit card">credit card</option>
                  <option value="paypal">paypal</option>
                  <option value="momo">momo</option>
               </select>
            </div>
            <div class="inputBox">
               <span><i class="fa-solid fa-house"></i> house number :</span>
               <input required type="text" min="0" name="house-num" value="<?php echo $check['house_number']; ?>">
            </div>
            <div class="inputBox">
               <span><i class="fa-solid fa-location-dot"></i> road :</span>
               <input required type="text" name="road" value="<?php echo $check['road']; ?>">
            </div>
            <div class="inputBox">
               <span><i class="fa-solid fa-location-dot"></i> ward :</span>
               <br>
               <select required class="" name="ward" id="ward">
                  <option value="" selected disabled>Choose ward</option>
                  <?php
                  for ($i = 1; $i <= 12; $i++) {
                     $selected = ($_POST['ward'] == "Ward $i") ? 'selected' : '';
                     "<option value='Ward $i' $selected >Ward $i</option>";
                     $selected = ($check['ward'] == "Ward $i") ? 'selected' : '';
                     echo "<option value='Ward $i' $selected>Ward $i</option>";
                  }
                  ?>
               </select>
            </div>
            <div class="inputBox">
               <span><i class="fa-solid fa-location-dot"></i> district :</span>
               <br>
               <select required class="" name="district" id="district" aria-label=".form-select-sm">
                  <option value="" selected disabled>Choose district</option>
                  <?php
                  for ($i = 1; $i <= 12; $i++) {
                     $selected = ($_POST['district'] == "District $i") ? 'selected' : '';
                     "<option value='District $i' $selected>District $i</option>";
                     $selected = ($check['district'] == "District $i") ? 'selected' : '';
                     echo "<option value='District $i' $selected>District $i</option>";
                  }
                  ?>
               </select>
            </div>
            <div class="inputBox">
               <span><i class="fa-solid fa-location-dot"></i> city :</span>
               <br>
               <select required class="" name="city" id="city" style="width: 49.3%;">
                  <option value="" selected disabled>Choose city</option>
                  <option value="Hồ Chí Minh" selected>Ho Chi Minh city</option>
               </select>
            </div>
         </div>

         <div style="display: flex; justify-content:end">
            <input type="submit" value="🚩 order now" class="btn" name="order_btn">
         </div>
      </form>

      <?php
      $grand_total = 0;

      $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
      ?>

      <?php


      if (isset($_POST['order_btn'])) {
         // Lấy thông tin từ form
         $number = mysqli_real_escape_string($conn, $_POST['number']);
         $email = mysqli_real_escape_string($conn, $_POST['email']);
         $house_number = mysqli_real_escape_string($conn, $_POST['house-num']);
         $road = mysqli_real_escape_string($conn, $_POST['road']);
         $ward = mysqli_real_escape_string($conn, $_POST['ward']);
         $district = mysqli_real_escape_string($conn, $_POST['district']);
         $city = mysqli_real_escape_string($conn, $_POST['city']);

         // Kiểm tra xem email đã tồn tại trong bảng users chưa
         $sql_check_email = "SELECT * FROM users WHERE email = '$email'";
         $result_check_email = mysqli_query($conn, $sql_check_email);

         if (mysqli_num_rows($result_check_email) > 0) {
            // Cập nhật thông tin người dùng nếu email đã tồn tại
            $sql_update_user = "UPDATE users SET  phone_number = '$number',  house_number = '$house_number', road = '$road', city = '$city', district = '$district', ward = '$ward' WHERE email = '$email'";
            if (mysqli_query($conn, $sql_update_user)) {
               echo "<strong style='font-size:14px;'>Thông tin người dùng đã được cập nhật thành công !</strong>";
            } else {
               echo "Lỗi: " . mysqli_error($conn);
            }
         }
      }
      ?>
      <div class="summary-order">
         <div class="summary-header">
            <h2><i class="fa-solid fa-cart-flatbed"></i> Your cart</h2>
            <h5 style="background: #888; border-radius: 50%; width:3.5rem; height:3.5rem; color:white; display:flex; justify-content:center; align-items:center"><?php echo mysqli_num_rows($select_cart) ?></h5>
         </div>
         <div class="summary-list">
            <?php
            if (mysqli_num_rows($select_cart) > 0) {
               while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                  $total_price = ($fetch_cart['price'] * $fetch_cart['quantity']);
                  $grand_total += $total_price;
                  $name = $fetch_cart['name'];
                  mysqli_query($conn, "UPDATE products SET SoldYet = 'Yes' WHERE Name = '$name'") or die('query failed');
            ?>
                  <div class="summary-item">
                     <p><?php echo $fetch_cart['name']; ?></p>
                     <p><?php echo '$' . $fetch_cart['price']; ?> &bull; <?php echo $fetch_cart['quantity']; ?></p>
                  </div>
            <?php
               }
            } else {
               echo '<p class="empty">your cart is empty</p>';
            }
            ?>
         </div>
         <div class="summary-total">
            <p><i class="fa-solid fa-border-all"></i> grand total : </p>
            <p style="color:red">$<?php echo $grand_total; ?></p>
         </div>
      </div>
      <div id="paymentModal" class="payment-modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: #fff; margin: 10% auto; padding: 25px; border-radius: 10px; width: 450px; text-align: center; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
        <span onclick="closeModal()" style="position: absolute; right: 15px; top: 10px; font-size: 28px; cursor: pointer; color: #888;">&times;</span>
        <div id="paymentDetail">
            </div>
    </div>
</div>
   </section>

   <?php include 'footer.php'; ?>

   <!-- custom js file link  -->
   <script src="js/script.js">

   </script>
<script>
const checkoutForm = document.querySelector('form[action=""]');
const paymentModal = document.getElementById('paymentModal');
const paymentDetail = document.getElementById('paymentDetail');

// Hàm đóng modal
function closeModal() {
    paymentModal.style.display = "none";
}

// Xử lý khi bấm nút "Order Now"
checkoutForm.onsubmit = function(e) {
    const method = document.getElementById('payment-method').value;
    
    // Nếu chọn COD thì cho gửi form đi luôn như bình thường
    if (method === 'cash on delivery') return true;

    // Các phương thức khác: Chặn gửi form để hiện Modal
    e.preventDefault();
    paymentModal.style.display = "block";
    
    let content = "";
    if (method === 'momo') {
        content = `
            <h2 style="color: #ae2070; margin-bottom: 15px;">Thanh toán Momo</h2>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=MoMoPay_Bookept" alt="QR Momo" style="width: 200px;">
            <p style="font-size: 16px; margin: 10px 0;">Số tài khoản: <b>0987 654 321</b></p>
            <p style="font-size: 16px;">Chủ TK: <b>BOOKEPT SHOP</b></p>
            <p style="color: red; font-style: italic;">Vui lòng chuyển khoản đúng số tiền đơn hàng.</p>
        `;
    } else if (method === 'credit card') {
        content = `
            <h2 style="margin-bottom: 15px;"><i class="fa-solid fa-credit-card"></i> Nhập thẻ tín dụng</h2>
            <input type="text" placeholder="Số thẻ (16 chữ số)" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ddd;">
            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                <input type="text" placeholder="MM/YY" style="flex: 1; padding: 10px; border: 1px solid #ddd;">
                <input type="password" placeholder="CVV" style="flex: 1; padding: 10px; border: 1px solid #ddd;">
            </div>
            <p style="font-size: 13px; color: #666;">Thông tin thẻ sẽ được mã hóa bảo mật.</p>
        `;
    } else if (method === 'paypal') {
        content = `
            <h2 style="color: #003087; margin-bottom: 15px;"><i class="fa-brands fa-paypal"></i> Cổng Paypal</h2>
            <p style="font-size: 16px; margin-bottom: 20px;">Bạn sẽ được kết nối tới tài khoản:<br><b>payment@bookept.com</b></p>
            <div style="font-size: 40px; color: #003087; margin-bottom: 15px;"><i class="fa-brands fa-cc-paypal"></i></div>
        `;
    }

    // Thêm nút xác nhận cuối modal
    paymentDetail.innerHTML = content + `
        <button type="button" onclick="confirmPayment()" class="btn" style="width: 100%; margin-top: 20px;">Xác nhận đã thanh toán</button>
    `;
};

// Hàm gửi form thật sau khi khách đã xác nhận trên Modal
function confirmPayment() {
    alert("Thanh toán thành công! Hệ thống đang xử lý đơn hàng.");
    checkoutForm.submit();
}
</script>
</body>

</html>