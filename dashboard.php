<?php
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name']);
$is_admin = ($_SESSION['user_role'] === 'admin');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Book Review System</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .menu { margin: 20px 0; }
        .menu a { margin-right: 15px; text-decoration: none; color: blue; }
        .welcome { font-size: 18px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="welcome">
        Welcome, <?php echo $user_name; ?>! 
        (Role: <?php echo $_SESSION['user_role']; ?>)
    </div>
    
    <div class="menu">
        <a href="books_list.php">📚 All Books</a>
        <a href="add_book.php">➕ Add Book</a>
        <a href="my_reviews.php">⭐ My Reviews</a>
        
        <?php if ($is_admin): ?>
            <a href="admin.php">🔧 Admin Panel</a>
        <?php endif; ?>
        
        <a href="logout.php">🚪 Logout</a>
    </div>
    
    <h3>Quick Stats</h3>
    <?php
    // Get book count
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM books");
    $book_count = mysqli_fetch_assoc($result)['total'];
    
    // Get user's review count
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM reviews WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $review_count = mysqli_fetch_assoc($result)['total'];
    mysqli_stmt_close($stmt);
    ?>
    
    <ul>
        <li>Total Books in System: <?php echo $book_count; ?></li>
        <li>Your Reviews: <?php echo $review_count; ?></li>
    </ul>
</body>
</html>