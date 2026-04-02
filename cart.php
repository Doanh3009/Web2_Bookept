<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

if(isset($_POST['update_cart'])){
   $cart_id = $_POST['cart_id'];
   $cart_quantity = $_POST['cart_quantity'];
   
   // Kiểm tra: Nếu số lượng cập nhật lớn hơn 100 thì ép nó về 100
   if($cart_quantity > 100) {
       $cart_quantity = 100;
   }

   mysqli_query($conn, "UPDATE `cart` SET quantity = '$cart_quantity' WHERE id = '$cart_id'") or die('query failed');
   $message[] = 'cart quantity updated!';
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$delete_id'") or die('query failed');
   header('location:cart.php');
}

if(isset($_GET['delete_all'])){
   mysqli_query($conn, "DELETE FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
   header('location:cart.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Bookept | Cart</title>
   <meta name="description" content="Knowledge space for nerds. Search online books by subject and add them to your favorite cart">
   <meta name="keywords" content="php, sql, mysql, html, css, javascript, book">
   <link rel="shortcut icon" href="./public/favicon.ico" type="image/x-icon">

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="styles/main.css">
   <link rel="stylesheet" href="styles/customers/cart.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>shopping cart</h3>
   <p> <a href="home.php">home</a> / cart </p>
</div>

<section class="cart-container">
   <div class="cart-head">
      <?php $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed'); ?>
      <div class="head-left">
         <h2>My List</h2>
         <h6>&bull; <?php echo mysqli_num_rows($select_cart) ?> items</h6>
      </div>
   </div>

   <ul class="cart-list">
      <?php
         $grand_total = 0;
         $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE user_id = '$user_id'") or die('query failed');
         if(mysqli_num_rows($select_cart) > 0){
            while($fetch_cart = mysqli_fetch_assoc($select_cart)){   
      ?>
      <li class="cart-item">
         <div class="cart-item-content">
            <div class="image">
               <img src="image/<?php echo $fetch_cart['image']; ?>" alt="">
            </div>
            <div class="name">
               <h2><?php echo $fetch_cart['name']; ?></h2>
               <p>#id: <?php echo $fetch_cart['id']; ?></p>
            </div>
         </div>
         <form action="" method="post" class="cart-item-metrics">
            <div class="item-quantity">
               <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
               <input type="number" min="1" max="100" name="cart_quantity" value="<?php echo $fetch_cart['quantity']; ?>" oninput="if(this.value > 100) this.value = 100;">
            </div>
            <div class="item-price">
               <div>
                  <div class="price">$<?php echo number_format($fetch_cart['price'], 0, ',', '.'); ?> <span style="font-size: 1em; color:#888"> &bull; (<?php echo $fetch_cart['quantity']; ?>)</span></div>
<div class="sub-total"> sub total : <span>$<?php $sub_total = ($fetch_cart['quantity'] * $fetch_cart['price']); echo number_format($sub_total, 0, ',', '.'); ?></span></div>
               </div>
            </div>
            <div class="item-btn">
               <button type="submit" name="update_cart" value="update" class="option-btn">update</button>
            </div>
            <div class="item-delete">
               <a href="cart.php?delete=<?php echo $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('delete this from cart?');"></a>
            </div>
         </form>
      </li>
      <?php
      $grand_total += $sub_total;
         }
      }else{
         echo '<p class="empty">your cart is empty</p>';
      }
      ?>
      <li class="cart-action">
         <div class="cart-btn">
            <a href="shop.php" class="option-btn"><img src="./public/cart/continue.svg" alt="continue_icon">continue shopping</a>
            
            <?php
            // Kiểm tra kho hàng trước khi cho phép thanh toán
            $out_of_stock = false;
            $stock_message = "";
            
            // Nối bảng cart và products để lấy số lượng tồn kho (Quantity)
            $check_stock = mysqli_query($conn, "SELECT cart.name, products.Quantity FROM `cart` JOIN `products` ON cart.name = products.Name WHERE cart.user_id = '$user_id'");
            
            if (mysqli_num_rows($check_stock) > 0) {
                while ($item = mysqli_fetch_assoc($check_stock)) {
                    if ($item['Quantity'] <= 0) {
                        $out_of_stock = true;
                        // Thêm dấu backslash (\\) để tránh lỗi nháy đơn trong JavaScript alert
                        $stock_message = "Sản phẩm \\'" . $item['name'] . "\\' hiện đã hết hàng trong kho! Vui lòng xóa khỏi giỏ để tiếp tục thanh toán.";
                        break; 
                    }
                }
            }
            ?>

            <?php if ($out_of_stock) { ?>
               <a href="javascript:void(0);" onclick="alert('<?php echo $stock_message; ?>');" class="btn" style="background-color: #e74c3c;"><img src="./public/cart/checkout.svg" alt="checkout_icon">proceed to checkout</a>
            <?php } else { ?>
               <a href="checkout.php" class="btn <?php echo ($grand_total > 1)?'':'disabled'; ?>"><img src="./public/cart/checkout.svg" alt="checkout_icon">proceed to checkout</a>
            <?php } ?>

            <a href="cart.php?delete_all" class="delete-btn <?php echo ($grand_total > 1)?'':'disabled'; ?>" onclick="return confirm('delete all from cart?');"><img src="./public/cart/remove.svg" alt="delete_all_icon">delete all</a>
         </div>
         <div class="cart-total">
            <p>grand total : <span>$<?php echo number_format($grand_total, 0, ',', '.'); ?></span></p>
         </div>
      </li>
   </ul>
</section>

<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>

</body>
</html>