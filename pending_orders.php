<?php
include '../include/connection.php';
include 'inc/header.php';

$query = $conn->query("SELECT * FROM orders WHERE status = 'Pending' ORDER BY order_id DESC");
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid"><h1><i class="fas fa-clock"></i> Pending Orders</h1></div>
  </section>
  <section class="content">
    <div class="container-fluid">
      <div class="card"><div class="card-header bg-warning text-dark"><h3 class="card-title">Pending Orders</h3></div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover text-nowrap">
            <thead><tr><th>#</th><th>User ID</th><th>Total</th><th>Date</th><th>Received</th><th>Actions</th></tr></thead>
            <tbody>
              <?php if ($query && $query->num_rows > 0): while ($row = $query->fetch_assoc()): ?>
                <tr>
                  <td><?= $row['order_id'] ?></td>
                  <td><?= $row['user_id'] ?></td>
                  <td>₹<?= $row['total_amount'] ?></td>
                  <td><?= $row['order_date'] ?></td>
                  <td><input type="checkbox" <?= $row['received'] ? 'checked' : '' ?> disabled></td>
                  <td><a href="update_order.php?id=<?= $row['order_id'] ?>&action=approve" class="btn btn-sm btn-success">Approve</a></td>
                </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="6" class="text-center">No pending orders found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include 'inc/footer.php'; ?>
