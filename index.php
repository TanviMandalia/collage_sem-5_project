<?php
include '../include/connection.php';

// Category Stats
$totalCategories = $activeCategories = $inactiveCategories = 0;
$q = $conn->query("SELECT COUNT(*) FROM categories");
if ($q && ($row = mysqli_fetch_row($q))) $totalCategories = $row[0];
$q = $conn->query("SELECT COUNT(*) FROM categories WHERE category_status=1");
if ($q && ($row = mysqli_fetch_row($q))) $activeCategories = $row[0];
$q = $conn->query("SELECT COUNT(*) FROM categories WHERE category_status=0");
if ($q && ($row = mysqli_fetch_row($q))) $inactiveCategories = $row[0];

// User Stats
$totalUsers = $blockedUsers = $unblockedUsers = 0;
$q = $conn->query("SELECT COUNT(*) FROM users");
if ($q && ($row = mysqli_fetch_row($q))) $totalUsers = $row[0];
$q = $conn->query("SELECT COUNT(*) FROM users WHERE r_status='blocked'");
if ($q && ($row = mysqli_fetch_row($q))) $blockedUsers = $row[0];
$q = $conn->query("SELECT COUNT(*) FROM users WHERE r_status='unblocked'");
if ($q && ($row = mysqli_fetch_row($q))) $unblockedUsers = $row[0];

// Order Stats
$totalOrders = $pendingOrders = $completedOrders = 0;
$q = $conn->query("SELECT COUNT(*) FROM orders");
if ($q && ($row = mysqli_fetch_row($q))) $totalOrders = $row[0];
$q = $conn->query("SELECT COUNT(*) FROM orders WHERE order_status='pending'");
if ($q && ($row = mysqli_fetch_row($q))) $pendingOrders = $row[0];
$q = $conn->query("SELECT COUNT(*) FROM orders WHERE order_status='completed'");
if ($q && ($row = mysqli_fetch_row($q))) $completedOrders = $row[0];

// Product Stats
$totalProducts = $lowStockProducts = 0;
$q = $conn->query("SELECT COUNT(*) FROM products");
if ($q && ($row = mysqli_fetch_row($q))) $totalProducts = $row[0];
$q = $conn->query("SELECT COUNT(*) FROM products WHERE stock <= 5");
if ($q && ($row = mysqli_fetch_row($q))) $lowStockProducts = $row[0];

// Recent Entries
$recentUsers = null;
$recentOrders = null;
$recentProducts = $conn->query("SELECT * FROM products ORDER BY product_id DESC LIMIT 5");
$recentCategories = $conn->query("SELECT * FROM categories ORDER BY category_id DESC LIMIT 5");

// detect which users table exists and determine id column
$users_table = null;
if ($conn->query("SHOW TABLES LIKE 'register'") && $conn->query("SHOW TABLES LIKE 'register'")->num_rows > 0) {
  $users_table = 'register';
} elseif ($conn->query("SHOW TABLES LIKE 'users'") && $conn->query("SHOW TABLES LIKE 'users'")->num_rows > 0) {
  $users_table = 'users';
}

// helper: get first column name (usually primary id)
function first_column($conn, $table) {
  $res = $conn->query("SHOW COLUMNS FROM `" . $table . "`");
  if ($res && $row = $res->fetch_assoc()) return $row['Field'];
  return null;
}

// helper: pick first existing column from list
function pick_col(array $row, array $names, $default = '') {
  foreach ($names as $n) {
    if (array_key_exists($n, $row) && $row[$n] !== null) return $row[$n];
  }
  return $default;
}

if ($users_table) {
  $id_col = first_column($conn, $users_table) ?: 'id';
  // recent users
  $recentUsers = $conn->query("SELECT * FROM `" . $users_table . "` ORDER BY `" . $id_col . "` DESC LIMIT 5");
  // recent orders with left join to users (if join fails it's still OK)
  $recentOrders = $conn->query("SELECT o.*, u.* FROM orders o LEFT JOIN `" . $users_table . "` u ON o.user_id = u.`" . $id_col . "` ORDER BY o.order_id DESC LIMIT 5");
} else {
  // no users table found — fetch orders without user info
  $recentOrders = $conn->query("SELECT * FROM orders ORDER BY order_id DESC LIMIT 5");
}
?>

