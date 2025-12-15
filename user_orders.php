<?php
// Database connection
include '../include/connection.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Run query safely
$orderQuery = $conn->query("SELECT * FROM orders WHERE user_id = $id");
include('inc/header.php');
?>

<div class="content-wrapper">
  <!-- Page Header -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>User Order History</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">User Orders</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <!-- Main Content -->
  <section class="content">
    <div class="container-fluid">

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Orders for User #<?= $id ?></h3>
        </div>
        <div class="card-body">

          <?php if ($orderQuery && $orderQuery->num_rows > 0): ?>
            <table class="table table-bordered table-hover">
              <thead class="thead-dark">
                <tr>
                  <th>Order ID</th>
                  <th>Status</th>
                  <th>Received?</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($order = $orderQuery->fetch_assoc()): ?>
                  <tr>
                    <td><?= $order['order_id'] ?></td>
                    <td>
                      <?php if ($order['status'] === 'Completed'): ?>
                        <span class="badge badge-success">Completed</span>
                      <?php elseif ($order['status'] === 'Pending'): ?>
                        <span class="badge badge-warning">Pending</span>
                      <?php elseif ($order['status'] === 'Cancelled'): ?>
                        <span class="badge badge-danger">Cancelled</span>
                      <?php else: ?>
                        <span class="badge badge-secondary"><?= htmlspecialchars($order['status']); ?></span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <input type="checkbox" <?= $order['received'] == '1' ? 'checked' : '' ?> disabled>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div class="alert alert-warning">No orders found for this user.</div>
          <?php endif; ?>

        </div>
        <div class="card-footer">
          <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include('inc/footer.php'); ?>
