<?php
include('db_connection.php'); // Corrected the database connection file

// Get the explore_id from the URL
if (isset($_GET['id'])) {
    $explore_id = $_GET['id'];

    // Fetch the explore name for editing
    $query = "SELECT * FROM explore_options WHERE explore_id = '$explore_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        echo "Explore item not found!";
        exit;
    }
} else {
    echo "No explore id provided!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update the explore name
    $new_explore_name = $_POST['explore_name'];

    $update_query = "UPDATE explore_options SET explore_name = '$new_explore_name' WHERE explore_id = '$explore_id'";
    if (mysqli_query($conn, $update_query)) {
        $success_message = "Explore name updated successfully!";
    } else {
        $error_message = "Error updating explore name!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Explore</title>
    <style>
.form-container {
    max-width: 600px; /* Adjust the width as per your need */
    margin: 0 auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.form-container input[type="text"] {
    width: 100%; /* Makes the input field take full width */
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.form-container .button-group {
    display: flex;
    gap: 10px; /* Adjusts the gap between buttons */
    justify-content: flex-start;
}

.form-container .button-group a {
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.form-container .button-group a.update-btn {
    background-color: #4CAF50;
    color: white;
}

.form-container .button-group a.update-btn:hover {
    background-color: #45a049;
}

.form-container .button-group a.back-btn {
    background-color: #DC143C;
    color: white;
}

.form-container .button-group a.back-btn:hover {
    background-color: #9B0F26;
}

    </style>
</head>
<body>
    <div class="form-container">
        <h2 style="font-size: 2rem; text-align: center;">Edit Explore Options</h2>

        <?php if (isset($success_message)) { ?>
            <p style="color: green;"><?php echo $success_message; ?></p>
        <?php } elseif (isset($error_message)) { ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php } ?>

        <form action="edit_explore.php?id=<?php echo $explore_id; ?>" method="POST">
            <input type="text" name="explore_name" value="<?php echo $row['explore_name']; ?>" required><button type="submit" class="update-btn" name="update_explore" style="padding: 10px 20px; background-color: #DC143C; color: white; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#9B0F26'" onmouseout="this.style.backgroundColor='#DC143C'">
                Update
            </button>
            <a href="admin_dashboard.php" class="back-btn" style="padding: 10px 20px; background-color: #DC143C; color: white; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#9B0F26'" onmouseout="this.style.backgroundColor='#DC143C'">
                Back
            </a>
        </form>

        
    </div>
</body>
</html>
