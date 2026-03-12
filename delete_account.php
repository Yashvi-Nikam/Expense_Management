<?php
session_start();
require 'db_connect.php';

$user_id = $_SESSION['user_id'] ?? 0;

if($user_id){
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    session_unset();
    session_destroy();

    header("Location: index.html");
    exit();
} else {
    echo "User not found.";
}
?>