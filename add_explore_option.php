<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php'); // Redirect to login page if not logged in
    exit;
}

// Include the database connection file
include('db_connection.php');

// Handle Add Explore Option Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_explore_option'])) {
    $option_name = $_POST['option_name'];

    // Prepare the SQL query to insert the new explore option
    $stmt = $conn->prepare("INSERT INTO explore_options (option_name) VALUES (?)");
    $stmt->bind_param("s", $option_name);

    if ($stmt->execute()) {
        echo "<p>Explore option added successfully!</p>";
    } else {
        echo "<p>Error: Could not add explore option.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Explore Option - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #C70039;
            color: white;
            text-align: center;
            padding: 10px 0;
        }

        header h1 {
            margin: 0;
            font-size: 2em;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #DC143C;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #9B0F26;
        }

        .form-container {
            width: 50%;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        .form-container button {
            width: 100%;
            padding: 12px;
            background-color: #C70039;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #9B0F26;
        }
    </style>
</head>
<body>

<!-- Logout Button -->
<a href="logout.php" class="logout-btn">Logout</a>

<!-- Header -->
<header>
    <h1>Add Explore Option</h1>
</header>

<!-- Add Explore Option Form -->
<div class="form-container">
    <form method="POST" action="">
        <input type="text" name="option_name" placeholder="Option Name" required>
        <button type="submit" name="add_explore_option">Add Explore Option</button>
    </form>
</div>

</body>
</html>
