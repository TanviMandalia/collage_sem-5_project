<?php
include('include/connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['c_name']);
  $email = trim($_POST['c_email']);
  $mobileno = trim($_POST['c_mobileno']);
  $subject = trim($_POST['c_subject']);
  $message = trim($_POST['c_message']);
  $time = date("Y-m-d H:i:s");
  $status = "1";

  // Combine subject and message into one field if needed
  $full_message = $subject . " - " . $message;

  if ($name && $email && $mobileno && $message) {
    $query = "INSERT INTO contact (c_name, c_email, c_mobileno, c_message, c_time, c_status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssssss", $name, $email, $mobileno, $full_message, $time, $status);

    if (mysqli_stmt_execute($stmt)) {
      header("Location: contact.php?success=1");
    } else {
      header("Location: contact.php?error=1");
    }
  } else {
    header("Location: contact.php?error=1");
  }
} else {
  header("Location: contact.php");
}
?>
