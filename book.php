<?php
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($book_id <= 0) {
    die("Invalid book ID");
}

// Get book details with prepared statement
$stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $book_id);
mysqli_stmt_execute($stmt);
$book_result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($book_result);
mysqli_stmt_close($stmt);

if (!$book) {
    die("Book not found");
}

// Get approved reviews with user names
$stmt = mysqli_prepare($conn, "
    SELECT r.*, u.name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.book_id = ? AND r.status = 'approved' 
    ORDER BY r.created_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $book_id);
mysqli_stmt_execute($stmt);
$reviews_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// Calculate average rating
$stmt = mysqli_prepare($conn, "
    SELECT AVG(rating) as avg_rating, COUNT(*) as total 
    FROM reviews 
    WHERE book_id = ? AND status = 'approved'
");
mysqli_stmt_bind_param($stmt, "i", $book_id);
mysqli_stmt_execute($stmt);
$rating_stats = mysqli_stmt_get_result($stmt);
$stats = mysqli_fetch_assoc($rating_stats);
mysqli_stmt_close($stmt);

// Check if user already reviewed this book
$user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "SELECT id FROM reviews WHERE book_id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $book_id, $user_id);
mysqli_stmt_execute($stmt);
$existing_review = mysqli_stmt_get_result($stmt);
$has_reviewed = mysqli_num_rows($existing_review) > 0;
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($book['title']); ?> - Book Review System</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .book-info { margin: 20px 0; padding: 15px; background: #f0f0f0; }
        .review { margin: 15px 0; padding: 10px; border-left: 3px solid #ccc; }
        .rating { color: gold; font-size: 20px; }
        .review-form { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        textarea { width: 100%; height: 100px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <a href="books_list.php">← Back to Books</a>
    
    <div class="book-info">
        <h2><?php echo htmlspecialchars($book['title']); ?></h2>
        <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
        <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
        
        <?php if ($stats['total'] > 0): ?>
            <p><strong>Average Rating:</strong> 
                <?php echo round($stats['avg_rating'], 1); ?> ⭐ 
                (<?php echo $stats['total']; ?> reviews)
            </p>
        <?php else: ?>
            <p><em>No reviews yet. Be the first to review!</em></p>
        <?php endif; ?>
    </div>
    
    <?php if (!$has_reviewed): ?>
        <div class="review-form">
            <h3>Write a Review</h3>
            <form method="POST" action="submit_review.php">
                <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                <textarea name="review" placeholder="Your review..." required></textarea><br>
                <select name="rating" required>
                    <option value="">Select Rating</option>
                    <option value="5">5 ⭐ - Excellent</option>
                    <option value="4">4 ⭐ - Good</option>
                    <option value="3">3 ⭐ - Average</option>
                    <option value="2">2 ⭐ - Poor</option>
                    <option value="1">1 ⭐ - Terrible</option>
                </select><br>
                <button type="submit">Submit Review</button>
            </form>
        </div>
    <?php else: ?>
        <p><em>You have already reviewed this book.</em></p>
    <?php endif; ?>
    
    <h3>Reviews (<?php echo $stats['total']; ?>)</h3>
    <?php if (mysqli_num_rows($reviews_result) > 0): ?>
        <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
            <div class="review">
                <div class="rating">
                    <?php echo str_repeat('⭐', $review['rating']); ?>
                </div>
                <p><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                <small>By <?php echo htmlspecialchars($review['name']); ?> 
                       on <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                </small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No approved reviews yet.</p>
    <?php endif; ?>
</body>
</html>