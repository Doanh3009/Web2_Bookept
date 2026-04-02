<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:login_customer.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Bắt buộc phải có order_id truyền vào url (ví dụ: bill_details.php?order_id=5)
if (!isset($_GET['order_id'])) {
    header('location:bill.php');
    exit();
}
$order_id = $_GET['order_id'];

// Lấy thông tin đơn hàng cụ thể dựa trên order_id và phải là của user đang đăng nhập
$sql_order = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id' AND user_id = '$user_id'");

if (mysqli_num_rows($sql_order) == 0) {
    echo "<script>alert('Order not found!'); window.location.href='bill.php';</script>";
    exit();
}
$order_info = mysqli_fetch_assoc($sql_order);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Details #<?php echo $order_id; ?></title>
    <link rel="shortcut icon" href="./public/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/main.css">
    <style>
        .details-container {
            max-width: 800px;
            margin: 20px auto;
            background: #f9f9f9;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .info-group {
            margin-bottom: 20px;
            font-size: 1.5rem;
        }
        .product-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }
        .product-list th, .product-list td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            font-size: 1.4rem;
        }
        .product-list th {
            background-color: var(--light-bg);
        }
        .total-row {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--red);
            text-align: right;
            margin-top: 20px;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: var(--purple);
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="heading">
    <h3>ORDER DETAILS #<?php echo $order_id; ?></h3>
    <p> <a href="bill.php">my orders</a> / details </p>
</div>

<div class="details-container">
    <div class="info-group">
        <p><strong>Customer Name:</strong> <?php echo $order_info['name']; ?></p>
        <p><strong>Phone:</strong> <?php echo $order_info['number']; ?></p>
        <p><strong>Shipping Address:</strong> <?php echo $order_info['address']; ?></p>
        <p><strong>Payment Status:</strong> <span style="color: <?php echo ($order_info['payment_status'] == 'Completed') ? 'green' : 'red'; ?>"><?php echo $order_info['payment_status']; ?></span></p>
    </div>

    <h3>Purchased Items</h3>
    <table class="product-list">
        <thead>
            <tr>
                <th>No.</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Thuật toán tách chuỗi "Tên sản phẩm (Số lượng)" của bạn
            $total_products = $order_info['total_products']; 
            $products_array = explode(',', $total_products); 
            $product_number = 1;

            if (count($products_array) > 0 && empty(trim($products_array[0]))) {
                array_shift($products_array); 
            }

            foreach ($products_array as $product_string) {
                if(trim($product_string) == '') continue;

                $product_data = explode('(', $product_string);
                $product_name = trim($product_data[0]); 
                $product_quantity = isset($product_data[1]) ? intval($product_data[1]) : 1; 

                // Lấy đơn giá từ DB để tính toán hiển thị
                $sql_product = mysqli_query($conn, "SELECT Price FROM products WHERE name='$product_name'");
                $product_detail = mysqli_fetch_assoc($sql_product);
                $product_price = $product_detail ? $product_detail['Price'] : 0;
                $line_total = $product_price * $product_quantity;

                echo "<tr>";
                echo "<td>" . $product_number . "</td>";
                echo "<td><strong>" . $product_name . "</strong></td>";
                echo "<td>" . $product_quantity . "</td>";
                echo "<td>$" . number_format($line_total, 0, ',', '.') . "</td>";
                echo "</tr>";

                $product_number++;
            }
            ?>
        </tbody>
    </table>

    <div class="total-row">
        Grand Total: $<?php echo number_format($order_info['total_price'], 0, ',', '.'); ?>
    </div>

    <a href="bill.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to History</a>
</div>

</body>
</html>