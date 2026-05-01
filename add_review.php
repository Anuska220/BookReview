<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$book_id = $_GET['book_id'] ?? 0;

// Check if already reviewed
$check_stmt = mysqli_prepare($conn, "SELECT id FROM reviews WHERE book_id = ? AND user_id = ?");
mysqli_stmt_bind_param($check_stmt, "ii", $book_id, $_SESSION['user_id']);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    mysqli_stmt_close($check_stmt);
    header('Location: book_details.php?id=' . $book_id . '&error=already_reviewed');
    exit();
}
mysqli_stmt_close($check_stmt);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment'] ?? '');
    
    // IMPORTANT: Use uppercase COMMENT since that's the column name in your database
    $insert_stmt = mysqli_prepare($conn, "INSERT INTO reviews (book_id, user_id, rating, COMMENT, created_at) VALUES (?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($insert_stmt, "iiis", $book_id, $_SESSION['user_id'], $rating, $comment);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        mysqli_stmt_close($insert_stmt);
        header('Location: book_details.php?id=' . $book_id . '&success=review_added');
        exit();
    } else {
        echo "Error saving review: " . mysqli_error($conn);
        mysqli_stmt_close($insert_stmt);
        exit();
    }
}

// Get book title
$book_title = '';
$stmt = mysqli_prepare($conn, "SELECT title FROM books WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $book_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($result);
if ($book) {
    $book_title = $book['title'];
}
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Review - <?php echo htmlspecialchars($book_title); ?></title>
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

        .navbar {
            background: rgba(51, 51, 51, 0.95);
            padding: 1rem 0;
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
            color: white;
            font-size: 1.5rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 5px;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
        }

        .container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .review-form {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .review-form h2 {
            color: #333;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .review-form h3 {
            color: #666;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: normal;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #333;
        }

        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
        }

        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .rating-stars {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .rating-stars input {
            display: none;
        }

        .rating-stars label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s;
        }

        .rating-stars label:hover,
        .rating-stars label:hover ~ label,
        .rating-stars input:checked ~ label {
            color: #ffc107;
        }

        .btn {
            display: inline-block;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            display: block;
            text-align: center;
            margin-top: 1rem;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .review-form {
                padding: 1.5rem;
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
        <div class="review-form">
            <h2>Write a Review</h2>
            <h3>"<?php echo htmlspecialchars($book_title); ?>"</h3>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Your Rating:</label>
                    <div class="rating-stars">
                        <input type="radio" name="rating" value="5" id="star5" required>
                        <label for="star5">★</label>
                        <input type="radio" name="rating" value="4" id="star4">
                        <label for="star4">★</label>
                        <input type="radio" name="rating" value="3" id="star3">
                        <label for="star3">★</label>
                        <input type="radio" name="rating" value="2" id="star2">
                        <label for="star2">★</label>
                        <input type="radio" name="rating" value="1" id="star1">
                        <label for="star1">★</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Your Review:</label>
                    <textarea name="comment" placeholder="Review" rows="5"></textarea>
                    <small style="color: #666; display: block; margin-top: 0.3rem;"></small>
                </div>
                
                <button type="submit" class="btn btn-primary">Submit Review</button>
                <a href="book_details.php?id=<?php echo $book_id; ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    
    
</body>
</html>
