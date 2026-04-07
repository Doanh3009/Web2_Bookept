<?php
include 'config.php';
session_start();
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('Location:login_admin.php');
    exit();
}

// ==========================================
// XỬ LÝ LƯU TRỮ TRẠNG THÁI TÌM KIẾM (SESSION)
// ==========================================

// 1. Nếu người dùng bấm "Reset All"
if (isset($_GET['reset'])) {
    unset($_SESSION['stats_threshold']);
    unset($_SESSION['stats_ie_start']);
    unset($_SESSION['stats_ie_end']);
    unset($_SESSION['stats_target_date']);
    header("Location: admin_stats.php");
    exit();
}

// 2. Lưu trạng thái Low Stock
if (isset($_GET['threshold'])) {
    $_SESSION['stats_threshold'] = (int)$_GET['threshold'];
    $_SESSION['stats_include_hidden'] = isset($_GET['include_hidden']) ? 1 : 0;
}
$threshold = isset($_SESSION['stats_threshold']) ? $_SESSION['stats_threshold'] : 10;
$include_hidden = isset($_SESSION['stats_include_hidden']) ? $_SESSION['stats_include_hidden'] : 0;

// 3. Lưu trạng thái Import/Export
if (isset($_GET['ie_start']) && isset($_GET['ie_end'])) {
    $_SESSION['stats_ie_start'] = $_GET['ie_start'];
    $_SESSION['stats_ie_end'] = $_GET['ie_end'];
}
$start_date = isset($_SESSION['stats_ie_start']) ? $_SESSION['stats_ie_start'] : date('Y-m-01');
$end_date = isset($_SESSION['stats_ie_end']) ? $_SESSION['stats_ie_end'] : date('Y-m-d');

