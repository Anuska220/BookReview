<?php
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$book_id = $_GET['book_id'] ?? 0;

// Check if already reviewed
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE book_id = ? AND user_id = ?");
$stmt->execute([$book_id, $_SESSION['user_id']]);
if ($stmt->fetch()) {
    header('Location: book_details.php?id=' . $book_id);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    $stmt = $pdo->prepare("INSERT INTO reviews (book_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->execute([$book_id, $_SESSION['user_id'], $rating, $comment]);
    
    header('Location: book_details.php?id=' . $book_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Review - Book Reviews</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="logo">📚 BookReviews</h1>
            <div class="nav-links">
                <a href="books_list.php">Home</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="auth-form">
            <h2>Write a Review</h2>
            <form method="POST">
                <select name="rating" required>
                    <option value="">Select Rating</option>
                    <option value="5">★★★★★ (5 stars)</option>
                    <option value="4">★★★★☆ (4 stars)</option>
                    <option value="3">★★★☆☆ (3 stars)</option>
                    <option value="2">★★☆☆☆ (2 stars)</option>
                    <option value="1">★☆☆☆☆ (1 star)</option>
                </select>
                <textarea name="comment" placeholder="Your review..." rows="5" required></textarea>
                <button type="submit" class="btn btn-primary">Submit Review</button>
                <a href="book_details.php?id=<?php echo $book_id; ?>" class="btn">Cancel</a>
            </form>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2026 Book Review System. All rights reserved.</p>
    </footer>
</body>
</html>