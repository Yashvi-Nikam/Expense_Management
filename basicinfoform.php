<?php
session_start();
include("db_connect.php");

// check if user is logged in
if(!isset($_SESSION['user_id'])){
    echo "User not logged in.";
    exit();
}

$user_id = $_SESSION['user_id'];

// get form values
$name = $_POST['name'];
$age = $_POST['age'];
$gender = $_POST['gender'];
$occupation = $_POST['occupation'];

// update users table
$sql = "UPDATE users 
        SET 
            name='$name',
            age='$age',
            gender='$gender',
            occupation='$occupation'
        WHERE user_id='$user_id'";

if(mysqli_query($conn,$sql)){

    if($occupation == "student"){
        echo "<script>alert('Basic details saved successfully');
        window.location.href='student_form.html';</script>";
    }
    elseif($occupation == "employed"){
        echo "<script>alert('Basic details saved successfully');
        window.location.href='employed_form.html';</script>";
    }
    elseif($occupation == "unemployed"){
        echo "<script>alert('Basic details saved successfully');
        window.location.href='unemployed_form.html';</script>";
    }
    elseif($occupation == "housewife"){
        echo "<script>alert('Basic details saved successfully');
        window.location.href='housewife_form.html';</script>";
    }
    elseif($occupation == "self-employed"){
        echo "<script>alert('Basic details saved successfully');
        window.location.href='selfemployed_form.html';</script>";
    }

    exit();

}else{
    echo "Error updating details: " . mysqli_error($conn);
}
?>