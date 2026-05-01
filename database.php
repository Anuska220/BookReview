<?php
$servername = 'localhost';
$dbname = 'book_review_db';
$username = 'root';
$password = '';

//Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

//Check connection
if(!$conn) {
    die("Connection failed: " . musqli_connect_error());
}
?>