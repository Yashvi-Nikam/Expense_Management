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
$name       = $_POST['name'];
$age        = $_POST['age'];
$gender     = $_POST['gender'];
$occupation = $_POST['occupation'];
$is_edit    = isset($_POST['is_edit']) ? true : false;

// update users table
$result = pg_query_params($conn, "
    UPDATE users 
    SET 
        name=$1,
        age=$2,
        gender=$3,
        occupation=$4
    WHERE user_id=$5
", array($name, $age, $gender, $occupation, $user_id));

if($result){

    if($occupation == "student"){
        $redirect = $is_edit ? 'edit_student.php' : 'student_form.html';
        echo "<script>alert('Basic details saved successfully');
        window.location.href='$redirect';</script>";
    }
    elseif($occupation == "employed"){
        $redirect = $is_edit ? 'edit_employed.php' : 'employed_form.html';
        echo "<script>alert('Basic details saved successfully');
        window.location.href='$redirect';</script>";
    }
    elseif($occupation == "unemployed"){
        $redirect = $is_edit ? 'edit_unemployed.php' : 'unemp.html';
        echo "<script>alert('Basic details saved successfully');
        window.location.href='$redirect';</script>";
    }
    elseif($occupation == "housewife"){
        $redirect = $is_edit ? 'edit_housewife.php' : 'housewife_form.html';
        echo "<script>alert('Basic details saved successfully');
        window.location.href='$redirect';</script>";
    }
    elseif($occupation == "self-employed"){
        $redirect = $is_edit ? 'edit_selfemployed.php' : 'selfemployed_form.html';
        echo "<script>alert('Basic details saved successfully');
        window.location.href='$redirect';</script>";
    }
    else{
        echo "<script>alert('Invalid occupation selected. Please try again.');
        window.location.href='edit_profile.php';</script>";
    }

    exit();

}else{
    echo "Error updating details: " . pg_last_error($conn);
}
?>