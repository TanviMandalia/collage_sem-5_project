<?php
include '../include/connection.php';
include 'inc/header.php';

$sql = "SELECT * FROM users ORDER BY r_id DESC";
$result = $conn->query($sql);
?>

<div class="content-wrapper">
  <section class="content-header"><h1>User Management</h1></section>
  <section class="content">
    <div class="card">
      <div class="card-header"><h3 class="card-title">All Registered Users</h3></div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
          <thead>
            <tr>
              <th>ID</th><th>First</th><th>Middle</th><th>Last</th><th>Email</th><th>Status</th><th>Orders</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $row['r_id'] ?></td>
                <td><?= htmlspecialchars($row['r_fname']) ?></td>
                <td><?= htmlspecialchars($row['r_mname']) ?></td>
                <td><?= htmlspecialchars($row['r_lname']) ?></td>
                <td><?= htmlspecialchars($row['r_email']) ?></td>
                <td>
                  <?php if ($row['r_status'] === 'blocked'): ?>
                    <span class="badge bg-danger">Blocked</span>
                  <?php elseif ($row['r_status'] === 'unblocked'): ?>
                    <span class="badge bg-success">Unblocked</span>
                  <?php else: ?>
                    <span class="badge bg-secondary"><?= htmlspecialchars($row['r_status']) ?></span>
                  <?php endif; ?>
                </td>
                <td><a href="order_history.php?user_id=<?= $row['r_id'] ?>" class="btn btn-info btn-sm">View</a></td>
                <td>
                  <a href="toggle_user.php?id=<?= $row['r_id'] ?>" class="btn btn-<?= $row['r_status'] === 'unblocked' ? 'danger' : 'success' ?> btn-sm">
                    <?= $row['r_status'] === 'unblocked' ? 'Block' : 'Unblock' ?>
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</div>

<?php include 'inc/footer.php'; ?>
