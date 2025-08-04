<?php
session_start();
include('db_connection.php');

// Fetch posts from blog_posts and their images, but limit to the first image for each blog post
$blogPostsQuery = "
    SELECT 
        bp.blog_id, bp.place_title,bp.description, bp.city, bp.area, 
        bpi.image_path
    FROM 
        blog_posts bp
    LEFT JOIN 
        blog_post_images bpi 
    ON 
        bp.blog_id = bpi.blog_id
    GROUP BY
        bp.blog_id  -- This ensures we only fetch one image per blog post
    ORDER BY 
        bp.blog_id DESC";
$blogPostsResult = $conn->query($blogPostsQuery);


// Fetch posts from admin_posts and their images, but limit to the first image for each post
$adminPostsQuery = "
    SELECT 
        ap.post_id, ap.title, ap.description, ap.cuisine_name, ap.explore_name, 
        ap.category_name, ap.city, ap.area, ap.google_maps_link, 
        api.image_name
    FROM 
        admin_posts ap
    LEFT JOIN 
        admin_post_images api 
    ON 
        ap.post_id = api.post_id
    GROUP BY
        ap.post_id  -- This ensures we only fetch one image per post
    ORDER BY 
        ap.post_id DESC";  // Adjust as needed for sorting
$adminPostsResult = $conn->query($adminPostsQuery);

// Initialize error messages
$error_username = "";
$error_password = "";
$error_name = "";
$error_email = "";
$success_message = "";

// Admin Login Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin-login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Initialize error variables
    $_SESSION['error_username'] = '';
    $_SESSION['error_password'] = '';

    // Query to check if username exists
    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // If username is found
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        // If password is correct
        if (password_verify($password, $admin['password'])) {
            // Successful login, set session variables and redirect
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: admin_dashboard.php");
            exit;
        } else {
            // Incorrect password error
            $_SESSION['error_password'] = "Invalid password. Please try again.";
            $display_modal = true; // Set to show the modal
        }
    } else {
        // Username not found error
        $_SESSION['error_username'] = "Admin username not found. Please try again.";
        $display_modal = true; // Set to show the modal
    }
}



// User Login Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user-login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            
            // Redirect to user dashboard
            header("Location: user_dashboard.php");
            exit;
        } else {
            // Invalid password
            $_SESSION['error_message'] = "Invalid password. Please try again.";
            $_SESSION['reopen_modal'] = true;
        }
    } else {
        // User not found
        $_SESSION['error_message'] = "User not found. Please try again.";
        $_SESSION['reopen_modal'] = true;
    }

 // Redirect back to index.html to reopen modal
    header("Location: index.php");
    exit;
}


// User Registration Handling
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register-user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];

    // Clear any previous errors
    $error_name = $error_email = $error_phone_number = $error_password = '';
    $_SESSION['registration_error'] = null;

    // Validation
    if (empty($name)) {
        $error_name = "Name is required.";
        $_SESSION['registration_error'] = $error_name;
    }

    if (empty($email)) {
        $error_email = "Email is required.";
        $_SESSION['registration_error'] = $error_email;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_email = "Invalid email format.";
        $_SESSION['registration_error'] = $error_email;
    }

    if (empty($phone_number)) {
        $error_phone_number = "Phone number is required.";
        $_SESSION['registration_error'] = $error_phone_number;
    } elseif (!preg_match('/^\d{10}$/', $phone_number)) {
        $error_phone_number = "Phone number must be exactly 10 digits.";
        $_SESSION['registration_error'] = $error_phone_number;
    }

// Password validation
    if (empty($password)) {
        $error_password = "Password is required.";
        $_SESSION['registration_error'] = $error_password;
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error_password = "Password must have at least 8 characters, one uppercase letter, one number, and one special character (@$!%*?&).";
        $_SESSION['registration_error'] = $error_password;
    }

    // If no errors, proceed with registration
    if (empty($error_name) && empty($error_email) && empty($error_phone_number) && empty($error_password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (name, email, phone_number, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $phone_number, $hashed_password);

        if ($stmt->execute()) {
            $success_message = "Registration successful. You can now log in.";
            $_SESSION['registration_error'] = null; // Clear errors if registration is successful
        } else {
            $error_email = "This email is already registered.";
            $_SESSION['registration_error'] = $error_email;
        }

        $stmt->close();
    }

    $conn->close();
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DineDiary</title>
    <style>
        /* Basic CSS for the layout */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        header {
            background-color: #C70039;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        header .login-buttons {
            position: absolute;
            top: 10px;
            left: 20px;
            display: flex;
            gap: 10px;
        }

        header .login-buttons a {
            color: white;
            text-decoration: none;
            background-color: #DC143C;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        header .login-buttons a:hover {
            background-color: #9B0F26;
        }

        header .center-title {
            font-size: 24px;
            font-weight: bold;
            color: white;
            margin-bottom: 15px;
        }

        header .nav-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 15px;
        }

        header .nav-buttons a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s, background-color 0.3s;
            padding: 5px 10px;
            border-radius: 5px;
        }

        header .nav-buttons a:hover {
            background-color: #9B0F26;
            color: white;
        }

       /* Search bar styles */
