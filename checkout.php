<?php
session_start();
require_once __DIR__ . '/include/connection.php';

if (empty($_SESSION['r_id'])) {
  $ret = $_SERVER['REQUEST_URI'] ?? 'checkout.php';
  header('Location: registation.php?redirect=' . urlencode($ret));
  exit();
}

$user_id = (int)$_SESSION['r_id'];

// Fetch cart items directly from denormalized cart table
$items = [];
$total = 0.0;

$q = mysqli_query($conn, "SELECT cart_id, product_name, product_price, product_image, quantity, total_price FROM cart WHERE user_id = $user_id");
if ($q) {
  while ($r = mysqli_fetch_assoc($q)) {
    $price = (float)$r['product_price'];
    $qty   = (int)$r['quantity'];
    $sub   = isset($r['total_price']) && $r['total_price'] !== null ? (float)$r['total_price'] : ($price * $qty);
    $items[] = [
      'cart_id' => (int)$r['cart_id'],
      'name'    => $r['product_name'],
      'price'   => $price,
      'image'   => $r['product_image'],
      'qty'     => $qty,
      'subtotal'=> $sub,
    ];
    $total += $sub;
  }
}

include __DIR__ . '/include/header.php';
?>

<div class="container py-4">
  <h2 class="mb-3">Checkout</h2>

  <?php if (empty($items)): ?>
    <div class="alert alert-warning">Your cart is empty. <a href="products.php">Continue shopping</a>.</div>
  <?php else: ?>
  <div class="row">
    <div class="col-md-7">
      <div class="card mb-3">
        <div class="card-header">Shipping Details</div>
        <div class="card-body">
          <form method="post" action="place_order.php">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" required>
              </div>
              <div class="form-group col-md-6">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Address</label>
              <input type="text" name="address" class="form-control" required>
            </div>
            <div class="form-row">
              <div class="form-group col-md-4">
                <label>City</label>
                <input type="text" name="city" class="form-control" required>
              </div>
              <div class="form-group col-md-4">
                <label>State</label>
                <input type="text" name="state" class="form-control" required>
              </div>
              <div class="form-group col-md-4">
                <label>Pincode</label>
                <input type="text" name="pincode" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Notes (optional)</label>
              <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Place Order (COD)</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-5">
      <div class="card">
        <div class="card-header">Order Summary</div>
        <div class="card-body">
          <?php foreach ($items as $it): ?>
            <div class="d-flex align-items-center mb-2">
              <div class="flex-grow-1">
                <div><strong><?php echo htmlspecialchars($it['name']); ?></strong></div>
                <div class="text-muted small">Qty: <?php echo (int)$it['qty']; ?> × ₹<?php echo number_format($it['price'],2); ?></div>
              </div>
              <div>₹<?php echo number_format($it['subtotal'], 2); ?></div>
            </div>
            <hr>
          <?php endforeach; ?>
          <div class="d-flex justify-content-between"><strong>Total</strong><strong>₹<?php echo number_format($total,2); ?></strong></div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>
