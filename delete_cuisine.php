<?php
// Include database connection
include('db_connection.php');

// Check if the cuisine ID is passed
if (isset($_GET['id'])) {
    $cuisine_id = intval($_GET['id']); // Sanitize input

    // Check if the cuisine exists
    $check_query = "SELECT * FROM cuisines WHERE cuisine_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $cuisine_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Begin transaction to delete the cuisine
        $conn->begin_transaction();

        try {
            // Delete the cuisine from cuisines table
            $delete_cuisine_query = "DELETE FROM cuisines WHERE cuisine_id = ?";
            $delete_cuisine_stmt = $conn->prepare($delete_cuisine_query);
            $delete_cuisine_stmt->bind_param("i", $cuisine_id);
            $delete_cuisine_stmt->execute();

            // Commit the transaction
            $conn->commit();

            // Redirect with success message
            header("Location: admin_dashboard.php?message=Cuisine%20deleted%20successfully");
            exit;
        } catch (Exception $e) {
            // Rollback the transaction on error
            $conn->rollback();

            // Log the error (optional) and redirect with failure message
            error_log("Error deleting cuisine: " . $e->getMessage());
            header("Location: admin_dashboard.php?message=Failed%20to%20delete%20cuisine");
            exit;
        }
    } else {
        // Cuisine not found
        header("Location: admin_dashboard.php?message=Cuisine%20not%20found");
        exit;
    }
} else {
    // Invalid request
    header("Location: admin_dashboard.php?message=Invalid%20request");
    exit;
}
?>
