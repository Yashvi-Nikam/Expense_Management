<?php
session_start();
include("db_connect.php");

// Initialize session variables for messages
if(!isset($_SESSION['signup_message'])){
    $_SESSION['signup_message'] = '';
}

if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['confirmPassword'])){

    try {
        // Validate and sanitize inputs
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm_password = trim($_POST['confirmPassword'] ?? '');

        // Check empty fields
        if(empty($username) || empty($password) || empty($confirm_password)){
            $_SESSION['signup_message'] = 'Please fill all fields.';
            header("Location: signup_form.php");
            exit();
        }

        // Validate username length
        if(strlen($username) < 3){
            $_SESSION['signup_message'] = 'Username must be at least 3 characters.';
            header("Location: signup_form.php");
            exit();
        }

        if(strlen($username) > 50){
            $_SESSION['signup_message'] = 'Username must be less than 50 characters.';
            header("Location: signup_form.php");
            exit();
        }

        // Validate password length
        if(strlen($password) < 6){
            $_SESSION['signup_message'] = 'Password must be at least 6 characters.';
            header("Location: signup_form.php");
            exit();
        }

        // Check if passwords match
        if($password !== $confirm_password){
            $_SESSION['signup_message'] = 'Passwords do not match. Please try again.';
            header("Location: signup_form.php");
            exit();
        }

        // Check if username already exists
        $check_sql = "SELECT user_id FROM users WHERE username=$1";
        $check_result = pg_query_params($conn, $check_sql, array($username));

        if(!$check_result){
            throw new Exception("Database query failed: " . pg_last_error($conn));
        }

        if(pg_num_rows($check_result) > 0){
            $_SESSION['signup_message'] = 'Username already exists. Please choose a different username.';
            header("Location: signup_form.php");
            exit();
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into users table and return inserted id
        $sql = "INSERT INTO users (username, password) VALUES ($1, $2) RETURNING user_id";
        $result = pg_query_params($conn, $sql, array($username, $hashed_password));

        if(!$result){
            throw new Exception("Error creating account: " . pg_last_error($conn));
        }

        // Get inserted user id
        $row = pg_fetch_assoc($result);
        $user_id = $row['user_id'];

        if(!$user_id){
            throw new Exception("Failed to retrieve user ID after signup.");
        }

        // Store in session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['signup_message'] = 'Account created successfully! Please complete your profile.';

        // Redirect to basic details page
        header("Location: basicinfoform.html");
        exit();

    } catch (Exception $e) {
        $_SESSION['signup_message'] = 'Error creating account. Please try again later.';
        header("Location: signup_form.php");
        exit();
    }

} else {
    $_SESSION['signup_message'] = 'Please fill all fields.';
    header("Location: signup_form.php");
    exit();
}
?>