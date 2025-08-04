<?php
session_start();

// Include database connection
require 'db_connection.php';

// Fetch user details
$user = null; // Ensure it's initialized to avoid undefined variable error.
$id = $_SESSION['user_id'];  // Using 'id' instead of 'user_id'
$query = "SELECT name, email, password FROM users WHERE id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = $_POST['name'];
    $new_email = $_POST['email'];
    $current_password = $_POST['current_password'];

    // Verify Current Password
    if (!password_verify($current_password, $user['password'])) {
        $error = "Incorrect current password!";
    } else {
        // If valid, check for new password update
        if (!empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password !== $confirm_password) {
                $error = "New passwords do not match!";
            } else {
                // Hash the new password before saving
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $update_query = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("sssi", $new_name, $new_email, $hashed_password, $user_id);
            }
        } else {
            // Update only name and email if no new password is provided
            $update_query = "UPDATE users SET name = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssi", $new_name, $new_email, $user_id);
        }

        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            $user['name'] = $new_name;
            $user['email'] = $new_email;
        } else {
            $error = "Error updating profile.";
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Assuming user ID is stored in session
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch the current password from the database
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($stored_password);
    $stmt->fetch();

    // Verify the current password
    if (!password_verify($current_password, $stored_password)) {
        $error = "Incorrect current password.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the database
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $user_id);

        if ($update_stmt->execute()) {
            $success = "Password updated successfully!";
        } else {
            $error = "Failed to update password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <style>
        .profile-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .profile-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-actions {
            display: flex;
            justify-content: space-between;
        }
        .form-actions button {
            padding: 10px 20px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-actions .edit-btn {
            background-color: #DC143C;
            color: #fff;
        }
        .form-actions .save-btn {
            background-color: #DC143C;
            color: #fff;
        }
        .form-actions .cancel-btn {
            background-color:  #DC143C;
            color: #fff;
        }
        .alert {
            margin-top: 10px;
            padding: 10px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert.success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert.error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
 .hidden { display: none; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
<div class="profile-container">
    <h2>My Profile</h2>
    <?php if (isset($success)): ?>
        <div class="alert success"><?= $success ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>
   <form method="POST" id="profileForm">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" readonly>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
        </div>
        
        <!-- Current Password Field -->
        <div class="form-group" id="currentPasswordField" style="display: none;">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password">
            <small id="passwordError" style="color: red; display: none;">Incorrect Password</small>
        </div>

        <!-- New Password & Confirm Password Fields -->
        <div id="passwordFields" style="display: none;">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="edit-btn" id="editButton">Edit</button>
            <button type="submit" class="save-btn" id="saveButton" style="display: none;">Save Changes</button>
            <button type="button" class="cancel-btn" id="cancelButton">Cancel</button>
        </div>
    </form>
</div>

<script>
const editButton = document.getElementById('editButton');
    const saveButton = document.getElementById('saveButton');
    const cancelButton = document.getElementById('cancelButton');
    const nameField = document.getElementById('name');
    const emailField = document.getElementById('email');
    const currentPasswordField = document.getElementById('currentPasswordField');
    const passwordFields = document.getElementById('passwordFields');
    const passwordError = document.getElementById('passwordError');
    const currentPasswordInput = document.getElementById('current_password');

    editButton.addEventListener('click', () => {
        // Make the name and email editable
        nameField.readOnly = false;
        emailField.readOnly = false;
        
        // Show the current password and new password fields
        currentPasswordField.style.display = 'block';
        passwordFields.style.display = 'block';  // Display both new password and confirm password
        
        // Hide the edit button and show the save/cancel buttons
        editButton.style.display = 'none';
        saveButton.style.display = 'inline-block';
        cancelButton.style.display = 'inline-block';
    });

    currentPasswordInput.addEventListener('input', function() {
        let currentPassword = this.value;

        // Send a request to validate the password
        fetch('check_password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'current_password=' + encodeURIComponent(currentPassword)
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'valid') {
                passwordFields.style.display = 'block';  // Show New Password Fields
                passwordError.style.display = 'none';
            } else {
                passwordFields.style.display = 'none';  // Hide New Password Fields
                passwordError.style.display = 'block';  // Show Error Message
            }
        });
    });

    cancelButton.addEventListener('click', () => {
        // Redirect to user dashboard
        window.location.href = 'user_dashboard.php';
    });

</script>
</body>
</html>
