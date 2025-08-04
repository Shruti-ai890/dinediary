<?php
// Include database connection
include('db_connection.php');

// Initialize variables
$search_query = '';
$users_result = [];

// Handle search
if (isset($_GET['query'])) {
    $search_query = $_GET['query'];

    // Search query for users based on name or email
    $query = "SELECT * FROM users WHERE name LIKE '%$search_query%' OR email LIKE '%$search_query%'";
    $users_result = mysqli_query($conn, $query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Search</title>
    <style>
           <style>
        /* Add the same CSS styles as in admin_search.php */
.form-container {
            width: 90%;
            margin: 20px auto;
            max-width: 1200px; /* Adjust max-width if needed */
            padding: 20px;
            background-color: white; /* Light background color */
            border-radius: 8px; /* Rounded corners for the container */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Soft shadow */
        }

        /* Highlight matched rows */
        .highlight {
            background-color: #f0f8ff; /* Yellow highlight */
        }

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

        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f2f2f2;
        }

        table td a {
            background-color: #DC143C;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        table td a:hover {
            background-color: #9B0F26;
        }
    </style>
</head>
<body>
 <div class="table-container">
    <h2  style="font-size: 2rem; text-align: center;">Search Results for Users</h2>

<a href="admin_dashboard.php#usersForm" style="text-decoration: none; padding: 10px 20px; background-color: #DC143C; color: white; border-radius: 5px; font-size: 14px;">Back </a>



    <table border="1">
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Name</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($users_result) > 0) {
                $srNo = 1;
                while ($user = mysqli_fetch_assoc($users_result)) {
                    echo "<tr class='highlight'>
                            <td>{$srNo}</td>
                            <td>{$user['name']}</td>
                            <td>{$user['email']}</td>
                            <td>
                                <a href='user_post_detail.php?id={$user['id']}'>View</a>
                            </td>
                          </tr>";
                    $srNo++;
                }
            } else {
                echo "<tr><td colspan='4'>No users found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
