<?php
session_start();
include("db_connect.php");

// Initialize session variables for messages
if(!isset($_SESSION['login_message'])){
    $_SESSION['login_message'] = '';
}

if(isset($_POST['login']) && isset($_POST['password'])){

    try {
        // Validate inputs
        $username = trim($_POST['login'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if(empty($username) || empty($password)){
            $_SESSION['login_message'] = 'Please fill all fields.';
            header("Location: signin.php");
            exit();
        }

        if(strlen($username) < 3){
            $_SESSION['login_message'] = 'Username must be at least 3 characters.';
            header("Location: signin.php");
            exit();
        }

        // Find user
        $sql = "SELECT * FROM users WHERE username=$1";
        $result = pg_query_params($conn, $sql, array($username));

        if(!$result){
            throw new Exception("Database query failed: " . pg_last_error($conn));
        }

        if(pg_num_rows($result) == 1){

            $user = pg_fetch_assoc($result);

            // Verify password
            if(password_verify($password, $user['password'])){

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['login_message'] = 'Login successful! Welcome back.';

                header("Location: dashboard.php");
                exit();

            }else{
                $_SESSION['login_message'] = 'Incorrect password. Please try again.';
                header("Location: signin.php");
                exit();
            }

        }else{
            $_SESSION['login_message'] = 'Username not found. Please check or sign up.';
            header("Location: signin.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['login_message'] = 'Error during login. Please try again later.';
        header("Location: signin.php");
        exit();
    }

}else{
    $_SESSION['login_message'] = 'Please fill all fields.';
    header("Location: signin.php");
    exit();
}
?>
