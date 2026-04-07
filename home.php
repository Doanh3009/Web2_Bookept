<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login_customer.php');
}

if (isset($_POST['add_to_cart'])) {

   $product_name = $_POST['product_name'];
   $product_price = $_POST['product_price'];
   $product_image = $_POST['product_image'];
   $product_quantity = $_POST['product_quantity'];

   $check_cart_numbers = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name' AND user_id = '$user_id'") or die('query failed');

   if (mysqli_num_rows($check_cart_numbers) > 0) {
      $message[] = 'already added to cart!';
   } else {
      mysqli_query($conn, "INSERT INTO `cart`(user_id, name, price, quantity, image) VALUES('$user_id', '$product_name', '$product_price', '$product_quantity', '$product_image')") or die('query failed');
      $message[] = 'product added to cart!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Bookept | Home</title>
   <meta name="description" content="Knowledge space for nerds. Search online books by subject and add them to your favorite cart">
   <meta name="keywords" content="php, sql, mysql, html, css, javascript, book">
   <link rel="shortcut icon" href="./public/favicon.ico" type="image/x-icon">

   <link rel="icon" href="public/favicon.ico">

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="styles/main.css">
   <link rel="stylesheet" href="styles/customers/service.css">
   <style>
      /* --- HIỆU ỨNG KHI RÀ CHUỘT VÀO TÊN THỂ LOẠI --- */
      .hover-cate {
         font-weight: normal !important; /* Chữ bình thường trước khi rà chuột */
         transition: all 0.1s ease-in-out;
      }
      
      .hover-cate:hover {
         font-weight: bold !important; /* In đậm khi rà chuột */
         text-decoration: underline !important; /* Có gạch chân khi rà chuột */
         color: #8e44ad !important; /* Vẫn giữ màu tím đặc trưng */
      }
   /* --- ÉP NÚT ADD TO CART SANG LỀ TRÁI CHẮC CHẮN 100% --- */
      .products .box-container .box .action {
         display: flex !important;
         justify-content: flex-start !important; 
         align-items: center !important;
         margin-top: 1rem !important; 
         width: 100% !important;
      }

      .products .box-container .box .action button {
         margin: 0 !important; 
      }
   
      /* --- BỐ CỤC CHÍNH: ÉP SÁT LỀ TRÁI --- */
      .products-layout {
         display: grid !important;
         grid-template-columns: 250px 1fr !important; /* Cột trái 250px cho sidebar, còn lại cho sách */
         gap: 3rem !important;
         max-width: 100% !important; /* QUAN TRỌNG: Gỡ bỏ giới hạn 1200px để tràn lề */
         width: 100% !important;
         margin: 0 !important; /* QUAN TRỌNG: Xoá lệnh tự động căn giữa gây ra khoảng trống */
         padding: 0 2rem !important; /* Cách mép màn hình một chút xíu cho đẹp */
         align-items: start !important;
      }
      
      /* --- SIDEBAR DANH MỤC --- */
      .category-sidebar {
         background: var(--white);
         padding: 2rem;
         border-radius: .5rem;
         border: var(--border);
         box-shadow: var(--box-shadow);
         position: sticky;
         top: 2rem;
         text-align: left !important;
      }
      
      /* --- LƯỚI SẢN PHẨM: 4 QUYỂN / HÀNG --- */
      .products .products-layout .box-container {
         display: grid !important;
         /* Cố định kích thước mỗi cuốn sách (từ 220px đến 280px) để chúng căng đều đặn */
         grid-template-columns: repeat(4, minmax(220px, 280px)) !important; 
         gap: 2rem !important;
         margin: 0 !important;
         max-width: 100% !important;
         width: 100% !important;
         align-items: start !important;
         /* QUAN TRỌNG: Ép sách xếp sát về lề trái cạnh sidebar, để kệ khoảng trống dồn hết sang phải */
         justify-content: start !important; 
      }
      
      /* --- CSS LÀM ĐẸP SIDEBAR --- */
      .category-sidebar h3 { font-size: 2rem; margin-bottom: 1.5rem; text-transform: uppercase; border-bottom: .1rem solid #ccc; padding-bottom: 1rem; }
      .category-sidebar ul { list-style: none; padding: 0; }
      .category-sidebar ul li { margin-bottom: 1rem; }
      .category-sidebar ul li a { font-size: 1.5rem; color: #666; display: block; transition: .2s; }
      .category-sidebar ul li a:hover { color: purple; padding-left: .5rem; }
      
      /* --- Responsive cho màn hình nhỏ --- */
      @media (max-width: 1300px) {
         .products .products-layout .box-container { grid-template-columns: repeat(3, minmax(220px, 280px)) !important; } /* Rớt xuống 3 cuốn nếu màn hơi nhỏ */
      }
      @media (max-width: 991px) {
         .products-layout { grid-template-columns: 1fr !important; }
         .products .products-layout .box-container { grid-template-columns: repeat(2, 1fr) !important; justify-content: center !important;}
         .category-sidebar { position: relative !important; top: 0; margin-bottom: 2rem; }
      }
      @media (max-width: 450px) {
         .products .products-layout .box-container { grid-template-columns: 1fr !important; }
      }
</style>

</head>

<body>
   <?php include 'header.php'; ?>

   <section class="home">
      <div class="content">
         <h3>Hand Picked Book to your door.</h3>
         <p>Embark on a literary journey with our online bookstore – where every page holds a new adventure.</p>
         <a href="about.php" class="white-btn">discover more</a>
      </div>
   </section>

   <section class="products">
      <h1 class="title">latest products</h1>
      
      <div class="products-layout">
         
         <div class="category-sidebar">
            <h3>Categories</h3>
            <ul>
               <?php
               // Truy vấn tất cả thể loại từ database
               $sidebar_category_query = mysqli_query($conn, "SELECT CateName FROM category");
               if (mysqli_num_rows($sidebar_category_query) > 0) {
                  while ($sidebar_cat = mysqli_fetch_assoc($sidebar_category_query)) {
                     // Tạo link chuyển hướng khách sang search_page
                     echo '<li><a href="search_page.php?category_name=' . urlencode($sidebar_cat['CateName']) . '"><i class="fas fa-angle-right"></i> ' . $sidebar_cat['CateName'] . '</a></li>';
                  }
               } else {
                  echo '<li><p style="font-size:1.5rem;">No categories found.</p></li>';
               }
               ?>
            </ul>
         </div>

         <div class="box-container">
            <?php
            // Truy vấn lấy sản phẩm có hiển thị cả Thể loại (CateName)
            $select_products = mysqli_query($conn, "SELECT products.*, category.CateName FROM `products` LEFT JOIN `category` ON products.CategoryId = category.CateId WHERE products.Status = '1' LIMIT 8") or die('query failed');
            if (mysqli_num_rows($select_products) > 0) {
               while ($fetch_products = mysqli_fetch_assoc($select_products)) {
            ?>
                  <form action="" method="post" class="box">
                     <input type="hidden" name="product_price" value="<?php echo $fetch_products['Price']; ?>" class="price">
                     <input type="hidden" name="product_image" value="<?php echo $fetch_products['Image']; ?>">

                     <a href="products_details.php?product_id=<?php echo $fetch_products['Id']; ?>">
                     <div class="image">
                        <img src="image/<?php echo $fetch_products['Image']; ?>" alt="">
                     </div>
                     </a>
                     <div class="details">
                        <div class="name" style="font-size: 18px;">
                           <img src="./public/card/name.svg" alt="name_icon">
                           <?php echo $fetch_products['Name']; ?>
                        </div>
                        
                        <div class="type" style="font-size: 13px; color: #8e44ad; font-weight: 600; margin-bottom: 5px;;">
                           <a href="search_page.php?category_name=<?php echo urlencode($fetch_products['CateName']); ?>" class="hover-cate" style="color: #8e44ad; text-decoration: none; transition: 0.2s;">
                              <?php echo $fetch_products['CateName']; ?>
                           </a>
                        </div>

                        <input type="hidden" name="product_name" value="<?php echo $fetch_products['Name']; ?>" >
                        <div class="qty-pri">
                           <input type="number" min="1" max="100" name="product_quantity" value="1" class="qty" oninput="if(this.value > 100) this.value = 100;">
                           <div class="price">
                              <span style="font-size:0.7em">$</span><?php echo $fetch_products['Price']; ?>
                           </div>
                        </div>
                        <div class="action">
                           <button id="cart" type="submit" name="add_to_cart">
                              <img src="./public/card/cart.svg" alt="cart_icon">add to cart
                            </button>
                        </div>
                     </div>
                  </form>
            <?php
               }
            } else {
               echo '<p class="empty">no products added yet!</p>';
            }
            ?>
         </div>
         </div>
      <div class="load-more" style="margin-top: 3rem; text-align:center">
         <a href="shop.php" class="transparent-btn">load more...</a>
      </div>
   </section>

   <section class="home-contact">
      <div>
         <img src="https://cdn.pixabay.com/photo/2022/03/01/08/11/call-center-7040784_960_720.png" alt="" style="border-radius: 1rem; width:32rem; height:25rem">
      </div>
      <div class="content">
         <div class="service-title">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSFwUZmt1x3c_O9OQEuyVIumLVmi3p85OEW-A&usqp=CAU" alt="" style="width:4rem">
            <h3>have any questions?</h3>
         </div>
         <div class="service-content">
            <p>24/7 customer care team ready to answer all your questions.</p>
            <p>Contact us for the best service support!</p>
         </div>
         <div>
            <a href="https://www.facebook.com/kzie30" class="option-btn">contact us</a>
         </div>
      </div>
   </section>

   <?php include 'footer.php'; ?>

   <div id="fcircle" onclick="scrollToTop()">
      <img src="public/icon/scroll-up-circle.svg" alt="Move up">
   </div>

   <!-- custom js file link  -->
   <script src="js/script.js"></script>

</body>

</html>