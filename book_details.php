<?php 
session_start();
require_once 'database.php';

// Check connection
if (!isset($conn) || !$conn) {
    die("Database connection failed");
}

if (!isset($_GET['id'])) {
    header('Location: books_list.php');
    exit();
}

$book_id = $_GET['id'];

// Fetch book details using MySQLi
$stmt = mysqli_prepare($conn, "
    SELECT b.*, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count
    FROM books b
    LEFT JOIN reviews r ON b.id = r.book_id
    WHERE b.id = ?
    GROUP BY b.id
");

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $book_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$book) {
    die("Book not found");
}

// Fetch reviews using MySQLi
$stmt = mysqli_prepare($conn, "
    SELECT r.*, u.username
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.book_id = ?
    ORDER BY r.id DESC
");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $book_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $reviews = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reviews[] = $row;
    }
    mysqli_stmt_close($stmt);
} else {
    $reviews = [];
}

// Check if user has reviewed
$user_review = null;
if (isset($_SESSION['user_id'])) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM reviews WHERE book_id = ? AND user_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $book_id, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user_review = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - Book Reviews</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="logo">📚 BookReviews</h1>
            <div class="nav-links">
                <a href="books_list.php">Home</a>
                <a href="search.php">Search</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="my_reviews.php">My Reviews</a>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="book-detail">
            <div class="book-header">
                <?php if($book['cover_image']): ?>
                    <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Cover" class="detail-cover">
                <?php endif; ?>
                
                <div class="book-info">
                    <h1><?php echo htmlspecialchars($book['title']); ?></h1>
                    <h3>by <?php echo htmlspecialchars($book['author']); ?></h3>
                    
                    <div class="rating-large">
                        <?php 
                        $avg_rating = round($book['avg_rating'], 1);
                        for($i = 1; $i <= 5; $i++): ?>
                            <?php echo $i <= $avg_rating ? '★' : '☆'; ?>
                        <?php endfor; ?>
                        <span><?php echo number_format($avg_rating, 1); ?> / 5 (<?php echo $book['review_count']; ?> reviews)</span>
                    </div>
                    
                    <p class="description"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                    
                    <?php if(isset($_SESSION['user_id']) && !$user_review): ?>
                        <a href="add_review.php?book_id=<?php echo $book_id; ?>" class="btn btn-primary">Write a Review</a>
                    <?php elseif(isset($_SESSION['user_id']) && $user_review): ?>
                        <div class="review-actions">
                            <a href="edit_review.php?id=<?php echo $user_review['id']; ?>" class="btn btn-warning">Edit Your Review</a>
                            <a href="delete_review.php?id=<?php echo $user_review['id']; ?>&book_id=<?php echo $book_id; ?>" class="btn btn-danger" onclick="return confirm('Delete your review?')">Delete Your Review</a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn">Login to Write a Review</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <hr>
            
            <h2>Reviews (<?php echo count($reviews); ?>)</h2>
            <?php foreach($reviews as $review): ?>
                <div class="review">
                    <div class="review-header">
                        <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                        <div class="review-rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    <small><?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2026 Book Review System. All rights reserved.</p>
    </footer>
</body>
</html>