<?php
include("inc/header.php");
include("../include/connection.php");

$limit = 10; // Products per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchSql = $search ? "WHERE p.name LIKE '%" . mysqli_real_escape_string($conn, $search) . "%'" : '';

// Count total products
$countSql = "SELECT COUNT(*) AS total FROM products p $searchSql";
$countResult = mysqli_query($conn, $countSql);
$total = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($total / $limit);

// Fetch paginated products
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        $searchSql 
        ORDER BY p.created_at DESC 
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);
?>

<div class="content-wrapper">
  <section class="content-header d-flex justify-content-between align-items-center">
    <h1>Product List</h1>
    <a href="product_add.php" class="btn btn-primary">Add Product</a>
  </section>

  <section class="content">
    <div class="card">
      <div class="card-header">
        <form method="GET" class="form-inline">
          <input type="text" name="search" class="form-control mr-2" placeholder="Search by name" value="<?php echo htmlspecialchars($search); ?>">
          <button type="submit" class="btn btn-secondary">Search</button>
        </form>
      </div>

      <div class="card-body table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Category</th>
              <th>Image</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($result) === 0): ?>
              <tr><td colspan="7" class="text-center">No products found.</td></tr>
            <?php else: ?>
              <?php while ($row = mysqli_fetch_assoc($result)): 
                $imgPath = '../img/product_dynamic/';
                $imgFile = $imgPath . $row['image'];
                $img = (!empty($row['image']) && file_exists($imgFile)) ? $imgFile : '../img/2.jpg';
              ?>
                <tr>
                  <td><?php echo $row['product_id']; ?></td>
                  <td><?php echo htmlspecialchars($row['name']); ?></td>
                  <td>₹<?php echo number_format($row['price'], 2); ?></td>
                  <td><?php echo $row['stock']; ?></td>
                  <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                  <td><img src="<?php echo $img; ?>" width="60" alt="Product Image"></td>
                  <td>
                    <a href="product_edit.php?id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="product_delete.php?id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="card-footer d-flex justify-content-between align-items-center">
        <span>Total Products: <?php echo $total; ?></span>
        <nav>
          <ul class="pagination mb-0">
            <?php for ($i = 1; $i <= $totalPages; $i++): 
              $active = $i === $page ? 'active' : '';
              $query = http_build_query(array_merge($_GET, ['page' => $i]));
            ?>
              <li class="page-item <?php echo $active; ?>">
                <a class="page-link" href="?<?php echo $query; ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
          </ul>
        </nav>
      </div>
    </div>
  </section>
</div>

<?php include("inc/footer.php"); ?>
