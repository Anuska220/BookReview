<?php
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$review_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM reviews WHERE id = ? AND user_id = ?");
$stmt->execute([$review_id, $_SESSION['user_id']]);
$review = $stmt->fetch();

if (!$review) {
    die("Review not found");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE id = ?");
    $stmt->execute([$rating, $comment, $review_id]);
    
    header('Location: book_details.php?id=' . $review['book_id']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Review - Book Reviews</title>
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
            <h2>Edit Your Review</h2>
            <form method="POST">
                <select name="rating" required>
                    <option value="5" <?php echo $review['rating'] == 5 ? 'selected' : ''; ?>>★★★★★ (5 stars)</option>
                    <option value="4" <?php echo $review['rating'] == 4 ? 'selected' : ''; ?>>★★★★☆ (4 stars)</option>
                    <option value="3" <?php echo $review['rating'] == 3 ? 'selected' : ''; ?>>★★★☆☆ (3 stars)</option>
                    <option value="2" <?php echo $review['rating'] == 2 ? 'selected' : ''; ?>>★★☆☆☆ (2 stars)</option>
                    <option value="1" <?php echo $review['rating'] == 1 ? 'selected' : ''; ?>>★☆☆☆☆ (1 star)</option>
                </select>
                <textarea name="comment" placeholder="Your review..." rows="5" required><?php echo htmlspecialchars($review['comment']); ?></textarea>
                <button type="submit" class="btn btn-primary">Update Review</button>
                <a href="book_details.php?id=<?php echo $review['book_id']; ?>" class="btn">Cancel</a>
            </form>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2026 Book Review System. All rights reserved.</p>
    </footer>
</body>
</html>