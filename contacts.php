<?php
include '../include/connection.php';
include 'inc/header.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Contact Submissions</h1>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-body table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>Message</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM contact ORDER BY c_id DESC");
                        while ($row = $result->fetch_assoc()) {
                            // Status label logic
                            $status = $row['c_status'];
                            if ($status == 'Active') {
                                $statusLabel = "<span class='badge badge-success'>Active</span>";
                            } elseif ($status == 'Inactive') {
                                $statusLabel = "<span class='badge badge-secondary'>Inactive</span>";
                            } elseif ($status == 'Blocked') {
                                $statusLabel = "<span class='badge badge-danger'>Blocked</span>";
                            } else {
                                $statusLabel = "<span class='badge badge-warning'>Unknown</span>";
                            }

                            echo "<tr>
                                <td>{$row['c_id']}</td>
                                <td>{$row['c_name']}</td>
                                <td>{$row['c_email']}</td>
                                <td>{$row['c_mobileno']}</td>
                                <td>{$row['c_message']}</td>
                                <td>{$row['c_time']}</td>
                                <td>{$statusLabel}</td>
                                <td>
                                    <a href='contact_delete.php?id={$row['c_id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure you want to delete this entry?')\"><i class='fas fa-trash'></i> Delete</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>


<?php include 'inc/footer.php'; ?>