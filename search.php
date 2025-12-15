<?php
include '../include/connection.php';
include 'inc/header.php';

$searchTerm = isset($_GET['query']) ? trim($_GET['query']) : '';
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Search Contacts</h1>
    </section>

    <section class="content">
        <form method="GET" class="mb-3">
            <div class="input-group" style="max-width: 400px;">
                <input type="text" name="query" class="form-control" placeholder="Search by name, email, or mobile" value="<?= htmlspecialchars($searchTerm) ?>" required>
                <span class="input-group-btn">
                    <button class="btn btn-primary" type="submit">Search</button>
                </span>
            </div>
        </form>

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
                        if ($searchTerm !== '') {
                            $sql = "SELECT * FROM contact WHERE c_name LIKE ? OR c_email LIKE ? OR c_mobileno LIKE ? ORDER BY c_id DESC";
                            $stmt = $conn->prepare($sql);

                            if ($stmt === false) {
                                echo "<tr><td colspan='8' class='text-danger'>Error preparing query: " . htmlspecialchars($conn->error) . "</td></tr>";
                            } else {
                                $likeTerm = "%{$searchTerm}%";
                                $stmt->bind_param("sss", $likeTerm, $likeTerm, $likeTerm);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                            <td>{$row['c_id']}</td>
                                            <td>{$row['c_name']}</td>
                                            <td>{$row['c_email']}</td>
                                            <td>{$row['c_mobileno']}</td>
                                            <td>{$row['c_message']}</td>
                                            <td>{$row['c_time']}</td>
                                            <td>{$row['c_status']}</td>
                                            <td>
                                                <a href='contact_edit.php?id={$row['c_id']}' class='btn btn-sm btn-warning'>Edit</a>
                                                <a href='contact_delete.php?id={$row['c_id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('Are you sure?')\">Delete</a>
                                            </td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8'>No results found for '<strong>" . htmlspecialchars($searchTerm) . "</strong>'</td></tr>";
                                }

                                $stmt->close();
                            }
                        } else {
                            echo "<tr><td colspan='8'>Please enter a search term.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<?php include 'inc/footer.php'; ?>
