<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:login_customer.php');
    exit();
}
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Order History</title>
    <link rel="shortcut icon" href="./public/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/main.css">
    <style>
        .history-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .order-table th, .order-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            font-size: 1.5rem;
        }
        .order-table th {
            background-color: var(--purple);
            color: white;
        }
        .btn-view {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--orange);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.4rem;
        }
        .btn-view:hover {
            background-color: var(--black);
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="heading">
    <h3>ORDER HISTORY</h3>
    <p> <a href="home.php">home</a> / my orders </p>
</div>

<div class="history-container">
    <table class="order-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Order Date</th>
                <th>Estimated Ship Date</th>
                <th>Total Price</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Lấy TẤT CẢ đơn hàng của user này, sắp xếp mới nhất lên đầu (Bỏ LIMIT 1)
            $sql_orders = mysqli_query($conn, "SELECT * FROM `orders` WHERE user_id = '$user_id' ORDER BY placed_on DESC");
            
            if (mysqli_num_rows($sql_orders) > 0) {
                while ($fetch_orders = mysqli_fetch_assoc($sql_orders)) {
                    $delivery_date = date('Y-m-d', strtotime($fetch_orders['placed_on'] . ' +3 days'));
                    
                    // Tạo màu sắc cho trạng thái
                    $status_color = 'black';
                    if($fetch_orders['payment_status'] == 'pending') $status_color = 'orange';
                    if($fetch_orders['payment_status'] == 'Completed') $status_color = 'green';
                    if($fetch_orders['payment_status'] == 'Cancel') $status_color = 'red';
            ?>
            <tr>
                <td><strong>#<?php echo $fetch_orders['id']; ?></strong></td>
                <td><?php echo $fetch_orders['placed_on']; ?></td>
                <td><?php echo $delivery_date; ?></td>
                <td>$<?php echo number_format($fetch_orders['total_price'], 0, ',', '.'); ?></td>
                <td style="text-transform: uppercase;"><?php echo $fetch_orders['method']; ?></td>
                <td style="color: <?php echo $status_color; ?>; font-weight: bold;"><?php echo $fetch_orders['payment_status']; ?></td>
                <td>
                    <a href="bill_details.php?order_id=<?php echo $fetch_orders['id']; ?>" class="btn-view"><i class="fas fa-eye"></i> View Details</a>
                </td>
            </tr>
            <?php
                }
            } else {
                echo '<tr><td colspan="7" style="padding: 30px; color: red;">You have no order history yet!</td></tr>';
            }
            ?>
        </tbody>
    </table>
</div>

<script src="js/script.js"></script>
</body>
</html>