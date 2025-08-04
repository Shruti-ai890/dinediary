<?php
// Include database connection
include('db_connection.php');

// Check if the explore option ID is passed
if (isset($_GET['id'])) {
    $explore_id = intval($_GET['id']); // Sanitize input

    // Check if the explore option exists
    $check_query = "SELECT * FROM explore_options WHERE explore_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $explore_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Begin transaction to delete the explore option
        $conn->begin_transaction();

        try {
            // Delete the explore option from explore_options table
            $delete_explore_query = "DELETE FROM explore_options WHERE explore_id = ?";
            $delete_explore_stmt = $conn->prepare($delete_explore_query);
            $delete_explore_stmt->bind_param("i", $explore_id);
            $delete_explore_stmt->execute();

            // Commit the transaction
            $conn->commit();

            // Redirect with success message
            header("Location: admin_dashboard.php?message=Explore%20Option%20deleted%20successfully");
            exit;
        } catch (Exception $e) {
            // Rollback the transaction on error
            $conn->rollback();

            // Log the error (optional) and redirect with failure message
            error_log("Error deleting explore option: " . $e->getMessage());
            header("Location: admin_dashboard.php?message=Failed%20to%20delete%20explore%20option");
            exit;
        }
    } else {
        // Explore option not found
        header("Location: admin_dashboard.php?message=Explore%20Option%20not%20found");
        exit;
    }
} else {
    // Invalid request
    header("Location: admin_dashboard.php?message=Invalid%20request");
    exit;
}
?>
