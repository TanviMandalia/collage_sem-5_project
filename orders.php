<?php
include '../include/connection.php'; // MySQLi connection
include 'inc/header.php'; // AdminLTE header + sidebar

// Fetch all orders
$orderQuery = $conn->query("SELECT * FROM orders ORDER BY order_id DESC");
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1><i class="fas fa-shopping-cart"></i> Manage Orders</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h3 class="card-title"><i class="fas fa-box"></i> All Orders</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover text-nowrap">
            <thead>
              <tr>
                <th>#</th>
                <th>User ID</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Order Date</th>
                <th>Received</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($orderQuery && $orderQuery->num_rows > 0): ?>
                <?php while ($order = $orderQuery->fetch_assoc()): ?>
                  <tr>
                    <td><?= $order['order_id'] ?></td>
                    <td><?= $order['user_id'] ?></td>
                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                    <td>
                      <span class="badge 
                        <?= $order['status'] == 'Pending' ? 'bg-warning' : 
                            ($order['status'] == 'Shipped' ? 'bg-success' : 
                            ($order['status'] == 'Cancelled' ? 'bg-danger' : 'bg-info')) ?>">
                        <?= $order['status'] ?>
                      </span>
                    </td>
                    <td><?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></td>
                    <td><input type="checkbox" <?= $order['received'] ? 'checked' : '' ?> disabled></td>
                    <td>
                      <a href="user_orders.php?id=<?= $order['user_id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                      <a href="update_order.php?id=<?= $order['order_id'] ?>&action=ship" class="btn btn-sm btn-success">Ship</a>
                      <a href="update_order.php?id=<?= $order['order_id'] ?>&action=cancel" class="btn btn-sm btn-danger">Cancel</a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="7" class="text-center">No orders found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include 'inc/footer.php'; ?>
