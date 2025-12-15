<?php
// filepath: d:\wamp64\www\project batch\my_edits\admin\delete_subcategory.php
session_start();
include("../include/connection.php"); // Ensure this initializes $conn

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $subcategory_id = intval($_GET['id']);

    // Fetch image name to delete from server
    $img_sql = "SELECT category_image FROM categories WHERE category_id = ? AND parent_id IS NOT NULL";
    $img_stmt = mysqli_prepare($conn, $img_sql);
    mysqli_stmt_bind_param($img_stmt, "i", $subcategory_id);
    mysqli_stmt_execute($img_stmt);
    mysqli_stmt_bind_result($img_stmt, $image_name);
    mysqli_stmt_fetch($img_stmt);
    mysqli_stmt_close($img_stmt);

    // Delete subcategory from database
    $del_sql = "DELETE FROM categories WHERE category_id = ? AND parent_id IS NOT NULL";
    $del_stmt = mysqli_prepare($conn, $del_sql);
    mysqli_stmt_bind_param($del_stmt, "i", $subcategory_id);

    if (mysqli_stmt_execute($del_stmt)) {
        // Delete image file if it exists
        if (!empty($image_name)) {
            $image_path = "../uploads/" . $image_name;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        $_SESSION['msg'] = "Subcategory deleted successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['msg'] = "Error deleting subcategory: " . mysqli_error($conn);
        $_SESSION['msg_type'] = "danger";
    }

    mysqli_stmt_close($del_stmt);
} else {
    $_SESSION['msg'] = "Invalid subcategory ID.";
    $_SESSION['msg_type'] = "danger";
}

header("Location: view_subcategories.php");
exit;
