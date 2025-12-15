<?php
// admin/delete_category.php (mysqli + categories)
session_start();
include("../include/connection.php");

if (!$conn) {
  $_SESSION['msg'] = 'Database connection failed';
  $_SESSION['msg_type'] = 'danger';
  header('Location: index.php');
  exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  $_SESSION['msg'] = 'Invalid category ID.';
  $_SESSION['msg_type'] = 'danger';
  header('Location: index.php');
  exit;
}

$category_id = intval($_GET['id']);

// Prevent deleting if there are subcategories
$has_children = false; $cnt_children = 0;
if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM categories WHERE parent_id = ?")) {
  $stmt->bind_param('i', $category_id);
  $stmt->execute();
  $stmt->bind_result($cnt_children);
  if ($stmt->fetch()) { $has_children = ((int)$cnt_children > 0); }
  $stmt->close();
}

// Also prevent delete if subcategories table has children
$has_subcategories = false; $cnt_sub = 0;
if ($chk = $conn->query("SHOW TABLES LIKE 'subcategories'")) {
  if ($chk->num_rows > 0) {
    if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM subcategories WHERE category_id = ?")) {
      $stmt->bind_param('i', $category_id);
      $stmt->execute();
      $stmt->bind_result($cnt_sub);
      if ($stmt->fetch()) { $has_subcategories = ((int)$cnt_sub > 0); }
      $stmt->close();
    }
  }
}

// Prevent deleting if there are products under this category
$has_products = false; $cnt_products = 0;
$products_table_exists = false;
if ($chk = $conn->query("SHOW TABLES LIKE 'products'")) {
  $products_table_exists = ($chk->num_rows > 0);
}
if ($products_table_exists) {
  if ($stmt = $conn->prepare("SELECT COUNT(*) AS c FROM products WHERE category_id = ?")) {
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $stmt->bind_result($cnt_products);
    if ($stmt->fetch()) { $has_products = ((int)$cnt_products > 0); }
    $stmt->close();
  }
}

if ($has_children || $has_subcategories || $has_products) {
  $why = ($has_children || $has_subcategories)
    ? ('Cannot delete: remove or reassign subcategories first. (categories.children=' . (int)$cnt_children . ', subcategories=' . (int)$cnt_sub . ')')
    : ('Cannot delete: remove or reassign products first. (products=' . (int)$cnt_products . ')');
  $_SESSION['msg'] = $why;
  $_SESSION['msg_type'] = 'warning';
  header('Location: index.php');
  exit;
}

// Get image filename to delete after record deletion
$image_name = '';
if ($stmt = $conn->prepare('SELECT category_image FROM categories WHERE category_id = ?')) {
  $stmt->bind_param('i', $category_id);
  $stmt->execute();
  $stmt->bind_result($image_name);
  $stmt->fetch();
  $stmt->close();
}

// Delete the category
if ($stmt = $conn->prepare('DELETE FROM categories WHERE category_id = ?')) {
  $stmt->bind_param('i', $category_id);
  $ok = $stmt->execute();
  $err = $conn->error;
  $stmt->close();
  if ($ok) {
    // Remove image if exists
    if (!empty($image_name)) {
      $path = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $image_name;
      if (file_exists($path)) { @unlink($path); }
    }
    $_SESSION['msg'] = 'Category deleted successfully!';
    $_SESSION['msg_type'] = 'success';
  } else {
    $_SESSION['msg'] = 'Error deleting category: ' . $err;
    $_SESSION['msg_type'] = 'danger';
  }
}

header('Location: index.php');
exit;
