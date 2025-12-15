<?php
include("../include/connection.php");

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Request!");
}

$id = intval($_GET['id']);

// Fetch subcategory details
$sql = "SELECT * FROM subcategories WHERE subcategory_id = $id";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Subcategory not found!");
}

$row = mysqli_fetch_assoc($result);

// Fetch categories for dropdown
$cat_sql = "SELECT * FROM categories WHERE category_status = 1";
$cat_result = mysqli_query($conn, $cat_sql);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['subcategory_name']);
    $category_id = intval($_POST['category_id']);
    $desc = mysqli_real_escape_string($conn, $_POST['subcategory_desc']);
    $status = intval($_POST['subcategory_status']);
    $image = $row['subcategory_image']; // default to old image

    // Check if new image uploaded
    if (!empty($_FILES['subcategory_image']['name'])) {
        $image = time() . "_" . basename($_FILES['subcategory_image']['name']);
        $target = "uploads/" . $image;
        move_uploaded_file($_FILES['subcategory_image']['tmp_name'], $target);
    }

    // Update query
    $update_sql = "UPDATE subcategories 
                   SET subcategory_name='$name', 
                       category_id='$category_id', 
                       subcategory_desc='$desc', 
                       subcategory_status='$status',
                       subcategory_image='$image'
                   WHERE subcategory_id=$id";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: view_subcategories.php?msg=updated");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Subcategory</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
  <h2 class="mb-3">Update Subcategory</h2>
  
  <form method="POST" enctype="multipart/form-data">
    <!-- Subcategory Name -->
    <div class="mb-3">
      <label class="form-label">Subcategory Name</label>
      <input type="text" name="subcategory_name" class="form-control" 
             value="<?php echo htmlspecialchars($row['subcategory_name']); ?>" required>
    </div>

    <!-- Parent Category -->
    <div class="mb-3">
      <label class="form-label">Parent Category</label>
      <select name="category_id" class="form-control" required>
        <?php while($cat = mysqli_fetch_assoc($cat_result)) { ?>
          <option value="<?php echo $cat['category_id']; ?>" 
            <?php if($cat['category_id'] == $row['category_id']) echo "selected"; ?>>
            <?php echo htmlspecialchars($cat['category_name']); ?>
          </option>
        <?php } ?>
      </select>
    </div>

    <!-- Description -->
    <div class="mb-3">
      <label class="form-label">Description</label>
      <input type="text" name="subcategory_desc" class="form-control"
             value="<?php echo htmlspecialchars($row['subcategory_desc']); ?>">
    </div>

    <!-- Image -->
    <div class="mb-3">
      <label class="form-label">Current Image</label><br>
      <?php if(!empty($row['subcategory_image'])) { ?>
        <img src="uploads/<?php echo $row['subcategory_image']; ?>" width="100" class="mb-2">
      <?php } else { echo "No image uploaded."; } ?>
      <input type="file" name="subcategory_image" class="form-control mt-2">
    </div>

    <!-- Status -->
    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="subcategory_status" class="form-control">
        <option value="1" <?php if($row['subcategory_status']==1) echo "selected"; ?>>Active</option>
        <option value="0" <?php if($row['subcategory_status']==0) echo "selected"; ?>>Inactive</option>
      </select>
    </div>

    <!-- Buttons -->
    <button type="submit" class="btn btn-primary">Update Subcategory</button>
    <a href="view_subcategories.php" class="btn btn-secondary">Back to List</a>
  </form>
</div>
</body>
</html>
