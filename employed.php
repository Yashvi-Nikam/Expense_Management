<?php

session_start();
include("db_connect.php");

/* --------------------------
CHECK LOGIN
---------------------------*/

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

$profession = isset($_POST['business']) ? $_POST['business'] : '';

$income = isset($_POST['income']) ? floatval($_POST['income']) : 0;

$other_income = isset($_POST['other_income']) ? floatval($_POST['other_income']) : 0;

$businessExpenses = isset($_POST['businessExpenses']) ? floatval($_POST['businessExpenses']) : 0;

$rent = isset($_POST['rent']) ? floatval($_POST['rent']) : 0;

$materials = isset($_POST['materials']) ? floatval($_POST['materials']) : 0;

$utilities = isset($_POST['utilities']) ? floatval($_POST['utilities']) : 0;

$personalExpenses = isset($_POST['personalExpenses']) ? floatval($_POST['personalExpenses']) : 0;

$otherExpenses = isset($_POST['otherExpenses']) ? floatval($_POST['otherExpenses']) : 0;

$saving_goal_amount = isset($_POST['savings']) ? floatval($_POST['savings']) : 0;

$goal = isset($_POST['savingGoal']) ? $_POST['savingGoal'] : '';


/* --------------------------
CURRENT MONTH & YEAR
---------------------------*/

$current_month = date('n');
$current_year = date('Y');


/* --------------------------
CHECK DUPLICATE SUBMISSION
---------------------------*/

$check = mysqli_query($conn,
"SELECT * FROM goals
WHERE user_id='$user_id'
AND start_month='$current_month'
AND start_year='$current_year'");

if(mysqli_num_rows($check) > 0){

echo "<script>
alert('You have already submitted details for this month.');
window.location.href='dashboard.php';
</script>";

exit();

}


/* --------------------------
CALCULATIONS
---------------------------*/

$total_income = $income + $other_income;

$total_expense =
$businessExpenses +
$rent +
$materials +
$utilities +
$personalExpenses +
$otherExpenses;

$calculated_monthly_saving = $total_income - $total_expense;


/* --------------------------
STORE OCCUPATION DETAILS
---------------------------*/

$fields = [

'profession' => $profession,

'income' => $income,
'other_income' => $other_income,

'business_expense' => $businessExpenses,
'rent_expense' => $rent,
'materials_expense' => $materials,
'utilities_expense' => $utilities,

'personal_expense' => $personalExpenses,
'other_expense' => $otherExpenses,

'monthly_saving' => $calculated_monthly_saving,
'goal_amount' => $saving_goal_amount,
'goal' => $goal

];

foreach($fields as $name => $value){

if(is_numeric($value)){
$value_str = floatval($value);
}else{
$value_str = mysqli_real_escape_string($conn,$value);
}

mysqli_query($conn,
"INSERT INTO occupation_details (user_id,field_name,field_value)
VALUES ('$user_id','$name','$value_str')");

}


/* --------------------------
STORE INCOME
---------------------------*/

mysqli_query($conn,
"INSERT INTO income (user_id,amount)
VALUES ('$user_id','$total_income')");


/* --------------------------
STORE EXPENSE
---------------------------*/

mysqli_query($conn,
"INSERT INTO expenses (user_id,amount)
VALUES ('$user_id','$total_expense')");

/* --------------------------
STORE GOAL DETAILS
---------------------------*/

mysqli_query($conn,
"INSERT INTO goals
(user_id, savings_amount, goal_amount, goal_purpose, start_month, start_year)
VALUES
('$user_id', '$calculated_monthly_saving', '$saving_goal_amount', '$goal', '$current_month', '$current_year')");
/* --------------------------
SUCCESS MESSAGE
---------------------------*/

echo "<script>
alert('Employed details saved successfully!');
window.location.href='dashboard.php';
</script>";

?>