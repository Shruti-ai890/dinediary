<?php
// Include the database connection
include("db_connection.php");
session_start(); // Start session to store success message

// Check if the blog_id is provided
if (isset($_GET['blog_id'])) {
    $blog_id = $_GET['blog_id'];
} else {
    echo "Invalid request.";
    exit;
}

// Fetch the existing blog post details
$query = "SELECT * FROM blog_posts WHERE blog_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

// Check if the post exists
if (!$post) {
    echo "Post not found!";
    exit;
}

// Handle form submission for updating the post
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the updated post data from the form
    $title = $_POST['title'];
    $description = $_POST['description'];
    $city = $_POST['city'];
    $area = $_POST['area'];
    $google_maps_link = $_POST['google_maps_link'];
    $cuisine_id = $_POST['cuisine'];
    $explore_id = $_POST['explore'];
    $category_id = $_POST['category'];
    
    // Handle image upload
    $image_path = $_FILES['image']['name'];
    if ($image_path) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image_path);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    } else {
        // Keep the existing image if no new image is uploaded
        $image_path = $post['image_path']; // existing image from DB
    }

    // Update the blog post in the database
    $update_query = "UPDATE blog_posts SET place_title = ?, description = ?, city = ?, area = ?, google_maps_link = ?, cuisine_id = ?, explore_id = ?, category_id = ? WHERE blog_id = ?";
    $stmt_update = $conn->prepare($update_query);
    $stmt_update->bind_param("ssssssssi", $title, $description, $city, $area, $google_maps_link, $cuisine_id, $explore_id, $category_id, $blog_id);
    $stmt_update->execute();

    // Update the image in blog_post_images table if a new image was uploaded
    if ($image_path) {
        $image_update_query = "UPDATE blog_post_images SET image_path = ? WHERE blog_id = ?";
        $stmt_image_update = $conn->prepare($image_update_query);
        $stmt_image_update->bind_param("si", $image_path, $blog_id);
        $stmt_image_update->execute();
    }

    // Set a success message
    $_SESSION['success_message'] = "Post updated successfully!";

    // Redirect to the updated post page after successful update
    header("Location: update_post.php?blog_id=" . $blog_id); // Refresh page to show success message
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post</title>
</head>
<body>

<h1>Edit Post</h1>

<!-- Display success message if set -->
<?php
if (isset($_SESSION['success_message'])) {
    echo "<p style='color: green;'>" . $_SESSION['success_message'] . "</p>";
    unset($_SESSION['success_message']); // Remove success message after displaying it
}
?>

<!-- Form to update post -->
<form action="" method="POST" enctype="multipart/form-data">
    <div>
        <label for="title">Title of the Place:</label>
        <input type="text" name="title" id="title" value="<?php echo $post['place_title']; ?>" required>
    </div>

    <div>
        <label for="description">Description:</label>
        <textarea name="description" id="description" rows="5" required><?php echo $post['description']; ?></textarea>
    </div>

    <div>
        <label for="city">City:</label>
        <input type="text" name="city" id="city" value="<?php echo $post['city']; ?>" required>
    </div>

    <div>
        <label for="area">Area:</label>
        <input type="text" name="area" id="area" value="<?php echo $post['area']; ?>" required>
    </div>

    <div>
        <label for="google_maps_link">Google Maps Link:</label>
        <input type="url" name="google_maps_link" id="google_maps_link" value="<?php echo $post['google_maps_link']; ?>" required>
    </div>

    <div>
        <label for="image">Upload New Image (Optional):</label>
        <input type="file" name="image" id="image" accept="image/*">
        <!-- Display current image if exists -->
        <?php if ($post['image_path']): ?>
            <div><img src="uploads/<?php echo $post['image_path']; ?>" alt="Current Image" width="100"></div>
        <?php endif; ?>
    </div>

    <div>
        <label for="cuisine">Cuisine:</label>
        <select name="cuisine" id="cuisine" required>
            <option value="">Select Cuisine</option>
            <?php
            // Fetch and display cuisine options
            $result_cuisine = mysqli_query($conn, "SELECT * FROM cuisines");
            while ($row = mysqli_fetch_assoc($result_cuisine)) {
                $selected = ($row['cuisine_id'] == $post['cuisine_id']) ? "selected" : "";
                echo "<option value='{$row['cuisine_id']}' $selected>{$row['cuisine_name']}</option>";
            }
            ?>
        </select>
    </div>

    <div>
        <label for="explore">Explore:</label>
        <select name="explore" id="explore" required>
            <option value="">Select Explore Option</option>
            <?php
            // Fetch and display explore options
            $result_explore = mysqli_query($conn, "SELECT * FROM explore_options");
            while ($row = mysqli_fetch_assoc($result_explore)) {
                $selected = ($row['explore_id'] == $post['explore_id']) ? "selected" : "";
                echo "<option value='{$row['explore_id']}' $selected>{$row['explore_name']}</option>";
            }
            ?>
        </select>
    </div>

    <div>
        <label for="category">Category:</label>
        <select name="category" id="category" required>
            <option value="">Select Category</option>
            <?php
            // Fetch and display category options
            $result_category = mysqli_query($conn, "SELECT * FROM categories");
            while ($row = mysqli_fetch_assoc($result_category)) {
                $selected = ($row['category_id'] == $post['category_id']) ? "selected" : "";
                echo "<option value='{$row['category_id']}' $selected>{$row['category_name']}</option>";
            }
            ?>
        </select>
    </div>

    <div>
        <button type="submit">Update Post</button>
    </div>
</form>

</body>
</html>
