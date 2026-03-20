<?php

session_start();
include("db_connect.php");

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
$result = pg_query_params($conn, "SELECT user_id FROM users WHERE username=$1", array($username));

if(pg_num_rows($result) === 1){

    // update password
    pg_query_params($conn, "UPDATE users SET password=$1 WHERE username=$2", array($hashed_password, $username));

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
