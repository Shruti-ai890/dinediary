<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect to login page if not logged in
    exit;
}

$user_id = $_SESSION['user_id']; // Get the logged-in user ID

// Include the database connection file
include('db_connection.php');


// Display success message if set in session
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Unset after displaying
}


// Query to fetch all posts of the logged-in user
$query = "SELECT * FROM blog_posts WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle post deletion if the delete action is set
if (isset($_GET['delete']) && $_GET['delete'] == 'true' && isset($_GET['blog_id'])) {
    $blog_id = $_GET['blog_id'];

    // Fetch the blog post details to delete the post
    $query = "SELECT * FROM blog_posts WHERE blog_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    // Check if the post exists
    if ($post) {
        // Delete the image from the uploads folder if it exists
        if (isset($post['image_path'])) {
            $image_path = "uploads/" . $post['image_path'];
            if (file_exists($image_path)) {
                unlink($image_path);  // Delete the image file
            }
        }

        // Delete the image record from the blog_post_images table
        $delete_image_query = "DELETE FROM blog_post_images WHERE blog_id = ?";
        $stmt_delete_image = $conn->prepare($delete_image_query);
        $stmt_delete_image->bind_param("i", $blog_id);
        $stmt_delete_image->execute();

        // Delete the blog post
        $delete_post_query = "DELETE FROM blog_posts WHERE blog_id = ?";
        $stmt_delete_post = $conn->prepare($delete_post_query);
        $stmt_delete_post->bind_param("i", $blog_id);
        $stmt_delete_post->execute();

        // Redirect to user_dashboard.php with a success flag
        header("Location: user_dashboard.php?success=true");
        exit;
    } else {
        echo "Post not found!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - DineDiary</title>
    
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
            font-size: 14px;
            text-decoration: none;
            border-radius: 5px;
        }

        .logout-btn:hover {
            background-color: #9B0F26;
        }

        .header-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .header-buttons a {
            background-color: #DC143C;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        .header-buttons a:hover {
            background-color: #9B0F26;
        }

       .post-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px; /* Adds spacing between grid items */
    padding: 10px; /* Adds padding around the grid container */
}

.post-item {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.post-item img {
    width: 200px;        /* Fixed width */
    height: 200px;       /* Fixed height */
    object-fit: cover;   /* Maintains the aspect ratio */
    border-radius: 8px;  /* Optional: For rounded corners */
}



        .post-item h3 {
            font-size: 18px;
            margin: 15px 10px 5px;
            color: #333;
        }

        .post-item p {
            font-size: 14px;
            margin: 10px;
            color: #666;
        }

        .post-item a {
            display: inline-block;
            margin: 10px;
            padding: 8px 15px;
            background-color: #C70039;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .post-item a:hover {
            background-color: #9B0F26;
        }

.success-message {
    background-color: #4CAF50;
    color: white;
    padding: 10px;
    margin: 10px 0;
    text-align: center;
    border-radius: 5px;
    display: block; /* Ensure it's visible when set to display */
}


    </style>
</head>
<body>

<!-- Logout Button -->
<a href="logout.php" class="logout-btn">Logout</a>

<!-- Header -->
<header>
    <h1>User Dashboard</h1>

    <!-- Button Container inside Header -->
    <div class="header-buttons">
        <a href="my_profile.php">My Profile</a>
        <a href="add_blog_post.php">Add Post</a>
         <a href="?view=all_posts">All Posts</a>
    </div>
</header>


<?php if (isset($_GET['deletion']) && $_GET['deletion'] === 'success'): ?>
    <div id="success-message" class="success-message">
        Post deleted successfully!
    </div>
    <script>
        setTimeout(function() {
            var successMessage = document.getElementById('success-message');
            if (successMessage) {
                successMessage.style.display = 'none';
            }
        }, 3000); // 3 seconds
    </script>
<?php endif; ?>

<!-- Post Grid -->
<div class="post-grid">
    <?php while ($post = $result->fetch_assoc()): ?>
        <div class="post-item">
            <h3><?php echo htmlspecialchars($post['place_title']); ?></h3>
            <?php
            // Fetch image from the blog_post_images table
            $imageQuery = "SELECT image_path FROM blog_post_images WHERE blog_id = ? LIMIT 1";
            $imageStmt = $conn->prepare($imageQuery);
            $imageStmt->bind_param("i", $post['blog_id']);
            $imageStmt->execute();
            $imageResult = $imageStmt->get_result();
            $image = $imageResult->fetch_assoc();
            
            // Check if image exists
            $imagePath = isset($image['image_path']) && !empty($image['image_path']) ? 'uploads/' . $image['image_path'] : 'path/to/default-image.jpg';
            ?>
            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Post Image">
            <p><?php echo substr(htmlspecialchars($post['description']), 0, 100) . '...'; ?></p>
            <a href="post_detail.php?blog_id=<?php echo $post['blog_id']; ?>">Read More</a>
        </div>
    <?php endwhile; ?>
</div>






</body>
</html>
