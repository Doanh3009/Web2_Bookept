<?php
include 'config.php';
session_start();
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:login_admin.php');
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
   <title>Order Management</title>
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
                  <a href="admin_main.php" class="sidebar-link"><div class="sidebar-icon"><i class="fa fa-home"></i></div><div class="hidden-sidebar">Overview</div></a>
               </li>
               <li class="sidebar-list-item tab-content">
                  <a href="admin_products.php" class="sidebar-link"><div class="sidebar-icon"><i class="fa fa-book"></i></div><div class="hidden-sidebar">Products</div></a>
               </li>
               <li class="sidebar-list-item tab-content">
                  <a href="admin_imports.php" class="sidebar-link"><div class="sidebar-icon"><i class="fa fa-truck"></i></div><div class="hidden-sidebar">Imports</div></a>
               </li>
               <li class="sidebar-list-item tab-content">
                  <a href="admin_users.php" class="sidebar-link"><div class="sidebar-icon"><i class="fa fa-group"></i></div><div class="hidden-sidebar">Customer</div></a>
               </li>
               <li class="sidebar-list-item tab-content active">
                  <a href="admin_orders.php" class="sidebar-link"><div class="sidebar-icon"><i class="fa fa-shopping-cart"></i></div><div class="hidden-sidebar">Order</div></a>
               </li>
               <li class="sidebar-list-item tab-content">
                  <a href="admin_stats.php" class="sidebar-link"><div class="sidebar-icon"><i class="fa fa-bar-chart"></i></div><div class="hidden-sidebar">Statistical</div></a>
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
            <h1 class="page-title">Manage Orders</h1>
            
            <div class="admin-control" style="background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 20px;">
               <form method="GET" action="admin_orders.php" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; width: 100%;">
                  

                  <div style="flex: 1; min-width: 150px;">
                     <label class="form-label">Ward</label>
                     <select name="ward" class="form-control">
                        <option value="">All Wards</option>
                        <?php for ($i = 1; $i <= 15; $i++) {
                           $selected = (isset($_GET['ward']) && $_GET['ward'] == "Ward $i") ? 'selected' : '';
                           echo "<option value='Ward $i' $selected>Ward $i</option>";
                        } ?>
                     </select>
                  </div>

                  <div style="flex: 1; min-width: 150px;">
                     <label class="form-label">Status</label>
                     <select name="payment_status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?php if(isset($_GET['payment_status']) && $_GET['payment_status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                        <option value="Confirmed" <?php if(isset($_GET['payment_status']) && $_GET['payment_status'] == 'Confirmed') echo 'selected'; ?>>Confirmed</option>
                        <option value="Delivered" <?php if(isset($_GET['payment_status']) && $_GET['payment_status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                        <option value="Cancelled" <?php if(isset($_GET['payment_status']) && $_GET['payment_status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                     </select>
                  </div>

                  <div style="flex: 1; min-width: 150px;">
                     <label class="form-label">From Date:</label>
                     <input type="date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>" class="form-control">
                  </div>

                  <div style="flex: 1; min-width: 150px;">
                     <label class="form-label">To Date:</label>
                     <input type="date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>" class="form-control">
                  </div>

                  <div>
                     <button class="option-btn" type="submit" style="margin: 0; padding: 10px 25px;"><i class="fa fa-filter"></i> Filter</button>
                     <a href="admin_orders.php" class="delete-btn" style="text-decoration: none; padding: 10px 15px; display: inline-block; margin-left: 5px;"><i class="fa fa-refresh"></i></a>
                  </div>
               </form>
            </div>

            <div class="table">
               <table width="100%">
                  <thead>
                     <tr>
                        <td>Order ID</td>
                        <td>Customer</td>
                        <td>Order Date</td>
                        <td>Total Price</td>
                        <td>Status</td>
                        <td>Action</td>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     // --- DYNAMIC SQL LOGIC (Saves 300 lines of code) ---
                     $query = "SELECT * FROM orders WHERE 1=1"; 
                     
                     if (!empty($_GET['ward'])) {
                         $ward = mysqli_real_escape_string($conn, $_GET['ward']);
                         $query .= " AND address LIKE '%$ward%'";
                     }
                     // Filter by Status
                     if (!empty($_GET['payment_status'])) {
                         $status = mysqli_real_escape_string($conn, $_GET['payment_status']);
                         $query .= " AND payment_status = '$status'";
                     }
                     // Filter by Date
                     if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
                         $start = mysqli_real_escape_string($conn, $_GET['start_date']);
                         $end = mysqli_real_escape_string($conn, $_GET['end_date']);
                         $query .= " AND placed_on BETWEEN '$start' AND '$end'";
                     }

                     // Order by Address (to group wards together as requested) and then by Date
                     $query .= " ORDER BY address ASC, placed_on DESC";

                     $select_orders = mysqli_query($conn, $query) or die('Query failed');
                     
                     if (mysqli_num_rows($select_orders) > 0) {
                        while ($fetch_orders = mysqli_fetch_assoc($select_orders)) {
                           // Set color based on status
                           $status_color = 'black';
                           if($fetch_orders['payment_status'] == 'Pending') $status_color = 'orange';
                           if($fetch_orders['payment_status'] == 'Confirmed') $status_color = 'blue';
                           if($fetch_orders['payment_status'] == 'Delivered') $status_color = 'green';
                           if($fetch_orders['payment_status'] == 'Cancelled') $status_color = 'red';
                     ?>
                           <tr>
                              <td><strong>#<?php echo $fetch_orders['id']; ?></strong></td>
                              <td><?php echo $fetch_orders['name']; ?></td>
                              <td><?php echo $fetch_orders['placed_on']; ?></td>
                              <td><strong>$<?php echo $fetch_orders['total_price']; ?></strong></td>
                              <td style="color: <?php echo $status_color; ?>; font-weight: bold;"><?php echo $fetch_orders['payment_status']; ?></td>
                              <td class="control">
                                 <a href="admin_orderdetail.php?order_id=<?php echo $fetch_orders['id']; ?>" class="btn-detail" style="text-decoration: none; display: inline-block;">
                                    <i class="fa fa-eye"></i> Details
                                 </a>
                              </td>
                           </tr>
                     <?php
                        }
                     } else {
                        echo '<tr><td colspan="6" style="text-align: center; padding: 20px;">No orders match your filter criteria.</td></tr>';
                     }
                     ?>
                  </tbody>
               </table>
            </div>
         </div>
      </main>
   </div>
   <script src="js/admin.js"></script>
</body>
</html>