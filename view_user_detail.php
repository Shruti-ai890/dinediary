<?php
session_start();
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

// Initialize error message
$error_message = "";

// Check if the form is submitted
if (isset($_POST['login'])) {
    // Get email and password from the login form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query the database to find the user by email
    $query = "SELECT id, name, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User exists, now check the password
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Password is correct, start the session and redirect to comment page
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['name'];
            header("Location: comment.php?blog_id=" . $_GET['blog_id']);
            exit;
        } else {
            // Invalid password
            $error_message = "Invalid password. Please try again.";
        }
    } else {
        // No user found with that email
        $error_message = "No user found with that email. Please check your email and try again.";
    }

    // If login fails, redirect back to the page with the error message
    header("Location:view_user_detail.php?error_message=" . urlencode($error_message) . "&blog_id=" . $_GET['blog_id']);
    exit;
}

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


/* Styling for the Comment button */
.btn-comment {
    padding: 10px 20px;
    background-color: #DC143C;
    color: #fff;
    text-decoration: none;
    border: none; /* Remove default button border */
    border-radius: 5px;
    margin-top: 20px;
    display: inline-block;
    margin-left: 10px;  /* Space between the buttons */
    cursor: pointer; /* Add pointer cursor */
    font-size: 16px; /* Adjust font size */
}

.btn-comment:hover {
    background-color: #9B0F26;
}

/* Ensuring buttons are aligned properly */
.button-container {
    display: flex;
    align-items: center;
    gap: 10px; /* Space between buttons */
}




/* Style the modal for Login Form */
#loginFormModal {
    display: none; /* Initially hide the modal */
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4); /* Slightly dark background */
    padding-top: 60px; /* Adjust the position of the modal */
}

/* Modal content (form container) */
#loginFormModal .modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 400px;
}

/* Close button styling */
#loginFormModal .close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

#loginFormModal .close:hover,
#loginFormModal .close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Error Message Styling */
#loginFormModal div[style="color: red;"] {
    font-size: 14px;
    margin-bottom: 15px;
}


/* Comment Form Styling */
        form {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            width: 100%;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        form label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        form input[type="email"],
        form input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        form button {
            width: 100%;
            padding: 12px;
            background-color: #DC143C;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        form button:hover {
            background-color: #9B0F26;
        }

        /* Error Message Styling */
        div[style="color: red;"] {
            font-size: 14px;
            margin-bottom: 15px;
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

   <!--Back Buttons -->
        <a href="index.php" class="btn btn-back">Back</a>

<button class="btn-comment" onclick="openLoginModal('<?php echo $_GET['blog_id']; ?>')">Comments</button>


    </div>
    </div>

<?php
if (isset($_GET['error_message'])) {
    // Show modal with the error message
    echo "<script>openLoginModal('".$_GET['blog_id']."');</script>";
}
?>

<!-- Login Modal -->
<div id="loginFormModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeLoginModal()">&times;</span>
        <h2>Login</h2> <!-- This will be centered -->
        <form method="POST" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br><br>

            <!-- Display error message if credentials are invalid -->
            <?php if (isset($_GET['error_message'])): ?>
                <div style="color: red;"><?php echo htmlspecialchars($_GET['error_message']); ?></div>
            <?php endif; ?>

            <button type="submit" name="login">Login</button>
        </form>
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
    // Lightbox Modal Logic
    var currentIndex = 0;
    var images = document.querySelectorAll('.gallery-image');
    var modal = document.getElementById("modal");
    var modalImg = document.getElementById("modal-img");

    // Open the image modal
    function openModal(image) {
        modal.style.display = "flex";  // Center the modal
        modalImg.src = image.src;
        currentIndex = Array.from(images).indexOf(image);
    }

    // Close the image modal
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

    // Login Modal Logic
    // Open the login modal
    function openLoginModal() {
        document.getElementById('loginFormModal').style.display = 'block'; // Updated ID
    }

    // Close the login modal
    function closeLoginModal() {
        document.getElementById('loginFormModal').style.display = 'none'; // Updated ID
    }

    // If the user clicks outside the login modal, close it
    window.onclick = function(event) {
        if (event.target == document.getElementById('loginFormModal')) { // Updated ID
            closeLoginModal();
        }
    }

    // Ensure that the modals are properly handled on page load
    window.onload = function() {
        // Hide the lightbox modal and the login modal by default
        modal.style.display = "none";
        document.getElementById("loginFormModal").style.display = "none"; // Hide login modal initially
        
        // Automatically open the login modal if there's an error message in the URL
        var errorMessage = new URLSearchParams(window.location.search).get('error_message');
        if (errorMessage) {
            openLoginModal(); // Open the login modal if there's an error message
        }
    };
</script>




</body>
</html>
