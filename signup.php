<?php
session_start();
include("db_connect.php");

if(isset($_POST['username']) && isset($_POST['password'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into users table and return inserted id
    $sql = "INSERT INTO users (username, password) VALUES ($1, $2) RETURNING user_id";

    $result = pg_query_params($conn, $sql, array($username, $hashed_password));

    if($result){

        // get inserted user id
        $row = pg_fetch_assoc($result);
        $user_id = $row['user_id'];

        // store in session
        $_SESSION['user_id'] = $user_id;

        // redirect to basic details page
        header("Location: basicinfoform.html");
        exit();

    } else {
        echo "Error creating account: " . pg_last_error($conn);
    }

} else {
    echo "Please fill all fields.";
}
?>