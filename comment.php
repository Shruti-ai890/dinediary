<?php
session_start();
include("db_connection.php");


// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    echo "User is not logged in!";
    exit;
}

// Get session values
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Determine the post ID (either blog_id for user posts or post_id for admin posts)
if (isset($_GET['blog_id'])) {
    $postId = $_GET['blog_id'];
    $postType = 'user'; // This is a user post
} elseif (isset($_GET['post_id'])) {
    $postId = $_GET['post_id'];
    $postType = 'admin'; // This is an admin post
} else {
    echo "Post ID is missing!";
    exit;
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment_submit'])) {
    $rating = $_POST['rating'];
    $commentText = $_POST['comment_text'];

    if (!empty($username)) {
        // Insert comment into the database
        $query = "INSERT INTO comments (post_id, post_type, user_id, username, rating, comment_text) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isisis", $postId, $postType, $userId, $username, $rating, $commentText);

        if ($stmt->execute()) {
            // Redirect back to the comment page
            header("Location: comment.php?" . ($postType === 'user' ? 'blog_id' : 'post_id') . "=" . $postId);
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Error: Username is not set in session!";
    }
}

// Fetch existing comments
$commentQuery = "SELECT c.*, u.name AS username FROM comments c
                 JOIN users u ON c.user_id = u.id
                 WHERE c.post_id = ? AND c.post_type = ? ORDER BY c.created_at DESC";
$stmt = $conn->prepare($commentQuery);
$stmt->bind_param("is", $postId, $postType);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments</title>
    <style>
        /* Style the modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Comment Section Styles */
        h2 {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        div {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #555;
        }

        /* Make the comments section scrollable */
        div[style="max-height: 400px; overflow-y: scroll;"] {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 10px;
            margin-bottom: 20px;
        }

        div hr {
            border: 1px solid #ccc;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        div strong {
            font-weight: bold;
            color: #007bff;
        }

        /* Comment Form Styling */
        form {
            background-color: #f9f9f9;
            padding: 50px;
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

        form input[type="number"],
        form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
            resize: vertical;
        }

        form textarea {
            height: 100px;
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

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .modal-content {
                width: 90%;
            }

            form {
                width: 90%;
            }

            h2 {
                font-size: 20px;
            }
        }


header {
    background-color: #C70039;
    color: white;
    text-align: center;
    padding: 30px 0;
    position: relative;
    width: 100%;
    margin: 0;
    box-sizing: border-box;
}

header h1 {
    margin: 0;
    font-size: 1.8em;
}

header .back-buttons {
    position: absolute;
    top: 10px;
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

.comment-section {
    margin-top: 30px; /* Adds space between the header and comments */
}
/* Change the username color to #DC143C */
.comment-section strong {
    color: #DC143C;
}
/* Change the star color to yellow */
.comment-section .stars {
    color: #FFD700; /* Yellow */
    font-size: 1.2em; /* Slightly larger for better visibility */
}

    </style>
</head>
<body>

<header>
    <div class="back-buttons">
        <a href="<?php echo $postType === 'user' ? 'view_user_detail.php' : 'view_admin_post_detail'; ?>?<?php echo $postType === 'user' ? 'blog_id' : 'post_id'; ?>=<?php echo $postId; ?>" class="back-btn">Back</a>
    </div>
    <h1>Comments</h1>
</header>

<div class="comment-section">
    <!-- Existing Comments -->
<div style="max-height: 400px; overflow-y: auto; padding-right: 10px; margin-bottom: 20px; border: 1px solid #ccc; padding: 10px;">
      <?php while ($row = $result->fetch_assoc()): ?>
        <div>
            <strong><?php echo htmlspecialchars($row['username']); ?></strong> 
            <span class="stars"><?php echo str_repeat('&#9733;', $row['rating']); ?></span> <!-- Stars in yellow -->
            <br>
            <p><?php echo nl2br(htmlspecialchars($row['comment_text'])); ?></p>
            <small>Posted on: <?php echo $row['created_at']; ?></small>
        </div>
        <hr>
    <?php endwhile; ?>
</div>
</div>

<!-- Add New Comment Form -->
<form action="comment.php?<?php echo $postType === 'user' ? 'blog_id' : 'post_id'; ?>=<?php echo $postId; ?>" method="POST">
    <label for="rating">Rating (1 to 5):</label>
    <input type="number" name="rating" min="1" max="5" required><br><br>

    <label for="comment_text">Comment:</label><br>
    <textarea name="comment_text" rows="4" required></textarea><br><br>

    <button type="submit" name="comment_submit">Submit Comment</button>
</form>


</body>
</html>
