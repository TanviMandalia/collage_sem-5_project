<?php
// category_process.php
$db = new PDO('mysql:host=localhost;dbname=project', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$message = '';
$success = false;

// Common image upload logic
function handleImageUpload($fieldName = 'category_image') {
    if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $filename = uniqid() . '_' . basename($_FILES[$fieldName]['name']);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $target_file)) {
            return $filename;
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $category_name = trim($_POST['category_name']);
    $catagory_desc = trim($_POST['catagory_desc']);
    $category_status = isset($_POST['category_status']) ? intval($_POST['category_status']) : 1;
    $parent_id = !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
    $category_image = handleImageUpload();

    if ($action === 'add') {
        $sql = "INSERT INTO categories (category_name, parent_id, catagory_desc, category_image, category_status)
                VALUES (:name, :parent_id, :desc, :image, :status)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $category_name);
        $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
        $stmt->bindParam(':desc', $catagory_desc);
        $stmt->bindParam(':image', $category_image);
        $stmt->bindParam(':status', $category_status, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $message = "Category added successfully!";
            $success = true;
        } else {
            $message = "Error adding category.";
        }

    } elseif ($action === 'edit_cat' || $action === 'edit_sub') {
        $category_id = intval($_POST['category_id']);

        $sql = "UPDATE categories SET 
                    category_name = :name, 
                    catagory_desc = :desc, 
                    category_status = :status, 
                    parent_id = :parent_id";

        if ($category_image) {
            $sql .= ", category_image = :image";
        }

        $sql .= " WHERE category_id = :id";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':name', $category_name);
        $stmt->bindParam(':desc', $catagory_desc);
        $stmt->bindParam(':status', $category_status, PDO::PARAM_INT);
        $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
        $stmt->bindParam(':id', $category_id, PDO::PARAM_INT);
        if ($category_image) {
            $stmt->bindParam(':image', $category_image);
        }

        if ($stmt->execute()) {
            $message = "Subcategory updated successfully!";
            $success = true;
        } else {
            $message = "Error updating subcategory.";
        }
    }
}

include('inc/header.php');
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1><?php echo $success ? 'Success' : 'Error'; ?> Result</h1>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card <?php echo $success ? 'card-success' : 'card-danger'; ?>">
            <div class="card-header">
              <h3 class="card-title"><?php echo $success ? 'Success' : 'Error'; ?></h3>
            </div>
            <div class="card-body">
              <p><?php echo htmlspecialchars($message); ?></p>
            </div>
            <div class="card-footer">
              <a href="category.php" class="btn btn-primary">Back to Add Category</a>
              <a href="view_subcategories.php" class="btn btn-secondary float-right">View Subcategories</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include('inc/footer.php'); ?>
