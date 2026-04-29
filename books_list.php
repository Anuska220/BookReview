<?php
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query with search
if (!empty($search)) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE title LIKE ? OR author LIKE ? LIMIT ? OFFSET ?");
    $search_param = "%$search%";
    mysqli_stmt_bind_param($stmt, "ssii", $search_param, $search_param, $limit, $offset);
} else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM books LIMIT ? OFFSET ?");
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
}

mysqli_stmt_execute($stmt);
$books_result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// Get total count for pagination
if (!empty($search)) {
    $count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM books WHERE title LIKE ? OR author LIKE ?");
    mysqli_stmt_bind_param($count_stmt, "ss", $search_param, $search_param);
} else {
    $count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM books");
}
mysqli_stmt_execute($count_stmt);
$total_result = mysqli_stmt_get_result($count_stmt);
$total_books = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_books / $limit);
mysqli_stmt_close($count_stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Books List - Book Review System</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .book-item { margin: 15px 0; padding: 10px; border: 1px solid #ddd; }
        .book-title { font-size: 18px; font-weight: bold; }
        .search-box { margin: 20px 0; }
        .pagination { margin: 20px 0; }
        .pagination a { margin: 0 5px; text-decoration: none; }
        .current-page { font-weight: bold; color: red; }
    </style>
</head>
<body>
    <h2>Books List</h2>
    <a href="dashboard.php">← Back to Dashboard</a>
    
    <div class="search-box">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search by title or author" 
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <?php if (!empty($search)): ?>
                <a href="books_list.php">Clear</a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if (mysqli_num_rows($books_result) > 0): ?>
        <?php while ($book = mysqli_fetch_assoc($books_result)): ?>
            <div class="book-item">
                <div class="book-title">
                    <a href="book.php?id=<?php echo $book['id']; ?>">
                        <?php echo htmlspecialchars($book['title']); ?>
                    </a>
                </div>
                <div>Author: <?php echo htmlspecialchars($book['author']); ?></div>
                <div>Description: <?php echo htmlspecialchars(substr($book['description'], 0, 100)) . '...'; ?></div>
            </div>
        <?php endwhile; ?>
        
        <!-- Pagination -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current-page"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <p>No books found.</p>
    <?php endif; ?>
</body>
</html>