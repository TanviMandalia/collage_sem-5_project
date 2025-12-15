<?php
// filepath: d:\wamp64\www\project batch\my_edits\admin\edit-product.php

include("../include/connection.php");
include("inc/header.php");

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
  echo "<div class='alert alert-danger'>Invalid product ID.</div>";
  exit;
}

$productQuery = mysqli_query($conn, "SELECT * FROM products WHERE product_id = $product_id");
if (!$productQuery || mysqli_num_rows($productQuery) == 0) {
  echo "<div class='alert alert-danger'>Product not found.</div>";
  exit;
}
$product = mysqli_fetch_assoc($productQuery);

$categories = mysqli_query($conn, "SELECT category_id, category_name FROM categories WHERE parent_id IS NULL ORDER BY category_name");
$subcategories = mysqli_query($conn, "SELECT subcategory_id, subcategory_name FROM subcategories ORDER BY subcategory_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $desc = $_POST['description'];
  $price = $_POST['price'];
  $stock = $_POST['stock'];
  $cat = $_POST['category_id'];
  $subcat = $_POST['subcategory_id'];

  if (!empty($_FILES['image']['name'])) {
    $image = $_FILES['image']['name'];
    move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/$image");
  } else {
    $image = $product['image'];
  }

  $update = "UPDATE products SET 
    name = '$name',
    description = '$desc',
    price = '$price',
    stock = '$stock',
    category_id = '$cat',
    subcategory_id = '$subcat',
    image = '$image'
    WHERE product_id = $product_id";

  if (mysqli_query($conn, $update)) {
    echo "<script>window.location.href='product-list.php';</script>";
    exit;
  } else {
    echo "<div class='alert alert-danger'>Update failed: " . mysqli_error($conn) . "</div>";
  }
}
?>

<div class="content-wrapper">
  <!-- Content Header -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Edit Product</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="product-list.php">Product List</a></li>
            <li class="breadcrumb-item active">Edit Product</li>
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
          <div class="card card-warning">
            <div class="card-header">
              <h3 class="card-title">Update Product Details</h3>
            </div>
            <form method="POST" enctype="multipart/form-data">
              <div class="card-body">
                <div class="form-group">
                  <label for="name">Product Name</label>
                  <input type="text" class="form-control" name="name" id="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="form-group">
                  <label for="description">Description</label>
                  <input type="text" class="form-control" name="description" id="description" value="<?= htmlspecialchars($product['description']) ?>">
                </div>

                <div class="form-group">
                  <label for="price">Price</label>
                  <input type="number" class="form-control" name="price" id="price" step="0.01" value="<?= $product['price'] ?>" required>
                </div>

                <div class="form-group">
                  <label for="stock">Stock</label>
                  <input type="number" class="form-control" name="stock" id="stock" value="<?= $product['stock'] ?>">
                </div>

                <div class="form-group">
                  <label for="category_id">Category</label>
                  <select class="form-control" name="category_id" id="category_id" required>
                    <option value="">Select Category</option>
                    <?php while ($row = mysqli_fetch_assoc($categories)): ?>
                      <option value="<?= $row['category_id'] ?>" <?= $row['category_id'] == $product['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['category_name']) ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="subcategory_id">Subcategory</label>
                  <select class="form-control" name="subcategory_id" id="subcategory_id">
                    <option value="">Select Subcategory</option>
                    <?php while ($row = mysqli_fetch_assoc($subcategories)): ?>
                      <option value="<?= $row['subcategory_id'] ?>" <?= $row['subcategory_id'] == $product['subcategory_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['subcategory_name']) ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="form-group">
                  <label for="image">Product Image</label><br>
                  <td>
                    <?php if (!empty($product['image'])) { ?>
                      <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" width="100" height="100" class="rounded mb-2" style="object-fit: cover;">
                    <?php } else { ?>
                      <span class="text-muted">No Image</span>
                    <?php } ?>
                  </td>
                  <input type="file" class="form-control" name="image" id="image">
                </div>
              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-warning">Update Product</button>
                <a href="product-list.php" class="btn btn-secondary float-right">Back to List</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include("inc/footer.php"); ?>
