<?php
include("../include/connection.php");

$id = intval($_GET['id']);
mysqli_query($conn, "DELETE FROM products WHERE product_id = $id");
header("Location: product_list.php");
exit;
