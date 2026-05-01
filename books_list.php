<?php 
session_start();
require_once 'database.php';

// Check connection
if (!isset($conn) || !$conn) {
    die("Database connection failed");
}

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build the query
$sql = "SELECT b.*, COALESCE(AVG(r.rating), 0) as avg_rating, COUNT(r.id) as review_count 
        FROM books b 
        LEFT JOIN reviews r ON b.id = r.book_id";

if (!empty($search)) {
    $sql .= " WHERE b.title LIKE '%$search%' OR b.author LIKE '%$search%'";
}

$sql .= " GROUP BY b.id";

// Add sorting
switch($sort) {
    case 'rating':
        $sql .= " ORDER BY avg_rating DESC";
        break;
    case 'title':
        $sql .= " ORDER BY b.title ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY b.id DESC";
        break;
}

// Execute query
$result = mysqli_query($conn, $sql);
$books = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $books[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books List - Book Review System</title>
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
                    <a href="add_book.php">Add Book</a>
                    <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>)</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>📖 Featured Books</h1>
        
        <?php if(empty($books)): ?>
            <div class="empty-state">
                <h3>No books available yet</h3>
                <p>Be the first to add a book!</p>
            </div>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach($books as $book): 
                    $avg_rating = round($book['avg_rating'], 1);
                ?>
                    <div class="book-card">
                        <?php if($book['cover_image']): ?>
                            <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>" class="book-cover">
                        <?php else: ?>
                            <div class="book-cover placeholder">📚</div>
                        <?php endif; ?>
                        
                        <h2><?php echo htmlspecialchars($book['title']); ?></h2>
                        <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>
                        
                        <div class="rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <?php echo $i <= $avg_rating ? '★' : '☆'; ?>
                            <?php endfor; ?>
                            <span>(<?php echo $book['review_count']; ?> reviews)</span>
                        </div>
                        
                        <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <footer class="footer">
        <p>&copy; 2026 Book Review System. All rights reserved.</p>
    </footer>
</body>
</html>