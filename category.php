<?php
// add_category.php
// Database connection
$db = new PDO('mysql:host=localhost;dbname=project', 'root', '');

// Fetch all main categories for parent selection
$stmt = $db->query("SELECT category_id, category_name FROM categories WHERE parent_id IS NULL ORDER BY category_name");
$mainCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include('inc/header.php');
?>
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Add Category</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Add Category</li>
          </ol>
        </div>
      </div>
    </div>
  </section>
  <section class="content">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-12">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Add New Category</h3>
            </div>
            <form action="category_process.php" method="post" enctype="multipart/form-data">
              <div class="card-body">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                  <label for="category_name">Category Name</label>
                  <input type="text" class="form-control" name="category_name" id="category_name" required>
                </div>
                <div class="form-group">
                  <label for="catagory_desc">Description</label>
                  <input type="text" class="form-control" name="catagory_desc" id="catagory_desc">
                </div>
                <div class="form-group">
                  <label for="parent_id">Parent Category</label>
                  <select class="form-control" name="parent_id" id="parent_id">
                    <option value="">None (Main Category)</option>
                    <?php foreach($mainCategories as $cat): ?>
                      <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                    <?php endforeach; ?>
                  </select>
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
                <button type="submit" class="btn btn-primary">Add Category</button>
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