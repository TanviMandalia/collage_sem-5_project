<?php
include("../include/connection.php");
ini_set('display_errors', 1);
error_reporting(E_ALL);

$id = intval($_GET['id']);
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE product_id = $id"));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $desc = trim($_POST['description']);
  $price = floatval($_POST['price']);
  $stock = intval($_POST['stock']);
  $cat_id = intval($_POST['category_id']);

  $image = $product['image'];
  if (!empty($_FILES['image']['name'])) {
    $uploadDir = "../img/product_dynamic/";
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }

    $image = time() . '_' . basename($_FILES['image']['name']);
    $targetPath = $uploadDir . $image;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
      die("Image upload failed.");
    }
  }

  $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, image=? WHERE product_id=?");
  $stmt->bind_param("ssdissi", $name, $desc, $price, $stock, $cat_id, $image, $id);
  $stmt->execute();

  header("Location: product_list.php");
  exit;
}

$categories = mysqli_query($conn, "SELECT * FROM categories WHERE category_status = 1");
include("inc/header.php");
?>

<div class="content-wrapper">
  <section class="content-header"><h1>Edit Product</h1></section>
  <section class="content">
    <form method="POST" enctype="multipart/form-data" class="card card-body">
      <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
      </div>

      <div class="form-group">
        <label>Price</label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
      </div>

      <div class="form-group">
        <label>Stock</label>
        <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>">
      </div>

      <div class="form-group">
        <label>Category</label>
        <select name="category_id" class="form-control" required>
          <?php while ($cat = mysqli_fetch_assoc($categories)): 
            $selected = $cat['category_id'] == $product['category_id'] ? 'selected' : ''; ?>
            <option value="<?= $cat['category_id'] ?>" <?= $selected ?>><?= htmlspecialchars($cat['category_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Image</label><br>
        <?php
          $imgPath = "../img/product_dynamic/" . $product['image'];
          $imgSrc = (!empty($product['image']) && file_exists($imgPath)) ? $imgPath : "../img/2.jpg";
        ?>
        <img src="<?= $imgSrc ?>" width="80" alt="Product Image"><br><br>
        <input type="file" name="image" class="form-control-file">
      </div>

      <button type="submit" class="btn btn-primary">Update Product</button>
    </form>
  </section>
</div>

<?php include("inc/footer.php"); ?>
