<?php
include '../include/connection.php';
include 'inc/header.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['c_name']);
    $email = trim($_POST['c_email']);
    $mobile = trim($_POST['c_mobileno']);
    $message = trim($_POST['c_message']);
    $status = trim($_POST['c_status']);

    $updateSql = "UPDATE contact SET c_name=?, c_email=?, c_mobileno=?, c_message=?, c_status=? WHERE c_id=?";
    $stmt = $conn->prepare($updateSql);

    if ($stmt === false) {
        $error = "Prepare failed: " . htmlspecialchars($conn->error);
    } else {
        $stmt->bind_param("sssssi", $name, $email, $mobile, $message, $status, $id);
        if ($stmt->execute()) {
            $success = "Contact updated successfully.";
        } else {
            $error = "Update failed: " . htmlspecialchars($stmt->error);
        }
        $stmt->close();
    }
}

// Fetch existing contact
$contact = null;
$selectSql = "SELECT * FROM contact WHERE c_id=?";
$stmt = $conn->prepare($selectSql);

if ($stmt === false) {
    $error = "Prepare failed while fetching contact: " . htmlspecialchars($conn->error);
} else {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $contact = $result->fetch_assoc();
    $stmt->close();
}
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Edit Contact</h1>
    </section>

    <section class="content">
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($contact): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="c_name" class="form-control" value="<?= htmlspecialchars($contact['c_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="c_email" class="form-control" value="<?= htmlspecialchars($contact['c_email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Mobile</label>
                    <input type="text" name="c_mobileno" class="form-control" value="<?= htmlspecialchars($contact['c_mobileno']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="c_message" class="form-control" required><?= htmlspecialchars($contact['c_message']) ?></textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="c_status" class="form-control">
                        <option value="New" <?= $contact['c_status'] === 'New' ? 'selected' : '' ?>>New</option>
                        <option value="Reviewed" <?= $contact['c_status'] === 'Reviewed' ? 'selected' : '' ?>>Reviewed</option>
                        <option value="Resolved" <?= $contact['c_status'] === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Update Contact</button>
                <a href="search.php" class="btn btn-default">Back</a>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">Contact not found or invalid ID.</div>
        <?php endif; ?>
    </section>
</div>

<?php include 'inc/footer.php'; ?>
