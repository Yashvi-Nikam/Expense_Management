<?php
session_start();
require 'db_connect.php';

if(!isset($_SESSION['user_id'])){
    echo "User not logged in.";
    exit();
}

$user_id = $_SESSION['user_id'];

$result = pg_query_params($conn, "DELETE FROM users WHERE user_id=$1", array($user_id));

if($result){

    session_unset();
    session_destroy();

    header("Location: index.html?deleted=1");
    exit();

}else{
    echo "Error deleting account.";
}
?>