header .search-bar {
    position: absolute;
    top: 10px;
    right: 20px;
    display: flex;
    align-items: center;
    gap: 5px;
}

header .search-bar input {
    padding: 5px 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
}

header .search-bar button {
    padding: 5px 15px;
    border: none;
    background-color: #DC143C; /* Crimson color for the button */
    color: white;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

header .search-bar button:hover {
    background-color: #9B0F26; /* Darker crimson color */
}


        .hero {
            text-align: center;
            padding: 50px;
            background-image: url('hero-image.jpg');
            background-size: cover;
            color: white;
        }

        .hero h1 {
            font-size: 3em;
            margin-bottom: 20px;
        }

        .hero p {
            font-size: 1.2em;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: white;
            margin: auto;
            padding: 30px 40px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .modal-content h2 {
            margin-bottom: 20px;
        }

        .modal-content input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .modal-content button {
            width: 100%;
            padding: 12px;
            background-color: #DC143C;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .modal-content button:hover {
            background-color: #B2122B;
        }

        .modal-content a {
            color: #DC143C;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
            margin-top: 10px;
        }

        .modal-content a:hover {
            text-decoration: underline;
        }

        .modal-content .error-message {
            color: red;
            font-size: 14px;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #aaa;
            font-size: 30px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal .success-message {
            color: green;
            font-size: 14px;
        }

#success-message {
    background-color: #4CAF50; /* Green background for success */
    color: white;
    padding: 10px;
    text-align: center;
    margin-top: 20px;
    border-radius: 5px;
    opacity: 1;
    transition: opacity 1s ease-out;
    display: inline-block; /* Adjust width to content */
    max-width: 300px; /* Prevent it from being too wide */
    word-wrap: break-word; /* Ensures long text wraps inside */
}


#success-message.fade-out {
    opacity: 0;
}


/* The Modal (background) */
.modal {
    display: none; 
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4);
    padding-top: 60px;
}

/* Modal content box */
.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 30px;  /* Increased padding for spacing */
    border: 1px solid #888;
    width: 60%;      /* Adjust the width to make the modal wider */
    max-width: 500px; /* Maximum width for large screens */
    box-sizing: border-box; /* Include padding in the width */
}

/* Input fields inside the modal */
input[type="email"], input[type="password"], button {
    width: 100%;       /* Make the fields take up full width */
    padding: 10px;     /* Add padding for better usability */
    margin: 10px 0;    /* Space between fields */
    border: 1px solid #ccc;
    border-radius: 5px; /* Rounded corners */
}

/* Buttons */
button {
    background-color: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 16px;
}

/* Button on hover */
button:hover {
    background-color: #45a049;
}

/* Close button */
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

/* For small screens */
@media (max-width: 600px) {
    .modal-content {
        width: 90%;  /* Make modal take up 90% width on small screens */
    }
}

/* For medium screens */
@media (min-width: 601px) and (max-width: 1024px) {
    .modal-content {
        width: 70%;  /* Make modal take up 70% width on medium screens */
    }
}



/* Styling for the navigation buttons (About Us, Cuisine, Explore, Categories, Contact) */
.nav-buttons a {
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    font-size: 18px;
    background-color: #DC143C; /* Crimson color */
    border-radius: 5px;
    transition: background-color 0.3s; /* Smooth transition for background color */
    display: inline-block;
}

/* Hover effect for the navigation buttons */
.nav-buttons a:hover {
    background-color: #9B0F26; /* Darker crimson on hover */
}

/* Styling for individual dropdown buttons like "Cuisine", "Explore" */
.nav-buttons .dropdown {
    position: relative;
}

