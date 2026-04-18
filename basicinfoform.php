<?php
session_start();
include("db_connect.php");

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    $_SESSION['error_message'] = 'User not logged in.';
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize success/error messages
if(!isset($_SESSION['form_message'])){
    $_SESSION['form_message'] = '';
}

try {
    // Get form values
    $name       = trim($_POST['name'] ?? '');
    $age        = trim($_POST['age'] ?? '');
    $gender     = trim($_POST['gender'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');
    $is_edit    = isset($_POST['is_edit']) ? true : false;

    // Validate inputs
    if(empty($name) || empty($age) || empty($gender) || empty($occupation)){
        $_SESSION['form_message'] = 'Please fill all fields.';
        header("Location: basicinfoform.html");
        exit();
    }

    // Validate name
    if(strlen($name) < 2 || strlen($name) > 100){
        $_SESSION['form_message'] = 'Name must be between 2 and 100 characters.';
        header("Location: basicinfoform.html");
        exit();
    }

    // Validate age
    if(!is_numeric($age) || $age < 12 || $age > 100){
        $_SESSION['form_message'] = 'Please enter a valid age between 12 and 100.';
        header("Location: basicinfoform.html");
        exit();
    }

    $age = intval($age);

    // Validate gender
    $valid_genders = ['male', 'female', 'other'];
    if(!in_array($gender, $valid_genders)){
        $_SESSION['form_message'] = 'Invalid gender selection.';
        header("Location: basicinfoform.html");
        exit();
    }

    // Validate occupation
    $valid_occupations = ['student', 'employed', 'unemployed', 'housewife', 'self-employed'];
    if(!in_array($occupation, $valid_occupations)){
        $_SESSION['form_message'] = 'Invalid occupation selection.';
        header("Location: basicinfoform.html");
        exit();
    }

    // Update users table
    $result = pg_query_params($conn, "
        UPDATE users 
        SET 
            name=$1,
            age=$2,
            gender=$3,
            occupation=$4
        WHERE user_id=$5
    ", array($name, $age, $gender, $occupation, $user_id));

    if(!$result){
        throw new Exception("Database update failed: " . pg_last_error($conn));
    }

    // Determine redirect based on occupation
    $redirect = '';
    if($occupation == "student"){
        $redirect = $is_edit ? 'edit_student.php' : 'student_form.html';
    }
    elseif($occupation == "employed"){
        $redirect = $is_edit ? 'edit_employed.php' : 'employed_form.html';
    }
    elseif($occupation == "unemployed"){
        $redirect = $is_edit ? 'edit_unemployed.php' : 'unemp.html';
    }
    elseif($occupation == "housewife"){
        $redirect = $is_edit ? 'edit_housewife.php' : 'housewife_form.html';
    }
    elseif($occupation == "self-employed"){
        $redirect = $is_edit ? 'edit_selfemployed.php' : 'selfemployed_form.html';
    }
    else{
        throw new Exception("Invalid occupation selected.");
    }

    $_SESSION['form_message'] = 'Basic details saved successfully!';
    echo "<script>
        alert('Basic details saved successfully!');
        window.location.href='$redirect';
    </script>";
    exit();

} catch (Exception $e) {
    $_SESSION['form_message'] = 'Error updating details. Please try again.';
    header("Location: basicinfoform.html");
    exit();
}
?>