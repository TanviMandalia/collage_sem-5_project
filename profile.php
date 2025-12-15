<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/include/connection.php';

// Require login
if (empty($_SESSION['r_id'])) {
  $ret = $_SERVER['REQUEST_URI'] ?? 'profile.php';
  header('Location: login.php?redirect=' . urlencode($ret));
  exit();
}

$user_id = (int)$_SESSION['r_id'];

// Determine users table and common columns
function detect_user_table(mysqli $conn, int $uid): array {
  // Try registration
  $q1 = @mysqli_query($conn, "SELECT r_fname, r_lname, r_email, user_mobileno FROM registration WHERE r_id = $uid LIMIT 1");
  if ($q1 && mysqli_num_rows($q1) > 0) {
    $row = mysqli_fetch_assoc($q1);
    return [
      'table' => 'registration',
      'data' => [
        'first_name' => $row['r_fname'] ?? '',
        'last_name'  => $row['r_lname'] ?? '',
        'email'      => $row['r_email'] ?? '',
        'mobile'     => $row['user_mobileno'] ?? '',
      ]
    ];
  }
  // Try users
  $q2 = @mysqli_query($conn, "SELECT * FROM users WHERE id = $uid OR user_id = $uid LIMIT 1");
  if ($q2 && mysqli_num_rows($q2) > 0) {
    $row = mysqli_fetch_assoc($q2);
    return [
      'table' => 'users',
      'id_col' => isset($row['id']) ? 'id' : 'user_id',
      'data' => [
        'first_name' => $row['first_name'] ?? ($row['fname'] ?? ($row['name'] ?? '')),
        'last_name'  => $row['last_name'] ?? ($row['lname'] ?? ''),
        'email'      => $row['email'] ?? ($row['user_email'] ?? ''),
        'mobile'     => $row['mobile'] ?? ($row['phone'] ?? ($row['user_mobileno'] ?? '')),
      ]
    ];
  }
  return ['table' => null, 'data' => ['first_name'=>'','last_name'=>'','email'=>'','mobile'=>'']];
}

$det = detect_user_table($conn, $user_id);
$table = $det['table'];
$profile = $det['data'];
$id_col = $det['id_col'] ?? 'r_id';

// Handle POST update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $table) {
  $first = mysqli_real_escape_string($conn, trim($_POST['first_name'] ?? ''));
  $last  = mysqli_real_escape_string($conn, trim($_POST['last_name'] ?? ''));
  $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
  $mobile= mysqli_real_escape_string($conn, trim($_POST['mobile'] ?? ''));

  if ($table === 'registration') {
    $sql = "UPDATE registration 
            SET r_fname='$first', r_lname='$last', r_email='$email', user_mobileno='$mobile'
            WHERE r_id = $user_id";
  } else { // users
    // determine id column again safely
    $idcol = 'id';
    $chk = @mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'user_id'");
    if ($chk && mysqli_num_rows($chk) > 0) { $idcol = 'user_id'; }
    $sql = "UPDATE users 
            SET first_name='$first', last_name='$last', email='$email', mobile='$mobile'
            WHERE $idcol = $user_id";
  }

  if (@mysqli_query($conn, $sql)) {
    $message = 'Profile updated successfully';
    // refresh in-memory profile
    $det = detect_user_table($conn, $user_id);
    $profile = $det['data'];
  } else {
    $message = 'Failed to update profile';
  }
}

include __DIR__ . '/include/header.php';
?>

<div class="container py-4">
  <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo (strpos($message, 'successfully')!==false)?'success':'danger'; ?>">
      <?php echo htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-lg-8 mx-auto">
      <div class="card shadow-sm border-0">
        <div class="card-body p-4">
          <h4 class="mb-1">My Profile</h4>
          <p class="text-muted mb-3">View and update your account details</p>

          <form method="POST" action="profile.php">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>First name</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($profile['first_name']); ?>" required>
              </div>
              <div class="form-group col-md-6">
                <label>Last name</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($profile['last_name']); ?>">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($profile['email']); ?>">
              </div>
              <div class="form-group col-md-6">
                <label>Mobile</label>
                <input type="text" name="mobile" class="form-control" value="<?php echo htmlspecialchars($profile['mobile']); ?>">
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mt-3">
              <a href="shoping-cart.php" class="btn btn-outline-secondary">Back</a>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>
