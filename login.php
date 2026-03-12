<?php
include("db_connect.php");
$login = $_POST['login'];
$password = $_POST['password'];
$sql = "SELECT * FROM users WHERE (username='$login' OR email='$login') 
        AND password='$password'";
$result = mysqli_query($conn,$sql);
if(mysqli_num_rows($result) == 1)
{
    echo "Login Successful";
}
else
{
    echo "Invalid Username/Email or Password";
}
?>