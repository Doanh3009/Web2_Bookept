<?php
include 'config.php';
session_start();
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:login_admin.php');
    exit();
}

if (!isset($_GET['order_id'])) {
    header('location:admin_orders.php');
    exit();
}

$orderId = $_GET['order_id'];

// UPDATE STATUS LOGIC
if (isset($_POST['update_status'])) {
    $new_status = $_POST['order_status'];
    mysqli_query($conn, "UPDATE orders SET payment_status = '$new_status' WHERE id = '$orderId'") or die('Query failed');
    echo "<script>alert('Order status updated to $new_status!');</script>";
}

// Fetch Order Info
$sql_order = mysqli_query($conn, "SELECT * FROM orders WHERE id = '$orderId'");
$result_order = mysqli_fetch_assoc($sql_order);

if (!$result_order) {
    echo "<script>alert('Order not found!'); window.location.href='admin_orders.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='image/Logo.png' rel='icon' type='image/x-icon' />
    <link rel="stylesheet" href="styles/admin/admin.css">
    <link rel="stylesheet" href="styles/admin/admin-reponsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>Order Detail #<?php echo $orderId; ?></title>
</head>

<body>
    <?php include 'admin_header.php'; ?>
    <div class="container">
        <aside class="sidebar open">
             <div class="top-sidebar">
                <a href="admin_main.php" class="channel-logo"><img src="public/icon/logo.png" alt="Channel Logo"></a>
            </div>
            </aside>

        <main class="content">
            <div class="section active">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h1 class="page-title">Order Details #<?php echo $orderId; ?></h1>
                    <a href="admin_orders.php" class="option-btn" style="text-decoration: none;"><i class="fa fa-arrow-left"></i> Back to Orders</a>
                </div>

                <div class="box" style="display: flex; flex-wrap: wrap; gap: 20px;">
                    <div style="flex: 1; min-width: 300px; border-right: 1px solid #eee; padding-right: 20px;">
                        <h3 style="margin-bottom: 15px; color: var(--dark-gray); border-bottom: 2px solid #eee; padding-bottom: 10px;">Items Purchased</h3>
                        <div style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                            <?php
                            $total_products = $result_order['total_products']; 
                            $products_array = explode(',', $total_products); 
                            
                            if (count($products_array) > 0 && empty(trim($products_array[0]))) {
                                array_shift($products_array); 
                            }

                            foreach ($products_array as $product_string) {
                                if (trim($product_string) == '') continue;
                                
                                $product_data = explode('(', $product_string);
                                $product_name = trim($product_data[0]); 
                                $product_quantity = isset($product_data[1]) ? intval($product_data[1]) : 1; 

                                // Fetch product image and price
                                $sql_product = mysqli_query($conn, "SELECT Image, Price FROM products WHERE Name = '$product_name'");
                                $product_detail = mysqli_fetch_assoc($sql_product);
                                
                                $product_img = $product_detail ? $product_detail['Image'] : 'default.png';
                                $product_price = $product_detail ? $product_detail['Price'] : 0;
                                $line_total = $product_price * $product_quantity;

                                echo '
                                <div class="order-product" style="display: flex; gap: 15px; margin-bottom: 15px; padding: 10px; background: var(--lighter-gray); border-radius: 10px;">
                                    <img src="image/' . $product_img . '" alt="" style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                    <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
                                        <h4 style="margin-bottom: 5px; font-size: 1.1rem; color: var(--dark-gray);">' . $product_name . '</h4>
                                        <p style="color: var(--medium-gray); font-size: 0.9rem;">Qty: <strong>' . $product_quantity . '</strong>  |  Unit Price: $' . $product_price . '</p>
                                    </div>
                                    <div style="display: flex; align-items: center; font-weight: bold; color: var(--red); font-size: 1.2rem;">
                                        $' . $line_total . '
                                    </div>
                                </div>';
                            }
                            ?>
                        </div>
                    </div>

                    <div style="flex: 1; min-width: 300px;">
                        <h3 style="margin-bottom: 15px; color: var(--dark-gray); border-bottom: 2px solid #eee; padding-bottom: 10px;">Customer & Delivery Info</h3>
                        <ul class="detail-order-group" style="padding: 0; list-style: none;">
                            <li class="detail-order-item"><span class="detail-order-item-left"><i class="fa fa-calendar"></i> Date:</span> <strong><?php echo $result_order['placed_on']; ?></strong></li>
                            <li class="detail-order-item"><span class="detail-order-item-left"><i class="fa fa-user"></i> Name:</span> <strong><?php echo $result_order['name']; ?></strong></li>
                            <li class="detail-order-item"><span class="detail-order-item-left"><i class="fa fa-phone"></i> Phone:</span> <strong><?php echo $result_order['number']; ?></strong></li>
                            <li class="detail-order-item"><span class="detail-order-item-left"><i class="fa fa-envelope"></i> Email:</span> <strong><?php echo $result_order['email']; ?></strong></li>
                            <li class="detail-order-item"><span class="detail-order-item-left"><i class="fa fa-credit-card"></i> Method:</span> <strong style="text-transform: uppercase;"><?php echo $result_order['method']; ?></strong></li>
                            <li class="detail-order-item tb" style="flex-direction: column; align-items: flex-start;">
                                <span class="detail-order-item-t"><i class="fa fa-location-arrow"></i> Shipping Address:</span>
                                <p class="detail-order-item-b" style="width: 100%; background: #f9f9f9; padding: 10px; border-radius: 5px; margin-top: 5px;"><?php echo $result_order['address']; ?></p>
                            </li>
                        </ul>

                        <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <span style="font-size: 1.2rem; color: var(--medium-gray);">Grand Total:</span>
                                <span style="font-size: 1.8rem; font-weight: bold; color: var(--red);">$<?php echo $result_order['total_price']; ?></span>
                            </div>

                            <form method="POST" action="" style="background: var(--lighter-gray); padding: 15px; border-radius: 10px;">
                                <label style="display: block; margin-bottom: 10px; font-weight: bold; color: var(--dark-gray);">Update Order Status:</label>
                                <div style="display: flex; gap: 10px;">
                                    <select name="order_status" class="form-control" style="flex: 1;">
                                        <option value="Pending" <?php if($result_order['payment_status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="Confirmed" <?php if($result_order['payment_status'] == 'Confirmed') echo 'selected'; ?>>Confirmed</option>
                                        <option value="Delivered" <?php if($result_order['payment_status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                                        <option value="Cancelled" <?php if($result_order['payment_status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="option-btn" style="margin: 0; padding: 0 20px;"><i class="fa fa-save"></i> Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
    <script src="js/admin.js"></script>
</body>
</html>