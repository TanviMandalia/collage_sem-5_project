<?php
include '../include/connection.php'; // MySQLi connection
include 'inc/header.php'; // AdminLTE header + sidebar

// Fetch returned or refunded orders
$query = $conn->query("SELECT * FROM orders WHERE status IN ('Returned', 'Refunded') ORDER BY order_id DESC");
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1><i class="fas fa-undo-alt"></i> Return & Refund Orders</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header bg-secondary text-white">
          <h3 class="card-title"><i class="fas fa-exchange-alt"></i> Returned / Refunded Orders</h3>
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
              </tr>
            </thead>
            <tbody>
              <?php if ($query && $query->num_rows > 0): ?>
                <?php while ($order = $query->fetch_assoc()): ?>
                  <tr>
                    <td><?= $order['order_id'] ?></td>
                    <td><?= $order['user_id'] ?></td>
                    <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                    <td>
                      <span class="badge <?= $order['status'] === 'Returned' ? 'bg-warning' : 'bg-info' ?>">
                        <?= $order['status'] ?>
                      </span>
                    </td>
                    <td><?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></td>
                    <td><input type="checkbox" <?= $order['received'] ? 'checked' : '' ?> disabled></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="6" class="text-center">No returned or refunded orders found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include 'inc/footer.php'; ?>
