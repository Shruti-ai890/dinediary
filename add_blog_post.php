<?php
session_start();

// Database connection
include 'db_connection.php';

$user_id = $_SESSION['user_id'];  // Logged-in user ID

// Initialize success message session variable
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $city = $_POST['city'];
    $area = $_POST['area'];
    $google_maps_link = $_POST['google_maps_link'];

    // Get selected names for cuisine, explore, and category
    $cuisine_id = $_POST['cuisine'];
    $explore_id = $_POST['explore'];
    $category_id = $_POST['category'];

    // Fetch the cuisine name
    $query_cuisine = "SELECT cuisine_name FROM cuisines WHERE cuisine_id = '$cuisine_id'";
    $result_cuisine = mysqli_query($conn, $query_cuisine);
    $cuisine_name = mysqli_fetch_assoc($result_cuisine)['cuisine_name'];

    // Fetch the explore name
    $query_explore = "SELECT explore_name FROM explore_options WHERE explore_id = '$explore_id'";
    $result_explore = mysqli_query($conn, $query_explore);
    $explore_name = mysqli_fetch_assoc($result_explore)['explore_name'];

    // Fetch the category name
    $query_category = "SELECT category_name FROM categories WHERE category_id = '$category_id'";
    $result_category = mysqli_query($conn, $query_category);
    $category_name = mysqli_fetch_assoc($result_category)['category_name'];

    // Insert blog post into the database with cuisine_name, explore_name, category_name
    $sql = "INSERT INTO blog_posts (user_id, place_title, description, city, area, google_maps_link, cuisine_name, explore_name, category_name) 
            VALUES ('$user_id', '$title', '$description', '$city', '$area', '$google_maps_link', '$cuisine_name', '$explore_name', '$category_name')";
    if (mysqli_query($conn, $sql)) {
        $blog_id = mysqli_insert_id($conn);  // Get the ID of the inserted blog post

// Handle image uploads
if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
    $files = $_FILES['images'];
    $total_files = count($files['name']);
    
    for ($i = 0; $i < $total_files; $i++) {
        $file_name = basename($files['name'][$i]);  // Get only the file name (no path)
        $file_tmp = $files['tmp_name'][$i];
        $file_path = "uploads/" . $file_name;  // The full path where the file will be saved

        // Validate file type and size (optional but recommended)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];  // Add allowed file types
        $file_type = mime_content_type($file_tmp);
        $max_file_size = 5 * 1024 * 1024;  // Max file size 5MB
        
        // Check if file type is allowed
        if (!in_array($file_type, $allowed_types)) {
            echo "File type not allowed for file: $file_name.";
            continue;
        }
        
        // Check if file size is within the limit
        if (filesize($file_tmp) > $max_file_size) {
            echo "File size exceeds the limit for file: $file_name.";
            continue;
        }

        // Move the uploaded image to the uploads folder
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Insert just the file name (not the full path) into the blog_post_images table
            $image_sql = "INSERT INTO blog_post_images (blog_id, image_path) 
                          VALUES ('$blog_id', '$file_name')";
            if (mysqli_query($conn, $image_sql)) {
                //echo "Image '$file_name' uploaded and inserted into database successfully.";
            } else {
                echo "Error inserting image path into database: " . mysqli_error($conn);
            }
        } else {
            echo "Failed to upload image: $file_name.";
        }
    }
} else {
    echo "No files were uploaded.";
}



         // Set success message
        $message = 'Blog post created successfully!';
    } else {
        $message = 'Error: ' . mysqli_error($conn);
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
    <title>Create Blog Post</title>
    
    <style>
    /* General Styles */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    h1 {
        text-align: center;
        color: #333;
        margin-top: 20px;
        font-size: 2rem;
    }

    form {
        width: 80%;
        margin: 20px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    label {
        font-size: 1rem;
        color: #080808;
        margin-bottom: 8px;
        display: inline-block;
    }

    form input[type="text"], 
    form input[type="url"], 
    form textarea,
    form select {
        width: 100%;
        padding: 10px;
        margin: 5px 0;
        border: 1px solid #ccc;
        border-radius: 8px;  /* Rounded corners */
        background-color: #f9f9f9;  /* Light background color */
        font-size: 14px;  /* Ensures text consistency */
        color: #333;  /* Dark text color */
        box-sizing: border-box;  /* Ensures padding and borders are included in total width */
    }

    form textarea {
        resize: vertical;  /* Allow vertical resizing for textarea */
    }

    /* Optional: Focus effect */
    form input[type="text"]:focus, 
    form input[type="url"]:focus, 
    form textarea:focus,
    form select:focus {
        border-color: #007BFF;  /* Change border color when the field is focused */
        background-color: #fff;  /* Slightly change background on focus */
        outline: none;  /* Remove the default outline */
    }

   input[type="file"] {
    width: 100%; 
    padding: 10px;
    box-sizing: border-box;
    background-color: #fff; /* Match the background color of your other inputs */
    border: 1px solid #ccc; /* Similar border as other text fields */
    border-radius: 5px; /* Optional: if your text inputs have rounded corners */
    font-size: 16px; /* Match the font size of other inputs */
}


    button[type="submit"] {
        background-color: #DC143C;
        color: white;
        padding: 8px 16px; /* Reduced padding for smaller button */
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        width: 15%;
        margin-top: 10px;
    }

    button[type="submit"]:hover {
        background-color: #9B0F26;
    }

    button[type="button"] {
        background-color: #DC143C;  /* Blue color for back button */
        color: white;
        padding: 8px 16px; /* Reduced padding for smaller button */
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        margin-top: 10px;
        width: 15%;
    }

    button[type="button"]:hover {
        background-color: #9B0F26;
    }


/* Aligning the label with other labels in the form */
.image-gallery label {
    display: block;       /* Ensures the label is on its own line */
    margin-bottom: 10px;  /* Space between the label and input */
    font-size: 14px;      /* Matches the font size of other labels */
    color: #333;          /* Color of the text */
    font-weight: normal;  /* Standard font weight */
}

/* Styling the file input field */
.image-gallery input[type="file"] {
    display: block;        /* Makes it a block element */
    width: 100%;           /* Full width of the form */
    padding: 10px;         /* Padding for input */
    margin-bottom: 20px;   /* Space below input */
    border: 1px solid #ccc; /* Subtle border for the input field */
    border-radius: 4px;    /* Rounded corners */
    background-color: #f9f9f9; /* Light background color */
    font-size: 14px;       /* Matches the font size with other inputs */
}

/* Image preview container for displaying uploaded images */
#image-preview {
    display: flex;          /* Flexbox layout for images */
    flex-wrap: wrap;        /* Allows wrapping to the next line if images exceed the container width */
    gap: 10px;              /* Adds space between images */
    margin-top: 15px;       /* Adds space above the preview container */
}

/* Individual image preview styling */
.image-preview {
    width: 100%;            /* Makes the preview take full width */
    max-width: 120px;       /* Limits the preview image size */
    height: auto;           /* Maintains aspect ratio */
    margin: 5px;            /* Space between images */
    object-fit: cover;      /* Ensures the image fits the box without distortion */
    border-radius: 8px;     /* Rounded corners for images */
}
    /* Responsive Design */
    @media (max-width: 768px) {
        form {
            width: 90%;
        }

        .image-gallery img {
            max-width: 100%;
        }

        button[type="submit"], button[type="button"] {
            width: 100%;
        }
    }

    #success-message {
        background-color: #28a745; /* Green */
        color: white;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        text-align: center;
    }


