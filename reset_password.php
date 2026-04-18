<?php

session_start();
include("db_connect.php");

// Initialize session variables for messages
if(!isset($_SESSION['reset_message'])){
    $_SESSION['reset_message'] = '';
}

if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['confirm_password'])){
    
    try {
        // Get and sanitize form data
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        // Check empty fields
        if(empty($username) || empty($password) || empty($confirm_password)){
            $_SESSION['reset_message'] = 'Please fill all fields.';
            header("Location: forgot_password.php");
            exit();
        }

        // Validate username
        if(strlen($username) < 3){
            $_SESSION['reset_message'] = 'Invalid username.';
            header("Location: forgot_password.php");
            exit();
        }

        // Validate password length
        if(strlen($password) < 6){
            $_SESSION['reset_message'] = 'New password must be at least 6 characters.';
            header("Location: forgot_password.php");
            exit();
        }

        // Check if passwords match
        if($password !== $confirm_password){
            $_SESSION['reset_message'] = 'Passwords do not match. Please try again.';
            header("Location: forgot_password.php");
            exit();
        }

        // Check if user exists
        $result = pg_query_params($conn, "SELECT user_id FROM users WHERE username=$1", array($username));

        if(!$result){
            throw new Exception("Database query failed: " . pg_last_error($conn));
        }

        if(pg_num_rows($result) === 1){

            // Hash new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Update password
            $update_result = pg_query_params($conn, "UPDATE users SET password=$1 WHERE username=$2", array($hashed_password, $username));

            if(!$update_result){
                throw new Exception("Failed to update password: " . pg_last_error($conn));
            }

            // Destroy session so user must login again
            session_unset();
            session_destroy();

            echo "<script>
                alert('Password reset successful. Please login again.');
                window.location.href='signin.php';
            </script>";
            exit();

        }else{
            $_SESSION['reset_message'] = 'Username not found. Please check and try again.';
            header("Location: forgot_password.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['reset_message'] = 'Error resetting password. Please try again later.';
        header("Location: forgot_password.php");
        exit();
    }

}else{
    $_SESSION['reset_message'] = 'Please fill all fields.';
    header("Location: forgot_password.php");
    exit();
}

?>
