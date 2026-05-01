<?php
require_once 'database.php';

// Redirect to books_list if logged in
if (isset($_SESSION['user_id'])) {
    header("Location: books_list.php");
    exit();
}

class MySQLiWrapper {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function prepare($sql) {
        return new MySQLiStmtWrapper($this->conn, $sql);
    }
    
    public function query($sql) {
        $result = mysqli_query($this->conn, $sql);
        if ($result) {
            $rows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            return $rows;
        }
        return [];
    }
}

class MySQLiStmtWrapper {
    private $stmt;
    private $result;
    
    public function __construct($conn, $sql) {
        $this->stmt = mysqli_prepare($conn, $sql);
    }
    
    public function execute() {
        mysqli_stmt_execute($this->stmt);
        $this->result = mysqli_stmt_get_result($this->stmt);
    }
    
    public function fetchAll() {
        $rows = [];
        while ($row = mysqli_fetch_assoc($this->result)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function fetch() {
        return mysqli_fetch_assoc($this->result);
    }
}

// Create wrapper instance
$pdo = new MySQLiWrapper($conn);


// Get featured books with average ratings
try {
    $stmt = $pdo->prepare("
        SELECT b.id, b.title, b.author, 
               COALESCE(AVG(r.rating), 0) as avg_rating,
               COUNT(r.id) as review_count
        FROM books b
        LEFT JOIN reviews r ON b.id = r.book_id
        GROUP BY b.id
        ORDER BY b.id DESC
        LIMIT 10
    ");
    $stmt->execute();
    $featured_books = $stmt->fetchAll();
} catch(Exception $e) {
    $featured_books = [];
}

// Get total stats
try {
    $result = $pdo->query("SELECT COUNT(*) as count FROM books");
    $total_books = isset($result[0]) ? $result[0]['count'] : 0;
    
    $result = $pdo->query("SELECT COUNT(*) as count FROM reviews");
    $total_reviews = isset($result[0]) ? $result[0]['count'] : 0;
    
    $result = $pdo->query("SELECT COUNT(*) as count FROM users");
    $total_users = isset($result[0]) ? $result[0]['count'] : 0;
} catch(Exception $e) {
    $total_books = 0;
    $total_reviews = 0;
    $total_users = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Review System</title>
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
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem;
            text-align: center;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: fadeInUp 0.8s ease;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            animation: fadeInUp 0.8s ease 0.2s backwards;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .buttons {
            margin-top: 1.5rem;
            animation: fadeInUp 0.8s ease 0.4s backwards;
        }
        
        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            margin: 0 0.5rem;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: white;
            color: #667eea;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        
        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-secondary:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
        }
        
        /* Stats Section */
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            text-align: center;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #666;
            margin-top: 0.5rem;
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
        
        .view-all {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .view-all:hover {
            text-decoration: underline;
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
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem;
            background: #333;
            color: white;
            margin-top: 2rem;
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .hero {
                padding: 2rem;
            }
            
            .btn {
                padding: 0.8rem 1.5rem;
            }
            
            .book-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
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
            
            .stats-section {
                grid-template-columns: 1fr;
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
                <a href="login.php" class="nav-link">Login</a>
                <a href="register.php" class="nav-link">Register</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="hero">
            <h1>Welcome to BookReview</h1>
            <p>Discover amazing books and share your honest reviews with our community</p>
            <div class="buttons">
                <a href="register.php" class="btn btn-primary">Get Started</a>
                <a href="login.php" class="btn btn-secondary">Login</a>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_books; ?></div>
                <div class="stat-label">📖 Total Books</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_reviews; ?></div>
                <div class="stat-label">⭐ Total Reviews</div>
            </div>
        </div>
        
        <!-- Featured Books -->
        <div class="books-section">
            <div class="section-header">
                <h2>📖 Featured Books</h2>
                <?php if(count($featured_books) > 0): ?>
                <a href="books_list.php" class="view-all">View All →</a>
                <?php endif; ?>
            </div>
            
            <?php if (count($featured_books) > 0): ?>
                <div class="book-grid">
                    <?php foreach ($featured_books as $book): ?>
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
                                <span class="rating-value"><?php echo number_format($book['avg_rating'], 1); ?></span>
                                <span class="review-count">(<?php echo $book['review_count']; ?> reviews)</span>
                            </div>
                            <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn-small">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No books available yet.</p>
                    <p>Be the first to <a href="login.php" style="color: #667eea;">add a book</a> to our collection!</p>
                </div>
            <?php endif; ?>
        </div>
        
    
    </div>
    
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> Book Review System. All rights reserved.</p>
    </div>
</body>
</html>