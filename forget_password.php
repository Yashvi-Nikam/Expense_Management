<?php

session_start();
include("db_connect.php");
session_start();
if(!isset($_SESSION['user_id'])){
    echo "<script>
    alert('User not logged in');
    window.location.href='signin.html';
    </script>";
    exit();
}

$username = $_POST['username'];

$sql = "SELECT * FROM users WHERE username='$username'";
$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result) == 1)
{
    $user = mysqli_fetch_assoc($result);

    $_SESSION['reset_user_id'] = $user['user_id'];

    header("Location: reset_password.html");
    exit();
}
else
{
    echo "User not found";
}


?>
