<?php
// filepath: d:\wamp64\www\project batch\my_edits\admin\add_subcategory.php
include("../include/connection.php"); // Ensure this file exists and initializes $conn

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_sub') {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);
    $parent_id = intval($_POST['parent_id']);
    $catagory_desc = mysqli_real_escape_string($conn, $_POST['catagory_desc']);
    $category_status = intval($_POST['category_status']);

    // Handle image upload
    $image_name = '';
    if (!empty($_FILES['category_image']['name'])) {
        $target_dir = "../img/category_dynamic/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $image_name = basename($_FILES["category_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($image_type, $allowed_types)) {
            $_SESSION['msg'] = "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
            $_SESSION['msg_type'] = "danger";
            header("Location: add_subcategory.php");
            exit;
        }

        // Move uploaded file
        if (!move_uploaded_file($_FILES["category_image"]["tmp_name"], $target_file)) {
            $_SESSION['msg'] = "Failed to upload image.";
            $_SESSION['msg_type'] = "danger";
            header("Location: add_subcategory.php");
            exit;
        }
    }

    // Insert into database
    $insert_sql = "INSERT INTO categories (category_name, parent_id, catagory_desc, category_image, category_status)
                   VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($stmt, "sisss", $category_name, $parent_id, $catagory_desc, $image_name, $category_status);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['msg'] = "Subcategory added successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Error adding subcategory: " . mysqli_error($conn);
        $_SESSION['msg_type'] = "danger";
    }

    mysqli_stmt_close($stmt);
    header("Location: add_subcategory.php");
    exit;
}

// Fetch all main categories for parent selection
$sql = "SELECT category_id, category_name FROM categories WHERE parent_id IS NULL ORDER BY category_name";
$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Error fetching categories: " . mysqli_error($conn));
}

include('inc/header.php');
?>

<!-- HTML form remains unchanged -->
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1>Add Subcategory</h1></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Add Subcategory</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">Add New Subcategory</h3></div>
            <form method="POST" action="add_subcategory.php" enctype="multipart/form-data">
              <div class="card-body">
                <?php if (isset($_SESSION['msg'])): ?>
                  <div class="alert <?php echo $_SESSION['msg_type'] === 'success' ? 'alert-success' : 'alert-danger'; ?>">
                    <?php 
                      echo htmlspecialchars($_SESSION['msg']);
                      unset($_SESSION['msg'], $_SESSION['msg_type']);
                    ?>
                  </div>
                <?php endif; ?>
                <input type="hidden" name="action" value="add_sub">
                <div class="form-group">
                  <label for="subcategory_name">Subcategory Name</label>
                  <input type="text" class="form-control" name="category_name" id="subcategory_name" placeholder="Enter Subcategory Name" required>
                </div>
                <div class="form-group">
                  <label for="parent_id">Parent Category</label>
                  <select class="form-control" name="parent_id" id="parent_id" required>
                    <option value="">Select Parent Category</option>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                      <option value="<?php echo $row['category_id']; ?>"><?php echo htmlspecialchars($row['category_name']); ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label for="catagory_desc">Description</label>
                  <input type="text" class="form-control" name="catagory_desc" id="catagory_desc" placeholder="Enter Description">
                </div>
                <div class="form-group">
                  <label for="category_image">Image</label>
                  <input type="file" class="form-control" name="category_image" id="category_image">
                </div>
                <div class="form-group">
                  <label for="category_status">Status</label>
                  <select class="form-control" name="category_status" id="category_status">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                  </select>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">Add Subcategory</button>
                <a href="view_subcategories.php" class="btn btn-secondary float-right">View Subcategories</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include('inc/footer.php'); ?>
