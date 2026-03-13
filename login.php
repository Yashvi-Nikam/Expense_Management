
<?php
session_start();
include("db_connect.php");

if(isset($_POST['login']) && isset($_POST['password'])){

    $username = $_POST['login'];
    $password = $_POST['password'];

    // find user
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn,$sql);

    if(mysqli_num_rows($result) == 1){

        $user = mysqli_fetch_assoc($result);

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
