<?php
session_start();
require_once __DIR__ . '/include/connection.php';

if (empty($_SESSION['r_id'])) {
  $ret = $_SERVER['REQUEST_URI'] ?? 'order_success.php';
  header('Location: registation.php?redirect=' . urlencode($ret));
  exit();
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$order = null;
$total = 0.0;
$status = '';
$date = '';

if ($order_id > 0) {
  $q = mysqli_query($conn, "SELECT * FROM orders WHERE order_id = $order_id AND user_id = " . (int)$_SESSION['r_id']);
  if ($q && mysqli_num_rows($q) > 0) {
    $order = mysqli_fetch_assoc($q);
    $total = isset($order['total_amount']) ? (float)$order['total_amount'] : (isset($order['order_total']) ? (float)$order['order_total'] : 0.0);
    $status = isset($order['status']) ? $order['status'] : '';
    $date = !empty($order['order_date']) ? date('d M Y, h:i A', strtotime($order['order_date'])) : '';
  }
}

include __DIR__ . '/include/header.php';
?>
<div class="container py-5">
  <div class="card mx-auto" style="max-width: 640px;">
    <div class="card-body text-center">
      <div class="display-4 mb-3">✅</div>
      <h3 class="mb-2">Thank you! Your order has been placed.</h3>
      <?php if ($order): ?>
        <p class="text-muted mb-1">Order ID: <strong>#<?php echo (int)$order_id; ?></strong></p>
        <p class="text-muted mb-1">Total: <strong>₹<?php echo number_format($total, 2); ?></strong></p>
        <?php if ($status !== ''): ?><p class="text-muted mb-1">Status: <strong><?php echo htmlspecialchars($status); ?></strong></p><?php endif; ?>
        <?php if ($date !== ''): ?><p class="text-muted mb-3">Date: <?php echo htmlspecialchars($date); ?></p><?php endif; ?>
      <?php else: ?>
        <p class="text-muted">Order placed successfully.</p>
      <?php endif; ?>
      <div class="mt-3 d-flex justify-content-center" style="gap:12px;">
        <a href="my_orders.php" class="btn btn-primary">View My Orders</a>
        <a href="products.php" class="btn btn-outline-secondary">Continue Shopping</a>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/include/footer.php'; ?>
