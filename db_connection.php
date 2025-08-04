<?php
$host = 'localhost'; // or your database server
$user = 'root'; // your MySQL username
$password = ''; // your MySQL password (or specify it if you have one)
$database = 'dinediary'; // your database name

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>