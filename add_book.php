<?php
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'];
    
    if (empty($title) || empty($author)) {
        $error = "Title and author are required";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO books (title, author, description, added_by) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssi", $title, $author, $description, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Book added successfully!";
            // Clear form
            $title = $author = $description = '';
        } else {
            $error = "Failed to add book. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Book - Book Review System</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .error { color: red; margin: 10px 0; }
        .success { color: green; margin: 10px 0; }
        input, textarea { margin: 5px 0; padding: 5px; width: 300px; }
        button { margin-top: 10px; padding: 5px 15px; }
    </style>
</head>
<body>
    <h2>Add New Book</h2>
    <a href="dashboard.php">← Back to Dashboard</a>
    
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <input type="text" name="title" placeholder="Book Title" required 
               value="<?php echo htmlspecialchars($title ?? ''); ?>"><br>
        <input type="text" name="author" placeholder="Author" required
               value="<?php echo htmlspecialchars($author ?? ''); ?>"><br>
        <textarea name="description" placeholder="Description (optional)" rows="5"></textarea><br>
        <button type="submit">Add Book</button>
    </form>
</body>
</html>