<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("include/header.php");
include("include/connection.php"); // $conn must be defined here

define('DEFAULT_PRODUCT_IMAGE', 'img/default.jpg');

// Fetch products
$sql = isset($_GET['c_id']) && is_numeric($_GET['c_id'])
    ? "SELECT * FROM products WHERE category_id = " . intval($_GET['c_id']) . " ORDER BY created_at DESC"
    : "SELECT * FROM products ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

// Fetch categories
$catQuery = "SELECT * FROM categories WHERE category_status = 1 ORDER BY category_name ASC";
$catResult = mysqli_query($conn, $catQuery);
?>

<!-- Breadcrumb -->
<div class="bg-light py-3 border-bottom">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0 bg-light p-0">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active text-black">Products</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>

<div class="site-section">
  <div class="container">
    <div class="row mb-5">
      <!-- Sidebar -->
      <div class="col-md-3 order-1 mb-5 mb-md-0">
        <div class="card mb-4 shadow-sm">
          <div class="card-header py-2">
            <h3 class="h6 text-uppercase mb-0 text-black">Categories</h3>
          </div>
          <ul class="list-group list-group-flush">
            <?php
            if ($catResult) {
                while ($cat = mysqli_fetch_assoc($catResult)) {
            ?>
              <li class="list-group-item px-3 py-2">
                <a href="products.php?c_id=<?php echo $cat['category_id']; ?>" class="d-flex text-decoration-none text-dark">
                  <span><?php echo htmlspecialchars($cat['category_name']); ?></span>
                </a>
              </li>
            <?php
                }
            } else {
                echo "<li class='list-group-item'>Unable to load categories.</li>";
            }
            ?>
          </ul>
        </div>

        <div class="card mb-4 shadow-sm">
          <div class="card-header py-2">
            <h3 class="h6 text-uppercase mb-0 text-black">Filter by Price</h3>
          </div>
          <div class="card-body">
            <div id="slider-range" class="border-primary mb-3"></div>
            <input type="text" id="amount" class="form-control border-0 pl-0 bg-white" disabled />
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="card-header py-2">
            <h3 class="h6 text-uppercase mb-0 text-black">Size</h3>
          </div>
          <ul class="list-group list-group-flush">
            <li class="list-group-item px-3 py-2"><a href="#" class="d-flex text-decoration-none text-dark"><span>Small</span></a></li>
            <li class="list-group-item px-3 py-2"><a href="#" class="d-flex text-decoration-none text-dark"><span>Medium</span></a></li>
            <li class="list-group-item px-3 py-2"><a href="#" class="d-flex text-decoration-none text-dark"><span>Large</span></a></li>
          </ul>
        </div>
      </div>
      <!-- /Sidebar -->

      <!-- Product List -->
      <div class="col-md-9 order-2">
        <div class="row mb-5">
          <?php
          if (!$result) {
              echo "<div class='col-12'><p class='text-danger'>Database error: " . htmlspecialchars(mysqli_error($conn)) . "</p></div>";
          } elseif (mysqli_num_rows($result) === 0) {
              echo "<div class='col-12'><p>No products found.</p></div>";
          } else {
              while ($row = mysqli_fetch_assoc($result)) {
                  $id    = $row['product_id'];
                  $name  = htmlspecialchars($row['name']);
                  $price = number_format($row['price'], 2);
                  $img   = htmlspecialchars($row['image']);
                  $imgPath = "img/" . $img;
                  if (empty($img) || !file_exists($imgPath)) {
                      $imgPath = DEFAULT_PRODUCT_IMAGE;
                  }
          ?>
          <div class="col-sm-6 col-lg-4 mb-4 d-flex align-items-stretch" data-aos="fade-up">
            <div class="card product-card shadow-sm w-100 border-0 h-100">
              <a href="product-single.php?id=<?php echo $id; ?>" class="card-img-top d-block bg-light position-relative" style="height:220px;overflow:hidden;">
                <img src="<?php echo $imgPath; ?>" alt="<?php echo $name; ?>" class="img-fluid h-100 w-100" style="object-fit:cover;transition:transform .3s;">
              </a>
              <div class="card-body d-flex flex-column justify-content-between p-3">
                <div>
                  <h5 class="card-title mb-2"><a href="product-single.php?id=<?php echo $id; ?>" class="text-dark text-decoration-none"><?php echo $name; ?></a></h5>
                  <p class="text-primary font-weight-bold mb-3">₹<?php echo $price; ?></p>
                </div>
                <button class="btn btn-outline-primary w-100 mt-auto">Add to Cart</button>
              </div>
            </div>
          </div>
          <?php
              }
          }
          ?>
        </div>
      </div>
      <!-- /Product List -->
    </div>

    <!-- Collections Section -->
    <div class="row">
      <div class="col-12">
        <div class="container py-5">
        <div class="site-section site-blocks-2">
          <div class="row justify-content-center text-center mb-5">
            <div class="col-md-7 site-section-heading pt-4">
              <h2>Collections</h2>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 col-md-6 col-lg-4 mb-5 mb-lg-0" data-aos="fade" data-aos-delay="100">
              <a class="block-2-item d-block bg-light rounded shadow-sm h-100 p-3 text-center text-decoration-none" href="#" style="transition:box-shadow .3s;">
                <figure class="image mb-3">
                  <img src="img/download.jpg" alt="Accessories" class="img-fluid rounded" style="object-fit:cover;max-height:250px;width:100%;">
                </figure>
                <div class="text">
                  <span class="text-uppercase small text-muted">Collections</span>
                  <h3 class="mt-2 mb-0">Laddu Gopal</h3>
                </div>
              </a>
            </div>
            <div class="col-sm-6 col-md-6 col-lg-4 mb-5 mb-lg-0" data-aos="fade" data-aos-delay="100">
              <a class="block-2-item d-block bg-light rounded shadow-sm h-100 p-3 text-center text-decoration-none" href="#" style="transition:box-shadow .3s;">
                <figure class="image mb-3">
                  <img src="img/186.jpg" alt="Zula Collection" class="img-fluid rounded" style="object-fit:cover;max-height:250px;width:100%;">
                </figure>
                <div class="text">
                  <span class="text-uppercase small text-muted">Collections</span>
                  <h3 class="mt-2 mb-0">Zula</h3>
                </div>
              </a>
            </div>
            <div class="col-sm-6 col-md-6 col-lg-4 mb-5 mb-lg-0" data-aos="fade" data-aos-delay="200">
              <a class="block-2-item d-block bg-light rounded shadow-sm h-100 p-3 text-center text-decoration-none" href="#" style="transition:box-shadow .3s;">
                <figure class="image mb-3">
                  <img src="img/188.jpg" alt="Poshakh Collection" class="img-fluid rounded" style="object-fit:cover;max-height:250px;width:100%;">
                </figure>
                <div class="text">
                  <span class="text-uppercase small text-muted">Collections</span>
                  <h3 class="mt-2 mb-0">Poshakh</h3>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
    <!-- /Collections Section -->

  </div>
</div>

<?php include("include/footer.php"); ?>
