<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php'); // Redirect to login page if not logged in
    exit;
}

// Include the database connection file
include('db_connection.php');



// Initialize success messages
$successMessage = [
    'add_cuisine' => '',
    'add_explore' => '',
    'add_category' => ''
];

// Handle Add Cuisine Form Submission
if (isset($_POST['add_cuisine'])) {
    $cuisine = $_POST['cuisine'];
    // Perform your insert query here for the cuisine
    $sql = "INSERT INTO cuisines (cuisine_name) VALUES ('$cuisine')";
    if (mysqli_query($conn, $sql)) {
        $successMessage['add_cuisine'] = "Cuisine added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

// Handle Add Explore Form Submission
if (isset($_POST['add_explore'])) {
    $explore = $_POST['explore'];
    // Perform your insert query here for explore
    $sql = "INSERT INTO explore_options (explore_name) VALUES ('$explore')";
    if (mysqli_query($conn, $sql)) {
        $successMessage['add_explore'] = "Explore added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

// Handle Add Category Form Submission
if (isset($_POST['add_category'])) {
    $category = $_POST['category'];
    // Perform your insert query here for category
    $sql = "INSERT INTO categories (category_name) VALUES ('$category')";
    if (mysqli_query($conn, $sql)) {
        $successMessage['add_category'] = "Category added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}




$sql = "
    SELECT 
        ap.post_id,
        ap.title AS place_name,
        ap.description,
        MIN(api.image_name) AS image,  -- Get the first image for each post
        ap.cuisine_name,
        ap.explore_name,
        ap.category_name,
        ap.city,
        ap.area,
        ap.google_maps_link
    FROM 
        admin_posts ap
    LEFT JOIN 
        admin_post_images api ON ap.post_id = api.post_id
    GROUP BY 
        ap.post_id
";
$result = mysqli_query($conn, $sql);


// Fetch cuisines
$cuisines_query = "SELECT * FROM cuisines";
$cuisines_result = mysqli_query($conn, $cuisines_query);

// Fetch explore_options
$explore_options_query = "SELECT * FROM explore_options";
$explore_options_result = mysqli_query($conn, $explore_options_query);

// Fetch categories
$categories_query = "SELECT * FROM categories";
$categories_result = mysqli_query($conn, $categories_query);


// Fetch users from the 'users' table
$users_sql = "SELECT id, name, email, phone_number FROM users";
$users_result = mysqli_query($conn, $users_sql);


// Check if the query was successful
if (!$users_result) {
    echo "Error: " . mysqli_error($connection);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - DineDiary</title>

    <style>
        /* Admin specific styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        /* Header Styling with #C70039 */
        header {
            background-color: #C70039; /* Header color */
            color: white;
            text-align: center;
            padding: 10px 0; /* Reduced padding */
        }

        header h1 {
            margin: 0;
            font-size: 2em; /* Reduced font size */
        }

        /* Logout Button Styling at Top Left Corner */
        .logout-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #DC143C;
            color: white;
            padding: 10px 20px;
            font-size: 14px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #9B0F26; /* Hover color */
        }

        .logout-btn:active {
            background-color: #8B0A22; /* Active state color */
        }

        /* Button Container (Horizontal Row) inside header */
        .header-buttons {
            display: flex;
            justify-content: center;
            gap: 15px; /* Reduced gap between buttons */
            margin-top: 15px; /* Reduced space between title and buttons */
        }

        /* Button Styling with #DC143C for normal state */
        .header-buttons a {
            background-color: #DC143C; /* Button color */
            color: white;
            text-decoration: none;
            padding: 12px 25px; /* Reduced padding */
            font-size: 16px; /* Reduced font size */
            border-radius: 8px;
            transition: background-color 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Button hover effect with #9B0F26 */
        .header-buttons a:hover {
            background-color: #9B0F26; /* Hover color */
        }

        /* Button active state */
        .header-buttons a:active {
            background-color: #8B0A22; /* Slightly darker active state */
        }

        /* Main container for the dashboard content */
        .container {
            padding: 40px;
            text-align: center;
        }


/* Admin specific styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #C70039;
            color: white;
            text-align: center;
            padding: 10px 0;
        }

        header h1 {
            margin: 0;
            font-size: 2em;
        }

        .logout-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #DC143C;
            color: white;
            padding: 10px 20px;
            font-size: 14px;
            text-decoration: none;
            border-radius: 5px;
        }

        .logout-btn:hover {
            background-color: #9B0F26;
        }

        .header-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
        }

        .header-buttons a {
            background-color: #DC143C;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            font-size: 16px;
            border-radius: 8px;
        }

        .header-buttons a:hover {
            background-color: #9B0F26;
        }

        .container {
            padding: 40px;
            text-align: center;
        }

       /* Prevent Horizontal Overflow */
body, .container {
    overflow-x: hidden;  /* Prevents horizontal scrolling */
    margin: 0;
    padding: 0;
}

/* Form container with no horizontal overflow */
.form-container {
    display: none;
    background: white;
    margin: 20px auto;
    padding: 30px;
    max-width: 100%; /* Ensures it does not overflow horizontally */
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    box-sizing: border-box; /* Ensures padding doesn't cause overflow */
}

/* Ensure inputs, select, and textarea do not overflow */
.form-container input,
.form-container select,
.form-container textarea,
.form-container button {
    width: 100%;  /* Makes elements take up full available width */
    padding: 12px;
    font-size: 16px;
    margin-bottom: 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
    box-sizing: border-box; /* Prevents padding from causing overflow */
}
/* Adjusting label styling */
.form-container label {
    font-size: 18px; /* Larger font size for labels */
    color: #333;
    margin-bottom: 8px;
    display: block;
}


/* Add some basic box-sizing for overall container */
.form-container {
    box-sizing: border-box; /* Prevents padding/margin from causing overflow */
}

/* Button styling for submit */
.form-container button {
    background-color: #DC143C;
    color: white;
    padding: 15px 25px; /* Larger button padding */
    border: none;
    cursor: pointer;
    font-size: 18px;
}

.form-container button:hover {
    background-color: #9B0F26;
}


/* Form container */
        .form-container {
            display: none;
            background: white;
            margin: 20px auto;
            padding: 30px;
            max-width: 700px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-container.active {
            display: block;
        }

        .form-container h2 {
            color: #C70039;
        }

        .success-message {
            color: green;
            margin-top: 10px;
            font-size: 14px;
            display: none;
        }

/* Button Styling for Add Cuisine, Explore, and Add Category buttons */
header .header-buttons button {
    background-color: #DC143C; /* Button color */
    color: white;
    border: none;
    padding: 12px 25px; /* Padding for spacing */
    font-size: 16px; /* Font size */
    border-radius: 8px; /* Rounded corners */
    transition: background-color 0.3s ease; /* Smooth background color transition */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Light shadow */
    cursor: pointer; /* Pointer cursor on hover */
    margin: 5px; /* Space between the buttons */
}

header .header-buttons button:hover {
    background-color: #9B0F26; /* Hover color */
}

header .header-buttons button:active {
    background-color: #8B0A22; /* Active state color */
}

/* Button Container Styling */
.header-buttons {
    display: flex;
    justify-content: center;
    gap: 15px; /* Space between buttons */
    margin-top: 15px; /* Space between header and buttons */
}

/* Additional Button Styling inside the form container (if needed) */
.form-container button {
    background-color: #DC143C;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.form-container button:hover {
    background-color: #9B0F26;
}


/* Button Styling for Admin and Add Recommendation buttons */
header .header-buttons a {
    background-color: #DC143C; /* Button color */
    color: white;
    text-decoration: none; /* Remove underline */
    padding: 12px 25px; /* Padding for spacing */
    font-size: 16px; /* Font size */
    border-radius: 8px; /* Rounded corners */
    transition: background-color 0.3s ease; /* Smooth background color transition */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Light shadow */
    cursor: pointer; /* Pointer cursor on hover */
    margin: 5px; /* Space between the buttons */
}

/* Hover effect for Admin and Add Recommendation buttons */
header .header-buttons a:hover {
    background-color: #9B0F26; /* Hover color */
}

/* Active state for Admin and Add Recommendation buttons */
header .header-buttons a:active {
    background-color: #8B0A22; /* Active state color */
}


/* Container Style for the Table */
.form-container {
    background-color: #fff;
    padding: 20px;
    margin: 20px 0;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 100%;
    overflow-x: auto;
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


.success-message {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 10px;
    margin-bottom: 20px;
    text-align: center;
    border-radius: 5px;
    display: none; /* Initially hidden */
}

    </style>
</head>
<body>

<!-- Logout Button -->
<a href="logout.php" class="logout-btn">Logout</a>

<header>
    <h1>Admin Dashboard</h1>

    <div class="header-buttons">
        <!-- Admin Button -->
        <a href="admin_dashboard.php">Admin</a>

        <!-- Buttons to show respective forms -->
        <button onclick="showForm('cuisineForm')">Add Cuisine</button>
        <button onclick="showForm('exploreForm')">Add Explore</button>
        <button onclick="showForm('categoryForm')">Add Categories</button>
<!-- Users Button -->
    <button id="usersButton" onclick="showForm('usersForm')">Users</button>
         
        <!-- Add Post Button -->
        <a href="admin_post.php">Add Post</a>
    </div>
</header>



<?php if (isset($_GET['message'])): ?>
    <div class="success-message">
        <?php echo htmlspecialchars($_GET['message']); ?>
    </div>
<?php endif; ?>


<div class="container">
    <!-- Add Cuisine Form -->
    <div id="cuisineForm" class="form-container">
        <h2>Add Cuisine</h2>
        <form action="" method="POST">
            <label for="cuisine"><b>Cuisine Name:</b></label><br>
            <input type="text" id="cuisine" name="cuisine" required><br><br>
            <button type="submit" name="add_cuisine">Add Cuisine</button>
        </form>
        <div class="success-message" id="cuisineSuccessMessage">
            <?php echo isset($successMessage['add_cuisine']) ? $successMessage['add_cuisine'] : ''; ?>
        </div>
    </div>

    <!-- Add Explore Form -->
    <div id="exploreForm" class="form-container">
        <h2>Add Explore</h2>
        <form action="" method="POST">
            <label for="explore"><b>Explore Name:</b></label><br>
            <input type="text" id="explore" name="explore" value="<?php echo isset($_POST['explore']) ? $_POST['explore'] : ''; ?>" required><br><br>
            <button type="submit" name="add_explore">Add Explore</button>
        </form>
        <div class="success-message" id="exploreSuccessMessage">
            <?php echo isset($successMessage['add_explore']) ? $successMessage['add_explore'] : ''; ?>
        </div>
    </div>

    <!-- Add Category Form -->
    <div id="categoryForm" class="form-container">
        <h2>Add Category</h2>
        <form action="" method="POST">
            <label for="category"><b>Category Name:</b></label><br>
            <input type="text" id="category" name="category" value="<?php echo isset($_POST['category']) ? $_POST['category'] : ''; ?>" required><br><br>
            <button type="submit" name="add_category">Add Category</button>
        </form>
        <div class="success-message" id="categorySuccessMessage">
            <?php echo isset($successMessage['add_category']) ? $successMessage['add_category'] : ''; ?>
        </div>
    </div>

</div>



<div class="container">
    <div id="adminTable" class="form-container active">
        <h2>Admin Added Posts</h2>

<!-- Search Bar below Heading -->
<div class="search-bar" style="text-align: right; margin-top: 10px;">
    <form action="admin_search.php" method="GET" class="search-bar" style="display: inline-block;">
        <input type="text" name="query" placeholder="Search..." required 
               style="padding: 5px 10px; width: 180px; border: 1px solid #ccc; border-radius: 5px; margin-right: 5px;">
        <button type="submit" 
                style="padding: 5px 10px;width: 90px; border: none; background-color: #DC143C; color: white; 
                       border-radius: 5px; cursor: pointer; font-size: 14px; transition: background-color 0.3s;">
            Search
        </button>
    </form>
</div>


        <table border="1" class="admin-table">
            <thead>
                <tr>
                    <th>Sr. No.</th>
                    <th>Place Name</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Cuisine</th>
                    <th>Explore</th>
                    <th>Category</th>
                    <th>City</th>
                    <th>Area</th>
                    <th>Google Maps Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    $srNo = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>
                                <td>" . $srNo++ . "</td>
                                <td>" . $row['place_name'] . "</td>
                                <td>" . $row['description'] . "</td>
                                <td><img src='uploads/" . $row['image'] . "' alt='Post Image' width='100'></td>
                                <td>" . $row['cuisine_name'] . "</td>
                                <td>" . $row['explore_name'] . "</td>
                                <td>" . $row['category_name'] . "</td>
                                <td>" . $row['city'] . "</td>
                                <td>" . $row['area'] . "</td>
      <td>
    <a href='" . $row['google_maps_link'] . "' target='_blank' 
       style='background-color: #DC143C; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block;' 
       onmouseover='this.style.backgroundColor=\"#9B0F26\"' 
       onmouseout='this.style.backgroundColor=\"#DC143C\"'>View</a>

</td>


                                <td>
 <a href='edit_admin_post.php?id=" . $row['post_id'] . "' 
                               style='background-color: #DC143C; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block; margin-right: 10px;' 
                               onmouseover='this.style.backgroundColor=\"#9B0F26\"' 
                               onmouseout='this.style.backgroundColor=\"#DC143C\"'>Edit</a>
                            |
                            <a href='delete_admin_post.php?id=" . $row['post_id'] . "' 
                               style='background-color: #DC143C; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block;' 
                               onmouseover='this.style.backgroundColor=\"#9B0F26\"' 
                               onmouseout='this.style.backgroundColor=\"#DC143C\"'>Delete</a>


                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='11'>No posts found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>



<!-- Cuisines Table -->
    <div id="cuisinesTable" class="form-container active">
        <h2>Cuisines</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Sr. No.</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sr_no = 1;
                while ($row = mysqli_fetch_assoc($cuisines_result)) {
                    echo "<tr>
                            <td>$sr_no</td>
                            <td>{$row['cuisine_name']}</td>
                            <td>
                              <a href='edit_cuisine.php?id={$row['cuisine_id']}'
   style='background-color: #DC143C; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block; margin-right: 10px;'
   onmouseover='this.style.backgroundColor=\"#9B0F26\"'
   onmouseout='this.style.backgroundColor=\"#DC143C\"'>Edit</a> |
<a href='delete_cuisine.php?id={$row['cuisine_id']}'
   style='background-color: #DC143C; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block;'
   onmouseover='this.style.backgroundColor=\"#9B0F26\"'
   onmouseout='this.style.backgroundColor=\"#DC143C\"'>Delete</a>



                            </td>
                          </tr>";
                    $sr_no++;
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Explore Options Table -->
    <div id="exploreOptionsTable" class="form-container active">
        <h2>Explore Options</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Sr. No.</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sr_no = 1;
                while ($row = mysqli_fetch_assoc($explore_options_result)) {
                    echo "<tr>
                            <td>$sr_no</td>
                            <td>{$row['explore_name']}</td>
                            <td>
                                <a href='edit_explore.php?id={$row['explore_id']}' 
   style='background-color: #DC143C; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block; margin-right: 10px;' 
   onmouseover='this.style.backgroundColor=\"#9B0F26\"' 
   onmouseout='this.style.backgroundColor=\"#DC143C\"'>Edit</a> | 
<a href='delete_explore_option.php?id={$row['explore_id']}' 
   style='background-color: #DC143C; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block;' 
   onmouseover='this.style.backgroundColor=\"#9B0F26\"' 
   onmouseout='this.style.backgroundColor=\"#DC143C\"'>Delete</a>



                            </td>
                          </tr>";
                    $sr_no++;
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Categories Table -->
    <div id="categoriesTable" class="form-container active">
        <h2>Categories</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>Sr. No.</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sr_no = 1;
                while ($row = mysqli_fetch_assoc($categories_result)) {
                    echo "<tr>
                            <td>$sr_no</td>
                            <td>{$row['category_name']}</td>
                            <td>
                               <a href='edit-category.php?id={$row['category_id']}' 
                               style='background-color: #DC143C; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block; margin-right: 10px;' 
                               onmouseover='this.style.backgroundColor=\"#9B0F26\"' 
                               onmouseout='this.style.backgroundColor=\"#DC143C\"'>Edit</a> | 

                            <a href='delete_category.php?id={$row['category_id']}' 
                               style='background-color: #DC143C; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block;' 
                               onmouseover='this.style.backgroundColor=\"#9B0F26\"' 
                               onmouseout='this.style.backgroundColor=\"#DC143C\"'>Delete</a>

                            </td>
                          </tr>";
                    $sr_no++;
                }
                ?>
            </tbody>
        </table>
    </div>

<!-- Users Form (separate content for Users table) -->
<div id="usersForm" class="form-container">
<h2>Users</h2>

<!-- Search Bar below Heading -->
<div class="search-bar" style="text-align: right; margin-top: 10px;">
    <form action="user_search.php" method="GET" class="search-bar" style="display: inline-block;">
        <input type="text" name="query" placeholder="Search..." required 
               style="padding: 5px 10px; width: 180px; border: 1px solid #ccc; border-radius: 5px; margin-right: 5px;">
        <button type="submit" 
                style="padding: 5px 10px; width: 80px; border: none; background-color: #DC143C; color: white; 
                       border-radius: 5px; cursor: pointer; font-size: 14px; transition: background-color 0.3s;">
            Search
        </button>
    </form>
</div>

    <table id="usersContainer"  border="1" style="display:none;">
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Name</th>
                <th>Email</th>
                 <th>Phone Number</th>
                <th>View</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($users_result) > 0): ?>
                <?php $srNo = 1; ?>
                <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                    <tr>
                        <td><?php echo $srNo++; ?></td>
                        <td><?php echo $user['name']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['phone_number'];?></td>
                        <td>
<div style="text-align: center;">
    <a href="user_post_detail.php?id=<?php echo $user['id']; ?>" 
       style="background-color: #DC143C; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block;" 
       onmouseover="this.style.backgroundColor='#9B0F26'" 
       onmouseout="this.style.backgroundColor='#DC143C'">View</a>
</div>
</td>


                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No users found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</div>




<script>
    // Show specific form when clicked
    function showForm(formId) {
        const forms = document.querySelectorAll('.form-container');
        forms.forEach(form => form.classList.remove('active')); // Hide all forms
        document.getElementById(formId).classList.add('active'); // Show the selected form
    }

    // Display success messages temporarily
    document.addEventListener('DOMContentLoaded', () => {
        const successMessages = document.querySelectorAll('.success-message');
        successMessages.forEach(message => {
            if (message.textContent.trim() !== '') {
                message.style.display = 'block'; // Show the message
                setTimeout(() => {
                    message.style.display = 'none'; // Hide after 3 seconds
                }, 3000);
            }
        });
    });

document.addEventListener('DOMContentLoaded', function () {
    const usersButton = document.getElementById('usersButton');
    const usersContainer = document.getElementById('usersContainer');

    if (usersButton && usersContainer) {
        // Add event listener for the Users button click
        usersButton.addEventListener('click', function () {
            // Toggle visibility of users table
            if (usersContainer.style.display === 'none' || usersContainer.style.display === '') {
                usersContainer.style.display = 'table';  // Show the users table
            } else {
                usersContainer.style.display = 'none';   // Hide the users table
            }
        });
    }
});
</script>


</body>


</body>
</html>
