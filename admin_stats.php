<?php
include 'config.php';
session_start();
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('Location:login_admin.php');
    exit();
}

// Hàm hỗ trợ: Bóc tách chuỗi total_products của bảng orders
function parseOrderProducts($total_products_string) {
    $parsed_items = [];
    $items = explode(',', $total_products_string);
    foreach ($items as $item) {
        $item = trim($item);
        if (empty($item)) continue;
        if (preg_match('/^(.*?)\s*\((\d+)\)$/', $item, $matches)) {
            $name = trim($matches[1]);
            $qty = (int)$matches[2];
            if (!isset($parsed_items[$name])) {
                $parsed_items[$name] = 0;
            }
            $parsed_items[$name] += $qty;
        }
    }
    return $parsed_items;
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
    <title>Statistical Reports</title>
    <style>
        .report-card {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 1px solid rgba(0,0,0,0.02);
        }
        .report-title {
            font-size: 1.3rem;
            color: var(--dark-gray);
            margin-bottom: 15px;
            border-bottom: 2px solid var(--lighter-gray);
            padding-bottom: 10px;
        }
        .report-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>
    <div class="container">
        
        <aside class="sidebar open">
            <div class="top-sidebar">
                <a href="admin_main.php" class="channel-logo"><img src="public/icon/logo.png" alt="Channel Logo"></a>
            </div>
            <div class="middle-sidebar">
                <ul class="sidebar-list">
                    <li class="sidebar-list-item tab-content"><a href="admin_main.php" class="sidebar-link"><div class="sidebar-icon"><i class="fa fa-home"></i></div><div class="hidden-sidebar">Overview</div></a></li>
                    <li class="sidebar-list-item tab-content"><a href="admin_products.php" class="sidebar-link"><div class="sidebar-icon"><i class="fa fa-book"></i></div><div class="hidden-sidebar">Products</div></a></li>
                    <li class="sidebar-list-item tab-content"><a href="admin_imports.php" class="sidebar-link"><div class="sidebar-icon"><i class="fa fa-truck"></i></div><div class="hidden-sidebar">Imports</div></a></li>
                    <li class="sidebar-list-item tab-content"><a href="admin_users.php" class="sidebar-link"><div class="sidebar-icon"><i class="fa fa-group"></i></div><div class="hidden-sidebar">Customer</div></a></li>
                    <li class="sidebar-list-item tab-content"><a href="admin_orders.php" class="sidebar-link"><div class="sidebar-icon"><i class="fa fa-shopping-cart"></i></div><div class="hidden-sidebar">Order</div></a></li>
                    <li class="sidebar-list-item tab-content active"><a href="admin_stats.php" class="sidebar-link"><div class="sidebar-icon"><i class="fa fa-bar-chart"></i></div><div class="hidden-sidebar">Statistical</div></a></li>
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
            <div class="section active">
                <h1 class="page-title">Inventory & Statistics Reports</h1>

                <div class="report-card">
                    <h2 class="report-title"><i class="fa fa-exclamation-triangle" style="color: #e63946;"></i> Low Stock Alert</h2>
                    <?php $threshold = isset($_GET['threshold']) ? (int)$_GET['threshold'] : 10; ?>
                    <form method="GET" class="report-form">
                        <?php if(isset($_GET['ie_start'])) echo '<input type="hidden" name="ie_start" value="'.$_GET['ie_start'].'">'; ?>
                        <?php if(isset($_GET['ie_end'])) echo '<input type="hidden" name="ie_end" value="'.$_GET['ie_end'].'">'; ?>
                        <?php if(isset($_GET['target_date'])) echo '<input type="hidden" name="target_date" value="'.$_GET['target_date'].'">'; ?>
                        
                        <div>
                            <label class="form-label">Set Low Stock Threshold (Units):</label>
                            <input type="number" name="threshold" value="<?php echo $threshold; ?>" min="0" class="form-control" style="width: 200px;">
                        </div>
                        <button type="submit" class="option-btn" style="margin:0; padding: 10px 20px;">Check</button>
                    </form>

                    <table class="table">
                        <thead>
                            <tr>
                                <td>Product Code</td>
                                <td>Product Name</td>
                                <td>Current Stock</td>
                                <td>Status</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $low_stock_q = mysqli_query($conn, "SELECT ProductCode, Name, Quantity FROM products WHERE Quantity <= '$threshold' AND Status = 1");
                            if (mysqli_num_rows($low_stock_q) > 0) {
                                while ($row = mysqli_fetch_assoc($low_stock_q)) {
                                    $warning_color = ($row['Quantity'] == 0) ? 'red' : 'orange';
                                    $warning_text = ($row['Quantity'] == 0) ? 'Out of Stock' : 'Low Stock';
                            ?>
                            <tr>
                                <td><?php echo $row['ProductCode']; ?></td>
                                <td><strong><?php echo $row['Name']; ?></strong></td>
                                <td style="color: <?php echo $warning_color; ?>; font-weight: bold; font-size: 1.2rem;"><?php echo $row['Quantity']; ?></td>
                                <td><span style="background: <?php echo $warning_color; ?>; color: #fff; padding: 5px 10px; border-radius: 5px; font-size: 0.9rem;"><?php echo $warning_text; ?></span></td>
                            </tr>
                            <?php } } else { echo '<tr><td colspan="4" style="text-align:center;">All products have sufficient stock.</td></tr>'; } ?>
                        </tbody>
                    </table>
                </div>

                <div class="report-card">
                    <h2 class="report-title"><i class="fa fa-exchange" style="color: #4361ee;"></i> Import / Export Report</h2>
                    <?php 
                        $start_date = isset($_GET['ie_start']) ? $_GET['ie_start'] : date('Y-m-01'); // Default 1st of month
                        $end_date = isset($_GET['ie_end']) ? $_GET['ie_end'] : date('Y-m-d');
                    ?>
                    <form method="GET" class="report-form">
                        <?php if(isset($_GET['threshold'])) echo '<input type="hidden" name="threshold" value="'.$_GET['threshold'].'">'; ?>
                        <?php if(isset($_GET['target_date'])) echo '<input type="hidden" name="target_date" value="'.$_GET['target_date'].'">'; ?>

                        <div><label class="form-label">From Date:</label><input type="date" name="ie_start" value="<?php echo $start_date; ?>" class="form-control"></div>
                        <div><label class="form-label">To Date:</label><input type="date" name="ie_end" value="<?php echo $end_date; ?>" class="form-control"></div>
                        <button type="submit" class="option-btn" style="margin:0; padding: 10px 20px;">Generate Report</button>
                    </form>

                    <table class="table">
                        <thead>
                            <tr>
                                <td>Product Name</td>
                                <td>Imported (Qty)</td>
                                <td>Exported/Sold (Qty)</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($_GET['ie_start'])) {
                                // 1. Calculate Imports in range
                                $import_data = [];
                                $imports_q = mysqli_query($conn, "SELECT d.ProductId, p.Name, SUM(d.Quantity) as total_in FROM import_details d JOIN imports i ON d.ImportId = i.Id JOIN products p ON d.ProductId = p.Id WHERE i.Status = 1 AND DATE(i.ImportDate) BETWEEN '$start_date' AND '$end_date' GROUP BY d.ProductId");
                                while($row = mysqli_fetch_assoc($imports_q)) {
                                    $import_data[$row['Name']] = $row['total_in'];
                                }

                                // 2. Calculate Exports in range
                                $export_data = [];
                                $orders_q = mysqli_query($conn, "SELECT total_products FROM orders WHERE payment_status = 'Delivered' AND placed_on BETWEEN '$start_date' AND '$end_date'");
                                while($row = mysqli_fetch_assoc($orders_q)) {
                                    $parsed = parseOrderProducts($row['total_products']);
                                    foreach($parsed as $name => $qty) {
                                        if(!isset($export_data[$name])) $export_data[$name] = 0;
                                        $export_data[$name] += $qty;
                                    }
                                }

                                // 3. Merge and display
                                $all_product_names = array_unique(array_merge(array_keys($import_data), array_keys($export_data)));
                                if (count($all_product_names) > 0) {
                                    foreach ($all_product_names as $p_name) {
                                        $in = isset($import_data[$p_name]) ? $import_data[$p_name] : 0;
                                        $out = isset($export_data[$p_name]) ? $export_data[$p_name] : 0;
                            ?>
                                <tr>
                                    <td><strong><?php echo $p_name; ?></strong></td>
                                    <td style="color: green; font-weight: bold;">+<?php echo $in; ?></td>
                                    <td style="color: red; font-weight: bold;">-<?php echo $out; ?></td>
                                </tr>
                            <?php 
                                    }
                                } else { echo '<tr><td colspan="3" style="text-align:center;">No import/export activity in this period.</td></tr>'; }
                            } else { echo '<tr><td colspan="3" style="text-align:center;">Select dates to view report.</td></tr>'; }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="report-card">
                    <h2 class="report-title"><i class="fa fa-history" style="color: #8e44ad;"></i> Historical Inventory Lookup</h2>
                    <p style="font-size: 0.9rem; color: var(--medium-gray); margin-bottom: 15px;">Check how many units were in stock at the end of a specific past date.</p>
                    
                    <?php $target_date = isset($_GET['target_date']) ? $_GET['target_date'] : ''; ?>
                    <form method="GET" class="report-form">
                        <?php if(isset($_GET['threshold'])) echo '<input type="hidden" name="threshold" value="'.$_GET['threshold'].'">'; ?>
                        <?php if(isset($_GET['ie_start'])) echo '<input type="hidden" name="ie_start" value="'.$_GET['ie_start'].'">'; ?>
                        <?php if(isset($_GET['ie_end'])) echo '<input type="hidden" name="ie_end" value="'.$_GET['ie_end'].'">'; ?>

                        <div>
                            <label class="form-label">Select Date in the Past:</label>
                            <input type="date" name="target_date" value="<?php echo $target_date; ?>" max="<?php echo date('Y-m-d'); ?>" class="form-control" style="width: 200px;" required>
                        </div>
                        <button type="submit" class="option-btn" style="margin:0; padding: 10px 20px;">Lookup Stock</button>
                        
                        <a href="admin_stats.php" class="delete-btn" style="text-decoration:none; padding: 10px 15px; margin-left: 5px;"><i class="fa fa-refresh"></i> Reset All</a>
                    </form>

                    <?php if ($target_date != '') { ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <td>Product Name</td>
                                <td>Current Stock (Now)</td>
                                <td>Stock on <?php echo date('d-M-Y', strtotime($target_date)); ?></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Get all sold items AFTER target date
                            $sold_after = [];
                            $orders_q = mysqli_query($conn, "SELECT total_products FROM orders WHERE payment_status = 'Delivered' AND placed_on > '$target_date'");
                            while($row = mysqli_fetch_assoc($orders_q)) {
                                $parsed = parseOrderProducts($row['total_products']);
                                foreach($parsed as $name => $qty) {
                                    if(!isset($sold_after[$name])) $sold_after[$name] = 0;
                                    $sold_after[$name] += $qty;
                                }
                            }

                            // Get all imported items AFTER target date
                            $imported_after = [];
                            $imports_q = mysqli_query($conn, "SELECT p.Name, SUM(d.Quantity) as total_in FROM import_details d JOIN imports i ON d.ImportId = i.Id JOIN products p ON d.ProductId = p.Id WHERE i.Status = 1 AND DATE(i.ImportDate) > '$target_date' GROUP BY d.ProductId");
                            while($row = mysqli_fetch_assoc($imports_q)) {
                                $imported_after[$row['Name']] = $row['total_in'];
                            }

                            // Calculate past stock for all products
                            $all_products = mysqli_query($conn, "SELECT Name, Quantity FROM products");
                            while($row = mysqli_fetch_assoc($all_products)) {
                                $p_name = $row['Name'];
                                $current_qty = (int)$row['Quantity'];
                                
                                $s_after = isset($sold_after[$p_name]) ? $sold_after[$p_name] : 0;
                                $i_after = isset($imported_after[$p_name]) ? $imported_after[$p_name] : 0;
                                
                                // Reverse Algorithm: Past = Current + Sold(since then) - Imported(since then)
                                $past_qty = $current_qty + $s_after - $i_after;
                            ?>
                            <tr>
                                <td><strong><?php echo $p_name; ?></strong></td>
                                <td><?php echo $current_qty; ?></td>
                                <td style="color: #8e44ad; font-weight: bold; font-size: 1.1rem;"><?php echo $past_qty; ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php } ?>
                </div>

            </div>
        </main>
    </div>
    <script src="js/admin.js"></script>
    <script>
        // 1. Khi trang vừa load xong, kiểm tra xem có vị trí cuộn nào được lưu không
        document.addEventListener("DOMContentLoaded", function(event) { 
            let scrollpos = sessionStorage.getItem('scrollpos');
            if (scrollpos) {
                window.scrollTo(0, scrollpos); // Cuộn mượt mà đến vị trí cũ
                sessionStorage.removeItem('scrollpos'); // Xóa đi để không bị lỗi khi sang trang khác
            }
        });

        // 2. Chỉ khi người dùng bấm nút Submit (gửi form), mới lưu lại vị trí cuộn
        window.addEventListener("submit", function() {
            sessionStorage.setItem('scrollpos', window.scrollY);
        });
    </script>
</body>
</html>