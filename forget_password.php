<?php

session_start();
include("db_connect.php");

$username = $_POST['username'];

$sql = "SELECT * FROM users WHERE username=$1";
$result = pg_query_params($conn, $sql, array($username));

if(pg_num_rows($result) == 1)
{
    $user = pg_fetch_assoc($result);

    $_SESSION['reset_user_id'] = $user['user_id'];

    header("Location: reset_password.html");
    exit();
}
else
{
    echo "User not found";
}

?>
