<?php
session_start();
require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the user ID from the session and the current password from the POST request
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];

    // Prepare a query to fetch the password from the database for the logged-in user
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if a user was found
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the current password
        if (password_verify($current_password, $user['password'])) {
            echo "valid";  // Send "valid" if the password is correct
        } else {
            echo "invalid"; // Send "invalid" if the password is incorrect
        }
    } else {
        echo "invalid"; // Send "invalid" if no user was found
    }
}
?>
