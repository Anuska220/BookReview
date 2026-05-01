<?php
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$review_id = $_GET['id'] ?? 0;
$book_id = $_GET['book_id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
$stmt->execute([$review_id, $_SESSION['user_id']]);

header('Location: book_details.php?id=' . $book_id);
exit();
?>