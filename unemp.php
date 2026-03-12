<?php
session_start();
include("db_connect.php");
// Check if user logged in
if(!isset($_SESSION['user_id'])){
    echo "<script>
    alert('User not logged in');
    window.location.href='signin.html';
    </script>";
    exit();
}
$user_id = $_SESSION['user_id'];

/* --------------------------
GET FORM VALUES
---------------------------*/

$income_source = isset($_POST['source']) ? $_POST['source'] : '';

$budget = isset($_POST['budget']) ? floatval($_POST['budget']) : 0;

$food_expense = isset($_POST['food_expense']) ? floatval($_POST['food_expense']) : 0;

$transport_expense = isset($_POST['transport_expense']) ? floatval($_POST['transport_expense']) : 0;

$internet_expense = isset($_POST['internet_expense']) ? floatval($_POST['internet_expense']) : 0;

$learning_expense = isset($_POST['learning_expense']) ? floatval($_POST['learning_expense']) : 0;

$other_expense = isset($_POST['other_expense']) ? floatval($_POST['other_expense']) : 0;

$saving = isset($_POST['saving']) ? floatval($_POST['saving']) : 0;

$goal = isset($_POST['goal']) ? $_POST['goal'] : '';
 //date and <month></month>
 $current_month = date('n'); 
 $current_year = date('Y');
/*check if already submitted*/
 $check = mysqli_query($conn, "SELECT * FROM goals WHERE user_id='$user_id' AND start_month='$current_month' AND start_year='$current_year'");
if(mysqli_num_rows($check) > 0){
    echo "<script>
        alert('You have already submitted your details for this month. Use Edit option in dashboard to modify.');
        window.location.href='dashboard.html';
        </script>";
    exit();
}
/* --------------------------
CALCULATIONS
---------------------------*/

$total_expense = $food_expense + $transport_expense + $internet_expense + $learning_expense + $other_expense;

$calculated_saving = $budget - $total_expense;

/* --------------------------
CHECK SAVINGS
---------------------------*/

if(abs($saving - $calculated_saving) > 0.01){
    echo "<script>
    alert('Your saving amount does not match the calculated value.');
    window.history.back();
    </script>";
    exit();
}

/* --------------------------
STORE DATA
---------------------------*/

mysqli_query($conn,"
INSERT INTO unemployed_details
(user_id,income_source,budget,food_expense,transport_expense,internet_expense,learning_expense,other_expense,saving,goal)
VALUES
('$user_id','$income_source','$budget','$food_expense','$transport_expense','$internet_expense','$learning_expense','$other_expense','$calculated_saving','$goal')
");

/* --------------------------
SUCCESS MESSAGE
---------------------------*/

echo "<script>
alert('Details saved successfully!');
window.location.href='dashboard.html';
</script>";
?>