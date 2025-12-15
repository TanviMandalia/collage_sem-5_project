<?php
// Database connection
include("../include/connection.php"); // Ensure this file exists and initializes $conn

// Query to fetch subcategories
$sql = "SELECT c.*, p.category_name AS parent_category
        FROM categories c
        LEFT JOIN categories p ON c.parent_id = p.category_id
        WHERE c.parent_id IS NOT NULL";
$result = mysqli_query($conn, $sql);

// Handle query errors
if (!$result) {
    echo "<div class='alert alert-danger'>An error occurred while fetching subcategories. Please try again later.</div>";
    exit;
}

include('inc/header.php'); // Include Admin Panel header
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Subcategories List</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">Subcategories</li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <!-- Main Content -->
  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Subcategories</h3>
          <div class="card-tools">
            <a href="add_subcategory.php" class="btn btn-success btn-sm">Add Subcategory</a>
          </div>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-striped">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Subcategory Name</th>
                <th>Parent Category</th>
                <th>Description</th>
                <th>Status</th>
                <th>Image</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $i = 1;
              if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) { ?>
                  <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['parent_category']); ?></td>
                    <td><?php echo htmlspecialchars($row['catagory_desc']); ?></td>
                    <td>
                      <?php if ($row['category_status'] == 1) { ?>
                        <span class="badge bg-success">Active</span>
                      <?php } else { ?>
                        <span class="badge bg-danger">Inactive</span>
                      <?php } ?>
                    </td>
                    <td>
                      <?php if (!empty($row['category_image'])) { ?>
                        <img src="../img/<?php echo htmlspecialchars($row['category_image']); ?>" width="60" height="60" class="rounded">
                      <?php } else { ?>
                        No Image
                      <?php } ?>
                    </td>
                    <td>
                      <a href="edit_subcategory.php?id=<?php echo $row['category_id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                      <a href="delete_subcategory.php?id=<?php echo $row['category_id']; ?>" 
                         class="btn btn-danger btn-sm"
                         onclick="return confirm('Are you sure you want to delete this subcategory?');">
                         Delete
                      </a>
                    </td>
                  </tr>
              <?php } } else { ?>
                <tr>
                  <td colspan="7" class="text-center">No Subcategories Found</td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include('inc/footer.php'); // Include Admin Panel footer ?>
