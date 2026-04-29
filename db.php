<?php
// Error reporting - turn off in production
error_reporting(0);
ini_set('display_errors', 0);

$conn = mysqli_connect("localhost", "root", "", "book_review");

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Set charset to prevent some XSS and encoding issues
mysqli_set_charset($conn, "utf8mb4");

session_start();
?>