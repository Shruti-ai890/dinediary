<?php
require 'db_connection.php'; // Database connection

// Check if the post ID is passed
if (isset($_GET['id'])) {
    $post_id = intval($_GET['id']); // Sanitize input

    // Check if the post exists
    $check_query = "SELECT * FROM admin_posts WHERE post_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $post_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Begin transaction to delete post and associated images
        $conn->begin_transaction();

        try {
            // Delete associated images from admin_post_images
            $delete_images_query = "DELETE FROM admin_post_images WHERE post_id = ?";
            $delete_images_stmt = $conn->prepare($delete_images_query);
            $delete_images_stmt->bind_param("i", $post_id);
            $delete_images_stmt->execute();

            // Delete the post from admin_posts
            $delete_post_query = "DELETE FROM admin_posts WHERE post_id = ?";
            $delete_post_stmt = $conn->prepare($delete_post_query);
            $delete_post_stmt->bind_param("i", $post_id);
            $delete_post_stmt->execute();

            // Commit the transaction
            $conn->commit();

            // Redirect with success message
            header("Location: admin_dashboard.php?message=Post%20deleted%20successfully");
            exit;
        } catch (Exception $e) {
            // Rollback the transaction on error
            $conn->rollback();

            // Log the error (optional) and redirect with failure message
            error_log("Error deleting post: " . $e->getMessage());
            header("Location: admin_dashboard.php?message=Failed%20to%20delete%20post");
            exit;
        }
    } else {
        // Post not found
        header("Location: admin_dashboard.php?message=Post%20not%20found");
        exit;
    }
} else {
    // Invalid request
    header("Location: admin_dashboard.php?message=Invalid%20request");
    exit;
}
?>
