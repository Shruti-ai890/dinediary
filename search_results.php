<?php
include('db_connection.php'); // Database connection

// Get the search query from the GET request
$search_term = '%' . $_GET['query'] . '%';

// Prepare the SQL query for blog_posts
$sql_blog = "SELECT blog_posts.blog_id, blog_posts.place_title, blog_posts.description, blog_posts.cuisine_name, blog_posts.explore_name, blog_posts.category_name, blog_posts.city, blog_posts.area, blog_posts.google_maps_link, 
                    GROUP_CONCAT(DISTINCT blog_post_images.image_path) AS image_path, 'blog' AS post_type
             FROM blog_posts
             LEFT JOIN blog_post_images ON blog_posts.blog_id = blog_post_images.blog_id
             WHERE blog_posts.place_title LIKE ? OR blog_posts.description LIKE ? OR blog_posts.cuisine_name LIKE ? OR blog_posts.explore_name LIKE ? OR blog_posts.category_name LIKE ? OR blog_posts.city LIKE ? OR blog_posts.area LIKE ?
             GROUP BY blog_posts.blog_id";


$stmt_blog = $conn->prepare($sql_blog);
$stmt_blog->bind_param("sssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
$stmt_blog->execute();
$result_blog = $stmt_blog->get_result();

// Prepare the SQL query for admin_posts
$sql_admin = "SELECT admin_posts.post_id, admin_posts.title, admin_posts.description, admin_posts.cuisine_name, admin_posts.explore_name, admin_posts.category_name, admin_posts.city, admin_posts.area, admin_posts.google_maps_link, 
                     GROUP_CONCAT(DISTINCT admin_post_images.image_name) AS image_name, 'admin' AS post_type
              FROM admin_posts
              LEFT JOIN admin_post_images ON admin_posts.post_id = admin_post_images.post_id
              WHERE admin_posts.title LIKE ? OR admin_posts.description LIKE ? OR admin_posts.cuisine_name LIKE ? OR admin_posts.explore_name LIKE ? OR admin_posts.category_name LIKE ? OR admin_posts.city LIKE ? OR admin_posts.area LIKE ?
              GROUP BY admin_posts.post_id";


$stmt_admin = $conn->prepare($sql_admin);
$stmt_admin->bind_param("sssssss", $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();

// Combine the results from both tables
$posts = [];
while ($row = $result_blog->fetch_assoc()) {
    $posts[] = $row;
}
while ($row = $result_admin->fetch_assoc()) {
    $posts[] = $row;
}
?>
<html>
<head>
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
<header>
 <div class="back-buttons">
       <a href="index.php" class="back-btn">Back</a>
        </div>

<h1>Search Results </h1>
</header>
     <?php if (!empty($posts)) : ?>
        <div class="grid-container">
            <?php foreach ($posts as $post) : ?>
                <div class="grid-item">
                    <h3><?php echo htmlspecialchars($post['title'] ?? $post['place_title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <img src="uploads/<?php echo htmlspecialchars(explode(',', $post['image_name'] ?? $post['image_path'])[0], ENT_QUOTES, 'UTF-8'); ?>" alt="Post Image">
                    <p><?php echo htmlspecialchars(substr($post['description'], 0, 100), ENT_QUOTES, 'UTF-8'); ?>...</p>
                    <?php 
                    // Determine redirect URL based on the post type
                    if ($post['post_type'] === 'blog') {
                        $detail_page = 'view_user_detail.php';  // Blog posts
                        $param_name = 'blog_id';  // For blog posts, we pass the blog_id
                        $id = $post['blog_id'];  // Blog ID for blog posts
                    } else {
                        $detail_page = 'view_admin_post_detail.php';  // Admin posts
                        $param_name = 'post_id';  // For admin posts, we pass the post_id
                        $id = $post['post_id'];  // Post ID for admin posts
                    }
                    ?>
                    <a href="<?php echo $detail_page; ?>?<?php echo $param_name; ?>=<?php echo $id; ?>" class="read-more">Read More</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p>No posts found.</p>
    <?php endif; ?>
</body>
</html>