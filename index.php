<?php
session_start();
include 'db.php';
include 'config.php';

// Redirect to dashboard if logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Get featured books
$featured_books = mysqli_query($conn, "SELECT id, title, author FROM books ORDER BY id DESC LIMIT 10");
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
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }
        
        .header {
            background: #333;
            color: white;
            padding: 1rem;
            text-align: center;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .buttons {
            margin-top: 1.5rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            margin: 0 0.5rem;
            text-decoration: none;
            border-radius: 5px;
            transition: transform 0.3s;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-primary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .btn-primary:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
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
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .books-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .books-section h2 {
            margin-bottom: 1.5rem;
            color: #333;
            font-size: 1.8rem;
            border-left: 4px solid #667eea;
            padding-left: 1rem;
        }
        
        .book-list {
            list-style: none;
        }
        
        .book-list li {
            padding: 0.8rem;
            border-bottom: 1px solid #eee;
        }
        
        .book-list li:last-child {
            border-bottom: none;
        }
        
        .book-list a {
            color: #667eea;
            text-decoration: none;
            font-size: 1.1rem;
        }
        
        .book-list a:hover {
            text-decoration: underline;
        }
        
        .footer {
            text-align: center;
            padding: 2rem;
            background: #333;
            color: white;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 Book Review System</h1>
        <p>Share your reading experience with others</p>
    </div>
    
    <div class="container">
        <div class="hero">
            <h1>Welcome to BookReview</h1>
            <p>Discover amazing books and share your honest reviews</p>
            <div class="buttons">
                <a href="login.php" class="btn btn-secondary">Login</a>
                <a href="register.php" class="btn btn-primary">Register</a>
            </div>
        </div>
        
        <div class="books-section">
            <h2>📖 Featured Books</h2>
            <?php if (mysqli_num_rows($featured_books) > 0): ?>
                <ul class="book-list">
                    <?php while ($book = mysqli_fetch_assoc($featured_books)): ?>
                        <li>
                            <a href="login.php"><?php echo htmlspecialchars($book['title']); ?></a>
                            <span style="color: #666; font-size: 0.9rem;">by <?php echo htmlspecialchars($book['author']); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>No books available yet. Check back soon!</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> Book Review System. All rights reserved.</p>
    </div>
</body>
</html>