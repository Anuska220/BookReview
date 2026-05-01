<?php
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("
    SELECT r.*, b.title as book_title, b.id as book_id
    FROM reviews r
    JOIN books b ON r.book_id = b.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reviews - Book Reviews</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="logo">📚 BookReviews</h1>
            <div class="nav-links">
                <a href="books_list.php">Home</a>
                <a href="my_reviews.php">My Reviews</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>My Reviews</h1>
        
        <?php if(empty($reviews)): ?>
            <div class="empty-state">
                <p>You haven't written any reviews yet.</p>
                <a href="books_list.php" class="btn">Browse Books</a>
            </div>
        <?php else: ?>
            <?php foreach($reviews as $review): ?>
                <div class="review">
                    <h3><a href="book_details.php?id=<?php echo $review['book_id']; ?>"><?php echo htmlspecialchars($review['book_title']); ?></a></h3>
                    <div class="rating">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                        <?php endfor; ?>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    <div class="review-actions">
                        <a href="edit_review.php?id=<?php echo $review['id']; ?>" class="btn btn-warning">Edit</a>
                        <a href="delete_review.php?id=<?php echo $review['id']; ?>&book_id=<?php echo $review['book_id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this review?')">Delete</a>
                    </div>
                    <small>Posted on <?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <footer class="footer">
        <p>&copy; 2026 Book Review System. All rights reserved.</p>
    </footer>
</body>
</html>