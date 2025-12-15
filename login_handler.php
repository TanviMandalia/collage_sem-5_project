<?php
session_start();
include 'include/connection.php'; // Ensure $conn is defined here

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    header("Location: login.php?error=empty_fields");
    exit();
}

// Prepare the statement safely
$sql = "SELECT * FROM users WHERE r_email = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    // Debugging: print SQL or MySQL error for development
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Your DB uses plain text passwords
    if ($password === $row['r_password']) {

        if ($row['r_status'] == 1) {
            $_SESSION['r_id'] = $row['r_id'];
            $_SESSION['r_name'] = $row['r_fname'] . ' ' . $row['r_lname'];
            $_SESSION['r_email'] = $row['r_email'];

            header("Location: index.php");
            exit();
        } else {
            header("Location: login.php?error=inactive");
            exit();
        }

    } else {
        header("Location: login.php?error=invalid_password");
        exit();
    }
} else {
    header("Location: login.php?error=no_user");
    exit();
}
?>
