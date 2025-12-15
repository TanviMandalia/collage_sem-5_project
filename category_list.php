<?php
session_start();
include("../include/connection.php");
include('inc/header.php');

// Fetch all categories with parent name
$sql = "SELECT c.category_id, c.category_name, c.category_status, c.category_image, c.parent_id,
               p.category_name AS parent_name
        FROM categories c
        LEFT JOIN categories p ON p.category_id = c.parent_id
        ORDER BY c.category_id DESC";
$rs = $conn->query($sql);
?>
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-end">
      <div>
        <h1>Categories</h1>
        <small class="text-muted">Manage all categories</small>
      </div>
      <div>
        <a href="category.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Category</a>
      </div>
    </div>
  </section>

  <?php if (!empty($_SESSION['msg'])): ?>
    <section class="content pt-0">
      <div class="container-fluid">
        <div class="alert <?php echo (isset($_SESSION['msg_type']) && $_SESSION['msg_type']==='success') ? 'alert-success' : ((isset($_SESSION['msg_type']) && $_SESSION['msg_type']==='warning') ? 'alert-warning' : 'alert-danger'); ?>">
          <?php echo htmlspecialchars($_SESSION['msg']); ?>
        </div>
      </div>
    </section>
    <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
  <?php endif; ?>

  <section class="content">
    <div class="container-fluid">
      <div class="card">
        <div class="card-body table-responsive">
          <table id="categoriesTable" class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Parent</th>
                <th>Status</th>
                <th>Image</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($rs && $rs->num_rows > 0): ?>
                <?php while ($row = $rs->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo (int)$row['category_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['parent_name'] ?? '—'); ?></td>
                    <td>
                      <span class="badge <?php echo ((int)$row['category_status'] === 1) ? 'bg-success' : 'bg-danger'; ?>">
                        <?php echo ((int)$row['category_status'] === 1) ? 'Active' : 'Inactive'; ?>
                      </span>
                    </td>
                    <td>
                      <?php if (!empty($row['category_image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($row['category_image']); ?>" alt="img" width="50" class="img-thumbnail">
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="edit_category.php?id=<?php echo (int)$row['category_id']; ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                      <a href="delete_category.php?id=<?php echo (int)$row['category_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?');"><i class="fas fa-trash"></i></a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="6" class="text-center">No categories found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- DataTables & init -->
<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/datatables/jquery.dataTables.min.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script>
  $(function(){
    $('#categoriesTable').DataTable({
      responsive: true,
      autoWidth: false,
      pageLength: 10,
      order: [[0, 'desc']]
    });
  });
</script>
<?php include('inc/footer.php'); ?>
