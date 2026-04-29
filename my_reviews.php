<?php
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's reviews
$stmt = mysqli_prepare($conn, "
    SELECT r.*, b.title as book_title 
    FROM reviews r 
    JOIN books b ON r.book_id = b.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$reviews_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Reviews - Book Review System</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .review-item { margin: 15px 0; padding: 10px; border: 1px solid #ddd; }
        .status-pending { color: orange; }
        .status-approved { color: green; }
        .rating { color: gold; }
    </style>
</head>
<body>
    <h2>My Reviews</h2>
    <a href="dashboard.php">← Back to Dashboard</a>
    
    <?php if (mysqli_num_rows($reviews_result) > 0): ?>
        <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
            <div class="review-item">
                <h3><?php echo htmlspecialchars($review['book_title']); ?></h3>
                <div class="rating"><?php echo str_repeat('⭐', $review['rating']); ?></div>
                <p><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                <p>
                    Status: 
                    <span class="status-<?php echo $review['status']; ?>">
                        <?php echo ucfirst($review['status']); ?>
                    </span>
                </p>
                <small>Submitted on: <?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>You haven't written any reviews yet.</p>
        <a href="books_list.php">Browse books to review</a>
    <?php endif; ?>
</body>
</html>