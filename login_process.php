<?php
session_start();
include("../include/connection.php");

$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unm = trim($_POST['unm'] ?? '');
    $pwd = trim($_POST['pwd'] ?? '');

    if (empty($unm)) {
        $error[] = "Please enter your email.";
    }

    if (empty($pwd)) {
        $error[] = "Please enter your password.";
    }

    if (empty($error)) {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE a_email = ? AND a_password = ?");
        $stmt->bind_param("ss", $unm, $pwd);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        if (!$row) {
            $error[] = "Wrong username or password.";
        } else {
            $_SESSION['admin']['email'] = $row['a_email'];
            $_SESSION['admin']['id'] = $row['a_id'];
            $_SESSION['admin']['status'] = true;

            if (!empty($_POST['remember'])) {
                setcookie("admin_email", $unm, time() + (86400 * 30), "/");
                setcookie("admin_password", $pwd, time() + (86400 * 30), "/");
            } else {
                setcookie("admin_email", "", time() - 3600, "/");
                setcookie("admin_password", "", time() - 3600, "/");
            }

            header("Location: index.php");
            exit;
        }
    }

    // Store errors and old input
    $_SESSION['login_error'] = $error;
    $_SESSION['old_email'] = $unm;
    header("Location: login.php");
    exit;
} else {
    header("Location: login.php");
    exit;
}
