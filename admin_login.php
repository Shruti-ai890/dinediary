<?php
session_start();
include('db_connection.php');

// Initialize error message
$error_message = "";

// Admin Login Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin-login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error_message = "Invalid password. Please try again.";
        }
    } else {
        $error_message = "Admin username not found. Please try again.";
    }
}

?>

<!-- HTML and JavaScript below -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4); /* Black with opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            text-align: center;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<!-- Admin Login Form -->
<form action="admin_login.php" method="POST">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <button type="submit" name="admin-login">Login</button>
</form>

<!-- Error Message Modal -->
<div id="errorModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Error</h2>
        <p id="error-message"><?php echo $error_message; ?></p>
    </div>
</div>

<script>
    // Check if error message is set and show the modal
    <?php if ($error_message != "") { ?>
        // Display the modal with the error message
        document.getElementById('errorModal').style.display = 'block';
    <?php } ?>

    // Close the modal
    function closeModal() {
        document.getElementById('errorModal').style.display = 'none';
    }
</script>

</body>
</html>
