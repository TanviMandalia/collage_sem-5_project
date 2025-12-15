<?php
// Database connection
$conn = mysqli_connect("localhost", "root", "", "project");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if cart table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'cart'");
if (mysqli_num_rows($table_check) == 0) {
    echo "Cart table does not exist. Please run add_cart_table.php first.";
    exit;
}

// Get cart data
$result = mysqli_query($conn, "SELECT * FROM cart");
$row_count = mysqli_num_rows($result);

echo "<h2>Cart Table Status</h2>";
echo "<p>Cart table exists with $row_count records.</p>";

if ($row_count > 0) {
    echo "<h3>Cart Items:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Cart ID</th><th>User ID</th><th>Product</th><th>Qty</th><th>Price</th><th>Added On</th></tr>";
    
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['cart_id'] . "</td>";
        echo "<td>" . $row['user_id'] . " - " . $row['user_fname'] . " " . $row['user_lname'] . "</td>";
        echo "<td>" . $row['product_name'] . " (ID: " . $row['product_id'] . ")</td>";
        echo "<td>" . $row['quantity'] . "</td>";
        echo "<td>$" . number_format($row['product_price'], 2) . "</td>";
        echo "<td>" . $row['added_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check for any SQL errors
if ($error = mysqli_error($conn)) {
    echo "<p style='color:red'>Error: " . $error . "</p>";
}

// Check if there are any products in the database
$products = mysqli_query($conn, "SELECT COUNT(*) as count FROM products");
$product_count = mysqli_fetch_assoc($products)['count'];
echo "<p>Total products in database: $product_count</p>";

mysqli_close($conn);
?>
