<?php
session_start();
include("db_connect.php");

if(isset($_POST['login']) && isset($_POST['password'])){

    $username = $_POST['login'];
    $password = $_POST['password'];

    // find user
    $sql = "SELECT * FROM users WHERE username=$1";
    $result = pg_query_params($conn, $sql, array($username));

    if(pg_num_rows($result) == 1){

        $user = pg_fetch_assoc($result);

        // verify password
        if(password_verify($password, $user['password'])){

            $_SESSION['user_id'] = $user['user_id'];

            header("Location: dashboard.php");
            exit();

        }else{
            echo "Incorrect password.";
        }

    }else{
        echo "User not found.";
    }

}else{
    echo "Please fill all fields.";
}
print_r($_POST);
?>
