<?php
include '../include/connection.php';
include 'inc/header.php';

$query = $conn->query("SELECT * FROM orders WHERE status = 'Cancelled' ORDER BY order_id DESC");
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid"><h1><i class="fas fa-ban"></i> Cancelled Orders</h1></div>
  </section>
  <section class="content">
    <div class="container-fluid">
      <div class="card"><div class="card-header bg-danger text-white"><h3 class="card-title">Cancelled Orders</h3></div>
        <div class="card-body table-responsive p-0">
          <table class="table table-hover text-nowrap">
            <thead><tr><th>#</th><th>User ID</th><th>Total</th><th>Date</th><th>Received</th></tr></thead>
            <tbody>
              <?php if ($query && $query->num_rows > 0): while ($row = $query->fetch_assoc()): ?>
                <tr>
                  <td><?= $row['order_id'] ?></td>
                  <td><?= $row['user_id'] ?></td>
                  <td>₹<?= $row['total_amount'] ?></td>
                  <td><?= $row['order_date'] ?></td>
                  <td><input type="checkbox" <?= $row['received'] ? 'checked' : '' ?> disabled></td>
                </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="5" class="text-center">No cancelled orders found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include 'inc/footer.php'; ?>
