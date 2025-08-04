<?php

// Assuming you're passing the blog_id via URL query
$blogId = $_GET['blog_id'];  // Get blog_id from URL query

// Include the database connection
include("db_connection.php");

// Check if the delete flag is set in the URL
if (isset($_GET['delete']) && $_GET['delete'] == 'true') {
    // Start by deleting the images associated with this blog post
    $imageQuery = "SELECT * FROM blog_post_images WHERE blog_id = ?";
    $stmt = $conn->prepare($imageQuery);
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    $stmt->bind_param("i", $blogId);
    $stmt->execute();
    $imageResult = $stmt->get_result();  // Get image data

    while ($image = $imageResult->fetch_assoc()) {
        // Delete the image from the file system
        $imagePath = 'uploads/' . $image['image_path'];  // Assuming images are stored in 'uploads/' directory
        if (file_exists($imagePath)) {
            unlink($imagePath);  // Delete the image
        }
    }

    // Now delete the records from the blog_post_images table
    $deleteImageQuery = "DELETE FROM blog_post_images WHERE blog_id = ?";
    $stmt_delete_image = $conn->prepare($deleteImageQuery);
    $stmt_delete_image->bind_param("i", $blogId);
    $stmt_delete_image->execute();

    // Finally, delete the blog post itself
    $deletePostQuery = "DELETE FROM blog_posts WHERE blog_id = ?";
    $stmt_delete_post = $conn->prepare($deletePostQuery);
    $stmt_delete_post->bind_param("i", $blogId);
    $stmt_delete_post->execute();

    // Redirect to a page after deletion
    header("Location: user_dashboard.php?deletion=success");
    exit;
}

// Fetch blog details
$query = "SELECT * FROM blog_posts WHERE blog_id = ?";
$stmt = $conn->prepare($query);

// Check if the query was prepared successfully
if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

$stmt->bind_param("i", $blogId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $post = $result->fetch_assoc();  // Get post data
} else {
    echo "Post not found";
    exit;
}
$stmt->close();

// Fetch images related to the blog
$imageQuery = "SELECT * FROM blog_post_images WHERE blog_id = ?";
$stmt = $conn->prepare($imageQuery);

// Check if the query was prepared successfully
if ($stmt === false) {
    die('MySQL prepare error: ' . $conn->error);
}

