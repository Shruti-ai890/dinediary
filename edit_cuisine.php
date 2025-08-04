<?php
include('db_connection.php');

// Fetch cuisine details from the database based on the passed ID
if (isset($_GET['id'])) {
    $cuisine_id = $_GET['id'];
    $query = "SELECT * FROM cuisines WHERE cuisine_id = '$cuisine_id'";
    $result = mysqli_query($conn, $query);
    $cuisine = mysqli_fetch_assoc($result);
} else {
    header("Location: admin_dashboard.php");
    exit;
}

// Handle the form submission to update cuisine
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cuisine_name'])) {
    $cuisine_name = $_POST['cuisine_name'];

    // Update the cuisine in the database
    $update_query = "UPDATE cuisines SET cuisine_name = '$cuisine_name' WHERE cuisine_id = '$cuisine_id'";
    $update_result = mysqli_query($conn, $update_query);

    // Show success message and remain on the same page
    if ($update_result) {
        $success_message = "Cuisine updated successfully!";
    } else {
        $error_message = "Error updating cuisine: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Cuisine</title>
<style>
form {
    width: 80%;
    margin: 20px auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

label {
    font-size: 1rem;
    color: #080808;
    margin-bottom: 8px;
    display: inline-block;
}

input[type="text"] {
    width: 100%;
    padding: 10px;
    margin: 5px 0;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
    font-size: 14px;
    color: #333;
    box-sizing: border-box;
}

input[type="text"]:focus {
    border-color: #007BFF;
    background-color: #fff;
    outline: none;
}

button[type="submit"] {
    background-color: #DC143C;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    width: 15%;
    margin-top: 10px;
}

button[type="submit"]:hover {
    background-color: #9B0F26;
}

/* Styling for the Back button */
.back-button {
    background-color: #DC143C;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: inline-block;
    margin-left: 10px;
}

.back-button:hover {
    background-color: #9B0F26;
}

.success-message {
    background-color: #4CAF50;
    color: white;
    padding: 10px;
    margin: 10px 0;
    text-align: center;
    border-radius: 5px;
    display: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    form {
        width: 90%;
    }
    button[type="submit"] {
        width: 100%;
    }
}

</style>
</head>
<body>
   <h2 style="font-size: 2rem; text-align: center;">Edit Cuisine</h2>

    <!-- Success or Error Message -->
    <?php if (isset($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php elseif (isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Edit Form -->
    <form method="POST">
        <input type="hidden" name="cuisine_id" value="<?php echo $cuisine['cuisine_id']; ?>">
        <label for="cuisine_name">Cuisine Name</label>
        <input type="text" name="cuisine_name" id="cuisine_name" value="<?php echo $cuisine['cuisine_name']; ?>" required>
        <button type="submit">Update Cuisine</button>
		
            <!-- Back Button -->
   <a href="admin_dashboard.php?id=<?php echo $_GET['id']; ?>" 
   style="background-color: #DC143C; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease;" 
   onmouseover="this.style.backgroundColor='#9B0F26'" 
   onmouseout="this.style.backgroundColor='#DC143C'">
   Back
</a>


    </form>

   

</body>
</html>
