<?php
require_once 'database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: books_list.php');
    exit();
}

$book_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    die("Book not found");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];
    $cover_image = $_POST['cover_image'];
    
    $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, description = ?, cover_image = ? WHERE id = ?");
    $stmt->execute([$title, $author, $description, $cover_image, $book_id]);
    
    header('Location: book_details.php?id=' . $book_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book - Book Reviews</title>
    <link rel="stylesheet" href="style.css">
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
            <h2>Edit Book</h2>
            <form method="POST">
                <input type="text" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>
                <input type="text" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>
                <textarea name="description" rows="5"><?php echo htmlspecialchars($book['description']); ?></textarea>
                <input type="url" name="cover_image" value="<?php echo htmlspecialchars($book['cover_image']); ?>" placeholder="Cover Image URL">
                <button type="submit" class="btn btn-primary">Update Book</button>
                <a href="book_details.php?id=<?php echo $book_id; ?>" class="btn">Cancel</a>
            </form>
        </div>
    </div>
    
    <footer class="footer">
        <p>&copy; 2026 Book Review System. All rights reserved.</p>
    </footer>
</body>
</html>