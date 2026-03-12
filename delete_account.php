<?php
session_start();
require 'db_connect.php';

if(!isset($_SESSION['user_id'])){
    echo "User not logged in.";
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);

if($stmt->execute()){

    session_unset();
    session_destroy();

    header("Location: index.html?deleted=1");
    exit();

}else{
    echo "Error deleting account.";
}
?>