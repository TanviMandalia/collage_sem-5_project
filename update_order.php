<?php
include '../include/connection.php'; // MySQLi connection

// Check if both 'id' and 'action' are set
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    // Determine new status based on action
    if ($action === 'ship') {
        $status = 'Shipped';
    } elseif ($action === 'cancel') {
        $status = 'Cancelled';
    } else {
        $status = ''; // Invalid action
    }

    // If status is valid, update the order
    if ($status !== '') {
        $update = $conn->query("UPDATE orders SET status='$status' WHERE order_id=$id");

        if ($update) {
            header("Location: orders.php");
            exit;
        } else {
            echo "Error updating order: " . $conn->error;
        }
    } else {
        echo "Invalid action specified.";
    }
} else {
    echo "Missing order ID or action.";
}
?>
