<?php
include 'db_connection.php'; // Include your database connection file

// Get the cuisine_id from the query string
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $cuisine_id = $_GET['id'];

    // Fetch the cuisine name for display
    $cuisine_query = "SELECT cuisine_name FROM cuisines WHERE cuisine_id = ?";
    $stmt = $conn->prepare($cuisine_query);
    $stmt->bind_param("i", $cuisine_id);
    $stmt->execute();
    $cuisine_result = $stmt->get_result();
    $cuisine_name = $cuisine_result->fetch_assoc()['cuisine_name'] ?? 'Unknown Cuisine';
    $stmt->close();

    // Fetch posts from admin_posts and blog_posts based on the cuisine name
    $query = "(
    SELECT 
        ap.post_id AS id, 
        ap.title, 
        ap.description, 
        MIN(api.image_name) AS image, 
        'admin' AS post_type
    FROM 
        admin_posts ap 
    LEFT JOIN 
        admin_post_images api 
    ON 
        ap.post_id = api.post_id 
    WHERE 
        ap.cuisine_name = ?
    GROUP BY 
        ap.post_id
)
UNION ALL
(
    SELECT 
        bp.blog_id AS id, 
        bp.place_title AS title, 
        bp.description, 
        MIN(bpi.image_path) AS image, 
        'blog' AS post_type
    FROM 
        blog_posts bp 
    LEFT JOIN 
        blog_post_images bpi 
    ON 
        bp.blog_id = bpi.blog_id 
    WHERE 
        bp.cuisine_name = ?
    GROUP BY 
        bp.blog_id
)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $cuisine_name, $cuisine_name);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all posts
    $posts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    die("Invalid cuisine ID.");
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Posts by Cuisine</title>
    <style>
   body {
            margin: 0;
            font-family: Arial, sans-serif;
        }


header {
    background-color: #C70039;
    color: white;
    text-align: center;
    padding: 30px 0;
    position: relative;
    width: 100%; /* Ensure the header takes full width */
    margin: 0; /* Remove any default margin */
    box-sizing: border-box; /* Ensure padding does not affect width */
}

header h1 {
    margin: 0;
    font-size: 1.8em;
}

header .back-buttons {
    position: absolute;
    top: 10px;  /* Adjust as needed to align the button properly */
    left: 20px;
}

header .back-btn {
    color: white;
    text-decoration: none;
    background-color: #DC143C;
    padding: 5px 10px;
    border-radius: 5px;
    transition: background-color 0.3s;
    font-size: 1em;
}

header .back-btn:hover {
    background-color: #9B0F26;
}

       

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .grid-item {
            border: 1px solid #ccc;
            border-radius: 8px;
            overflow: hidden;
            text-align: center;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
        }

        .grid-item:hover {
            transform: scale(1.05);
        }

        .grid-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .grid-item h3 {
            margin: 10px 0;
            font-size: 1.5em;
        }

        .grid-item p {
            padding: 0 10px;
            font-size: 1em;
            color: #555;
        }

        .grid-item .read-more {
            display: inline-block;
            margin: 10px 0;
            padding: 10px 20px;
            background-color: #DC143C;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1em;
            transition: background-color 0.3s;
        }

        .grid-item .read-more:hover {
            background-color: #9B0F26;
        }
    </style>
</head>
<body>
     <!-- Header with Cuisine Name -->
    <header>

 <div class="back-buttons">
       <a href="index.php" class="back-btn">Back</a>
        </div>


        <h1>Posts for Cuisine: <?php echo htmlspecialchars($cuisine_name, ENT_QUOTES, 'UTF-8'); ?></h1>
    </header>

  <?php if (!empty($posts)) : ?>
    <div class="grid-container">
        <?php foreach ($posts as $post) : ?>
            <div class="grid-item">
                <h3><?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <img src="uploads/<?php echo htmlspecialchars($post['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?>">
                <p><?php echo htmlspecialchars(substr($post['description'], 0, 100), ENT_QUOTES, 'UTF-8'); ?>...</p>
                <?php 
                // Determine redirect URL based on the post type
                if ($post['post_type'] === 'blog') {
                    $detail_page = 'view_user_detail.php';  // Blog posts
                    $param_name = 'blog_id';  // For blog posts, we pass the blog_id
                } else {
                    $detail_page = 'view_admin_post_detail.php';  // Admin posts
                    $param_name = 'post_id';  // For admin posts, we pass the post_id
                }
                ?>
                <a href="<?php echo $detail_page; ?>?<?php echo $param_name; ?>=<?php echo $post['id']; ?>" class="read-more">Read More</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <p>No posts found for this cuisine.</p>
<?php endif; ?>
</body>
</html>