<?php include('inc/header.php'); ?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1>Admin Dashboard</h1>
      <small class="text-muted">Overview of users, orders, products, and categories</small>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <!-- Stat Boxes (2 lines, up to 6 per line on large screens) -->
      <?php
        $stats = [
          ['Total Users', $totalUsers, 'bg-primary', 'fas fa-users'],
          ['Blocked Users', $blockedUsers, 'bg-warning', 'fas fa-user-lock'],
          ['Unblocked Users', $unblockedUsers, 'bg-success', 'fas fa-user-check'],
          ['Total Categories', $totalCategories, 'bg-info', 'fas fa-tags'],
          ['Active Categories', $activeCategories, 'bg-success', 'fas fa-check-circle'],
          ['Inactive Categories', $inactiveCategories, 'bg-danger', 'fas fa-times-circle'],
          ['Total Products', $totalProducts, 'bg-secondary', 'fas fa-box'],
          ['Low Stock Products', $lowStockProducts, 'bg-danger', 'fas fa-exclamation-triangle'],
          ['Total Orders', $totalOrders, 'bg-dark', 'fas fa-shopping-cart'],
          ['Pending Orders', $pendingOrders, 'bg-warning', 'fas fa-clock'],
          ['Completed Orders', $completedOrders, 'bg-success', 'fas fa-check'],
        ];

        // We want two rows with up to 6 items each on large screens.
        $perRow = 6;
        $i = 0;
        echo "<div class='row'>";
        foreach ($stats as [$label, $count, $color, $icon]) {
          // Use col-lg-2 so 6 columns on large screens; col-md-4 for 3 per row on medium; col-sm-6 for 2 on small
          echo "<div class='col-lg-2 col-md-4 col-sm-6 mb-4'>
                  <div class='small-box $color'>
                    <div class='inner'>
                      <h3>$count</h3>
                      <p>$label</p>
                    </div>
                    <div class='icon'><i class='$icon'></i></div>
                  </div>
                </div>";
          $i++;
          // If we've reached $perRow items, close the current row and start a new one (keeps layout to two rows total)
          if ($i % $perRow === 0 && $i < count($stats)) {
            echo "</div><div class='row'>";
          }
        }
        echo "</div>";
      ?>

      <!-- Recent Users -->
      <div class="card mt-4">
        <div class="card-header bg-primary text-white"><h3 class="card-title"><i class="fas fa-user-friends"></i> Recent Users</h3></div>
        <div class="card-body p-0 table-responsive">
          <table class="table table-bordered">
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if ($recentUsers && $recentUsers->num_rows > 0): ?>
                <?php while ($user = $recentUsers->fetch_assoc()): ?>
                  <?php
                    $uid = pick_col($user, ['r_id','id','user_id','uid']);
                    $fname = pick_col($user, ['r_fname','first_name','fname','name']);
                    $lname = pick_col($user, ['r_lname','last_name','lname'], '');
                    $email = pick_col($user, ['r_email','email','user_email'], '');
                    $status = pick_col($user, ['r_status','status','user_status'], 'unblocked');
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($uid) ?></td>
                    <td><?= htmlspecialchars(trim($fname . ' ' . $lname)) ?></td>
                    <td><?= htmlspecialchars($email) ?></td>
                    <td><span class="badge <?= $status === 'blocked' ? 'bg-danger' : 'bg-success' ?>"><?= ucfirst($status) ?></span></td>
                    <td>
                      <a href="toggle_user.php?id=<?= urlencode($uid) ?>" class="btn btn-sm btn-<?= $status === 'unblocked' ? 'danger' : 'success' ?>">
                        <?= $status === 'unblocked' ? 'Block' : 'Unblock' ?>
                      </a>
                      <a href="user_orders.php?id=<?= urlencode($uid) ?>" class="btn btn-sm btn-info"><i class="fas fa-box"></i> Orders</a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center">No users found or query failed.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="card mt-4">
        <div class="card-header bg-dark text-white"><h3 class="card-title"><i class="fas fa-shopping-cart"></i> Recent Orders</h3></div>
        <div class="card-body p-0 table-responsive">
          <table class="table table-striped">
            <thead><tr><th>#</th><th>User</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
              <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                <?php while ($order = $recentOrders->fetch_assoc()): ?>
                  <?php
                    // order row may include user columns (from joined user table) or not
                    $user_fname = pick_col($order, ['r_fname','first_name','fname','name']);
                    $user_lname = pick_col($order, ['r_lname','last_name','lname'], '');
                    $user_display = trim($user_fname . ' ' . $user_lname);
                    if ($user_display === '') $user_display = pick_col($order, ['email','r_email','user_email'], 'Guest');
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($order['order_id']) ?></td>
                    <td><?= htmlspecialchars($user_display) ?></td>
                    <td>₹<?= number_format($order['order_total'] ?? 0, 2) ?></td>
                    <td><span class="badge <?= ($order['order_status'] ?? '') === 'completed' ? 'bg-success' : ((($order['order_status'] ?? '') === 'pending') ? 'bg-warning' : 'bg-secondary') ?>"><?= ucfirst($order['order_status'] ?? '') ?></span></td>
                    <td><?= !empty($order['order_date']) ? date('d M Y', strtotime($order['order_date'])) : '' ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center">No orders found or query failed.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Recent Products -->
      <div class="card mt-4">
        <div class="card-header bg-secondary text-white"><h3 class="card-title"><i class="fas fa-box"></i> Recent Products</h3></div>
        <div class="card-body p-0 table-responsive">
          <table class="table table-bordered">
            <thead><tr><th>#</th><th>Name</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if ($recentProducts && $recentProducts->num_rows > 0): ?>
                <?php while ($product = $recentProducts->fetch_assoc()): ?>
                  <tr>
                    <td><?= $product['product_id'] ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td>₹<?= number_format($product['price'], 2) ?></td>
                    <td><span class="badge <?= $product['stock'] <= 5 ? 'bg-danger' : 'bg-success' ?>"><?= $product['stock'] ?></span></td>
                    <td>
                      <a href="edit_product.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
                      <a href="delete_product.php?id=<?= $product['product_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center">No products found or query  failed.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
       <!-- Recent Categories -->
      <div class="card mt-4 mb-5">
        <div class="card-header bg-info text-white"><h3 class="card-title"><i class="fas fa-tags"></i> Recent Categories</h3></div>
        <div class="card-body p-0 table-responsive">
          <table class="table table-striped">
            <thead><tr><th>#</th><th>Name</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if ($recentCategories && $recentCategories->num_rows > 0): ?>
                <?php while ($cat = $recentCategories->fetch_assoc()): ?>
                  <tr>
                    <td><?= $cat['category_id'] ?></td>
                    <td><?= htmlspecialchars($cat['category_name']) ?></td>
                    <td>
                      <span class="badge <?= $cat['category_status'] ? 'bg-success' : 'bg-danger' ?>">
                        <?= $cat['category_status'] ? 'Active' : 'Inactive' ?>
                      </span>
                    </td>
                    <td>
                      <a href="edit_category.php?id=<?= $cat['category_id'] ?>" class="btn btn-sm btn-info">
                        <i class="fas fa-edit"></i>
                      </a>
                      <a href="delete_category.php?id=<?= $cat['category_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?');">
                        <i class="fas fa-trash-alt"></i>
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="4" class="text-center">No categories found or query failed.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div> <!-- /.container-fluid -->
  </section> <!-- /.content -->
</div> <!-- /.content-wrapper -->

<?php include('inc/footer.php'); ?>