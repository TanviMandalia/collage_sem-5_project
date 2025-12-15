<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include("../include/connection.php");

$errorMsg = "";
$imageName = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = $_POST['stock'] !== '' ? intval($_POST['stock']) : null;
    $cat_id = intval($_POST['category_id'] ?? 0);
    $subcat_id = $_POST['subcategory_id'] !== '' ? intval($_POST['subcategory_id']) : null;

    if ($name && $price > 0 && $cat_id > 0) {
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = mime_content_type($_FILES['image']['tmp_name']);

            if (!in_array($fileType, $allowedTypes)) {
                $errorMsg = "Invalid image type. Only JPG, PNG, and GIF are allowed.";
            } else {
                $uploadDir = "../img/product_dynamic/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $imageName = time() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $imageName;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $errorMsg = "Failed to upload image.";
                }
            }
        }

        if (empty($errorMsg)) {
            try {
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, subcategory_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdiiis", $name, $desc, $price, $stock, $cat_id, $subcat_id, $imageName);
                $stmt->execute();
                header("Location: product_list.php");
                exit;
            } catch (Exception $e) {
                $errorMsg = "Database error: " . $e->getMessage();
            }
        }
    } else {
        $errorMsg = "Please fill in all required fields correctly.";
    }
}

// Fetch categories and subcategories
try {
    $categories = mysqli_query($conn, "SELECT category_id, category_name FROM categories WHERE category_status = 1");
    $subcategories = mysqli_query($conn, "SELECT subcategory_id, subcategory_name FROM subcategories WHERE subcategory_status = 1");
} catch (Exception $e) {
    die("Failed to fetch categories: " . $e->getMessage());
}

include("inc/header.php");
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <h1><i class="fas fa-plus-circle"></i> Add Product</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" class="card card-body">
        <div class="form-group">
          <label>Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Description</label>
          <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="form-group">
          <label>Price <span class="text-danger">*</span></label>
          <input type="number" step="0.01" name="price" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Stock</label>
          <input type="number" name="stock" class="form-control">
        </div>

        <div class="form-group">
          <label>Category <span class="text-danger">*</span></label>
          <select name="category_id" class="form-control" required>
            <option value="">Select</option>
            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
              <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Subcategory</label>
          <select name="subcategory_id" class="form-control">
            <option value="">Select</option>
            <?php while ($sub = mysqli_fetch_assoc($subcategories)): ?>
              <option value="<?= $sub['subcategory_id'] ?>"><?= htmlspecialchars($sub['subcategory_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Image</label>
          <input type="file" name="image" class="form-control-file">
        </div>

        <button type="submit" class="btn btn-success">Add Product</button>
      </form>
    </div>
  </section>
</div>

<?php include("inc/footer.php"); ?>