// 4. Lưu trạng thái Lookup Stock
if (isset($_GET['target_date'])) {
    $_SESSION['stats_target_date'] = $_GET['target_date'];
}
$target_date = isset($_SESSION['stats_target_date']) ? $_SESSION['stats_target_date'] : '';


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
    <title>Admin_Bookept</title>
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
        .detail-item a:hover {
            text-decoration: underline !important;
            opacity: 0.8;
        }
        .report-card details ul {
            border-left: 2px solid #eee;
            margin-left: 5px;
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
                    
                    <form method="GET" class="report-form" style="align-items: center;">
                        <div>
                            <label class="form-label">Set Low Stock Threshold (Units):</label>
                            <input type="number" name="threshold" value="<?php echo $threshold; ?>" min="0" class="form-control" style="width: 200px;">
                        </div>
                        
                        <div style="display: flex; align-items: center; margin-bottom: 0; margin-left: 10px;">
                            <input type="checkbox" id="include_hidden" name="include_hidden" value="1" <?php echo $include_hidden ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                            <label for="include_hidden" style="cursor: pointer; font-size: 0.95rem; color: #555; margin-left: 8px; font-weight: 500;">Include hidden products</label>
                        </div>

                        <button type="submit" class="option-btn" style="margin: 0 0 0 15px; padding: 10px 20px;">Check</button>
                    </form>

                    <table class="table">
                        <thead>
                            <tr>
                                <td>Product Name</td>
                                <td>Current Stock</td>
                                <td>Status</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Đã xóa ProductCode khỏi câu lệnh SELECT cho nhẹ Database
                            $status_condition = $include_hidden ? "" : "AND Status = 1";
                            $low_stock_q = mysqli_query($conn, "SELECT Name, Quantity, Status FROM products WHERE Quantity <= '$threshold' $status_condition");

                            if (mysqli_num_rows($low_stock_q) > 0) {
                                while ($row = mysqli_fetch_assoc($low_stock_q)) {
                                    $warning_color = ($row['Quantity'] == 0) ? 'red' : 'orange';
                                    $warning_text = ($row['Quantity'] == 0) ? 'Out of Stock' : 'Low Stock';
                                    
                                    // Tạo nhãn Hidden nếu sản phẩm đó đang bị ẩn
                                    $status_badge = ($row['Status'] == 0) ? '<span style="background: #95a5a6; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; margin-left: 10px; vertical-align: middle;">Hidden</span>' : '';
                            ?>
                            <tr>
                                <td><strong><?php echo $row['Name']; ?></strong> <?php echo $status_badge; ?></td>
                                <td style="color: <?php echo $warning_color; ?>; font-weight: bold; font-size: 1.2rem;"><?php echo $row['Quantity']; ?></td>
                                <td><span style="background: <?php echo $warning_color; ?>; color: #fff; padding: 5px 10px; border-radius: 5px; font-size: 0.9rem;"><?php echo $warning_text; ?></span></td>
                            </tr>
                            <?php 
                                } 
                            } else { 
                                // Đã sửa colspan từ 4 xuống 3 cho vừa với số cột mới
                                echo '<tr><td colspan="3" style="text-align:center;">All products have sufficient stock.</td></tr>'; 
                            } 
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="report-card">
                    <h2 class="report-title"><i class="fa fa-exchange" style="color: #4361ee;"></i> Import / Export Report</h2>
                    
                    <form method="GET" class="report-form">
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
                            // Kiểm tra biến session thay vì $_GET
                            if (isset($_SESSION['stats_ie_start'])) {
                                $import_data = [];
                                $export_data = [];

                               // 1. LẤY CHI TIẾT PHIẾU NHẬP
                                    $imports_q = mysqli_query($conn, "SELECT d.ProductId, p.Name, d.Quantity, i.Id as ReceiptId, i.ImportDate 
                                                                    FROM import_details d 
                                                                    JOIN imports i ON d.ImportId = i.Id 
                                                                    JOIN products p ON d.ProductId = p.Id 
                                                                    WHERE i.Status = 1 AND DATE(i.ImportDate) BETWEEN '$start_date' AND '$end_date'");
                                    while($row = mysqli_fetch_assoc($imports_q)) {
                                        $p_name = $row['Name'];
                                        if(!isset($import_data[$p_name])) {
                                            $import_data[$p_name] = ['total' => 0, 'details' => []];
                                        }
                                        $import_data[$p_name]['total'] += $row['Quantity'];
                                        
                                        $link = "admin_imports.php?action=edit&id=" . $row['ReceiptId'];
                                        $import_data[$p_name]['details'][] = "<a href='$link' style='color: #8e44ad; text-decoration: none; font-weight: 500;'>Receipt #{$row['ReceiptId']} ({$row['ImportDate']}): +{$row['Quantity']}</a>";
                                    }

                                    // 2. LẤY CHI TIẾT ĐƠN HÀNG XUẤT
                                    $orders_q = mysqli_query($conn, "SELECT id as OrderId, placed_on, total_products 
                                                                    FROM orders 
                                                                    WHERE payment_status = 'Delivered' AND placed_on BETWEEN '$start_date' AND '$end_date'");
                                    while($row = mysqli_fetch_assoc($orders_q)) {
                                        $parsed = parseOrderProducts($row['total_products']);
                                        foreach($parsed as $name => $qty) {
                                            if(!isset($export_data[$name])) {
                                                $export_data[$name] = ['total' => 0, 'details' => []];
                                            }
                                            $export_data[$name]['total'] += $qty;
                                            
                                            $link_order = "admin_orders.php";
                                            $export_data[$name]['details'][] = "<a href='$link_order' style='color: #e63946; text-decoration: none; font-weight: 500;'>Order #{$row['OrderId']} ({$row['placed_on']}): -{$qty}</a>";
                                        }
                                    }

                                // 3. HIỂN THỊ GỘP & CHI TIẾT
                                $all_product_names = array_unique(array_merge(array_keys($import_data), array_keys($export_data)));
                                if (count($all_product_names) > 0) {
                                    foreach ($all_product_names as $p_name) {
                                        $in_total = isset($import_data[$p_name]) ? $import_data[$p_name]['total'] : 0;
                                        $out_total = isset($export_data[$p_name]) ? $export_data[$p_name]['total'] : 0;
                            ?>
                                <tr>
                                    <td><strong><?php echo $p_name; ?></strong></td>
                                    <td>
                                        <details>
                                            <summary style="cursor:pointer; color: green; font-weight: bold; outline:none;">
                                                +<?php echo $in_total; ?> <i class="fa fa-caret-down" style="font-size: 12px;"></i>
                                            </summary>
                                            <ul style="list-style:none; padding-left:10px; margin-top:5px; font-size: 13px;">
                                                <?php 
                                                if($in_total > 0) {
                                                    foreach($import_data[$p_name]['details'] as $detail) {
                                                        echo "<li class='detail-item' style='margin-bottom: 5px;'>$detail</li>"; 
                                                    }
                                                } else {
                                                    echo "<li style='color: #999;'>No imports</li>";
                                                }
                                                ?>
                                            </ul>
                                        </details>
                                    </td>
                                    <td>
                                        <details>
                                            <summary style="cursor:pointer; color: red; font-weight: bold; outline:none;">
                                                -<?php echo $out_total; ?> <i class="fa fa-caret-down" style="font-size: 12px;"></i>
                                            </summary>
                                            <ul style="list-style:none; padding-left:10px; margin-top:5px; font-size: 13px; color: #555;">
                                                <?php 
                                                if($out_total > 0) {
                                                    foreach($export_data[$p_name]['details'] as $detail) echo "<li>$detail</li>"; 
                                                } else {
                                                    echo "<li>No sales</li>";
                                                }
                                                ?>
                                            </ul>
                                        </details>
                                    </td>
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
                    
                    <form method="GET" class="report-form">
                        <div>
                            <label class="form-label">Select Date in the Past:</label>
                            <input type="date" name="target_date" value="<?php echo $target_date; ?>" max="<?php echo date('Y-m-d'); ?>" class="form-control" style="width: 200px;" required>
                        </div>
                        <button type="submit" class="option-btn" style="margin:0; padding: 10px 20px;">Lookup Stock</button>
                        
                        <a href="admin_stats.php?reset=1" class="delete-btn" style="text-decoration:none; padding: 10px 15px; margin-left: 5px;"><i class="fa fa-refresh"></i> Reset All</a>
                    </form>

                    <?php if ($target_date != '') { ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <td>Product Name</td>
                                <td>Stock on <?php echo date('d-M-Y', strtotime($target_date)); ?></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sold_after = [];
                            $orders_q = mysqli_query($conn, "SELECT total_products FROM orders WHERE payment_status = 'Delivered' AND placed_on > '$target_date'");
                            while($row = mysqli_fetch_assoc($orders_q)) {
                                $parsed = parseOrderProducts($row['total_products']);
                                foreach($parsed as $name => $qty) {
                                    if(!isset($sold_after[$name])) $sold_after[$name] = 0;
                                    $sold_after[$name] += $qty;
                                }
                            }

                            $imported_after = [];
                            $imports_q = mysqli_query($conn, "SELECT p.Name, SUM(d.Quantity) as total_in FROM import_details d JOIN imports i ON d.ImportId = i.Id JOIN products p ON d.ProductId = p.Id WHERE i.Status = 1 AND DATE(i.ImportDate) > '$target_date' GROUP BY d.ProductId");
                            while($row = mysqli_fetch_assoc($imports_q)) {
                                $imported_after[$row['Name']] = $row['total_in'];
                            }

                            $all_products = mysqli_query($conn, "SELECT Name, Quantity FROM products");
                            while($row = mysqli_fetch_assoc($all_products)) {
                                $p_name = $row['Name'];
                                // Vẫn phải giữ biến $current_qty ở ngầm bên dưới để làm mốc tính toán lùi về quá khứ
                                $current_qty = (int)$row['Quantity']; 
                                
                                $s_after = isset($sold_after[$p_name]) ? $sold_after[$p_name] : 0;
                                $i_after = isset($imported_after[$p_name]) ? $imported_after[$p_name] : 0;
                                
                                $past_qty = $current_qty + $s_after - $i_after;
                            ?>
                            <tr>
                                <td><strong><?php echo $p_name; ?></strong></td>
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
        document.addEventListener("DOMContentLoaded", function(event) { 
            let scrollpos = sessionStorage.getItem('scrollpos');
            if (scrollpos) {
                window.scrollTo(0, scrollpos);
                sessionStorage.removeItem('scrollpos');
            }
        });

        window.addEventListener("submit", function() {
            sessionStorage.setItem('scrollpos', window.scrollY);
        });
    </script>
</body>
</html>