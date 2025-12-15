00263
*<?php
session_start();
require_once __DIR__ . '/include/connection.php';

if (empty($_SESSION['r_id'])) {
  $ret = $_SERVER['REQUEST_URI'] ?? 'checkout.php';
  header('Location: registation.php?redirect=' . urlencode($ret));
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: checkout.php');
  exit();
}

$user_id = (int)$_SESSION['r_id'];

// Read shipping fields (optional, only saved if columns exist)
$full_name = trim($_POST['full_name'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$address   = trim($_POST['address'] ?? '');
$city      = trim($_POST['city'] ?? '');
$state     = trim($_POST['state'] ?? '');
$pincode   = trim($_POST['pincode'] ?? '');
$notes     = trim($_POST['notes'] ?? '');

// Fetch cart items directly from denormalized cart
$items = [];
$total = 0.0;

$q = mysqli_query($conn, "SELECT cart_id, product_id, product_name, product_price, product_image, quantity, total_price FROM cart WHERE user_id = $user_id");
if ($q) {
  while ($r = mysqli_fetch_assoc($q)) {
    $price = (float)$r['product_price'];
    $qty   = (int)$r['quantity'];
    $sub   = isset($r['total_price']) && $r['total_price'] !== null ? (float)$r['total_price'] : ($price * $qty);
    $items[] = [
      'product_id' => (int)$r['product_id'],
      'name'       => $r['product_name'],
      'price'      => $price,
      'qty'        => $qty,
      'subtotal'   => $sub,
    ];
    $total += $sub;
  }
}

if (empty($items)) {
  header('Location: shoping-cart.php');
  exit();
}

// Helper: check if a column exists in a table
function column_exists($conn, $table, $column) {
  $res = mysqli_query($conn, "SHOW COLUMNS FROM `" . $table . "` LIKE '" . mysqli_real_escape_string($conn, $column) . "'");
  return $res && mysqli_num_rows($res) > 0;
}

// Build dynamic insert for orders table
$columns = [];
$values  = [];

$columns[] = 'user_id';
$values[]  = (string)$user_id;

// Total column: prefer total_amount, else order_total if present
if (column_exists($conn, 'orders', 'total_amount')) {
  $columns[] = 'total_amount';
  $values[]  = (string)$total;
} elseif (column_exists($conn, 'orders', 'order_total')) {
  $columns[] = 'order_total';
  $values[]  = (string)$total;
}

// Status and date
if (column_exists($conn, 'orders', 'status')) {
  $columns[] = 'status';
  $values[]  = "'Pending'";
}
if (column_exists($conn, 'orders', 'order_date')) {
  $columns[] = 'order_date';
  $values[]  = 'NOW()';
}

// Optional shipping fields if they exist
$shipping_map = [
  'full_name' => $full_name,
  'name' => $full_name,
  'customer_name' => $full_name,
  'phone' => $phone,
  'mobile' => $phone,
  'contact' => $phone,
  'address' => $address,
  'city' => $city,
  'state' => $state,
  'pincode' => $pincode,
  'zip' => $pincode,
  'notes' => $notes,
];
foreach ($shipping_map as $col => $val) {
  if ($val !== '' && column_exists($conn, 'orders', $col)) {
    $columns[] = $col;
    $values[]  = "'" . mysqli_real_escape_string($conn, $val) . "'";
  }
}

$columns_sql = implode(',', array_map(function($c){ return '`' . $c . '`'; }, $columns));
$values_sql  = implode(',', $values);

$insert_sql = "INSERT INTO `orders` ($columns_sql) VALUES ($values_sql)";
if (!mysqli_query($conn, $insert_sql)) {
  die('Failed to place order: ' . mysqli_error($conn));
}

$order_id = (int)mysqli_insert_id($conn);

// Insert order items if table exists
$has_order_items = $conn && mysqli_query($conn, "SHOW TABLES LIKE 'order_items'") && mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'order_items'")) > 0;
if ($has_order_items) {
  foreach ($items as $it) {
    $pid = (int)$it['product_id'];
    $qty = (int)$it['qty'];
    $price = (float)$it['price'];
    $name = mysqli_real_escape_string($conn, $it['name']);
    // dynamic columns
    $cols = ['order_id','product_id','qty','price','name','subtotal','created_at'];
    $vals = [
      (string)$order_id,
      (string)$pid,
      (string)$qty,
      (string)$price,
      "'{$name}'",
      (string)($price*$qty),
      'NOW()'
    ];
    $col_list = [];
    $val_list = [];
    foreach ($cols as $idx=>$c) {
      if (column_exists($conn, 'order_items', $c)) {
        $col_list[] = "`$c`";
        $val_list[] = $vals[$idx];
      }
    }
    if (!empty($col_list)) {
      $sql = "INSERT INTO `order_items` (" . implode(',', $col_list) . ") VALUES (" . implode(',', $val_list) . ")";
      mysqli_query($conn, $sql);
    }
  }
}

// Clear cart
mysqli_query($conn, "DELETE FROM cart WHERE user_id = $user_id");

header('Location: order_success.php?order_id=' . $order_id);
exit();
