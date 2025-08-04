<?php
// Include the database connection
include('db_connection.php');

// Get the user ID from the URL
$user_id = isset($_GET['id']) ? $_GET['id'] : 0;



// Initialize the search query
$query = '';

// Check if the search form is submitted
if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Sanitize the query to prevent SQL injection
    $search_query = mysqli_real_escape_string($conn, $query);

    // SQL query to fetch blog posts with the search query
    $sql = "SELECT blog_posts.blog_id, blog_posts.place_title, blog_posts.description, blog_posts.cuisine_name,
            blog_posts.explore_name, blog_posts.category_name, blog_posts.city, blog_posts.area, 
            blog_posts.google_maps_link, blog_post_images.image_path
            FROM blog_posts
            LEFT JOIN blog_post_images ON blog_posts.blog_id = blog_post_images.blog_id
            WHERE (blog_posts.place_title LIKE '%$search_query%' 
            OR blog_posts.cuisine_name LIKE '%$search_query%' 
            OR blog_posts.category_name LIKE '%$search_query%' 
            OR blog_posts.explore_name LIKE '%$search_query%' 
            OR blog_posts.city LIKE '%$search_query%' 
            OR blog_posts.area LIKE '%$search_query%')
            AND blog_posts.user_id = $user_id
            GROUP BY blog_posts.blog_id";
} else {
    // Default query to fetch all posts if no search is performed
    $sql = "SELECT blog_posts.blog_id, blog_posts.place_title, blog_posts.description, blog_posts.cuisine_name,
            blog_posts.explore_name, blog_posts.category_name, blog_posts.city, blog_posts.area, 
            blog_posts.google_maps_link, blog_post_images.image_path
            FROM blog_posts
            LEFT JOIN blog_post_images ON blog_posts.blog_id = blog_post_images.blog_id
            WHERE blog_posts.user_id = $user_id
            GROUP BY blog_posts.blog_id";
}

// Execute the query and check for errors
$result = mysqli_query($conn, $sql);
if (!$result) {
    echo "Error: " . mysqli_error($conn);
    exit;
}

// Handle deletion of a post
if (isset($_POST['delete_blog_id'])) {
    $blog_id_to_delete = $_POST['delete_blog_id'];
    // Delete the post images first (if any)
    $delete_image_query = "DELETE FROM blog_post_images WHERE blog_id = $blog_id_to_delete";
    mysqli_query($conn, $delete_image_query);
    
    // Delete the blog post
    $delete_blog_query = "DELETE FROM blog_posts WHERE blog_id = $blog_id_to_delete";
    if (mysqli_query($conn, $delete_blog_query)) {
        echo "Post deleted successfully.";
        // Redirect after deletion to avoid re-submitting the form
        header('Location: user_post_detail.php?id=' . $user_id);
        exit;
    } else {
        echo "Error deleting post: " . mysqli_error($conn);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Posts</title>
    <style>


/* Form Container Style */
.form-container {
    width: 90%;
            margin: 20px auto;
            max-width: 1200px; /* Adjust max-width if needed */
            padding: 20px;
            background-color: white; /* Light background color */
            border-radius: 8px; /* Rounded corners for the container */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Soft shadow */
}
.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    text-align: left;
}

.admin-table th, .admin-table td {
    padding: 12px;
    border: 1px solid #ddd;
}

.admin-table th {
    background-color: #f4f4f4;
}

.admin-table td img {
    max-width: 100px;
    height: auto;
}

.admin-table td a {
    color: #007BFF;
    text-decoration: none;
}

.admin-table td a:hover {
    text-decoration: underline;
}

.form-container h2 {
    margin-top: 30px;
    font-size: 24px;
    text-align: center;
    color: #333;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 30px;
}

table th, table td {
    padding: 10px;
    text-align: left;
}

table th {
    background-color: #f2f2f2;
}

a {
    text-decoration: none;
    color: #007bff;
    margin-right: 10px;
}

a:hover {
    text-decoration: underline;
}

button {
    background-color: #DC143C;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

button:hover {
    background-color: #9B0F26;
}


.search-bar {
            text-align: right;
            margin-bottom: 20px;
            position: relative;
        }

        .search-bar input[type="text"] {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            width: 300px;
        }

        .search-bar button {
            padding: 5px 15px;
            border: none;
            background-color: #DC143C;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .search-bar button:hover {
            background-color: #9B0F26;
        }

tr.highlight {
            background-color: #f0f8ff;
        }

img {
            max-width: 100px;
            height: auto;
        }

    </style>
</head>
<body>
    <div class="form-container">
        <div style="margin-bottom: 20px;">
            <a href="admin_dashboard.php#usersForm" style="text-decoration: none; padding: 10px 20px; background-color: #DC143C; color: white; border-radius: 5px; font-size: 14px;">Back</a>
        </div>
        <h2>User Posts</h2>

        <!-- Search Bar -->
        <div class="search-bar">
            <form method="GET" action="user_post_detail.php">
    <input type="text" name="query" placeholder="Search..." value="<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>" />
    <input type="hidden" name="id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>" />
    <button type="submit">Search</button>
</form>
        </div>

        <!-- Table to display blog posts -->
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Post Title</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Cuisine</th>
                    <th>Category</th>
                    <th>Explore</th>
                    <th>City</th>
                    <th>Area</th>
                    <th>Google Maps Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($post = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $post['place_title']; ?></td>
                            <td><?php echo $post['description']; ?></td>
                            <td>
                                <?php if ($post['image_path']): ?>
                                    <img src="uploads/<?php echo $post['image_path']; ?>" alt="Post Image">
                                <?php else: ?>
                                    No image available
                                <?php endif; ?>
                            </td>
                            <td><?php echo $post['cuisine_name']; ?></td>
                            <td><?php echo $post['category_name']; ?></td>
                            <td><?php echo $post['explore_name']; ?></td>
                            <td><?php echo $post['city']; ?></td>
                            <td><?php echo $post['area']; ?></td>
                            <td>
                                <a href="<?php echo $post['google_maps_link']; ?>" target="_blank">View on Google Maps</a>
                            </td>
                            <td>
                                <form action="user_post_detail.php?id=<?php echo $user_id; ?>" method="post">
                                    <input type="hidden" name="delete_blog_id" value="<?php echo $post['blog_id']; ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">No posts found for this user.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>