/* Style for dropdown content (menu) */
.dropdown-content {
    display: none;
    position: absolute;
    background-color: #DC143C;
    min-width: 160px;
    z-index: 1;
    border-radius: 5px;
}

/* Styling for each dropdown item (submenu links) */
.dropdown-content a {
    color: white;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

/* Hover effect for dropdown items */
.dropdown-content a:hover {
    background-color:#DC143C;
    color: black;
}

/* Show dropdown content on hover */
.dropdown:hover .dropdown-content {
    display: block;
}



.post-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px;
}

.post-item {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.2s ease-in-out;
}

.post-item:hover {
    transform: scale(1.05);
}

.post-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
}

.post-item h3 {
    font-size: 18px;
    margin: 15px 0 5px;
    color: #333;
}

.post-item p {
    font-size: 14px;
    margin: 10px 0;
    color: #666;
}

.post-item a {
    display: inline-block;
    margin: 10px 0;
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


    </style>
</head>
<body>
    <header>
        <!-- Login buttons section -->
        <div class="login-buttons">
            <a href="javascript:void(0);" id="admin-login-btn">Admin Login</a>
            <a href="javascript:void(0);" id="user-login-btn">User Login</a>
        </div>

        <!-- Center Title -->
        <div class="center-title"><font style="font-family:Lucida Calligraphy">DineDiary</font></div>

        <!-- Navigation buttons under the title in one line -->
        <div class="nav-buttons">
    <div class="dropdown">
        <a href="about.html">About Us</a>
        <div class="dropdown-content">
            <a href="about.html">About Us</a>
        </div>
    </div>
    
    <div class="dropdown">
        <a href="#">Cuisine</a>
        <div class="dropdown-content">
            <!-- Dynamic content will be inserted here by PHP -->
            <?php
            include 'db_connection.php'; // Make sure to connect to the DB

            // Fetch cuisines dynamically from the database
            $query = "SELECT * FROM cuisines";
            $result = mysqli_query($conn, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<a href="post_by_cuisine.php?id=' . $row['cuisine_id'] . '">' . htmlspecialchars($row['cuisine_name'], ENT_QUOTES, 'UTF-8') . '</a>';
                }
                mysqli_free_result($result);
            } else {
                echo 'No cuisines found.';
            }
            mysqli_close($conn);
            ?>
        </div>
    </div>
    
    <div class="dropdown">
        <a href="#">Explore</a>
        <div class="dropdown-content">
            <!-- Dynamic content for explore -->
            <?php
            include 'db_connection.php';

            // Fetch explore data
            $query = "SELECT * FROM explore_options";
            $result = mysqli_query($conn, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<a href="post_by_explore.php?id=' . $row['explore_id'] . '">' . htmlspecialchars($row['explore_name'], ENT_QUOTES, 'UTF-8') . '</a>';
                }
                mysqli_free_result($result);
            } else {
                echo 'No explore data found.';
            }
            mysqli_close($conn);
            ?>
        </div>
    </div>
    
    <div class="dropdown">
        <a href="#">Categories</a>
        <div class="dropdown-content">
            <!-- Dynamic content for categories -->
            <?php
            include 'db_connection.php';

            // Fetch categories dynamically from the database
            $query = "SELECT * FROM categories";
            $result = mysqli_query($conn, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<a href="post_by_category.php?id=' . $row['category_id'] . '">' . htmlspecialchars($row['category_name'], ENT_QUOTES, 'UTF-8') . '</a>';
                }
                mysqli_free_result($result);
            } else {
                echo 'No categories found.';
            }
            mysqli_close($conn);
            ?>
        </div>
    </div>

    <div class="dropdown">
        <a href="#">Contact Us</a>
        <div class="dropdown-content">
            <!-- Contact Us can be a static link or dynamic -->
        <a href="mailto:augustina5489@gmail.com">Email Us</a>
        <a href="https://www.instagram.com/onepage1021" target="_blank">Instagram</a>
        </div>
    </div>
</div>



        <!-- Search bar -->
        <form action="search_results.php" method="GET" class="search-bar">
            <input type="text" name="query" placeholder="Search..." required>
            <button type="submit">Search</button>
        </form>
    </header>



