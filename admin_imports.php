<?php
include 'config.php';
session_start();
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('Location: login_admin.php');
    exit();
}

// 1. CREATE NEW IMPORT RECEIPT
if (isset($_POST['create_import'])) {
    $note = $_POST['import_note'];
    mysqli_query($conn, "INSERT INTO imports (Status, Note, TotalAmount) VALUES (0, '$note', 0)") or die('Query failed');
    $new_import_id = mysqli_insert_id($conn);
    header("Location: admin_imports.php?action=edit&id=$new_import_id");
    exit();
}

// 2. ADD PRODUCT TO DRAFT RECEIPT
if (isset($_POST['add_to_receipt'])) {
    $import_id = $_POST['import_id'];
    $product_id = $_POST['product_id'];
    $import_qty = (int)$_POST['import_qty'];
    $import_price = (float)$_POST['import_price'];

    if ($import_qty > 0 && $import_price >= 0) {
        // Check if product is already in this receipt
        $check_exist = mysqli_query($conn, "SELECT * FROM import_details WHERE ImportId = '$import_id' AND ProductId = '$product_id'");
        if (mysqli_num_rows($check_exist) > 0) {
            // Update quantity and price if it exists
            mysqli_query($conn, "UPDATE import_details SET Quantity = Quantity + $import_qty, ImportPrice = '$import_price' WHERE ImportId = '$import_id' AND ProductId = '$product_id'");
        } else {
            // Insert new item
            mysqli_query($conn, "INSERT INTO import_details (ImportId, ProductId, Quantity, ImportPrice) VALUES ('$import_id', '$product_id', '$import_qty', '$import_price')");
        }
        
        // Update Total Amount of the receipt
        $calc_total = mysqli_query($conn, "SELECT SUM(Quantity * ImportPrice) AS Total FROM import_details WHERE ImportId = '$import_id'");
        $total_row = mysqli_fetch_assoc($calc_total);
        $new_total = $total_row['Total'];
        mysqli_query($conn, "UPDATE imports SET TotalAmount = '$new_total' WHERE Id = '$import_id'");
    }
    header("Location: admin_imports.php?action=edit&id=$import_id");
    exit();
}

// 3. COMPLETE RECEIPT & UPDATE PRODUCT INVENTORY (THE CORE LOGIC)
if (isset($_POST['complete_import'])) {
    $import_id = $_POST['import_id'];

    // Fetch all items in this receipt
    $details_query = mysqli_query($conn, "SELECT * FROM import_details WHERE ImportId = '$import_id'");
    
    if (mysqli_num_rows($details_query) > 0) {
        while ($detail = mysqli_fetch_assoc($details_query)) {
            $prod_id = $detail['ProductId'];
            $import_qty = (int)$detail['Quantity'];
            $new_import_price = (float)$detail['ImportPrice'];

            // Get current product data
            $prod_query = mysqli_query($conn, "SELECT Quantity, ImportPrice, Price FROM products WHERE Id = '$prod_id'");
            $prod_data = mysqli_fetch_assoc($prod_query);

            $old_qty = (int)$prod_data['Quantity'];
            $old_import_price = (float)$prod_data['ImportPrice'];
            $old_selling_price = (float)$prod_data['Price'];

            // Step A: Calculate current Profit Margin based on old prices
            $profit_margin = 0;
            if ($old_import_price > 0) {
                $profit_margin = ($old_selling_price / $old_import_price) - 1;
            }

            // Step B: Calculate New Average Import Price
            $total_qty = $old_qty + $import_qty;
            $avg_import_price = 0;
            if ($total_qty > 0) {
                $avg_import_price = (($old_qty * $old_import_price) + ($import_qty * $new_import_price)) / $total_qty;
            }

            // Step C: Calculate New Selling Price based on the maintained Profit Margin
            $new_selling_price = $avg_import_price * (1 + $profit_margin);

            // Step D: Update the Products table
            mysqli_query($conn, "UPDATE products SET Quantity = '$total_qty', ImportPrice = '$avg_import_price', Price = '$new_selling_price' WHERE Id = '$prod_id'") or die('Update product failed');
        }

        // Lock the receipt
        mysqli_query($conn, "UPDATE imports SET Status = 1, ImportDate = CURRENT_TIMESTAMP WHERE Id = '$import_id'");
        echo "<script>alert('Receipt completed successfully! Inventory and prices have been updated.'); window.location.href='admin_imports.php';</script>";
    } else {
        echo "<script>alert('Cannot complete an empty receipt!'); window.location.href='admin_imports.php?action=edit&id=$import_id';</script>";
    }
    exit();
}

