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

$name = isset($_POST['name']) ? $_POST['name'] : '';

$income_source = isset($_POST['source']) ? $_POST['source'] : '';

$budget = isset($_POST['budget']) ? floatval($_POST['budget']) : 0;

$extra_budget = isset($_POST['extra_budget']) ? floatval($_POST['extra_budget']) : 0;

$food_expense = isset($_POST['food_expense']) ? floatval($_POST['food_expense']) : 0;

$transport_expense = isset($_POST['transport_expense']) ? floatval($_POST['transport_expense']) : 0;

$internet_expense = isset($_POST['internet_expense']) ? floatval($_POST['internet_expense']) : 0;

$learning_expense = isset($_POST['learning_expense']) ? floatval($_POST['learning_expense']) : 0;

$other_expense = isset($_POST['other_expense']) ? floatval($_POST['other_expense']) : 0;

$saving_goal_amount = isset($_POST['saving']) ? floatval($_POST['saving']) : 0;

$goal = isset($_POST['goal']) ? $_POST['goal'] : '';


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

$total_income = $budget + $extra_budget;

$total_expense =
$food_expense +
$transport_expense +
$internet_expense +
$learning_expense +
$other_expense;

$calculated_saving = $total_income - $total_expense;


/* --------------------------
STORE OCCUPATION DETAILS
---------------------------*/

$fields = [

'name' => $name,
'income_source' => $income_source,

'budget' => $budget,
'extra_budget' => $extra_budget,

'food_expense' => $food_expense,
'transport_expense' => $transport_expense,
'internet_expense' => $internet_expense,
'learning_expense' => $learning_expense,
'other_expense' => $other_expense,

'monthly_saving' => $calculated_saving,
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
('$user_id', '$calculated_saving', '$saving_goal_amount', '$goal', '$current_month', '$current_year')");


/* --------------------------
SUCCESS MESSAGE
---------------------------*/

echo "<script>
alert('Unemployed details saved successfully!');
window.location.href='dashboard.php';
</script>";

?>