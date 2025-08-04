<?php
// Include database connection
include('db_connection.php');

// Check if the category ID is passed
if (isset($_GET['id'])) {
    $category_id = intval($_GET['id']); // Sanitize input

    // Check if the category exists
    $check_query = "SELECT * FROM categories WHERE category_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $category_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Begin transaction to delete the category
        $conn->begin_transaction();

        try {
            // Delete the category from categories table
            $delete_category_query = "DELETE FROM categories WHERE category_id = ?";
            $delete_category_stmt = $conn->prepare($delete_category_query);
            $delete_category_stmt->bind_param("i", $category_id);
            $delete_category_stmt->execute();

            // Commit the transaction
            $conn->commit();

            // Redirect with success message
            header("Location: admin_dashboard.php?message=Category%20deleted%20successfully");
            exit;
        } catch (Exception $e) {
            // Rollback the transaction on error
            $conn->rollback();

            // Log the error (optional) and redirect with failure message
            error_log("Error deleting category: " . $e->getMessage());
            header("Location: admin_dashboard.php?message=Failed%20to%20delete%20category");
            exit;
        }
    } else {
        // Category not found
        header("Location: admin_dashboard.php?message=Category%20not%20found");
        exit;
    }
} else {
    // Invalid request
    header("Location: admin_dashboard.php?message=Invalid%20request");
    exit;
}
?>
