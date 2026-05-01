<?php
session_start();
require_once 'database.php';

// Check connection
if (!isset($conn) || !$conn) {
    die("Database connection failed");
}

$search = $_GET['q'] ?? '';
$books = [];

if ($search) {
    $searchTerm = "%$search%";
    
    // Use MySQLi prepared statement
    $stmt = mysqli_prepare($conn, "
        SELECT b.*, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count
        FROM books b
        LEFT JOIN reviews r ON b.id = r.book_id
        WHERE b.title LIKE ? OR b.author LIKE ?
        GROUP BY b.id
    ");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $books[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - Book Reviews</title>
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
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="search-form">
            <h2>Search Books</h2>
            <form method="GET">
                <input type="text" name="q" placeholder="Search by title or author..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
        
        <?php if($search): ?>
            <h3>Results for "<?php echo htmlspecialchars($search); ?>"</h3>
            <div class="books-grid">
                <?php foreach($books as $book): ?>
                    <div class="book-card">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p>by <?php echo htmlspecialchars($book['author']); ?></p>
                        <div class="rating">
                            <?php 
                            $avg = round($book['avg_rating'], 1);
                            for($i = 1; $i <= 5; $i++): ?>
                                <?php echo $i <= $avg ? '★' : '☆'; ?>
                            <?php endfor; ?>
                        </div>
                        <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn">View</a>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if(empty($books)): ?>
                <p>No books found.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <footer class="footer">
        <p>&copy; 2026 Book Review System. All rights reserved.</p>
    </footer>
</body>
</html>