<?php
// filepath: d:\wamp64\www\project batch\my_edits\admin\add-product.php

include("../include/connection.php"); // Ensure this file exists and initializes $conn
include("inc/header.php"); // AdminLTE header

// Fetch categories and subcategories
$categories = mysqli_query($conn, "SELECT category_id, category_name FROM categories WHERE parent_id IS NULL ORDER BY category_name");
$subcategories = mysqli_query($conn, "SELECT subcategory_id, subcategory_name FROM subcategories ORDER BY subcategory_name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = $_POST['name'];
  $desc = $_POST['description'];
  $price = $_POST['price'];
  $stock = $_POST['stock'];
  $cat = $_POST['category_id'];
  $subcat = $_POST['subcategory_id'];
  $image = $_FILES['image']['name'];

  move_uploaded_file($_FILES['image']['tmp_name'], "../img/$image");

  $sql = "INSERT INTO products (name, description, price, stock, category_id, subcategory_id, image)
          VALUES ('$name', '$desc', '$price', '$stock', '$cat', '$subcat', '$image')";
  mysqli_query($conn, $sql);

  header("Location: product-list.php");
  exit;
}
?>

<div class="content-wrapper">
  <!-- Content Header -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Add Product</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Add Product</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <!-- Main Content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Add New Product</h3>
            </div>
            <form method="POST" enctype="multipart/form-data">
              <div class="card-body">
                <div class="form-group">
                  <label for="name">Product Name</label>
                  <input type="text" class="form-control" name="name" id="name" placeholder="Enter Product Name" required>
                </div>

                <div class="form-group">
                  <label for="description">Description</label>
                  <input type="text" class="form-control" name="description" id="description" placeholder="Enter Description">
                </div>

                <div class="form-group">
                  <label for="price">Price</label>
                  <input type="number" class="form-control" name="price" id="price" step="0.01" placeholder="Enter Price" required>
                </div>

                <div class="form-group">
                  <label for="stock">Stock</label>
                  <input type="number" class="form-control" name="stock" id="stock" placeholder="Enter Stock Quantity">
                </div>

                <div class="form-group">
                  <label for="category_id">Category</label>
                  <select class="form-control" name="category_id" id="category_id" required>
                    <option value="">Select Category</option>
                    <?php while ($row = mysqli_fetch_assoc($categories)): ?>
                      <option value="<?= $row['category_id'] ?>"><?= htmlspecialchars($row['category_name']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="subcategory_id">Subcategory</label>
                  <select class="form-control" name="subcategory_id" id="subcategory_id">
                    <option value="">Select Subcategory</option>
                    <?php while ($row = mysqli_fetch_assoc($subcategories)): ?>
                      <option value="<?= $row['subcategory_id'] ?>"><?= htmlspecialchars($row['subcategory_name']) ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="image">Product Image</label>
                  <input type="file" class="form-control" name="image" id="image">
                </div>
              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-primary">Add Product</button>
                <a href="product-list.php" class="btn btn-secondary float-right">View Products</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include("inc/footer.php"); // AdminLTE footer ?>
