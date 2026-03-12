<?php
session_start();
include("db_connect.php");

if(isset($_POST['username']) && isset($_POST['password'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into users table
    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";

    if(mysqli_query($conn, $sql)){

        // get inserted user id
        $user_id = mysqli_insert_id($conn);

        // store in session
        $_SESSION['user_id'] = $user_id;

        // redirect to basic details page
        echo "<script>alert('Account created successfully! Please fill in your basic details.');</script>";
        header("Location: basicinfoform.html");
        exit();

    } else {
        echo "Error creating account: " . mysqli_error($conn);
    }

} else {
    echo "Please fill all fields.";
}
?>