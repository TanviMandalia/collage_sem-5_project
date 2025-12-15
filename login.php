<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session BEFORE any output or includes
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include("include/connection.php");

// --------------------------------
// LOGIN LOGIC
// --------------------------------
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $message = "<div class='alert alert-warning'>Please fill in all fields.</div>";
    } else {
        if (!$conn) {
            $message = "<div class='alert alert-danger'>Database unavailable. Please try again later.</div>";
        } else {
            $stmt = mysqli_prepare($conn, "SELECT r_id, r_fname, r_lname, r_email, r_password, r_status 
                                           FROM users 
                                           WHERE r_email = ? 
                                           LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) === 1) {
                        mysqli_stmt_bind_result($stmt, $r_id, $r_fname, $r_lname, $r_email_db, $r_password_db, $r_status);
                        mysqli_stmt_fetch($stmt);
                        $user = [
                            'r_id' => $r_id,
                            'r_fname' => $r_fname,
                            'r_lname' => $r_lname,
                            'r_email' => $r_email_db,
                            'r_password' => $r_password_db,
                            'r_status' => $r_status
                        ];
                    }
                } else {
                    $message = "<div class='alert alert-danger'>System error. Please try again later.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>System error. Please try again later.</div>";
            }

            if (isset($user)) {
                $isActive = ($user['r_status'] == 1 || strtolower($user['r_status']) === 'unblocked' || strtolower($user['r_status']) === 'active');
                if (!$isActive) {
                    $message = "<div class='alert alert-danger'>Your account is inactive. Please contact admin.</div>";
                } elseif ((strlen($user['r_password']) > 0 && password_verify($password, $user['r_password'])) || $password === $user['r_password']) { 

                    $_SESSION['r_id'] = $user['r_id'];
                    $_SESSION['r_name'] = $user['r_fname'] . ' ' . $user['r_lname'];
                    $_SESSION['r_email'] = $user['r_email'];
                    session_regenerate_id(true);
                    $message = "<div class='alert alert-success'>Login successful. Redirecting to home...</div>";
                    $login_success = true;
                } else {
                    $message = "<div class='alert alert-danger'>Invalid password.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>No account found with that email.</div>";
            }
            if (isset($stmt) && $stmt) { mysqli_stmt_close($stmt); }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <?php 
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); 
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : (isset($_POST['__redirect']) ? $_POST['__redirect'] : '');
  ?>
  <link rel="stylesheet" href="<?php echo $base; ?>/css/main.css?v=<?php echo time(); ?>" />
  <?php if (!empty($login_success)) : ?>
    <meta http-equiv="refresh" content="1.5;url=<?php echo $redirect !== '' ? htmlspecialchars($redirect, ENT_QUOTES) : 'index.php'; ?>" />
  <?php endif; ?>
  <meta name="robots" content="noindex" />
</head>
<body class="login-page">
  <main class="login-card" role="main" aria-labelledby="login-title">
    <h1 id="login-title">Login</h1>
    <p class="sub">Sign in to continue</p>

    <?php echo $message; ?>

    <form method="POST" action="<?php echo $redirect !== '' ? ('?redirect=' . urlencode($redirect)) : '' ; ?>" novalidate>
      <input type="hidden" name="__redirect" value="<?php echo htmlspecialchars($redirect, ENT_QUOTES); ?>" />
      <div class="field">
        <label for="email">Email</label>
        <div class="input">
          <input type="email" id="email" name="email" placeholder="you@example.com" required />
        </div>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <div class="input">
          <input type="password" id="password" name="password" placeholder="********" required />
        </div>
      </div>

      <button class="btn-login" type="submit">Sign in</button>
    </form>

    <p class="helper">Don’t have an account? <a href="registation.php">Create one</a></p>
  </main>
</body>
</html>