// 4. DELETE RECEIPT (Only if draft)
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM import_details WHERE ImportId = '$delete_id'");
    mysqli_query($conn, "DELETE FROM imports WHERE Id = '$delete_id'");
    header("Location: admin_imports.php");
    exit();
}

// 5. REMOVE ITEM FROM DRAFT
if (isset($_GET['remove_item']) && isset($_GET['import_id'])) {
    $remove_prod_id = $_GET['remove_item'];
    $import_id = $_GET['import_id'];
    mysqli_query($conn, "DELETE FROM import_details WHERE ImportId = '$import_id' AND ProductId = '$remove_prod_id'");
    
    // Recalculate total
    $calc_total = mysqli_query($conn, "SELECT SUM(Quantity * ImportPrice) AS Total FROM import_details WHERE ImportId = '$import_id'");
    $total_row = mysqli_fetch_assoc($calc_total);
    $new_total = $total_row['Total'] ? $total_row['Total'] : 0;
    mysqli_query($conn, "UPDATE imports SET TotalAmount = '$new_total' WHERE Id = '$import_id'");
    
    header("Location: admin_imports.php?action=edit&id=$import_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Imports</title>
    <link href='image/Logo.png' rel='icon' type='image/x-icon' />
    <link rel="stylesheet" href="styles/admin/admin.css">
    <link rel="stylesheet" href="styles/admin/admin-reponsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
                    <li class="sidebar-list-item tab-content">
                        <a href="admin_main.php" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa fa-home"></i></div>
                            <div class="hidden-sidebar">Overview</div>
                        </a>
                    </li>
                    <li class="sidebar-list-item tab-content">
                        <a href="admin_products.php" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa fa-book"></i></div>
                            <div class="hidden-sidebar">Products</div>
                        </a>
                    </li>
                    <li class="sidebar-list-item tab-content active">
                        <a href="admin_imports.php" class="sidebar-link">
                            <div class="sidebar-icon"><i class="fa fa-truck"></i></div>
                            <div class="hidden-sidebar">Imports</div>
                        </a>
                    </li>
                    <li class="sidebar-list-item tab-content">
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
            <div class="section active">
                
                <?php 
                // VIEW: EDIT/CREATE SPECIFIC RECEIPT
                if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) { 
                    $import_id = $_GET['id'];
                    $import_query = mysqli_query($conn, "SELECT * FROM imports WHERE Id = '$import_id'");
                    $import_data = mysqli_fetch_assoc($import_query);
                    $is_completed = ($import_data['Status'] == 1);
                ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h1 class="page-title" style="margin-bottom: 0;">Receipt Details #<?php echo $import_id; ?> <?php echo $is_completed ? '<span style="color: green;">(Completed)</span>' : '<span style="color: red;">(Draft)</span>'; ?></h1>
                        <a href="admin_imports.php" class="option-btn" style="text-decoration: none; padding: 10px 20px;">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        
                        <?php if (!$is_completed) { ?>
                        <div class="box" style="flex: 1; min-width: 300px; height: fit-content;">
                            <h3 style="margin-bottom: 15px;">Search Product</h3>
                            <form method="GET" action="admin_imports.php">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?php echo $import_id; ?>">
                                <div style="display: flex; gap: 10px;">
                                    <input type="text" name="search" placeholder="Enter product name..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" class="form-control" required>
                                    <button type="submit" class="option-btn" style="padding: 0 15px; width: auto;"><i class="fa fa-search"></i></button>
                                </div>
                            </form>

                            <?php 
                            if (isset($_GET['search'])) {
                                $search = $_GET['search'];
                                $search_query = mysqli_query($conn, "SELECT * FROM products WHERE Name LIKE '%$search%' LIMIT 5");
                                if (mysqli_num_rows($search_query) > 0) {
                                    echo '<ul style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;">';
                                    while ($sp = mysqli_fetch_assoc($search_query)) {
                            ?>
                                        <li style="padding: 10px 0; border-bottom: 1px solid #eee;">
                                            <form method="POST" action="admin_imports.php" style="display: flex; flex-direction: column; gap: 10px;">
                                                <strong><?php echo $sp['Name']; ?></strong> (Stock: <?php echo $sp['Quantity']; ?>)
                                                <input type="hidden" name="import_id" value="<?php echo $import_id; ?>">
                                                <input type="hidden" name="product_id" value="<?php echo $sp['Id']; ?>">
                                                
                                                <div style="display: flex; gap: 10px;">
                                                    <input type="number" name="import_qty" placeholder="Qty" min="1" required class="form-control" style="width: 80px; padding: 5px;">
                                                    <input type="number" name="import_price" placeholder="Import Price ($)" min="0" step="any" required class="form-control" style="width: 120px; padding: 5px;">
                                                    <button type="submit" name="add_to_receipt" class="option-btn" style="padding: 5px 10px; width: auto; font-size: 14px;"><i class="fa fa-plus"></i> Add</button>
                                                </div>
                                            </form>
                                        </li>
                            <?php
                                    }
                                    echo '</ul>';
                                } else {
                                    echo '<p style="margin-top: 15px; color: red;">No products found.</p>';
                                }
                            }
                            ?>
                        </div>
                        <?php } ?>

                        <div class="box" style="flex: 2; min-width: 400px;">
                            <h3 style="margin-bottom: 15px;">Receipt Items</h3>
                            <table class="table" style="width: 100%; border-radius: 10px;">
                                <thead>
                                    <tr>
                                        <td>Product Name</td>
                                        <td>Qty</td>
                                        <td>Price</td>
                                        <td>Total</td>
                                        <?php if (!$is_completed) echo "<td>Action</td>"; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $details = mysqli_query($conn, "SELECT d.*, p.Name FROM import_details d JOIN products p ON d.ProductId = p.Id WHERE d.ImportId = '$import_id'");
                                    if (mysqli_num_rows($details) > 0) {
                                        while ($row = mysqli_fetch_assoc($details)) {
                                            $line_total = $row['Quantity'] * $row['ImportPrice'];
                                    ?>
                                        <tr>
                                            <td><?php echo $row['Name']; ?></td>
                                            <td><?php echo $row['Quantity']; ?></td>
                                            <td>$<?php echo $row['ImportPrice']; ?></td>
                                            <td>$<?php echo number_format($line_total, 2); ?></td>
                                            <?php if (!$is_completed) { ?>
                                            <td>
                                                <a href="admin_imports.php?remove_item=<?php echo $row['ProductId']; ?>&import_id=<?php echo $import_id; ?>" style="color: red;" onclick="return confirm('Remove this item?');"><i class="fa fa-trash"></i></a>
                                            </td>
                                            <?php } ?>
                                        </tr>
                                    <?php 
                                        } 
                                    } else {
                                        echo "<tr><td colspan='5' style='text-align: center;'>No items added yet.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <h3 style="text-align: right; margin-top: 20px;">Total Amount: $<?php echo number_format($import_data['TotalAmount'], 2); ?></h3>
                            
                            <?php if (!$is_completed) { ?>
                            <div style="display: flex; gap: 15px; margin-top: 20px; justify-content: flex-end;">
                                <a href="admin_imports.php" class="delete-btn" style="text-decoration: none;">Save Draft</a>
                                <form method="POST" action="admin_imports.php">
                                    <input type="hidden" name="import_id" value="<?php echo $import_id; ?>">
                                    <button type="submit" name="complete_import" class="option-btn" style="background-color: #27ae60;" onclick="return confirm('Are you sure? This will update the inventory and cannot be undone.');">Complete Receipt</button>
                                </form>
                            </div>
                            <?php } else { ?>
                                <a href="admin_imports.php" class="option-btn" style="display: block; width: fit-content; margin-left: auto; margin-top: 20px; text-decoration: none;">Back to List</a>
                            <?php } ?>
                        </div>
                    </div>

                <?php 
                } else { 
                    // VIEW: LIST ALL RECEIPTS
                ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h1 class="page-title">Goods Receipts List</h1>
                        <form method="POST" action="admin_imports.php">
                            <input type="text" name="import_note" placeholder="Note (Optional)" class="form-control" style="width: 200px; display: inline-block;">
                            <button type="submit" name="create_import" class="option-btn" style="padding: 10px 20px;"><i class="fa fa-plus"></i> Create New Receipt</button>
                        </form>
                    </div>

                    <div class="admin-control" style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px;">
                        <form method="GET" action="admin_imports.php" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; width: 100%;">
                            
                            <div style="flex: 1; min-width: 150px;">
                                <label class="form-label">Receipt ID</label>
                                <input type="number" name="search_id" placeholder="e.g. 5" value="<?php echo isset($_GET['search_id']) ? $_GET['search_id'] : ''; ?>" class="form-control" min="1">
                            </div>

                            <div style="flex: 1; min-width: 150px;">
                                <label class="form-label">Import Date</label>
                                <input type="date" name="search_date" value="<?php echo isset($_GET['search_date']) ? $_GET['search_date'] : ''; ?>" class="form-control">
                            </div>

                            <div style="flex: 1; min-width: 150px;">
                                <label class="form-label">Status</label>
                                <select name="search_status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="1" <?php if(isset($_GET['search_status']) && $_GET['search_status'] === '1') echo 'selected'; ?>>Completed</option>
                                    <option value="0" <?php if(isset($_GET['search_status']) && $_GET['search_status'] === '0') echo 'selected'; ?>>Draft</option>
                                </select>
                            </div>

                            <div>
                                <button class="option-btn" type="submit" style="margin: 0; padding: 10px 25px;"><i class="fa fa-search"></i> Search</button>
                                <a href="admin_imports.php" class="delete-btn" style="text-decoration: none; padding: 10px 15px; display: inline-block; margin-left: 5px;"><i class="fa fa-refresh"></i></a>
                            </div>
                        </form>
                    </div>

                    <div class="box" style="padding: 0;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td>Receipt ID</td>
                                    <td>Date</td>
                                    <td>Total Amount</td>
                                    <td>Note</td>
                                    <td>Status</td>
                                    <td>Actions</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // LOGIC TÌM KIẾM ĐỘNG (DYNAMIC SQL)
                                $query = "SELECT * FROM imports WHERE 1=1"; 
                                
                                // Lọc theo Receipt ID
                                if (!empty($_GET['search_id'])) {
                                    $s_id = mysqli_real_escape_string($conn, $_GET['search_id']);
                                    $query .= " AND Id = '$s_id'";
                                }
                                
                                // Lọc theo Ngày (Date)
                                if (!empty($_GET['search_date'])) {
                                    $s_date = mysqli_real_escape_string($conn, $_GET['search_date']);
                                    $query .= " AND DATE(ImportDate) = '$s_date'";
                                }
                                
                                // Lọc theo Trạng thái (Status)
                                if (isset($_GET['search_status']) && $_GET['search_status'] !== '') {
                                    $s_status = mysqli_real_escape_string($conn, $_GET['search_status']);
                                    $query .= " AND Status = '$s_status'";
                                }

                                // Sắp xếp giảm dần theo ID mới nhất
                                $query .= " ORDER BY Id DESC";

                                $select_imports = mysqli_query($conn, $query) or die('Query failed');
                                
                                if (mysqli_num_rows($select_imports) > 0) {
                                    while ($fetch_import = mysqli_fetch_assoc($select_imports)) {
                                ?>
                                    <tr>
                                        <td><strong>#<?php echo $fetch_import['Id']; ?></strong></td>
                                        <td><?php echo date('d-M-Y H:i', strtotime($fetch_import['ImportDate'])); ?></td>
                                        <td>$<?php echo number_format($fetch_import['TotalAmount'], 2); ?></td>
                                        <td><?php echo $fetch_import['Note']; ?></td>
                                        <td>
                                            <?php if ($fetch_import['Status'] == 1) { ?>
                                                <span style="color: green; font-weight: bold;"><i class="fa fa-check-circle"></i> Completed</span>
                                            <?php } else { ?>
                                                <span style="color: red; font-weight: bold;"><i class="fa fa-pencil-square-o"></i> Draft</span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <a href="admin_imports.php?action=edit&id=<?php echo $fetch_import['Id']; ?>" class="option-btn" style="padding: 5px 15px; font-size: 14px; text-decoration: none;">
                                                <i class="fa fa-eye"></i> View/Edit
                                            </a>
                                            <?php if ($fetch_import['Status'] == 0) { ?>
                                                <a href="admin_imports.php?delete=<?php echo $fetch_import['Id']; ?>" class="delete-btn" style="padding: 5px 15px; font-size: 14px; text-decoration: none;" onclick="return confirm('Delete this draft?');">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="6" style="text-align: center; padding: 20px;">No receipts found matching your criteria.</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>

            </div>
        </main>
    </div>
    <script src="js/admin.js"></script>
</body>
</html>