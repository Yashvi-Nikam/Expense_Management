<?php

session_start();
include("db_connect.php");

/* --------------------------
CHECK LOGIN
---------------------------*/

if(!isset($_SESSION['user_id'])){
    echo "<script>alert('User not logged in.'); window.location.href='signin.html';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];


/* --------------------------
GET FORM VALUES
---------------------------*/

$name = isset($_POST['name']) ? $_POST['name'] : '';

$profession = isset($_POST['business']) ? $_POST['business'] : '';

$income = isset($_POST['income']) ? floatval($_POST['income']) : 0;

$extra_income = isset($_POST['extra_income']) ? floatval($_POST['extra_income']) : 0;

$rent_expense = isset($_POST['rent_expense']) ? floatval($_POST['rent_expense']) : 0;
$materials_expense = isset($_POST['materials_expense']) ? floatval($_POST['materials_expense']) : 0;
$salary_expense = isset($_POST['salary_expense']) ? floatval($_POST['salary_expense']) : 0;
$utilities_expense = isset($_POST['utilities_expense']) ? floatval($_POST['utilities_expense']) : 0;

$food_expense = isset($_POST['food_expense']) ? floatval($_POST['food_expense']) : 0;
$transport_expense = isset($_POST['transport_expense']) ? floatval($_POST['transport_expense']) : 0;
$other_expense = isset($_POST['other_expense']) ? floatval($_POST['other_expense']) : 0;

$goal_saving = isset($_POST['saving']) ? floatval($_POST['saving']) : 0;

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

$total_income = $income + $extra_income;

$total_expense =
$rent_expense +
$materials_expense +
$salary_expense +
$utilities_expense +
$food_expense +
$transport_expense +
$other_expense;

$monthly_saving = $total_income - $total_expense;


/* --------------------------
STORE OCCUPATION DETAILS
---------------------------*/

$fields = [

'name' => $name,
'profession' => $profession,

'income' => $income,
'extra_income' => $extra_income,

'rent_expense' => $rent_expense,
'materials_expense' => $materials_expense,
'salary_expense' => $salary_expense,
'utilities_expense' => $utilities_expense,

'food_expense' => $food_expense,
'transport_expense' => $transport_expense,
'other_expense' => $other_expense,

'monthly_saving' => $monthly_saving,
'goal_amount' => $goal_saving,
'goal' => $goal

];

foreach($fields as $field_name => $field_value){

    if(is_numeric($field_value)){
        $value_str = floatval($field_value);
    } else {
        $value_str = mysqli_real_escape_string($conn,$field_value);
    }

    mysqli_query($conn,
    "INSERT INTO occupation_details (user_id, field_name, field_value)
    VALUES ('$user_id','$field_name','$value_str')");
}


/* --------------------------
STORE TOTAL INCOME
---------------------------*/

mysqli_query($conn,
"INSERT INTO income (user_id, amount)
VALUES ('$user_id','$total_income')");


/* --------------------------
STORE TOTAL EXPENSE
---------------------------*/

mysqli_query($conn,
"INSERT INTO expenses (user_id, amount)
VALUES ('$user_id','$total_expense')");


/* --------------------------
STORE GOAL
---------------------------*/

mysqli_query($conn,
"INSERT INTO goals
(user_id, goal_purpose, goal_amount, savings_amount, start_month, start_year)
VALUES
('$user_id','$goal','$goal_saving','$monthly_saving','$current_month','$current_year')");


/* --------------------------
SUCCESS MESSAGE
---------------------------*/

echo "<script>
alert('Self-employed details saved successfully!');
window.location.href='dashboard.php';
</script>";

?>