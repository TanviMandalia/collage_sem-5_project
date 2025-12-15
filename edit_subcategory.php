<?php
// edit_subcategory.php
$db = new PDO('mysql:host=localhost;dbname=project', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get subcategory ID from URL
$subcategory_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($subcategory_id <= 0) {
    die("Invalid subcategory ID.");
}

// Fetch subcategory details
$stmt = $db->prepare("SELECT * FROM categories WHERE category_id = :id");
$stmt->execute([':id' => $subcategory_id]);
$subcategory = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$subcategory) {
    die("Subcategory not found.");
}

// Fetch all main categories for parent selection
$parentStmt = $db->query("SELECT category_id, category_name FROM categories WHERE parent_id IS NULL ORDER BY category_name");
$mainCategories = $parentStmt->fetchAll(PDO::FETCH_ASSOC);

include('inc/header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Edit Subcategory</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Edit Subcategory</li>
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
            <div class="card-header">
              <h3 class="card-title">Update Subcategory</h3>
            </div>
            <form action="category_process.php" method="post" enctype="multipart/form-data">
              <div class="card-body">
                <input type="hidden" name="action" value="edit_sub">
                <input type="hidden" name="category_id" value="<?php echo $subcategory['category_id']; ?>">

                <div class="form-group">
                  <label for="category_name">Subcategory Name</label>
                  <input type="text" class="form-control" name="category_name" id="category_name"
                         value="<?php echo htmlspecialchars($subcategory['category_name']); ?>" required>
                </div>

                <div class="form-group">
                  <label for="catagory_desc">Description</label>
                  <input type="text" class="form-control" name="catagory_desc" id="catagory_desc"
                         value="<?php echo htmlspecialchars($subcategory['catagory_desc']); ?>">
                </div>

                <div class="form-group">
                  <label for="parent_id">Parent Category</label>
                  <select class="form-control" name="parent_id" id="parent_id">
                    <option value="">None (Main Category)</option>
                    <?php foreach($mainCategories as $cat): ?>
                      <option value="<?php echo $cat['category_id']; ?>"
                        <?php echo ($cat['category_id'] == $subcategory['parent_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category_name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="category_image">Image</label>
                  <input type="file" class="form-control" name="category_image" id="category_image">
                  <?php if (!empty($subcategory['category_image'])): ?>
                    <p class="mt-2">Current Image:<br>
                      <img src="uploads/<?php echo $subcategory['category_image']; ?>" width="100" class="img-thumbnail">
                    </p>
                  <?php endif; ?>
                </div>

                <div class="form-group">
                  <label for="category_status">Status</label>
                  <select class="form-control" name="category_status" id="category_status">
                    <option value="1" <?php echo ($subcategory['category_status'] == 1) ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo ($subcategory['category_status'] == 0) ? 'selected' : ''; ?>>Inactive</option>
                  </select>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">Update Subcategory</button>
                <a href="view_subcategories.php" class="btn btn-secondary float-right">Back to List</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<?php include('inc/footer.php'); ?>
