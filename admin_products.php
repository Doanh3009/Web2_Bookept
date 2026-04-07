<?php
include 'config.php';
session_start();
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('Location:login_admin.php');
    exit();
}
// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     // Lấy dữ liệu từ form
//     $name = $_POST['name'];
//     $email = $_POST['email'];
//     $password = $_POST['password'];
//     $phone_number = $_POST['phone_number'];
//     $insert_query = "INSERT INTO users (name, email, password, user_type, phone_number) VALUES ('$name', '$email', '$password', '$user_type', '$phone_number')";
//     header('Location: admin_products.php');
//     exit();
// }
//search
// if (isset($_GET['submit_search'])) 
// {
//     $search=$_GET['text_search'];
//     $sql_tk="SELECT * FROM users WHERE name LIKE '%" . $search . "%'";
//     $sql_search= mysqli_query($conn,$sql_tk);
// }
// else
// {
//     $search='';
//     $sql_tk="SELECT* FROM users limit 5";
//     $sql_search= mysqli_query($conn,$sql_tk);

// }
// xóa 

if (isset($_GET['page'])) {
    $id = $_GET['page'];
} else {
    $id = 1;
}

// Thay thế đoạn if(isset($_POST['add_product'])) cũ bằng đoạn này:
if (isset($_POST['add_product'])) {
    $code = ''; // Đã bỏ Product Code, gán rỗng
    $name = $_POST['Name'];
    $import_price = 0; // Mặc định = 0 khi tạo mới
    $profit_margin = isset($_POST['ProfitMargin']) ? (float)$_POST['ProfitMargin'] : 0; 
    $price = 0; // Mặc định giá bán = 0 khi tạo mới
    $image = $_FILES['Image']['name'];
    $image_tmp_name = $_FILES['Image']['tmp_name'];
    $author = $_POST['MainAuthor'];
    $publisher = $_POST['Publisher'];
    $pub_year = $_POST['PublicationYear'];
    $language = $_POST['Language'];
    $cover =  $_POST['CoverType'];
    $quantity = 0; // Mặc định = 0
    $unit = ''; // Đã bỏ Unit, gán rỗng
    $des = $_POST['Description'];
    $cate = $_POST['CategoryId'];
    $status = $_POST['Status'];

    // Câu lệnh INSERT được giữ nguyên cột để không báo lỗi Database
    $add_product_query = mysqli_query($conn, "INSERT INTO products(ProductCode, CategoryId, Name, ImportPrice, Price, Image, MainAuthor, Publisher, PublicationYear, Language, CoverType, Quantity, Unit, Description, SoldYet, Status)
         VALUES('$code', '$cate', '$name', '$import_price', '$price', '$image', '$author', '$publisher', '$pub_year', '$language', '$cover', '$quantity', '$unit', '$des', 'No', '$status')") or die('query failed');

    if ($add_product_query) {
        move_uploaded_file($image_tmp_name, "image/" . $image);
        echo "<script>alert('Add Product Success!');</script>";
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete']; 
    
    // Kiểm tra xem sản phẩm đã từng nhập hàng (Quantity > 0) hoặc đã bán (SoldYet = Yes) chưa
    $check_product = mysqli_query($conn, "SELECT Quantity, SoldYet FROM products WHERE Id = '$delete_id'");
    $fetch_check = mysqli_fetch_assoc($check_product);
    
    if ($fetch_check['Quantity'] > 0 || $fetch_check['SoldYet'] == "Yes") {
        // Nếu đã nhập hàng hoặc đã bán -> Chỉ đánh dấu Ẩn (Status = 0)
        mysqli_query($conn, "UPDATE products SET Status = 0 WHERE Id = '$delete_id'");
        echo "<script>alert('The product has already been imported/sold. It has been set to HIDDEN instead of being permanently deleted.');</script>";
    } else {
        // Chưa nhập hàng -> Xóa hẳn khỏi CSDL
        mysqli_query($conn, "DELETE FROM products WHERE Id = '$delete_id'") or die('query failed');
        echo "<script>alert('The product has been permanently deleted from the database.');</script>";
    }
    echo "<script>window.location.href='admin_products.php';</script>";
    exit();
}

if (isset($_GET['display'])) {
    $hidden_id = $_GET['display'];
    $display_sql = mysqli_query($conn, "SELECT * FROM products WHERE Id = '$hidden_id'");
    $fetch_display = mysqli_fetch_assoc($display_sql);
    mysqli_query($conn, "UPDATE products SET Status = 1 WHERE Id = '$hidden_id'");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='image/Logo.png' rel='icon' type='image/x-icon' />
    <link rel="stylesheet" href="styles/admin/admin.css">
    <link rel="stylesheet" href="styles/admin/admin-reponsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" type="text/css" />


    <link rel="stylesheet" href="">
    <title>Admin_Bookept</title>
</head>



<body id="<?php echo $id ?>">
    <?php include 'admin_header.php'; ?>
    <div class="container">
        <aside class="sidebar open">
            <div class="top-sidebar">
                <a href="admin_main.php" class="channel-logo"><img src="public/icon/logo.png" alt="Channel Logo"></a>
                <div class="hidden-sidebar your-channel"><img src="" style="height: 30px;" alt="">
                </div>
            </div>
            <div class="middle-sidebar">
                <ul class="sidebar-list">
                    <li id="main" class="sidebar-list-item tab-content">
                        <a href="admin_main.php" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa fa-home"></i></div>
                            <div class="hidden-sidebar">Overview</div>
                        </a>
                    </li>
                    <li class="sidebar-list-item tab-content active">
                        <a href="admin_products.php" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa fa-book"></i></div>
                            <div class="hidden-sidebar">Products</div>
                        </a>
                    </li>
                    <li class="sidebar-list-item tab-content">
                        <a href="admin_imports.php" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa fa-truck"></i></div>
                            <div class="hidden-sidebar">Imports</div>
                        </a>
                    </li>
                    <li id="customers" class="sidebar-list-item tab-content">
                        <a href="admin_users.php" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa fa-group"></i></div>
                            <div class="hidden-sidebar">Customer</div>
                        </a>
                    </li>
                    <li class="sidebar-list-item tab-content">
                        <a href="admin_orders.php" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa fa-shopping-cart"></i></div>
                            <div class="hidden-sidebar">Order</div>
                        </a>
                    </li>
                    <li class="sidebar-list-item tab-content">
                        <a href="admin_stats.php" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa fa-bar-chart"></i></div>
                            <div class="hidden-sidebar">Statistical</div>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="bottom-sidebar">
                <ul class="sidebar-list">
                    <li class="sidebar-list-item user-logout">
                        <a href="#" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa fa-arrow-right"></i></div>
                            <div class="hidden-sidebar" onclick="redirectToLogout()">Logout</div>
                            <script>
                                function redirectToLogout() {
                                    window.location.href = "logout_admin.php";
                                }
                            </script>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>
        <main class="content">
            <div class="section product-all active">
                <div class="admin-control">


                    <div class="admin-control-center">
                        <form method="get" class="form-search">
                            <input type="hidden" name="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                            <span class="search-btn"><i class="fa fa-search"></i></span>
                            <input id="form-search-product" type="text" name="search" class="form-search-input" placeholder="Search book name...">
                            <button type="submit" name="submit_search" class="btn-control-large ">Search</button>
                        </form>
                    </div>
                    <div class="admin-control-right">
                        <button class="btn-control-large" id="btn-add-product"><i class="fa fa-plus"></i> Add new product</button>
                    </div>
                </div>
                <div id="show-product">
                    <?php
                    $search_keyword = isset($_GET['search']) ? $_GET['search'] : '';

                    // Thêm điều kiện tìm kiếm vào truy vấn SQL
                    $sql_query = "SELECT * FROM products WHERE Name LIKE '%$search_keyword%'";
                    $products_per_page = 8;
                    // Tính số trang dựa trên tổng số sản phẩm và số sản phẩm mỗi trang
                    $total_products = mysqli_num_rows(mysqli_query($conn, $sql_query));
                    $total_pages = ceil($total_products / $products_per_page);
                    // Lấy trang hiện tại từ tham số truyền vào hoặc mặc định là trang 1
                    $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
                    // Tính offset (bắt đầu lấy từ vị trí nào trong cơ sở dữ liệu)
                    $offset = ($current_page - 1) * $products_per_page;
                    // $select_products = mysqli_query($conn, "SELECT * FROM `products` LIMIT $offset, $products_per_page") or die('query failed');
                    $select_products = mysqli_query($conn, $sql_query . " LIMIT $offset, $products_per_page") or die('query failed');




                    if (mysqli_num_rows($select_products) > 0) {
                        while ($fetch_products = mysqli_fetch_assoc($select_products)) {
                    ?>
                            <div class="list">
                                <div class="list-left">
                                    <img src="./image/<?php echo $fetch_products['Image'] ?>" alt="">
                                    <div class="list-info">
                                        <h4><?php echo $fetch_products['Name'] ?></h4>
                                        <p class="list-note"><?php echo $fetch_products['Description'] ?></p>
                                        <span class="list-category">
                                            <?php $category = mysqli_query($conn, "SELECT * FROM products p INNER JOIN category c ON p.CategoryId = c.CateId");
                                            if (mysqli_num_rows($category) > 0) {
                                                while ($fetch = mysqli_fetch_assoc($category)) {
                                                    if ($fetch['CateId'] == $fetch_products['CategoryId']) {
                                                        echo $fetch['CateName'];
                                                        break;
                                                    }
                                                }
                                            }
                                            ?></span>
                                    </div>
                                </div>
                                <div class="list-right">
                                    <div class="list-price">
                                        <span class="list-current-price"><?php echo $fetch_products['Price'] ?>$</span>
                                    </div>
                                    <div class="list-control">
                                        <div class="list-tool">
                                            <a href="admin_products_edit.php?edit_product=<?php echo $fetch_products['Id']; ?>" style="color:black;"><button id="edit-product" name="edit" class="btn-edit"><i class="fa fa-pencil"></i></button></a>
                                            <?php
                                            if ($fetch_products['Status'] == 0) {
                                            ?>

                                                <a style="color:black" href="admin_products.php?display=<?php echo $fetch_products['Id'] ?>"><button name="display" class="btn-edit" onclick="return confirm('Do you want to continue selling this item?')"><i class="fa fa-eye" aria-hidden="true"></i></button></a>
                                            <?php } elseif ($fetch_products['Status'] == 1) {
                                            ?>
                                                <a href="admin_products.php?delete=<?php echo $fetch_products['Id']; ?>"><button class="btn-delete" name="delete" onclick="return confirm('Delete this product?')"><i class="fa fa-trash"></i></button> </a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        echo '<div class="no-result"><div class="no-result-i"><i class="fa fa-home"></i></div><div class="no-result-h">Không có sản phẩm để hiển thị</div></div>';
                    }
                    ?>
                    <div class="page-nav">
                        <ul class="page-nav-list">
                            <?php
                            // Hiển thị các nút phân trang
                            for ($page = 1; $page <= $total_pages; $page++) {
                                // Kiểm tra xem có từ khóa tìm kiếm hay không
                                if (!empty($search_keyword)) {
                                    echo '<li class="page-nav-item"><a href="admin_products.php?page=' . $page . '&search=' . $search_keyword . '">' . $page . '</a></li>';
                                } else {
                                    echo '<li class="page-nav-item"><a href="admin_products.php?page=' . $page . '">' . $page . '</a></li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>

            </div>
        </main>
       <div class="modal add-product">
            <div class="modal-container">
                <h3 class="modal-container-title add-product-e">REGISTER NEW BOOK</h3>
                    <p style="color: #e63946; font-size: 14px; font-weight: 500; margin: 0 20px 15px; background: #ffe3e3; padding: 10px; border-radius: 5px;">
                        <i class="fa fa-exclamation-triangle"></i> Note: Use this form ONLY for completely new books. To restock existing books, please go to the <b>Imports</b> menu.
                    </p>
                <button class="modal-close product-form"><i class="fa fa-times"></i></button>
                <div class="modal-content">
                    <form action="" method="POST" class="add-product-form" enctype="multipart/form-data">
                        <div class="modal-content-left">
                            <img id="imagePreview" src="./image/" alt="" class="upload-image-preview">
                            <div class="form-group file">
                                <label for="up-image" class="form-label-file"><i class="fa fa-plus"></i>Upload Image</label>
                                <input accept="image/jpeg, image/png, image/jpg" id="up-image" name="Image" type="file" class="form-control" onchange="previewImage(event)">
                            </div>
                        </div>
                        <div class="modal-content-right">
                            <div class="form-group">
                                <label for="book-name" class="form-label">Book Name</label>
                                <input id="book-name" name="Name" type="text" placeholder="Please enter the book name" value="" required class="form-control">
                                <span class="form-message"></span>
                            </div>
                            <div class="form-group">
                                <label for="category" class="form-label">Category</label>
                                <select name="CategoryId" id="category" class="form-control">
                                    <?php
                                    $sql_cate = "SELECT * FROM category";
                                    $result = mysqli_query($conn, $sql_cate);
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<option value="' . $row['CateId'] . '">' . $row['CateName'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <span class="form-message"></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="profit-margin" class="form-label">Profit Margin (%)</label>
                                <input id="profit-margin" name="ProfitMargin" type="number" min="0" step="any" placeholder="E.g: 20 (for 20%)" required class="form-control">
                                <span class="form-message"></span>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="Status" class="form-control">
                                    <option value="1">Show (On Sale)</option>
                                    <option value="0">Hide (Not for Sale)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="author" class="form-label">Author</label>
                                <input id="author" name="MainAuthor" type="text" class="form-control">
                                <span class="form-message"></span>
                            </div>
                            <div class="form-group">
                                <label for="publisher" class="form-label">Publisher</label>
                                <input id="publisher" name="Publisher" type="text" class="form-control">
                                <span class="form-message"></span>
                            </div>
                            <div class="form-group">
                                <label for="pub-year" class="form-label">Publication Year</label>
                                <input id="pub-year" name="PublicationYear" type="number" min="0" class="form-control">
                                <span class="form-message"></span>
                            </div>
                            <div class="form-group">
                                <label for="language" class="form-label">Language</label>
                                <input id="language" name="Language" type="text" class="form-control">
                                <span class="form-message"></span>
                            </div>
                            <div class="form-group">
                                <label for="cover" class="form-label">Cover Type</label>
                                <input id="cover" name="CoverType" type="text" class="form-control">
                                <span class="form-message"></span>
                            </div>
                            <div class="form-group">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="product-desc" id="description" name="Description" placeholder="Enter book description..."></textarea>
                                <span class="form-message"></span>
                            </div>
                            
                            <button type="submit" class="form-submit btn-add-product-form add-product-e" id="add-product-button" name="add_product">
                                <i class="fa fa-plus"></i>
                                <span>REGISTER BOOK</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="js/admin.js">
        </script>
        <script>
            // if (typeof window.history.pushState === 'function') {
            //     // Remove GET parameters from the URL
            //     window.history.pushState({}, '', window.location.href.split('?')[0]);
            // }
            let links = document.querySelectorAll('.page-nav-item');
            let bodyId = parseInt(document.body.id) - 1;
            console.log(links + "as" + bodyId);
            links[bodyId].classList.add('active');
        </script>
        <?php

        ?>
</body>

</html>