/* General Styles for Form */
    form {
        width: 80%;
        margin: 20px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Styles for the buttons */
    .button-container {
        display: flex;
        justify-content: space-between; /* Align buttons side by side */
        margin-top: 20px;
    }

    .back-button {
        background-color: #DC143C;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        width: 10%; /* Set width to fit the layout */
        text-align: center;
        text-decoration: none;
    }

    .back-button:hover {
        background-color: #9B0F26;
    }

    /* Adjust buttons for smaller screens */
    @media (max-width: 768px) {
        .button-container {
            flex-direction: column;
            align-items: center;
        }

        .back-button {
            width: 100%;
            margin-top: 10px;
        }
    }


    </style>
</head>
<body>
    <h1>Create a New Blog Post</h1>

    <!-- HTML Form for Blog Post Submission -->
    <?php if ($message): ?>
    <div id="success-message"><?php echo $message; ?></div>
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
                    <option value="<?php echo $row_cuisine['cuisine_id']; ?>"><?php echo $row_cuisine['cuisine_name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label for="explore">Explore Option:</label>
            <select name="explore" id="explore" required>
                <option value="">Select Explore Option</option>
                <?php while ($row_explore = mysqli_fetch_assoc($result_explore)): ?>
                    <option value="<?php echo $row_explore['explore_id']; ?>"><?php echo $row_explore['explore_name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label for="category">Category:</label>
            <select name="category" id="category" required>
                <option value="">Select Category</option>
                <?php while ($row_category = mysqli_fetch_assoc($result_category)): ?>
                    <option value="<?php echo $row_category['category_id']; ?>"><?php echo $row_category['category_name']; ?></option>
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
        <button type="submit">Create Blog Post</button>
        <a href="user_dashboard.php" class="back-button">Back to Dashboard</a>
    </div>
    </form>




    <script>
       function previewImages() {
        var preview = document.getElementById('image-preview');
        preview.innerHTML = "";  // Clear previous images

        var files = document.getElementById('images').files;

        if (files) {
            Array.from(files).forEach(file => {
                var reader = new FileReader();
                reader.onload = function(event) {
                    var img = document.createElement('img');
                    img.src = event.target.result;
                    img.classList.add('image-preview');
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
    }
    </script>
</body>
</html>