<?php
// Include database connection file
include('db_connection.php');

// Initialize search query variable
$query = '';
$results = [];

// Check if the search form is submitted
if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Sanitize the query to prevent SQL injection
    $search_query = mysqli_real_escape_string($conn, $query);

    // Search query for matching title, city, area, cuisine_name, category_name, and explore_name
    $sql = "SELECT * FROM admin_posts WHERE 
            title LIKE '%$search_query%' OR
            city LIKE '%$search_query%' OR
            area LIKE '%$search_query%' OR
            cuisine_name LIKE '%$search_query%' OR
            category_name LIKE '%$search_query%' OR
            explore_name LIKE '%$search_query%'";

    // Execute the query
    $results = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Post Search</title>
    <style>

.form-container {
    width: 90%;
            margin: 20px auto;
            max-width: 1200px; /* Adjust max-width if needed */
            padding: 20px;
            background-color: white; /* Light background color */
            border-radius: 8px; /* Rounded corners for the container */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Soft shadow */
}
        /* Search bar and button styles */
        .search-bar {
            text-align: right;
            margin: 10px;
        }

        .search-bar input[type="text"] {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
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

        /* Table styles */

/* Table container style (wrap the table in this for styling) */
.table-container {
    overflow-x: auto; /* Allow horizontal scrolling if the table is wide */
    margin-top: 20px;
    border: 1px solid #ddd; /* Border for the table container */
    border-radius: 8px; /* Rounded corners for the container */
    background-color: #fff; /* White background for the table */
    padding: 10px; /* Padding around the table */
}
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 8px 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        tr.highlight {
            background-color: #f0f8ff; /* Light blue highlight */
        }
    </style>
</head>
<body>

 <div class="form-container">   

    <!-- Displaying Search Results -->

<h2  style="font-size: 2rem; text-align: center;">Search Results for Admin Posts</h2>

<!-- Back Button --><a href="admin_dashboard.php#usersForm" style="text-decoration: none; padding: 10px 20px; background-color: #DC143C; color: white; border-radius: 5px; font-size: 14px;">Back </a>

 <!-- Search Bar -->
    <div class="search-bar">
        <form action="admin_search.php" method="GET">
            <input type="text" name="query" placeholder="Search by title, city, area, cuisine, category, explore..." value="<?php echo $query; ?>" required>
            <button type="submit">Search</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>City</th>
                <th>Area</th>
                <th>Cuisine</th>
                <th>Category</th>
                <th>Explore</th>
               <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($results) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($results)): ?>
                    <tr id="row-<?php echo $row['post_id']; ?>" class="search-result">
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['city']; ?></td>
                        <td><?php echo $row['area']; ?></td>
                        <td><?php echo $row['cuisine_name']; ?></td>
                        <td><?php echo $row['category_name']; ?></td>
                        <td><?php echo $row['explore_name']; ?></td>
                       <td>
    <?php
    echo "<a href='edit_admin_post.php?id=" . $row['post_id'] . "' 
            style='background-color: #DC143C; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block; margin-right: 10px;' 
            onmouseover='this.style.backgroundColor=\"#9B0F26\"' 
            onmouseout='this.style.backgroundColor=\"#DC143C\"'>Edit</a> | ";
    echo "<a href='delete_admin_post.php?id=" . $row['post_id'] . "' 
            style='background-color: #DC143C; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; display: inline-block;' 
            onmouseover='this.style.backgroundColor=\"#9B0F26\"' 
            onmouseout='this.style.backgroundColor=\"#DC143C\"'>Delete</a>";
    ?>
</td>

                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6">No posts found matching your search criteria.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
    <!-- JavaScript to handle scrolling and highlighting -->
    <script>
        // Highlight and scroll to the first match after page load
        document.addEventListener("DOMContentLoaded", function() {
            var searchResults = document.querySelectorAll('.search-result');
            var query = '<?php echo $query; ?>';
            
            // Loop through the results to find matching rows and highlight them
            searchResults.forEach(function(row) {
                if (row.innerText.toLowerCase().includes(query.toLowerCase())) {
                    row.classList.add('highlight');
                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });

            // Remove highlight when clicked anywhere on the screen
            document.addEventListener('click', function() {
                searchResults.forEach(function(row) {
                    row.classList.remove('highlight');
                });
            });
        });
    </script>

</body>
</html>
