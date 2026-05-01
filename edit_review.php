<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$review_id = $_GET['id'] ?? 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM reviews WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $review_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$review = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$review) {
    die("Review not found");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    $stmt = mysqli_prepare($conn, "UPDATE reviews SET rating = ?, comment = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "isi", $rating, $comment, $review_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    header('Location: book_details.php?id=' . $review['book_id']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Review - Book Reviews</title>
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

        /* Auth Form Styles */
        .auth-form {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .auth-form h2 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 1.8rem;
            border-left: 4px solid #667eea;
            padding-left: 1rem;
        }

        .auth-form select,
        .auth-form textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        .auth-form select:focus,
        .auth-form textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .auth-form textarea {
            resize: vertical;
            min-height: 150px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 600;
            text-align: center;
        }

        .btn-primary {
            background: #667eea;
            color: white;
            width: 100%;
            margin-bottom: 1rem;
        }

        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .btn {
            background: #6c757d;
            color: white;
            display: inline-block;
            width: 100%;
        }

        .btn:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* Alert Messages */
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #dc3545;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #28a745;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .auth-form {
                padding: 1.5rem;
            }
            
            .container {
                padding: 0 1rem;
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
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="auth-form">
            <h2>Edit Your Review</h2>
            <form method="POST">
                <select name="rating" required>
                    <option value="5" <?php echo $review['rating'] == 5 ? 'selected' : ''; ?>>★★★★★ (5 stars)</option>
                    <option value="4" <?php echo $review['rating'] == 4 ? 'selected' : ''; ?>>★★★★☆ (4 stars)</option>
                    <option value="3" <?php echo $review['rating'] == 3 ? 'selected' : ''; ?>>★★★☆☆ (3 stars)</option>
                    <option value="2" <?php echo $review['rating'] == 2 ? 'selected' : ''; ?>>★★☆☆☆ (2 stars)</option>
                    <option value="1" <?php echo $review['rating'] == 1 ? 'selected' : ''; ?>>★☆☆☆☆ (1 star)</option>
                </select>
                <textarea name="comment" placeholder="Your review..." rows="5" required><?php echo htmlspecialchars($review['COMMENT']); ?></textarea>
                <button type="submit" class="btn btn-primary">Update Review</button>
                <a href="book_details.php?id=<?php echo $review['book_id']; ?>" class="btn">Cancel</a>
            </form>
        </div>
    </div>
    
    
</body>
</html>
