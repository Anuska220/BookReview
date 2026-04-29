<?php
include 'db.php';

// Check if user is logged in AND is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle review approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $review_id = isset($_POST['review_id']) ? (int)$_POST['review_id'] : 0;
    $action = $_POST['action'];
    
    if ($review_id > 0) {
        if ($action === 'approve') {
            $stmt = mysqli_prepare($conn, "UPDATE reviews SET status = 'approved' WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $review_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } elseif ($action === 'reject') {
            $stmt = mysqli_prepare($conn, "DELETE FROM reviews WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $review_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
}

// Get pending reviews
$pending_stmt = mysqli_prepare($conn, "
    SELECT r.*, u.name as user_name, b.title as book_title 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    JOIN books b ON r.book_id = b.id 
    WHERE r.status = 'pending' 
    ORDER BY r.created_at DESC
");
mysqli_stmt_execute($pending_stmt);
$pending_reviews = mysqli_stmt_get_result($pending_stmt);
mysqli_stmt_close($pending_stmt);

// Get statistics
$stats_stmt = mysqli_prepare($conn, "
    SELECT 
        COUNT(*) as total_reviews,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count
    FROM reviews
");
mysqli_stmt_execute($stats_stmt);
$stats = mysqli_stmt_get_result($stats_stmt);
$review_stats = mysqli_fetch_assoc($stats);
mysqli_stmt_close($stats_stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Book Review System</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .stats { margin: 20px 0; padding: 15px; background: #f0f0f0; }
        .stat-box { display: inline-block; margin: 0 20px; }
        .review-item { margin: 15px 0; padding: 10px; border: 1px solid #ddd; }
        .rating { color: gold; }
        button { margin: 5px; padding: 5px 10px; cursor: pointer; }
        .approve-btn { background: green; color: white; border: none; }
        .reject-btn { background: red; color: white; border: none; }
    </style>
</head>
<body>
    <h2>Admin Panel</h2>
    <a href="dashboard.php">← Back to Dashboard</a>
    <a href="logout.php" style="margin-left: 20px;">Logout</a>
    
    <div class="stats">
        <h3>Review Statistics</h3>
        <div class="stat-box">Total Reviews: <?php echo $review_stats['total_reviews']; ?></div>
        <div class="stat-box">Pending: <?php echo $review_stats['pending_count']; ?></div>
        <div class="stat-box">Approved: <?php echo $review_stats['approved_count']; ?></div>
    </div>
    
    <h3>Pending Reviews (<?php echo $review_stats['pending_count']; ?>)</h3>
    
    <?php if (mysqli_num_rows($pending_reviews) > 0): ?>
        <?php while ($review = mysqli_fetch_assoc($pending_reviews)): ?>
            <div class="review-item">
                <h4><?php echo htmlspecialchars($review['book_title']); ?></h4>
                <div class="rating"><?php echo str_repeat('⭐', $review['rating']); ?></div>
                <p><strong>By:</strong> <?php echo htmlspecialchars($review['user_name']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                <small>Submitted: <?php echo date('F j, Y g:i A', strtotime($review['created_at'])); ?></small>
                
                <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                    <button type="submit" name="action" value="approve" class="approve-btn">✓ Approve</button>
                    <button type="submit" name="action" value="reject" class="reject-btn" 
                            onclick="return confirm('Delete this review?')">✗ Reject</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No pending reviews to moderate.</p>
    <?php endif; ?>
</body>
</html>