<!-- Admin Login Modal -->
<div id="admin-login-modal" class="modal" <?php if(isset($display_modal) && $display_modal) echo 'style="display: block;"'; ?>>
    <div class="modal-content">
        <span class="close" id="close-admin-login-modal">&times;</span>
        <h2>Admin Login</h2>
        <form action="" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="admin-login">Login</button>

            <?php
            // Display error messages if available
            if (isset($_SESSION['error_username'])) {
                echo '<p class="error-message">' . $_SESSION['error_username'] . '</p>';
                unset($_SESSION['error_username']);
            }
            if (isset($_SESSION['error_password'])) {
                echo '<p class="error-message">' . $_SESSION['error_password'] . '</p>';
                unset($_SESSION['error_password']);
            }
            ?>
        </form>
    </div>
</div>






<!-- Success Message -->
<div id="success-message" style="display: none;">
    <p>Registered Successfully!</p>
</div>

<!-- Registration Modal -->
<div class="modal" id="user-login-modal">
    <div class="modal-content">
        <span class="close" id="close-register-modal">&times;</span>
        <h2>Register User</h2>
        <form method="POST" action="">
            <!-- Name Input -->
            <input type="text" name="name" placeholder="Full Name" value="<?php echo isset($name) ? $name : ''; ?>" required>
            <!-- Error Message for Name -->
            <?php if (isset($_SESSION['registration_error']) && $error_name): ?>
                <div class="error-message"><?php echo $error_name; ?></div>
            <?php endif; ?>

            <!-- Email Input -->
            <input type="email" name="email" placeholder="Email" value="<?php echo isset($email) ? $email : ''; ?>" required>
            <!-- Error Message for Email -->
            <?php if (isset($_SESSION['registration_error']) && $error_email): ?>
                <div class="error-message"><?php echo $error_email; ?></div>
            <?php endif; ?>

             <!-- Phone Number Input -->
             <input type="text" name="phone_number" placeholder="Phone Number" value="<?php echo isset($phone_number) ? $phone_number : ''; ?>" required                      pattern="^\d{10}$" title="Enter exactly 10 digits">
             <!-- Error Message for Phone Number -->
             <?php if (isset($_SESSION['registration_error']) && $error_phone_number): ?>
            <div class="error-message"><?php echo $error_phone_number; ?></div>
            <?php endif; ?>



            <!-- Password Input -->
            <input type="password" id="password" name="password" placeholder="Password" required>
            <p id="password-error" style="color: red; display: none;"></p> <!-- Error message for password validation -->

            <!-- Error Message for Password -->
            <?php if (isset($_SESSION['registration_error']) && $error_password): ?>
                <div class="error-message"><?php echo $error_password; ?></div>
            <?php endif; ?>

            <button type="submit" name="register-user">Register</button>

            <!-- Success Message -->
            <?php if (isset($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <a href="#" id="loginLink">Already registered? Login</a>
        </form>
    </div>
</div>



<!-- Login Modal -->
<div id="loginModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeLoginModal()">&times;</span>
        <h2>Login</h2>

        <!-- Display error message -->
        <?php if (isset($_SESSION['error_message'])): ?>
            <div style="color: red;"><?php echo $_SESSION['error_message']; ?></div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <form action="" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit" name="user-login">Login</button>
        </form>
    </div>
</div>


<div class="post-grid">
    <!-- Fetch and Display Blog Posts -->
    <?php while ($blogPost = $blogPostsResult->fetch_assoc()): ?>
        <div class="post-item">
            <!-- Post Title -->
            <h3><?php echo htmlspecialchars($blogPost['place_title']); ?></h3>
            
            <?php
            // Fetch the first image for each blog post
            $blogPostImage = isset($blogPost['image_path']) && !empty($blogPost['image_path']) 
                ? 'uploads/' . $blogPost['image_path'] 
                : 'path/to/default-image.jpg'; // Default image if no image is available
            ?>
            
            <!-- Display the Image -->
            <img src="<?php echo htmlspecialchars($blogPostImage); ?>" alt="Post Image">

            <!-- Truncated Description (First 100 characters) -->
            <p><?php echo substr(htmlspecialchars($blogPost['description']), 0, 100) . '...'; ?></p>

            <!-- Read More Button -->
            <a href="view_user_detail.php?blog_id=<?php echo $blogPost['blog_id']; ?>" class="read-more-btn">Read More</a>
        </div>
    <?php endwhile; ?>

    <!-- Fetch and Display Admin Posts -->
    <?php while ($adminPost = $adminPostsResult->fetch_assoc()): ?>
        <div class="post-item">
            <!-- Post Title -->
            <h3><?php echo htmlspecialchars($adminPost['title']); ?></h3>
            
            <?php
            // Fetch the first image for each admin post
            $adminPostImage = isset($adminPost['image_name']) && !empty($adminPost['image_name']) 
                ? 'uploads/' . $adminPost['image_name'] 
                : 'path/to/default-image.jpg'; // Default image if no image is available
            ?>
            
            <!-- Display the Image -->
            <img src="<?php echo htmlspecialchars($adminPostImage); ?>" alt="Post Image">

            <!-- Truncated Description (First 100 characters) -->
            <p><?php echo substr(htmlspecialchars($adminPost['description']), 0, 100) . '...'; ?></p>

            <!-- Read More Button -->
            <a href="view_admin_post_detail.php?post_id=<?php echo $adminPost['post_id']; ?>" class="read-more-btn">Read More</a>
        </div>
    <?php endwhile; ?>
</div>





<?php if (isset($success_message) && $success_message != ''): ?>
    <script>
        // Show success message
        document.getElementById('success-message').style.display = 'block';

        // Wait for 3 seconds, then fade it out
        setTimeout(function() {
            document.getElementById('success-message').classList.add('fade-out');
            
            // After fading out, hide the message
            setTimeout(function() {
                document.getElementById('success-message').style.display = 'none';
            }, 1000); // Match the fade-out duration
        }, 3000); // Show message for 3 seconds
    </script>
<?php endif; ?>



<script>
    // Open Admin Login Modal
    document.getElementById('admin-login-btn').onclick = function() {
        document.getElementById('admin-login-modal').style.display = 'block';
    }

    // Open User Login Modal
    document.getElementById('user-login-btn').onclick = function() {
        document.getElementById('user-login-modal').style.display = 'block';
    }

    // Close Admin Login Modal
    document.getElementById('close-admin-login-modal').onclick = function() {
        document.getElementById('admin-login-modal').style.display = 'none';
    }

    // User Registration Modal
    var registerModal = document.getElementById("user-login-modal");
    var userLoginBtn = document.getElementById("user-login-btn");
    var closeRegisterModal = document.getElementById("close-register-modal");

    userLoginBtn.onclick = function() {
        registerModal.style.display = "block";
    }

    closeRegisterModal.onclick = function() {
        registerModal.style.display = "none";
    }

    // Get the modal for login
    var modal = document.getElementById("loginModal");

    // Reopen the modal if there was an error
    <?php if (isset($_SESSION['registration_error']) && $_SESSION['registration_error']): ?>
    registerModal.style.display = "block"; // Open the registration modal
    <?php unset($_SESSION['registration_error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']): ?>
registerModal.style.display = "block"; // Keep modal open after success
alert("<?php echo $_SESSION['registration_success']; ?>"); // Show success message
<?php unset($_SESSION['registration_success']); ?> // Remove session after showing
<?php endif; ?>

// **Reopen the login modal if login failed**
    <?php if (isset($_SESSION['reopen_modal']) && $_SESSION['reopen_modal']): ?>
        modal.style.display = "block"; // Open the login modal
        <?php unset($_SESSION['reopen_modal']); ?> // Clear session flag after reopening
    <?php endif; ?>
    
    // Open the modal on clicking the login link
    var loginLink = document.getElementById("loginLink");
    if (loginLink) {
        loginLink.onclick = function() {
            modal.style.display = "block";
        }
    }

    // Close login modal
    function closeLoginModal() {
        modal.style.display = "none";
    }

    // Close the modal when clicked outside
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
        if (event.target == document.getElementById('admin-login-modal')) {
            document.getElementById('admin-login-modal').style.display = 'none';
        }
        if (event.target == document.getElementById('user-login-modal')) {
            document.getElementById('user-login-modal').style.display = 'none';
        }
    }

    // Reset Password Modal Close
    function closeResetPasswordModal() {
        document.getElementById("resetPasswordModal").style.display = "none";
    }

document.getElementById("register-form").onsubmit = function(event) {
        var password = document.getElementById("password").value;
        var errorMessage = document.getElementById("password-error");

        // Regular expression to check password strength
        var passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

        if (!passwordRegex.test(password)) {
            event.preventDefault(); // Prevent form submission
            errorMessage.innerHTML = "Password must be at least 8 characters long, contain one uppercase letter, one number, and one special character.";
            errorMessage.style.display = "block";
        } else {
            errorMessage.style.display = "none";
        }
    };



</script>

</body>
</html>
