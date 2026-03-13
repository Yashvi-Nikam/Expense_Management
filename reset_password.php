<?php

session_start();
include("db_connect.php");
if(!isset($_SESSION['user_id'])){
    echo "<script>
    alert('User not logged in');
    window.location.href='signin.html';
    </script>";
    exit();
}

// get form data
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// check empty fields
if(empty($username) || empty($password) || empty($confirm_password)){
    echo "Please fill all fields.";
    exit();
}

// check password match
if($password !== $confirm_password){
    echo "Passwords do not match.";
    exit();
}

// hash new password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// check if user exists
$stmt = $conn->prepare("SELECT user_id FROM users WHERE username=?");
$stmt->bind_param("s",$username);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 1){

    // update password
    $update = $conn->prepare("UPDATE users SET password=? WHERE username=?");
    $update->bind_param("ss",$hashed_password,$username);
    $update->execute();

    // destroy session so user must login again
    session_unset();
    session_destroy();

    echo "<script>
        alert('Password reset successful. Please login again.');
        window.location.href='signin.html';
    </script>";

}
else{
    echo "Username not found.";
}


?>
