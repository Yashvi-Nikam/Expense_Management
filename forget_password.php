<?php
include("db_connect.php");
$email = $_POST['email'];
$sql = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn,$sql);
if(mysqli_num_rows($result) == 1)
{
  header("Location: reset_password.html");
  exit();

}
else
{
    echo "Email not found";
}
?>