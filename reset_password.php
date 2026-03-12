<?php
include("db_connect.php");
$email = $_POST['email'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$source = $_POST['source'];   // dashboard or forgot
// check password match
if($password != $confirm_password){
    echo ('Passwords do not match');
    exit();
}
// check if email exists
$check = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn,$check);

if(mysqli_num_rows($result) == 1){

    // update password
    $update = "UPDATE users SET password='$password' WHERE email='$email'";
    mysqli_query($conn,$update);

    // redirect depending on source
    if($source == "dashboard"){
        echo "<script>
        alert('Password updated successfully');
        window.location.href='dashboard.html';
        </script>";
    }
    else{
        echo "<script>
        alert('Password reset successful');
        window.location.href='signin.html';
        </script>";
    }

}
else{
    echo ('Email not found');
}
?>