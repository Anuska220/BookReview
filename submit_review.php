<?php
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: books_list.php");
    exit();
}

$book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
$review = trim($_POST['review'] ?? '');
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$user_id = $_SESSION['user_id'];

// Validate inputs
if ($book_id <= 0 || empty($review) || $rating < 1 || $rating > 5) {
    $_SESSION['error'] = "Invalid input. Please check all fields.";
    header("Location: book.php?id=$book_id");
    exit();
}

// Check if user already reviewed this book
$stmt = mysqli_prepare($conn, "SELECT id FROM reviews WHERE book_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $book_id, $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    $_SESSION['error'] = "You have already reviewed this book.";
    mysqli_stmt_close($stmt);
    header("Location: book.php?id=$book_id");
    exit();
}
mysqli_stmt_close($stmt);

// Insert review
$status = 'pending'; // Requires admin approval
$stmt = mysqli_prepare($conn, "INSERT INTO reviews (book_id, user_id, review, rating, status) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "iisis", $book_id, $user_id, $review, $rating, $status);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = "Review submitted successfully! Waiting for admin approval.";
} else {
    $_SESSION['error'] = "Failed to submit review. Please try again.";
}

mysqli_stmt_close($stmt);
header("Location: book.php?id=$book_id");
exit();
?>