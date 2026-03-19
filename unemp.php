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
CHECK IF UPDATE
---------------------------*/

$is_update = isset($_POST['is_update']) && $_POST['is_update'] == '1';


/* --------------------------
GET FORM VALUES
---------------------------*/

$name = isset($_POST['name']) ? $_POST['name'] : '';

$income_source = isset($_POST['source']) ? $_POST['source'] : '';

$budget = isset($_POST['income']) ? floatval($_POST['income']) : 0;

$extra_budget = isset($_POST['extra_income']) ? floatval($_POST['extra_income']) : 0;

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
CHECK DUPLICATE SUBMISSION (only for new submissions)
---------------------------*/

if (!$is_update) {
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
}


/* --------------------------
CALCULATIONS
---------------------------*/

$total_income = $income + $extra_income;

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

'budget' => $income,
'extra_budget' => $extra_income,

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
        $value = floatval($value);
        $updateQuery = "UPDATE occupation_details SET field_value='$value' WHERE user_id='$user_id' AND field_name='$name'";
        mysqli_query($conn, $updateQuery);
        if (mysqli_affected_rows($conn) == 0) {
            mysqli_query($conn, "INSERT INTO occupation_details (user_id, field_name, field_value) VALUES ('$user_id', '$name', '$value')");
        }
    } else {
        $value = mysqli_real_escape_string($conn, $value);
        $updateQuery = "UPDATE occupation_details SET field_text='$value' WHERE user_id='$user_id' AND field_name='$name'";
        mysqli_query($conn, $updateQuery);
        if (mysqli_affected_rows($conn) == 0) {
            mysqli_query($conn, "INSERT INTO occupation_details (user_id, field_name, field_text) VALUES ('$user_id', '$name', '$value')");
        }
    }
}


/* --------------------------
STORE INCOME
---------------------------*/
if ($is_update) {
    mysqli_query($conn, "UPDATE income SET amount='$total_income' WHERE user_id='$user_id' AND MONTH(created_at)='$current_month' AND YEAR(created_at)='$current_year'");
} else {
    mysqli_query($conn, "INSERT INTO income (user_id, amount) VALUES ('$user_id', '$total_income')");
}


/* --------------------------
STORE EXPENSE
---------------------------*/
if ($is_update) {
    mysqli_query($conn, "UPDATE expenses SET amount='$total_expense' WHERE user_id='$user_id' AND MONTH(created_at)='$current_month' AND YEAR(created_at)='$current_year'");
} else {
    mysqli_query($conn, "INSERT INTO expenses (user_id, amount) VALUES ('$user_id', '$total_expense')");
}


/* --------------------------
STORE GOAL DETAILS
---------------------------*/
if ($is_update) {
    mysqli_query($conn, "UPDATE goals SET goal_purpose='$goal', goal_amount='$saving_goal_amount', savings_amount='$calculated_saving' WHERE user_id='$user_id' AND start_month='$current_month' AND start_year='$current_year'");
} else {
    mysqli_query($conn, "INSERT INTO goals (user_id, goal_purpose, goal_amount, savings_amount, start_month, start_year) VALUES ('$user_id', '$goal', '$saving_goal_amount', '$calculated_saving', '$current_month', '$current_year')");
}


/* --------------------------
SUCCESS MESSAGE
---------------------------*/

$message = $is_update ? 'Unemployed details updated successfully!' : 'Unemployed details saved successfully!';
echo "<script>
alert('$message');
window.location.href='dashboard.php';
</script>";

?>