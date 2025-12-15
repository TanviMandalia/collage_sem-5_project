<?php
include '../include/connection.php';

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT r_status FROM users WHERE r_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($current_status);
    $stmt->fetch();
    $stmt->close();

    $new_status = ($current_status === 'unblocked') ? 'blocked' : 'unblocked';

    $update = $conn->prepare("UPDATE users SET r_status = ? WHERE r_id = ?");
    $update->bind_param("si", $new_status, $user_id);
    $update->execute();
    $update->close();
}

header("Location: users.php");
exit;