$stmt->bind_param("i", $blogId);
$stmt->execute();
$imageResult = $stmt->get_result();  // Get image data
$stmt->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Detail</title>
    <style>
       /* Your existing CSS */
        .post-container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .image-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            gap: 10px;
            width: 100%;
            padding: 10px;
        }

        .image-grid img {
            width: 80%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }

        .post-details {
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
        }

        .post-details h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .post-details p {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .btn {
            padding: 10px 20px;
            background-color: #DC143C;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            display: inline-block;
        }

        .btn:hover {
            background-color: #9B0F26;
        }

/* Modal CSS */
    .modal {
        display: none;  /* Initially hide the modal */
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;  /* Center the content vertically and horizontally */
    }

    .modal-content {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 10px;
        overflow: hidden; /* Hide any overflow */
        width: 90%;  /* Make the modal take 90% of the screen width */
        max-width: 900px; /* Set a max width for the modal */
        max-height: 80vh; /* Allow max height of modal to be 80% of the screen height */
    }

    .modal-content img {
        width: 100%;              /* Make the image take up the full width of the container */
        height: auto;             /* Maintain aspect ratio */
        object-fit: contain;      /* Ensure image fits inside without cropping */
        max-height: 80vh;         /* Set a max height to prevent the image from becoming too large */
        border-radius: 10px;
    }

    .modal-nav {
        position: absolute;
        top: 50%;
        width: 100%;
        display: flex;
        justify-content: space-between;
        padding: 0 20px;
    }

    .modal-nav button {
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        font-size: 30px;
        cursor: pointer;
    }

    .modal-nav button:hover {
        background-color: rgba(0, 0, 0, 0.8);
    }

    .close {
        position: absolute;
        top: 10px;
        right: 25px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: color 0.3s;
    }

    .close:hover,
    .close:focus {
        color: #bbb;
        text-decoration: none;
        cursor: pointer;
    }


.details-container {
    display: flex;
    justify-content: space-between;
    background-color: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
    gap: 20px;
}

.details-column {
    width: 48%;
}

.details-column p {
    margin: 10px 0;
    font-size: 16px;
    color: #333;
}

.details-column strong {
    color: #444;
    margin-right: 5px;
}

.details-column a {
    color: #007BFF;
    text-decoration: none;
}

.details-column a:hover {
    text-decoration: underline;
}
    </style>
</head>
<body>
    <div class="post-container">
        <h1><?php echo htmlspecialchars($post['place_title']); ?></h1>
        <p><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>

        <!-- Displaying all images related to the blog -->
        <div class="image-grid">
            <?php while ($image = $imageResult->fetch_assoc()): ?>
                <img src="uploads/<?php echo htmlspecialchars($image['image_path']); ?>" alt="Post Image" class="gallery-image" onclick="openModal(this)">
            <?php endwhile; ?>
        </div>

<!-- Post Details in Two Columns -->
<div class="details-container">
    <div class="details-column">
        <p><strong>City:</strong> <?php echo htmlspecialchars($post['city']); ?></p>
        <p><strong>Cuisine:</strong> <?php echo htmlspecialchars($post['cuisine_name']); ?></p>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($post['category_name']); ?></p>
        <p><strong>Posted On:</strong> <?php echo date("F j, Y, g:i a", strtotime($post['created_at'])); ?></p>
    </div>
    <div class="details-column">
        <p><strong>Area:</strong> <?php echo htmlspecialchars($post['area']); ?></p>
        <p><strong>Explore:</strong> <?php echo htmlspecialchars($post['explore_name']); ?></p>
        <p><strong>Google Maps:</strong> <a href="<?php echo htmlspecialchars($post['google_maps_link']); ?>" target="_blank">Open in Google Maps</a></p>
    </div>
</div>
   <!-- Edit, Delete, and Back Buttons -->
        <a href="edit_post.php?blog_id=<?php echo $post['blog_id']; ?>" class="btn btn-edit">Edit</a>
        <a href="javascript:void(0);" class="btn btn-delete" onclick="confirmDeletion(<?php echo $post['blog_id']; ?>)">Delete</a>
        <a href="user_dashboard.php" class="btn btn-back">Back</a>
    </div>
    </div>

   <!-- Modal -->
<div id="modal" class="modal">
    <span class="close" onclick="closeModal()">&times;</span>
    <div class="modal-content">
        <img id="modal-img" src="" alt="Post Image">
    </div>
    <div class="modal-nav">
        <button onclick="prevImage()">&#10094;</button>
        <button onclick="nextImage()">&#10095;</button>
    </div>
</div>

    <script>
var currentIndex = 0;
    var images = document.querySelectorAll('.gallery-image');
    var modal = document.getElementById("modal");
    var modalImg = document.getElementById("modal-img");

    // When an image is clicked, open the modal and display the image
    function openModal(image) {
        modal.style.display = "block";
        modalImg.src = image.src;
        currentIndex = Array.from(images).indexOf(image);
    }

    // Close the modal
    function closeModal() {
        modal.style.display = "none";
    }

    // Navigate to the previous image
    function prevImage() {
        currentIndex = (currentIndex === 0) ? images.length - 1 : currentIndex - 1;
        modalImg.src = images[currentIndex].src;
    }

    // Navigate to the next image
    function nextImage() {
        currentIndex = (currentIndex === images.length - 1) ? 0 : currentIndex + 1;
        modalImg.src = images[currentIndex].src;
    }


function openModal(image) {
    modal.style.display = "flex";  // Use flex to center it
    modalImg.src = image.src;
    currentIndex = Array.from(images).indexOf(image);
}


    // Ensure the modal doesn't show automatically and is only triggered by clicking an image
    window.onload = function() {
        modal.style.display = "none"; // Make sure the modal is hidden on page load
    };


function confirmDeletion(blogId) {
        // Show the confirmation dialog
        if (confirm("Are you sure you want to delete this post?")) {
            // Send a request to the same page (post_detail.php) to delete the post
            window.location.href = "post_detail.php?blog_id=" + blogId + "&delete=true";
        }
    }

    </script>


 


</body>
</html>
