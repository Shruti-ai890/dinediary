<?php
// Include database connection
include 'db_connection.php';

// Fetch the existing admin post details
$post_id = isset($_GET['id']) ? $_GET['id'] : null; // Use 'id' instead of 'post_id'



if (!$post_id) {
    die("Error: Post ID is missing in the URL.");
}

// Prepare SQL statement
$sql = "SELECT * FROM admin_posts WHERE post_id = ?";
$stmt = $conn->prepare($sql);

// Check if the query prepared successfully
if (!$stmt) {
    die("Error in SQL query: " . $conn->error);
}

// Bind and execute
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    die("No post found with ID $post_id");
}

// Fetch options for dropdowns
$cuisines = $conn->query("SELECT cuisine_name FROM cuisines")->fetch_all(MYSQLI_ASSOC);
$explore_options = $conn->query("SELECT explore_name FROM explore_options")->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT category_name FROM categories")->fetch_all(MYSQLI_ASSOC);

// Handle form submission if update button is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    // Retrieve updated post data
    $title = $_POST['title'];
    $description = $_POST['description'];
    $city = $_POST['city'];
    $area = $_POST['area'];
    $google_maps_link = $_POST['google_maps_link'];
    $cuisine_name = $_POST['cuisine_name'];
    $explore_name = $_POST['explore_name'];
    $category_name = $_POST['category_name'];

    // Prepare the update query for admin post
    $sql = "UPDATE admin_posts SET title = ?, description = ?, cuisine_name = ?, explore_name = ?, category_name = ?, city = ?, area = ?, google_maps_link = ? WHERE post_id = ?";
    $stmt = $conn->prepare($sql);

    // Check if the query prepared successfully
    if ($stmt === false) {
        die('Error in SQL preparation: ' . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("sssssssss", $title, $description, $cuisine_name, $explore_name, $category_name, $city, $area, $google_maps_link, $post_id);

    // Execute the query to update the post
    if ($stmt->execute()) {
        // Handle image upload if new images are provided
        if (!empty($_FILES['new_images']['name'][0])) {
            // Delete old images first
            $delete_images_sql = "DELETE FROM admin_post_images WHERE post_id = ?";
            $delete_stmt = $conn->prepare($delete_images_sql);
            $delete_stmt->bind_param("i", $post_id);
            $delete_stmt->execute();

            // Insert new images
            foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
                // Get image name without path
                $image_name = basename($_FILES['new_images']['name'][$key]);

                // Move the uploaded file to the 'uploads' folder
                $image_path = 'uploads/' . $image_name;
                move_uploaded_file($tmp_name, $image_path);

                // Insert image name (without the 'uploads/' prefix) into the database
                $insert_image_sql = "INSERT INTO admin_post_images (post_id, image_name) VALUES (?, ?)";
                $insert_image_stmt = $conn->prepare($insert_image_sql);
                $insert_image_stmt->bind_param("is", $post_id, $image_name); // Save only the image name
                $insert_image_stmt->execute();
            }
        }

        // Redirect with success message
        header("Location: edit_admin_post.php?id=$post_id&success=1");
        exit;
    } else {
        echo "Error updating post: " . $stmt->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin Post</title>
     <style>
        /* Your provided CSS styles */
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

        form input[type="text"],
        form input[type="url"],
        form textarea,
        form select,
        form input[type="file"] {
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

        form textarea {
            resize: vertical;
        }

        form input[type="file"] {
            padding: 5px;
        }

        form select {
            background-color: #fff;
        }

        form input[type="text"]:focus,
        form input[type="url"]:focus,
        form textarea:focus,
        form select:focus {
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


<div id="edit-post-container">
    <h2 style="font-size: 2rem; text-align: center;">Edit Admin Post</h2>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div id="success-message" style="color: green; margin-bottom: 10px;">
            Post updated successfully!
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('success-message').style.display = 'none';
            }, 3000);
        </script>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">

        <label for="title">Title:</label>
        <input type="text" id="title" name="title" value="<?php echo $post['title']; ?>" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?php echo $post['description']; ?></textarea>

        <label for="city">City:</label>
        <input type="text" id="city" name="city" value="<?php echo $post['city']; ?>" required>

        <label for="area">Area:</label>
        <input type="text" id="area" name="area" value="<?php echo $post['area']; ?>" required>

        <label for="google_maps_link">Google Maps Link:</label>
        <input type="url" id="google_maps_link" name="google_maps_link" value="<?php echo $post['google_maps_link']; ?>" required>

        <label for="cuisine_name">Cuisine Name:</label>
        <select id="cuisine_name" name="cuisine_name" required>
            <?php foreach ($cuisines as $cuisine): ?>
                <option value="<?php echo $cuisine['cuisine_name']; ?>" <?php echo ($cuisine['cuisine_name'] == $post['cuisine_name']) ? 'selected' : ''; ?>>
                    <?php echo $cuisine['cuisine_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="explore_name">Explore Name:</label>
        <select id="explore_name" name="explore_name" required>
            <?php foreach ($explore_options as $explore): ?>
                <option value="<?php echo $explore['explore_name']; ?>" <?php echo ($explore['explore_name'] == $post['explore_name']) ? 'selected' : ''; ?>>
                    <?php echo $explore['explore_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="category_name">Category Name:</label>
        <select id="category_name" name="category_name" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['category_name']; ?>" <?php echo ($category['category_name'] == $post['category_name']) ? 'selected' : ''; ?>>
                    <?php echo $category['category_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="new_images">Upload New Images:</label>
        <input type="file" id="new_images" name="new_images[]" multiple accept="image/*">

        <div style="margin-top: 20px;">
            <button type="submit" name="update">Update Post</button>

<a href="admin_dashboard.php?id=<?php echo $post_id; ?>" style="background-color: #DC143C; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease;" onmouseover="this.style.backgroundColor='#9B0F26'" onmouseout="this.style.backgroundColor='#DC143C'">Back</a>

        </div>
    </form>
</div>
</body>
</html>