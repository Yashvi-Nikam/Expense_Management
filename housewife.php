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

$fullName = isset($_POST['fullName']) ? $_POST['fullName'] : '';

$monthlyBudget = isset($_POST['monthlyBudget']) ? floatval($_POST['monthlyBudget']) : 0;

$extra_budget = isset($_POST['extra_Budget']) ? floatval($_POST['extra_Budget']) : 0;

$incomeSource = isset($_POST['incomeSource']) ? $_POST['incomeSource'] : '';

$groceries = isset($_POST['groceries']) ? floatval($_POST['groceries']) : 0;

$utilities = isset($_POST['utilities']) ? floatval($_POST['utilities']) : 0;

$education = isset($_POST['education']) ? floatval($_POST['education']) : 0;

$transportation = isset($_POST['transportation']) ? floatval($_POST['transportation']) : 0;

$shopping = isset($_POST['shopping']) ? floatval($_POST['shopping']) : 0;

$otherExpenses = isset($_POST['otherExpenses']) ? floatval($_POST['otherExpenses']) : 0;

$saving_goal_amount = isset($_POST['monthlySavings']) ? floatval($_POST['monthlySavings']) : 0;

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

$total_income = $monthlyBudget + $extra_budget;

$total_expense =
$groceries +
$utilities +
$education +
$transportation +
$shopping +
$otherExpenses;

$calculated_monthly_saving = $total_income - $total_expense;


/* --------------------------
STORE OCCUPATION DETAILS
---------------------------*/

$fields = [

'full_name' => $fullName,

'monthly_budget' => $monthlyBudget,
'extra_budget' => $extra_budget,

'income_source' => $incomeSource,

'groceries_expense' => $groceries,
'utilities_expense' => $utilities,
'education_expense' => $education,
'transportation_expense' => $transportation,
'shopping_expense' => $shopping,
'other_expense' => $otherExpenses,

'monthly_saving' => $calculated_monthly_saving,
'goal_amount' => $saving_goal_amount,
'goal' => $goal

];

foreach($fields as $name => $value){

    if(is_numeric($value)){
        $value = floatval($value);

        mysqli_query($conn,
        "INSERT INTO occupation_details (user_id, field_name, field_value)
        VALUES ('$user_id', '$name', '$value')");

    } else {
        $value = mysqli_real_escape_string($conn, $value);

        mysqli_query($conn,
        "INSERT INTO occupation_details (user_id, field_name, field_text)
        VALUES ('$user_id', '$name', '$value')");
    }
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
alert('Household details saved successfully!');
window.location.href='dashboard.php';
</script>";

?>