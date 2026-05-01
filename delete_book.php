<?php
require_once 'database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: books_list.php');
    exit();
}

$book_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
$stmt->execute([$book_id]);

header('Location: books_list.php');
exit();
?>