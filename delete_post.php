<?php
// Include the database connection
include("db_connection.php");

if (isset($_GET['blog_id'])) {
    $blog_id = $_GET['blog_id'];
} else {
    echo "Invalid request.";
    exit;
}

// Fetch the blog post details to display
$query = "
    SELECT bp.*, bpi.image_path
    FROM blog_posts bp
    LEFT JOIN blog_post_images bpi ON bp.blog_id = bpi.blog_id
    WHERE bp.blog_id = ?
";
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

    // Redirect to user dashboard after deletion
    header("Location: user_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Post</title>

<style>
/* General Page Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        /* Main container to align form properly */
        .main-container {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 0;
        }

        /* Form container */
        .form-container {
            width: 100%;
            max-width: 800px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Form Fields Container */
        .form-fields {
            margin-bottom: 20px;
        }

        /* Header Styling */
        h1 {
            color: #e74c3c;
            font-size: 24px;
            margin-bottom: 20px;
        }

        /* Text Styling */
        p {
            font-size: 16px;
            color: #333;
            margin-bottom: 20px;
        }

        /* Image Styling */
        img {
            max-width: 100%;
            max-height: 300px;
            display: block;
            margin: 20px auto;
            border-radius: 8px;
        }

        /* Button Styling */
        button {
            padding: 10px 20px;
            background-color: #DC143C;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            display: inline-block;
        }

        button:hover {
            background-color: #9B0F26;
        }

        /* Cancel Button Styling */
        .cancel-button {
            background-color: #DC143C;
            margin-top: 10px;
        }

        .cancel-button:hover {
            background-color: #9B0F26;
        }

    </style>


</head>
<body>
    <h1>Are you sure you want to delete this post?</h1>

    <form method="POST">
        <p>Title: <?php echo htmlspecialchars($post['place_title']); ?></p>
        <p>Description: <?php echo nl2br(htmlspecialchars($post['description'])); ?></p>

        <?php if (isset($post['image_path']) && !empty($post['image_path'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image" width="100">
        <?php endif; ?>

        <button type="submit" name="delete">Yes, Delete it</button>
        
<div class="form-fields">
                    <button type="button" class="cancel-button" onclick="window.location.href='post_detail.php?blog_id=<?php echo $post['blog_id']; ?>'">Cancel</button>
                </div>

    </form>
</body>
</html>
