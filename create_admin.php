<?php
// Include database connection
include('db_connection.php');

// Sample admin credentials
$username = 'Shruti';
$password = '1234'; // Replace with the actual password

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert the admin credentials into the database
$sql = "INSERT INTO admin (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $hashed_password);

// Execute the query and check for success
if ($stmt->execute()) {
    echo "Admin user created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
