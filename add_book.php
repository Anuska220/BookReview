<?php
session_start();
require_once 'database.php';

// Check connection
if (!isset($conn) || !$conn) {
    die("Database connection failed");
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $cover_image = trim($_POST['cover_image'] ?? '');
    
    // Validate required fields
    if (empty($title) || empty($author)) {
        $error = "Title and author are required!";
    } else {
        // Use MySQLi prepared statement
        $stmt = mysqli_prepare($conn, "INSERT INTO books (title, author, description, cover_image, added_by) VALUES (?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            die("Prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "ssssi", $title, $author, $description, $cover_image, $_SESSION['user_id']);
        
        if (mysqli_stmt_execute($stmt)) {
            header('Location: books_list.php');
            exit();
        } else {
            $error = "Failed to add book: " . mysqli_error($conn);
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
    <title>Add Book - Book Reviews</title>
    <style>
        .auth-form {
            max-width: 600px;
            margin: 0 auto;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);

        }

        .auth-form h2 {
            color: #333;
            text-align: center;
            margin-bottom: 1.5rem;
            margin-top: 10px;
            font-size: 1.8rem;
        }

        .auth-form form {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .auth-form input,
        .auth-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }

        .auth-form input:focus,
        .auth-form textarea:focus {
            outline: none;
            border-color: #3498db;
        }

        .auth-form textarea {
            resize: vertical;
            min-height: 120px;
        }

        .auth-form .btn {
            display: inline-block;
            padding: 12px 24px;
            margin-top: 10px;
            margin-right: 10px;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            text-align: center;
        }

        .auth-form .btn-primary {
            background: #3498db;
            color: white;
        }

        .auth-form .btn-primary:hover {
            background: #2980b9;
        }

        .auth-form .btn:not(.btn-primary) {
            background: #95a5a6;
            color: white;
        }

        .auth-form .btn:not(.btn-primary):hover {
            background: #7f8c8d;
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 10px;
            margin-bottom: 1rem;
            border-radius: 4px;
            border-left: 3px solid #c33;
            font-size: 0.9rem;
        }

        .success-message {
            background: #e8f5e9;
            color: #4caf50;
            padding: 10px;
            margin-bottom: 1rem;
            border-radius: 4px;
            border-left: 3px solid #4caf50;
            font-size: 0.9rem;
        }

         /* Responsive for Add Book Form */
        @media (max-width: 768px) {
        .auth-form {
            margin: 0 1rem;
            padding: 1.5rem;
        }
    
        .auth-form .btn {
            display: block;
            width: 100%;
            margin: 10px 0;
        }
    
        .auth-form .btn-primary {
            margin-right: 0;
        }
    </style>
</head>
<body>
    

    <div class="container">
        <div class="auth-form">
            <h2>Add New Book</h2>
            <form method="POST">
                <input type="text" name="title" placeholder="Book Title" required>
                <input type="text" name="author" placeholder="Author" required>
                <textarea name="description" placeholder="Description" rows="5"></textarea>
                <input type="url" name="cover_image" placeholder="Cover Image URL">
                <button type="submit" class="btn btn-primary">Add Book</button>
            </form>
        </div>
    </div>
    

</body>
</html>