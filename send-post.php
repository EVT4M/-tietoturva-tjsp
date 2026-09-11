<?php
include 'connect.php';
session_start();
$title = $_POST['title'];
$body = $_POST['body'];
$userId = $_SESSION['user']['id'];
$today = date("Y-m-d");

$sql = "INSERT INTO posts (title, body, posted, author) VALUES ('$title', '$body', '$today', $userId);";

try {
    $query = $conn->prepare($sql);
    $query->execute();
    header('Location: home.php');
} catch (PDOException $e) {
    die('Virhe: ' . $e->getMessage());
}


?>