<?php
$host = "localhost";
$user = "postgres";
$password = "postgres";
$database = "expense_management";

$conn = pg_connect("host=$host dbname=$database user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
?>