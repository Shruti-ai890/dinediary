<?php
// Start the session to display messages
session_start();

// Include the database connection
include 'db_connection.php';

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $cuisine_name = mysqli_real_escape_string($conn, $_POST['cuisine']);
    $explore_name = mysqli_real_escape_string($conn, $_POST['explore']);
    $category_name = mysqli_real_escape_string($conn, $_POST['category']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $area = mysqli_real_escape_string($conn, $_POST['area']);
    $google_maps_link = mysqli_real_escape_string($conn, $_POST['google_maps_link']);

    // Insert into admin_posts table
    $sql_post = "INSERT INTO admin_posts (title, description, cuisine_name, explore_name, category_name, city, area, google_maps_link)
                 VALUES ('$title', '$description', '$cuisine_name', '$explore_name', '$category_name', '$city', '$area', '$google_maps_link')";

    if (mysqli_query($conn, $sql_post)) {
        $post_id = mysqli_insert_id($conn); // Get the inserted post_id

        // Handle uploaded images
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $image_name = basename($_FILES['images']['name'][$key]);

                // Move the uploaded file to the server
                if (move_uploaded_file($tmp_name, "uploads/" . $image_name)) {
                    // Insert into admin_post_images table
                    $sql_image = "INSERT INTO admin_post_images (post_id, image_name)
                                  VALUES ($post_id, '$image_name')";
                    mysqli_query($conn, $sql_image);
                }
            }
        }

        $message = "Blog post created successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

// Fetch cuisines, explore options, and categories from the database
$query_cuisine = "SELECT * FROM cuisines";
$result_cuisine = mysqli_query($conn, $query_cuisine);

$query_explore = "SELECT * FROM explore_options";
$result_explore = mysqli_query($conn, $query_explore);

$query_category = "SELECT * FROM categories";
$result_category = mysqli_query($conn, $query_category);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .container h1 {
            text-align: center;
            color: #333;
        }

        form div {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input, textarea, select, button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            resize: none;
        }

/* Specific Styling for Create Post Button */
form button[type="submit"] {
    background-color: #DC143C !important; /* Use !important to force the background color */
    color: white;
    padding: 8px 16px; /* Reduced padding for smaller button */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    width: 15%;
    margin-top: 10px;
    transition: background-color 0.3s ease; /* Smooth transition */
}

form button[type="submit"]:hover {
    background-color: #9B0F26 !important; /* Force hover color */
}

/* Back Button Styling */
form button[type="button"] {
    background-color: #DC143C !important;
    color: white;
    padding: 8px 16px; /* Reduced padding for smaller button */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    margin-top: 10px;
    width: 15%;
    transition: background-color 0.3s ease;
}

form button[type="button"]:hover {
    background-color: #9B0F26 !important;
}



        #image-preview img {
            max-width: 100px;
            margin: 5px;
        }

        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 20px;
        }

        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
    <script>
        function previewImages() {
            const imagePreview = document.getElementById("image-preview");
            imagePreview.innerHTML = ""; // Clear previous preview
            const files = document.getElementById("images").files;

            Array.from(files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement("img");
                    img.src = e.target.result;
                    imagePreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Add a New Blog Post</h1>

        <?php if ($message): ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Blog Post Form -->
        <form action="" method="POST" enctype="multipart/form-data">
            <div>
                <label for="title">Title of the Place:</label>
                <input type="text" name="title" id="title" required>
            </div>

            <div>
                <label for="description">Description:</label>
                <textarea name="description" id="description" rows="5" required></textarea>
            </div>

            <div class="image-gallery">
                <label for="images">Upload Images:</label>
                <input type="file" name="images[]" id="images" accept="image/*" multiple onchange="previewImages()" required>
                <div id="image-preview"></div>
            </div>

                    <div>
            <label for="cuisine">Cuisine:</label>
            <select name="cuisine" id="cuisine" required>
                <option value="">Select Cuisine</option>
                <?php while ($row_cuisine = mysqli_fetch_assoc($result_cuisine)): ?>
                    <option value="<?php echo $row_cuisine['cuisine_name']; ?>"><?php echo $row_cuisine['cuisine_name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label for="explore">Explore Option:</label>
            <select name="explore" id="explore" required>
                <option value="">Select Explore Option</option>
                <?php while ($row_explore = mysqli_fetch_assoc($result_explore)): ?>
                    <option value="<?php echo $row_explore['explore_name']; ?>"><?php echo $row_explore['explore_name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="">Select Category</option>
                <?php while ($row_category = mysqli_fetch_assoc($result_category)): ?>
                    <option value="<?php echo $row_category['category_name']; ?>"><?php echo $row_category['category_name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

            <div>
                <label for="city">City:</label>
                <input type="text" name="city" id="city" required>
            </div>

            <div>
                <label for="area">Area:</label>
                <input type="text" name="area" id="area" required>
            </div>

            <div>
                <label for="google_maps_link">Google Maps Link:</label>
                <input type="url" name="google_maps_link" id="google_maps_link" required>
            </div>

           <!-- Button Container for alignment -->
    <div class="button-container">
            <button type="submit">Create Post</button>
            <a href="admin_dashboard.php" class="back-button"><button type="button">Back </button></a>
        </div>
        </form>
    </div>
</body>
</html>
