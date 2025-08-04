<?php
include('db_connection.php'); // Database connection

if (isset($_GET['id'])) {
    $category_id = $_GET['id'];
    
    // Fetch the category details using the category ID
    $query = "SELECT * FROM categories WHERE category_id = '$category_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $category_name = $row['category_name'];
    }
}

// Update category logic
if (isset($_POST['update_category'])) {
    $new_category_name = $_POST['category_name'];
    
    // Update category name in the database
    $update_query = "UPDATE categories SET category_name = '$new_category_name' WHERE category_id = '$category_id'";
    if (mysqli_query($conn, $update_query)) {
        echo "<p>Category updated successfully!</p>";
    } else {
        echo "<p>Error updating category: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
<style>
.form-container {
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

   

    .success-message {
        background-color: #4CAF50;
        color: white;
        padding: 10px;
        margin: 10px 0;
        text-align: center;
        border-radius: 5px;
        display: none;
    }

    @media (max-width: 768px) {
        .form-container {
            width: 90%;
        }
        button[type="submit"] {
            width: 100%;
        }
    }

</style>
</head>
<body>

<div class="form-container">
    <h2 style="font-size: 2rem; text-align: center;">Edit Category</h2>
    <form method="POST" action="">
        <label for="category_name">Category Name:</label>
        <input type="text" id="category_name" name="category_name" value="<?php echo $category_name; ?>" required>
        
        <div class="button-group" style="display: flex; gap: 10px; justify-content: flex-start;">
            <button type="submit" class="update-btn" name="update_category" style="padding: 10px 20px; background-color: #DC143C; color: white; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#9B0F26'" onmouseout="this.style.backgroundColor='#DC143C'">
                Update
            </button>
            <a href="admin_dashboard.php" class="back-btn" style="padding: 10px 20px; background-color: #DC143C; color: white; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#9B0F26'" onmouseout="this.style.backgroundColor='#DC143C'">
                Back
            </a>
        </div>
    </form>
</div>
</body>
</html>
