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
        }
        
        /* Header Styles */
        .header {
            background: rgba(51, 51, 51, 0.95);
            color: white;
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo h1 {
            font-size: 1.5rem;
        }
        
        .logo p {
            font-size: 0.8rem;
            opacity: 0.9;
        }
        
        .nav-links {
            display: flex;
            gap: 1rem;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.2);
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        /* Books Section */
        .books-section {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .section-header h2 {
            color: #333;
            font-size: 1.8rem;
            border-left: 4px solid #667eea;
            padding-left: 1rem;
        }
        
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .book-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s;
            border: 1px solid #e0e0e0;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            border-color: #667eea;
        }
        
        .book-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .book-author {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .book-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .stars {
            color: #ffc107;
            font-size: 1rem;
        }
        
        .rating-value {
            font-weight: bold;
            color: #333;
        }
        
        .review-count {
            color: #666;
            font-size: 0.8rem;
        }
        
        .btn-small {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-small:hover {
            background: #5a67d8;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-state p {
            margin-bottom: 1rem;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem;
            background: #333;
            color: white;
            margin-top: 2rem;
        }
        
        /* Search and Filter Bar */
        
        
        .search-form {
            display: flex;
            gap: 0.5rem;
            flex: 1;
        }
        
        .search-input {
            flex: 1;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .search-btn {
            padding: 0.8rem 1.5rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .sort-select {
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        .clear-btn {
            padding: 0.8rem 1.5rem;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .book-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-bar {
                flex-direction: column;
            }
            
            .search-form {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .buttons {
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            
            .btn {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <h1>📚 BookReview</h1>
                <p>Share Your Reading Experience</p>
            </div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="books_list.php" class="nav-link">Books</a>
                <a href="search.php" class="nav-link">Search</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="my_reviews.php" class="nav-link">My Reviews</a>
                    <a href="profile.php" class="nav-link">Profile</a>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <a href="add_book.php" class="nav-link">Add Book</a>
                    <?php endif; ?>
                    <a href="logout.php" class="nav-link">Logout (<?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>)</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Login</a>
                    <a href="register.php" class="nav-link">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="books-section">
            <div class="section-header">
                <h2>📖 <?php echo !empty($search) ? 'Search Results for: "' . htmlspecialchars($search) . '"' : 'All Books'; ?></h2>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="add_book.php" class="btn-small">+ Add New Book</a>
                <?php endif; ?>
            </div>
            
           
            
            <?php if(empty($books)): ?>
                <div class="empty-state">
                    <p>No books found.</p>
                    <?php if(!empty($search)): ?>
                        <p>Try searching with different keywords.</p>
                    <?php else: ?>
                        <p>No books available yet.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="book-grid">
                    <?php foreach($books as $book): 
                        $avg_rating = round($book['avg_rating'], 1);
                    ?>
                        <div class="book-card">
                            <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                            <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                            <div class="book-rating">
                                <div class="stars">
                                    <?php 
                                    $rating = round($book['avg_rating']);
                                    for($i = 1; $i <= 5; $i++) {
                                        if($i <= $rating) {
                                            echo '★';
                                        } else {
                                            echo '☆';
                                        }
                                    }
                                    ?>
                                </div>
                                <span class="rating-value"><?php echo number_format($avg_rating, 1); ?></span>
                                <span class="review-count">(<?php echo $book['review_count']; ?> reviews)</span>
                            </div>
                            <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn-small">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> Book Review System. All rights reserved.</p>
    </div>
</body>
</html>
