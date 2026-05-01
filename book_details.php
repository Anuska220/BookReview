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
        <title><?php echo htmlspecialchars($book['title']); ?> - Book Reviews</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar Styles */
        .navbar {
            background: rgba(51, 51, 51, 0.95);
            color: white;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
            flex: 1;
        }

        /* Book Detail */
        .book-detail {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .book-header {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .detail-cover {
            max-width: 300px;
            width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .book-info {
            flex: 1;
        }

        .book-info h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .book-info h3 {
            color: #666;
            margin-bottom: 1rem;
            font-weight: normal;
        }

        .rating-large {
            font-size: 1.5rem;
            color: #ffc107;
            margin-bottom: 1rem;
        }

        .rating-large span {
            font-size: 1rem;
            color: #666;
            margin-left: 0.5rem;
        }

        .description {
            line-height: 1.6;
            color: #555;
            margin: 1rem 0;
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .btn-primary {
            background: #667eea;
        }

        .btn-warning {
            background: #f39c12;
        }

        .btn-warning:hover {
            background: #e67e22;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        hr {
            margin: 2rem 0;
            border: none;
            border-top: 2px solid #f0f0f0;
        }

        h2 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        /* Review Card Styles */
        .review {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid #667eea;
        }

        .review:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .review-author {
            font-weight: bold;
            color: #667eea;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .review-author::before {
            content: "👤";
            font-size: 1rem;
        }

        .review-rating {
            color: #ffc107;
            font-size: 1.1rem;
            letter-spacing: 2px;
        }

        .review-rating span {
            color: #666;
            margin-left: 0.5rem;
            font-size: 0.9rem;
        }

        .review-comment {
            margin: 1rem 0;
            line-height: 1.6;
            color: #555;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
        }

        .review-comment em {
            color: #999;
            font-style: italic;
        }

        .review-date {
            font-size: 0.8rem;
            color: #999;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .review-date::before {
            content: "📅";
            font-size: 0.8rem;
        }

        /* Review Actions */
        .review-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
            justify-content: flex-start;
        }

        .btn-edit {
            background: #ffc107;
            color: #333;
            padding: 0.3rem 0.8rem;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.8rem;
            transition: background 0.3s;
            display: inline-block;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.8rem;
            transition: background 0.3s;
            display: inline-block;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        /* Empty State */
        .no-reviews {
            text-align: center;
            padding: 3rem;
            background: #f8f9fa;
            border-radius: 12px;
            color: #666;
        }

        .no-reviews::before {
            content: "📝";
            font-size: 3rem;
            display: block;
            margin-bottom: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .book-header {
                flex-direction: column;
            }
            
            .detail-cover {
                max-width: 100%;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .book-detail {
                padding: 1rem;
            }
            
            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
        }
    </style>
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
            
            <h2>📝 Reviews (<?php echo count($reviews); ?>)</h2>

<?php if(empty($reviews)): ?>
    <div class="no-reviews">
        <p>No reviews yet. Be the first to review this book!</p>
    </div>
<?php else: ?>
    <?php foreach($reviews as $review): ?>
        <div class="review">
            <div class="review-header">
                <div class="review-author">
                    <?php echo htmlspecialchars($review['username']); ?>
                </div>
                <div class="review-rating">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                    <?php endfor; ?>
                    <span>(<?php echo $review['rating']; ?>/5)</span>
                </div>
            </div>
            
            <div class="review-comment">
    <?php 
    // Check if comment exists and is not empty
    if(isset($review['COMMENT']) && !empty(trim($review['COMMENT']))) {
        echo nl2br(htmlspecialchars($review['COMMENT']));
    } else {
        echo "<em style='color: #999;'>No comment provided.</em>";
    }
    ?>
</div>
            
            <div class="review-date">
                <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                <?php 
                // Check if updated_at exists and is different from created_at
                if(isset($review['updated_at']) && $review['updated_at'] != $review['created_at']): 
                ?>
                    <span style="color: #999;">(Edited)</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
        </div>
    </div>
</body>
</html>
