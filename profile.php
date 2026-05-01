<?php
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user stats
$stmt = $pdo->prepare("
    SELECT 
        COUNT(DISTINCT r.id) as review_count,
        AVG(r.rating) as avg_rating
    FROM reviews r
    WHERE r.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) as book_count FROM books WHERE added_by = ?");
$stmt->execute([$_SESSION['user_id']]);
$book_count = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Book Reviews</title>
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
        <h1>My Profile</h1>
        
        <div class="profile-info">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>Role: <?php echo htmlspecialchars($_SESSION['role'] ?? 'user'); ?></p>
            
            <div class="stats">
                <div class="stat-card">
                    <h3><?php echo $stats['review_count'] ?? 0; ?></h3>
                    <p>Reviews Written</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo number_format($stats['avg_rating'] ?? 0, 1); ?></h3>
                    <p>Average Rating Given</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $book_count['book_count'] ?? 0; ?></h3>
                    <p>Books Added</p>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2026 Book Review System. All rights reserved.</p>
    </footer>
</body>
</html>